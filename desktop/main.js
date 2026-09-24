const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const fs = require('fs');
const http = require('http');
const dgram = require('dgram');
const treeKill = require('tree-kill'); // npm install tree-kill

// Conditional import for Auto Updater to prevent Dev crashes
let autoUpdater = null;
if (app && app.isPackaged) {
    try {
        const updaterModule = require("electron-updater");
        autoUpdater = updaterModule.autoUpdater;
        // Disable automatic download and install - user must confirm
        autoUpdater.autoDownload = false;
        autoUpdater.autoInstallOnAppQuit = false;
    } catch (e) { console.log('Updater module missing or failed'); }
}

// --- Configuration ---
const PHP_PORT = 0;
let DB_PORT = 3307; // Changed to let to allow dynamic assignment
const DB_NAME = 'sgen_db';
let APP_MODE = 'server'; // 'server' or 'client'
let REMOTE_HOST = '127.0.0.1';
const DISCOVERY_PORT = 5555;
let discoveryServer = null;
let discoveryClient = null;

const DB_USER_SGEN = 'sgen_admin';
const DB_PASS_SGEN = 'SgenSupport2026!'; // Enterprise-grade password

const USER_DATA_PATH = app.getPath('userData');
const LOG_FILE = path.join(USER_DATA_PATH, 'debug.log');

function log(msg) {
    const timestamp = new Date().toISOString();
    const formattedMsg = `[${timestamp}] [Main] ${msg}`;
    console.log(formattedMsg);
    try {
        fs.appendFileSync(LOG_FILE, formattedMsg + '\n');
    } catch (e) { /* ignore log write errors */ }
}

// Helper: Check if a port is available (Refined for server environments)
async function findAvailablePort(startPort) {
    const net = require('net');
    const checkPort = (port) => new Promise(resolve => {
        const server = net.createServer();
        server.once('error', (err) => resolve(false)); // Any error means port is not available
        server.once('listening', () => {
            server.close();
            resolve(true);
        });
        try {
            server.listen(port, '127.0.0.1');
        } catch (e) {
            resolve(false);
        }
    });

    let port = startPort;
    log(`Scanning ports starting from ${startPort}...`);
    while (port < startPort + 200) { // Increased range for busy servers
        const available = await checkPort(port);
        if (available) return port;
        log(`Port ${port} is occupied or restricted, trying next...`);
        port++;
    }
    throw new Error('No se han encontrado puertos libres en el rango 3307-3507. Verifique los permisos del servidor.');
}


let mainWindow;
let phpProcess;
let dbProcess;
let phpUrl;
let isQuitting = false;

// Paths to binaries
const isDev = !app.isPackaged;
const resourcesPath = isDev ? __dirname : process.resourcesPath;

const BIN_PATH = path.join(resourcesPath, 'bin');
const PHP_EXE = path.join(BIN_PATH, 'php', 'php.exe');
const MYSQL_EXE = path.join(BIN_PATH, 'mariadb', 'bin', 'mysqld.exe');
const MYSQL_INIT_EXE = path.join(BIN_PATH, 'mariadb', 'bin', 'mysql_install_db.exe');
const PUBLIC_ROOT = path.resolve(resourcesPath, isDev ? '../public' : 'public');

// Database storage setup
const DB_DATA_DIR = path.join(USER_DATA_PATH, 'mysql_data_v2');
const LEGACY_DB_DATA_DIR = path.join(USER_DATA_PATH, 'mysql_data');

// Migration: If new data dir doesn't exist but old one does, RENAME it
if (fs.existsSync(LEGACY_DB_DATA_DIR)) {
    if (!fs.existsSync(DB_DATA_DIR)) {
        log('MIGRATION: Found legacy mysql_data. Renaming to mysql_data_v2...');
        try {
            fs.renameSync(LEGACY_DB_DATA_DIR, DB_DATA_DIR);
            log('MIGRATION: Success.');
        } catch (e) {
            log('MIGRATION: Failed to rename: ' + e.message);
        }
    } else {
        // Both exist! This means 1.0.19 failed to migrate but created a new v2
        log('MIGRATION: Both legacy and v2 exist. Favoring legacy data...');
        try {
            const backupPath = DB_DATA_DIR + '_empty_' + Date.now();
            fs.renameSync(DB_DATA_DIR, backupPath);
            fs.renameSync(LEGACY_DB_DATA_DIR, DB_DATA_DIR);
            log('MIGRATION: Success. Legacy data restored, empty v2 backed up to ' + path.basename(backupPath));
        } catch (e) {
            log('MIGRATION: Failed to swap: ' + e.message);
        }
    }
}
function waitForDatabaseReady(port, maxAttempts = 30, interval = 500) {
    const net = require('net');
    return new Promise((resolve, reject) => {
        let attempts = 0;

        const tryConnect = () => {
            attempts++;
            const socket = new net.Socket();

            socket.setTimeout(1000);

            socket.on('connect', () => {
                log(`Database connection verified on attempt ${attempts}`);
                socket.destroy();
                resolve();
            });

            socket.on('error', (err) => {
                socket.destroy();
                if (attempts < maxAttempts) {
                    setTimeout(tryConnect, interval);
                } else {
                    reject(new Error(`Database not ready after ${maxAttempts} attempts`));
                }
            });

            socket.on('timeout', () => {
                socket.destroy();
                if (attempts < maxAttempts) {
                    setTimeout(tryConnect, interval);
                } else {
                    reject(new Error(`Database connection timeout after ${maxAttempts} attempts`));
                }
            });

            socket.connect(port, '127.0.0.1');
        };

        tryConnect();
    });
}

// 1. Initialize Database
async function startDatabase() {
    log('Starting Database...');
    log(`MYSQL_EXE: ${MYSQL_EXE}`);
    log(`DB_DATA_DIR: ${DB_DATA_DIR}`);

    if (!fs.existsSync(DB_DATA_DIR)) {
        fs.mkdirSync(DB_DATA_DIR, { recursive: true });
        log(`Created DB Data Dir: ${DB_DATA_DIR}`);

        try {
            log('Initializing MariaDB Data...');
            require('child_process').execFileSync(MYSQL_INIT_EXE, [`--datadir=${DB_DATA_DIR}`]);
            log('MariaDB Initialized.');
        } catch (e) { log('Init Error: ' + e); }
    }

    // Clean up potential leftover .pid file if process is not running
    const pidFile = path.join(DB_DATA_DIR, `${require('os').hostname()}.pid`);
    if (fs.existsSync(pidFile)) {
        log(`Found leftover PID file at ${pidFile}, removing...`);
        try { fs.unlinkSync(pidFile); } catch (e) { }
    }

    const args = [
        '--no-defaults',
        '--console',
        `--port=${DB_PORT}`,
        `--datadir=${DB_DATA_DIR.replace(/\\/g, '/')}`,
        '--bind-address=0.0.0.0', // Changed from 127.0.0.1 to allow LAN connections
        '--default-storage-engine=InnoDB',
        '--innodb-flush-method=normal',
        '--innodb-buffer-pool-size=32M',
        `--plugin-dir=${path.join(BIN_PATH, 'mariadb', 'lib', 'plugin').replace(/\\/g, '/')}`,
        '--general-log=0'
    ];

    log(`DB args: ${args.join(' ')}`);

    return new Promise((resolve, reject) => {
        let resolved = false;
        let dbOutput = [];

        const tryResolve = () => {
            if (!resolved) {
                resolved = true;
                log('Database Promise resolved');
                resolve();
            }
        };

        if (!fs.existsSync(MYSQL_EXE)) {
            const msg = `El archivo de la base de datos no se encuentra en: ${MYSQL_EXE}. Es posible que su antivirus lo haya eliminado o puesto en cuarentena.`;
            log(msg);
            const err = new Error(msg);
            err.antivirus = true;
            return reject(err);
        }

        dbProcess = spawn(MYSQL_EXE, args, {
            cwd: path.dirname(MYSQL_EXE),
            windowsHide: true,
            stdio: ['ignore', 'pipe', 'pipe']
        });
        log(`DB process spawned with PID: ${dbProcess.pid}`);

        const handleOutput = (data) => {
            const output = data.toString();
            console.log(`[DB] ${output}`);
            dbOutput.push(output);
            if (dbOutput.length > 50) dbOutput.shift(); // Keep last 50 lines

            // Check for various "ready" indicators from MariaDB
            if (output.includes('ready for connections') ||
                output.includes('mysqld.exe: ready') ||
                output.includes('Server socket created')) {
                log('Database ready signal detected');
                tryResolve();
            }
        };

        dbProcess.stdout.on('data', handleOutput);
        dbProcess.stderr.on('data', handleOutput);

        dbProcess.on('error', (err) => {
            log(`DB spawn error: ${err.message}`);
            if (err.code === 'EACCES') {
                err.message = "Acceso denegado al iniciar la base de datos. Esto suele ser causado por un antivirus bloqueando la ejecución.";
                err.antivirus = true;
            }
            err.dbLogs = dbOutput.join('\n');
            reject(err);
        });

        dbProcess.on('exit', (code) => {
            log(`DB process exited with code: ${code}`);
            if (!resolved) {
                const err = new Error(`Database exited prematurely with code ${code}`);
                err.dbLogs = dbOutput.join('\n');
                reject(err);
            }
        });

        // Fallback timeout - assume ready after 8 seconds
        setTimeout(() => {
            if (dbProcess && !dbProcess.killed) {
                log('Database startup timeout - assuming ready (process still running)');
                tryResolve();
            } else if (!resolved) {
                log('Database startup timeout - process not running!');
                const err = new Error('Database process not running after timeout');
                err.dbLogs = dbOutput.join('\n');
                reject(err);
            }
        }, 8000);
    });
}


// 2. Start PHP Server
async function startPhpServer() {
    log('Starting PHP Server...');
    log(`PHP_EXE: ${PHP_EXE}`);
    log(`PUBLIC_ROOT: ${PUBLIC_ROOT}`);
    log(`PHP exists: ${fs.existsSync(PHP_EXE)}`);

    // Get app version from package.json
    const pkg = require('./package.json');

    const env = Object.assign({}, process.env, {
        DB_HOST: APP_MODE === 'server' ? '127.0.0.1' : REMOTE_HOST,
        DB_PORT: DB_PORT.toString(),
        DB_NAME: DB_NAME,
        DB_USER: DB_USER_SGEN,
        DB_PASS: DB_PASS_SGEN,
        PHPRC: path.join(BIN_PATH, 'php'),
        APP_VERSION: pkg.version,
        RESOURCES_PATH: resourcesPath,
        APP_MODE: APP_MODE
    });

    return new Promise((resolve, reject) => {
        let resolved = false;
        const tryResolve = (url) => {
            if (!resolved) {
                resolved = true;
                resolve(url);
            }
        };

        if (!fs.existsSync(PHP_EXE)) {
            const msg = `El servidor PHP no se encuentra en: ${PHP_EXE}. Verifique si su antivirus lo ha bloqueado.`;
            log(msg);
            const err = new Error(msg);
            err.antivirus = true;
            return reject(err);
        }

        const routerPath = path.join(PUBLIC_ROOT, 'router.php');
        log(`Router path: ${routerPath}`);
        phpProcess = spawn(PHP_EXE, ['-S', '127.0.0.1:0', routerPath], {
            env,
            cwd: PUBLIC_ROOT,
            windowsHide: true // Added for extra antivirus stealth
        });

        phpProcess.stdout.on('data', (data) => {
            console.log(`[PHP stdout] ${data.toString()}`);
        });

        phpProcess.stderr.on('data', (data) => {
            const output = data.toString();
            console.log(`[PHP] ${output}`);
            // PHP 8.x format: "Development Server (http://127.0.0.1:PORT) started"
            // PHP 7.x format: "Listening on http://127.0.0.1:PORT"
            let match = output.match(/Development Server \(http:\/\/127\.0\.0\.1:(\d+)\)/);
            if (!match) {
                match = output.match(/Listening on http:\/\/127\.0\.0\.1:(\d+)/);
            }
            if (match) {
                const port = match[1];
                phpUrl = `http://127.0.0.1:${port}`;
                log(`PHP Server ready at ${phpUrl}`);
                tryResolve(phpUrl);
            }
        });

        phpProcess.on('error', (err) => {
            log(`PHP spawn error: ${err.message}`);
            if (err.code === 'EACCES') {
                err.message = "Acceso denegado al iniciar el servidor PHP. Verifique las exclusiones de su antivirus.";
                err.antivirus = true;
            }
            reject(err);
        });

        phpProcess.on('exit', (code) => {
            log(`PHP process exited with code: ${code}`);
        });

        // Fallback timeout - 10 seconds
        setTimeout(() => {
            log('PHP startup timeout - check if PHP binary exists');
            if (!resolved) {
                reject(new Error('PHP server failed to start within 10 seconds'));
            }
        }, 10000);
    });
}

// 2.5 Run Migrations
async function runMigrations() {
    log('Running Database Migrations...');

    // In Dev: migrations are in ../migrations relative to main.js (desktop/main.js)
    // In Prod: migrations are in resources/migrations (copied via extraResources)
    const migrationScript = isDev
        ? path.join(__dirname, '../migrations/run_migration.php')
        : path.join(process.resourcesPath, 'migrations', 'run_migration.php');

    log(`Checking migration script at: ${migrationScript}`);

    if (!fs.existsSync(migrationScript)) {
        log(`Migration script not found!`);
        return;
    }

    return new Promise((resolve, reject) => {
        // CRITICAL: Pass DB environment to the migration process
        const migrationEnv = Object.assign({}, process.env, {
            DB_HOST: APP_MODE === 'server' ? '127.0.0.1' : REMOTE_HOST,
            DB_PORT: DB_PORT.toString(),
            DB_NAME: DB_NAME,
            DB_USER: DB_USER_SGEN,
            DB_PASS: DB_PASS_SGEN,
            PHPRC: path.join(BIN_PATH, 'php'),
            RESOURCES_PATH: resourcesPath,
            APP_MODE: APP_MODE
        });

        const migrationProcess = spawn(PHP_EXE, [migrationScript], {
            cwd: path.dirname(migrationScript),
            env: migrationEnv,
            windowsHide: true
        });

        migrationProcess.stdout.on('data', (data) => {
            log(`[Migration] ${data.toString().trim()}`);
        });

        migrationProcess.stderr.on('data', (data) => {
            console.error(`[Migration Error] ${data.toString().trim()}`);
        });

        migrationProcess.on('close', (code) => {
            log(`Migration process exited with code ${code}`);
            resolve();
        });

        migrationProcess.on('error', (err) => {
            log(`Failed to start migration process: ${err.message}`);
            // Don't block app startup if migrations fail, but log it
            resolve();
        });
    });
}

// 3. Create Window
function createWindow() {
    const { shell } = require('electron');

    mainWindow = new BrowserWindow({
        width: 1300,
        height: 900,
        title: "SGEN Support",
        icon: path.join(__dirname, '../public/favicon.ico'),
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            nodeIntegration: false,
            contextIsolation: true
        }
    });

    if (phpUrl) {
        mainWindow.loadURL(phpUrl);
    } else {
        mainWindow.loadFile(path.join(__dirname, 'splash.html'));
    }

    mainWindow.setMenuBarVisibility(false);

    // Handle target="_blank" links (like PDF downloads)
    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        log(`Window open request: ${url}`);

        // If it's a PDF URL from our app, open in a new window
        if (url.includes('/pdf/') || url.includes('/pdf?') || url.endsWith('.pdf')) {
            // Create a new window for PDF preview
            const pdfWindow = new BrowserWindow({
                width: 900,
                height: 700,
                title: 'Vista Previa PDF',
                parent: mainWindow,
                webPreferences: {
                    nodeIntegration: false,
                    contextIsolation: true
                }
            });
            pdfWindow.setMenuBarVisibility(false);
            pdfWindow.loadURL(url);
            return { action: 'deny' }; // We handled it ourselves
        }

        // For external URLs, open in system browser
        if (url.startsWith('http://') || url.startsWith('https://')) {
            if (!url.includes('127.0.0.1') && !url.includes('localhost')) {
                shell.openExternal(url);
                return { action: 'deny' };
            }
        }

        // Allow other windows to open
        return { action: 'allow' };
    });

    // Handle file downloads (PDFs, etc.)
    mainWindow.webContents.session.on('will-download', (event, item, webContents) => {
        const filename = item.getFilename();
        log(`Download requested: ${filename}`);

        // Let the user choose where to save, or auto-save to Downloads
        const downloadPath = path.join(app.getPath('downloads'), filename);
        item.setSavePath(downloadPath);

        item.on('done', (event, state) => {
            if (state === 'completed') {
                log(`Download completed: ${downloadPath}`);
                // Optionally open the file after download
                shell.openPath(downloadPath);
            } else {
                log(`Download failed: ${state}`);
            }
        });
    });

    // Intercept Close Event
    mainWindow.on('close', (e) => {
        // If we are in Splash/Wizard mode (file:// protocol) or forcing quit, allow close immediately
        const currentUrl = mainWindow.webContents.getURL();
        const isSplash = currentUrl.startsWith('file:') || currentUrl.includes('splash.html');

        if (!isQuitting && !isSplash) {
            e.preventDefault();
            mainWindow.webContents.send('show-exit-confirm');
        }
        // If it IS splash, we let it close naturally, which triggers 'closed' event -> app.quit()
    });

    // Check for updates (but don't download automatically)
    if (autoUpdater) autoUpdater.checkForUpdates();
}

// --- Process Lifecycle Management ---
const isSimulation = process.argv.includes('--simulation');
const PID_FILE = path.join(USER_DATA_PATH, isSimulation ? 'running_pids_client.json' : 'running_pids.json');

// Helper: Kill a process by PID using tree-kill (kills entire process tree)
function killProcess(pid, callback) {
    if (!pid) return;
    try {
        log(`Killing process tree for PID: ${pid}`);
        treeKill(pid, 'SIGKILL', (err) => {
            if (err) {
                log(`treeKill error (attempting fallback): ${err.message}`);
                // Fallback to taskkill
                try {
                    require('child_process').spawnSync('taskkill', ['/pid', String(pid), '/f', '/t']);
                } catch (e) { }
            } else {
                log(`Successfully killed process tree for PID: ${pid}`);
            }
            if (callback) callback();
        });
    } catch (e) {
        log(`killProcess error: ${e.message}`);
        // Ignore errors (process might not exist)
    }
}

// Synchronous version for exit handlers (blocks until processes are killed)
function killProcessSync(pid) {
    if (!pid) return;
    try {
        log(`Sync killing process tree for PID: ${pid}`);
        if (process.platform === 'win32') {
            // Use taskkill synchronously - /T kills child processes, /F forces
            require('child_process').execSync(`taskkill /pid ${pid} /f /t`, {
                stdio: 'ignore',
                timeout: 5000
            });
        } else {
            process.kill(pid, 'SIGKILL');
        }
        log(`Sync killed process PID: ${pid}`);
    } catch (e) {
        // Ignore errors (process might already be gone)
        log(`killProcessSync error (expected if already dead): ${e.message}`);
    }
}

// AGGRESSIVE cleanup - kill ALL mysqld.exe and php.exe from our bin path
function forceKillAllOurProcesses() {
    log('=== FORCE KILLING ALL OUR PROCESSES ===');
    const targets = ['mysqld.exe', 'php.exe'];

    for (const target of targets) {
        try {
            // First, kill by PID if we have it
            if (target === 'mysqld.exe' && dbProcess && dbProcess.pid) {
                log(`Killing DB by PID: ${dbProcess.pid}`);
                require('child_process').execSync(`taskkill /pid ${dbProcess.pid} /f /t`, { stdio: 'ignore', timeout: 5000 });
            }
            if (target === 'php.exe' && phpProcess && phpProcess.pid) {
                log(`Killing PHP by PID: ${phpProcess.pid}`);
                require('child_process').execSync(`taskkill /pid ${phpProcess.pid} /f /t`, { stdio: 'ignore', timeout: 5000 });
            }

            // Then, as a fallback, find and kill any lingering processes from our path
            const cmd = `wmic process where "name='${target}'" get ProcessId,ExecutablePath /FORMAT:CSV`;
            const output = require('child_process').execSync(cmd, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'], timeout: 5000 });

            const lines = output.trim().split('\r\n');
            const binPathLower = path.resolve(BIN_PATH).toLowerCase();

            for (const line of lines) {
                if (!line.trim() || line.startsWith('Node,')) continue;
                const parts = line.split(',');
                if (parts.length < 3) continue;

                const pid = parts.pop().trim();
                parts.shift();
                const exePath = parts.join(',').trim().toLowerCase();

                if (exePath && exePath.includes(binPathLower)) {
                    log(`Force killing lingering ${target} PID: ${pid}`);
                    try {
                        require('child_process').execSync(`taskkill /pid ${pid} /f /t`, { stdio: 'ignore', timeout: 3000 });
                    } catch (e) { /* ignore */ }
                }
            }
        } catch (e) {
            log(`Force cleanup for ${target}: ${e.message}`);
        }
    }
    log('=== FORCE KILL COMPLETE ===');
}

// 0. Cleanup Zombies from previous run
async function forceCleanupProcesses() {
    log('Performing aggressive zombie cleanup...');

    // 1. Kill any process using our DB port
    try {
        if (process.platform === 'win32') {
            log(`Checking for processes on port ${DB_PORT}...`);
            const netstatOutput = require('child_process').execSync(`netstat -ano | findstr :${DB_PORT}`, { encoding: 'utf8' }).catch(() => '');
            if (netstatOutput) {
                const lines = netstatOutput.trim().split('\n');
                for (const line of lines) {
                    const parts = line.trim().split(/\s+/);
                    const pid = parts[parts.length - 1];
                    if (pid && pid !== '0') {
                        log(`Killing process ${pid} using port ${DB_PORT}`);
                        try { require('child_process').execSync(`taskkill /pid ${pid} /f /t`, { stdio: 'ignore' }); } catch (e) { }
                    }
                }
            }
        }
    } catch (e) { }

    // 2. Kill by binary name and path using PowerShell (more robust than WMIC)
    const targets = ['mysqld.exe', 'mariadbd.exe', 'php.exe'];
    const binPathLower = path.resolve(BIN_PATH).toLowerCase();

    for (const target of targets) {
        try {
            if (process.platform === 'win32') {
                // Use PowerShell to get processes with their paths
                const psCmd = `Get-Process -Name "${target.replace('.exe', '')}" -ErrorAction SilentlyContinue | Select-Object Id, Path | ConvertTo-Json`;
                const output = require('child_process').execSync(`powershell -Command "${psCmd}"`, { encoding: 'utf8' }).trim();

                if (output) {
                    const processes = JSON.parse(output);
                    const procList = Array.isArray(processes) ? processes : [processes];

                    for (const proc of procList) {
                        if (proc.Path && proc.Path.toLowerCase().includes(binPathLower)) {
                            log(`Found zombie ${target} (PID: ${proc.Id}) at ${proc.Path}`);
                            try { require('child_process').execSync(`taskkill /pid ${proc.Id} /f /t`, { stdio: 'ignore' }); } catch (e) { }
                        }
                    }
                }
            }
        } catch (e) {
            log(`PowerShell cleanup failed for ${target} (this is normal if none found): ${e.message}`);
        }
    }
}


function cleanupZombies() {
    log('Checking for zombie processes via PID file...');
    // Legacy cleanup - still useful as a fallback
    if (fs.existsSync(PID_FILE)) {
        try {
            const pids = JSON.parse(fs.readFileSync(PID_FILE, 'utf8'));
            if (Array.isArray(pids)) {
                log(`Found ${pids.length} potential zombies in PID file. Terminating...`);
                pids.forEach(pid => killProcess(pid));
            }
            fs.unlinkSync(PID_FILE);
        } catch (e) {
            log(`Error reading PID file: ${e.message}`);
        }
    }
}

// Helper: Save PIDs to file
function savePids() {
    const pids = [];
    if (dbProcess && dbProcess.pid) pids.push(dbProcess.pid);
    if (phpProcess && phpProcess.pid) pids.push(phpProcess.pid);

    try {
        fs.writeFileSync(PID_FILE, JSON.stringify(pids), 'utf8');
    } catch (e) {
        log(`Error saving PIDs: ${e.message}`);
    }
}

// --- Multi-Node Network Helpers ---

async function checkNetworkConfig() {
    log('Checking for .env configuration...');
    // In Dev: resourcesPath is '.../desktop'. We want '.../.env' (root)
    // In Prod: resourcesPath is '.../resources'. We want '.../resources/.env' (next to app.asar)

    // Use the same path resolution logic as config:save
    const envPath = path.join(resourcesPath, isDev ? '../.env' : '.env');
    log(`Reading .env from: ${envPath}`);

    if (fs.existsSync(envPath)) {
        const envContent = fs.readFileSync(envPath, 'utf8');
        const lines = envContent.split('\n');
        let modeSet = false;
        let hostSet = false;

        for (const line of lines) {
            if (line.startsWith('APP_MODE=')) {
                APP_MODE = line.split('=')[1].trim();
                modeSet = true;
            }
            if (line.startsWith('DB_HOST=')) {
                REMOTE_HOST = line.split('=')[1].trim();
                hostSet = true;
            }
        }

        if (modeSet) {
            log(`Network Config found: Mode=${APP_MODE}, Host=${REMOTE_HOST}`);
            return true;
        }
    }

    log('.env not configured. Entering First Run Setup Mode.');
    return false; // Do not auto-create. Allow Splash Screen to handle setup.
}

async function ensureFirewallRule() {
    if (process.platform !== 'win32') return;

    log('Ensuring Windows Firewall rule for port 3307...');
    const cmd = `netsh advfirewall firewall show rule name="SGEN_Server_DB"`;

    try {
        require('child_process').execSync(cmd, { stdio: 'ignore' });
        log('Firewall rule "SGEN_Server_DB" already exists.');
    } catch (e) {
        log('Firewall rule not found. Creating...');
        const addCmd = `netsh advfirewall firewall add rule name="SGEN_Server_DB" dir=in action=allow protocol=TCP localport=3307`;
        try {
            require('child_process').execSync(addCmd);
            log('Firewall rule created successfully.');
        } catch (err) {
            log('FAILED to create firewall rule. Administrative privileges might be required: ' + err.message);
        }
    }
}

async function verifyRemoteConnectivity(host, port, retries = 0) {
    const net = require('net');

    for (let i = 0; i <= retries; i++) {
        const attempt = i + 1;
        const msg = `[Connection] Attempt ${attempt}/${retries + 1} checking ${host}:${port}...`;
        log(msg);
        if (mainWindow) mainWindow.webContents.send('update-status', `Conectando al servidor... (Intento ${attempt}/${retries + 1})`);

        try {
            await new Promise((resolve, reject) => {
                const socket = new net.Socket();
                socket.setTimeout(3000);

                socket.on('connect', () => {
                    socket.destroy();
                    resolve();
                });

                socket.on('error', (err) => {
                    socket.destroy();
                    reject(err);
                });

                socket.on('timeout', () => {
                    socket.destroy();
                    reject(new Error('Timeout'));
                });

                socket.connect(port, host);
            });
            log(`[Connection] Success to ${host}`);
            return;
        } catch (e) {
            log(`[Connection] Check failed: ${e.message}`);
            if (i < retries) {
                if (mainWindow) mainWindow.webContents.send('update-status', `Servidor no encontrado. Reintentando en 2s...`);
                await new Promise(r => setTimeout(r, 2000)); // Wait 2s between retries
            } else {
                throw new Error(`El servidor no responde. Verifique que esté encendido y conectado a la red.`);
            }
        }
    }
}
// --- Simulation Mode Check ---
if (isSimulation) {
    APP_MODE = 'client';
    REMOTE_HOST = '127.0.0.1';
    log('!!! RUNNING IN SIMULATION MODE (CLIENT) !!!');
}

const gotTheLock = isSimulation ? true : app.requestSingleInstanceLock();

if (!gotTheLock) {
    log('Another instance is already running. Quitting.');
    app.quit();
} else {
    app.on('second-instance', (event, commandLine, workingDirectory) => {
        // Someone tried to run a second instance, we should focus our window.
        if (mainWindow) {
            if (mainWindow.isMinimized()) mainWindow.restore();
            mainWindow.focus();
        }
    });

    app.whenReady().then(async () => {
        log('=== APP READY - Starting initialization ===');

        // Step 0: Create Window IMMEDIATELY to show "Loading..."
        createWindow();

        // Wait for splash screen to be fully loaded and ready to receive IPC messages
        if (mainWindow && mainWindow.webContents.isLoading()) {
            await new Promise(resolve => mainWindow.webContents.once('did-finish-load', resolve));
            // Small extra buffer to ensure JS execution
            await new Promise(r => setTimeout(r, 500));
        }

        // Step 0.1: Aggressive Cleanup
        if (!isSimulation) {
            cleanupZombies();
            await forceCleanupProcesses();
        } else {
            log('Skipping aggressive cleanup in Simulation Mode');
        }


        try {
            log('Step 0.1: Checking Network Config...');
            const hasConfig = await checkNetworkConfig(); // Now returns false if missing

            // RE-FORCE Client Mode if Simulation is active (ignoring .env)
            if (isSimulation) {
                APP_MODE = 'client';
                REMOTE_HOST = '127.0.0.1';
            }

            if (hasConfig === false && !isSimulation) {
                log('No config found. Skipping backend startup for Setup Mode.');
                // Window already created, just show wizard
                setTimeout(() => {
                    if (mainWindow) mainWindow.webContents.send('show-setup-wizard');
                }, 1000);
            } else {

                if (APP_MODE === 'server') {
                    log('Step 0.5: Finding available port...');
                    DB_PORT = await findAvailablePort(3307);
                    log(`Step 0.5 COMPLETE: Using port ${DB_PORT}`);

                    log('Step 0.6: Ensuring Firewall Rule...');
                    await ensureFirewallRule();

                    log('Step 1: Starting database process...');
                    await startDatabase();
                    savePids(); // Save immediately

                    log('Step 1 COMPLETE: Database process started');

                    log('Step 2: Verifying database is accepting connections...');
                    await waitForDatabaseReady(DB_PORT);
                    log('Step 2 COMPLETE: Database verified ready');

                    log('Step 2.1: Securing Database Users...');
                    await createSecureUser();

                    log('Step 2.2: Starting Network Discovery Service...');
                    startDiscoveryService();
                } else {
                    log('Step 1/2: Running in CLIENT mode. Verifying connection to ' + REMOTE_HOST);
                    // Perform STRICT connectivity check with RETRIES (10 retries ~40s wait)
                    await verifyRemoteConnectivity(REMOTE_HOST, DB_PORT, 10);
                    log('Connectivity OK. Starting local PHP server...');
                }

                log('Step 2.5: Running migrations...');
                await runMigrations();
                log('Step 2.5 COMPLETE: Migrations executed');

                log('Step 3: Starting PHP server...');
                await startPhpServer();
                savePids(); // Update with PHP PID
                log('Step 3 COMPLETE: PHP server started');

                log('Step 4: Loading App...');
                // Load PHP URL now that everything is ready
                if (phpUrl) {
                    mainWindow.loadURL(phpUrl);
                }
                log('Step 4 COMPLETE: App Loaded');
            }
        } catch (e) {
            console.error("STARTUP ERROR:", e);
            log(`STARTUP ERROR: ${e.message}`);

            // Don't quit! Show the error in the splash screen so user can reconfigure
            if (mainWindow) {
                // Return to splash if we were somehow navigating away (unlikely at this stage)
                // But mostly just send the error
                const send = () => {
                    mainWindow.webContents.send('startup-error', {
                        message: e.message,
                        details: e.dbLogs || '',
                        config: { host: REMOTE_HOST, mode: APP_MODE }
                    });
                };

                if (mainWindow.webContents.isLoading()) {
                    mainWindow.webContents.once('did-finish-load', send);
                } else {
                    send();
                }
            } else {
                // Fallback if window somehow died, we assume it's already created by now
                // and we just need to send the error. If it truly died, the app will likely crash.
                // No need to call createWindow() again.
                const send = () => {
                    if (mainWindow) { // Check if it somehow got created or exists
                        mainWindow.webContents.send('startup-error', {
                            message: e.message,
                            details: e.dbLogs || ''
                        });
                    }
                };

                if (mainWindow && mainWindow.webContents.isLoading()) {
                    mainWindow.webContents.once('did-finish-load', send);
                } else {
                    send();
                }
            }
        }

        app.on('activate', function () {
            if (BrowserWindow.getAllWindows().length === 0) createWindow();
        });
    });
}


app.on('window-all-closed', function () {
    if (process.platform !== 'darwin') app.quit();
});

app.on('before-quit', () => {
    log('before-quit: Stopping background processes...');
    isQuitting = true;
    // Use aggressive kill to ensure all our processes die
    forceKillAllOurProcesses();

    // Clean up PID file
    try {
        if (fs.existsSync(PID_FILE)) fs.unlinkSync(PID_FILE);
    } catch (e) { }
});

// Additional safety: will-quit is the last event before app quits
app.on('will-quit', () => {
    log('will-quit: Final cleanup...');
    // Final aggressive cleanup
    forceKillAllOurProcesses();
});

if (autoUpdater) {
    autoUpdater.on('update-available', (info) => {
        log(`Update available: ${info.version}`);
        if (mainWindow && !mainWindow.isDestroyed()) mainWindow.webContents.send('update_available', info);
    });
    autoUpdater.on('update-not-available', (info) => {
        log(`No update available. Current version is up to date.`);
    });
    autoUpdater.on('error', (err) => {
        log(`Update error: ${err.message}`);
    });
    autoUpdater.on('update-downloaded', (info) => {
        log(`Update downloaded: ${info.version}`);
        if (mainWindow && !mainWindow.isDestroyed()) mainWindow.webContents.send('update_downloaded', info);
    });
    autoUpdater.on('download-progress', (progressObj) => {
        if (mainWindow && !mainWindow.isDestroyed()) mainWindow.webContents.send('update_progress', progressObj);
    });
}


// --- IPC Handlers ---

ipcMain.on('exit-app', () => {
    log('exit-app IPC: User confirmed exit');
    isQuitting = true;
    // Aggressive kill to ensure all our processes die
    forceKillAllOurProcesses();
    try { if (fs.existsSync(PID_FILE)) fs.unlinkSync(PID_FILE); } catch (e) { }

    app.exit(0);
});

ipcMain.on('restart_app', () => {
    log('restart_app IPC: Restarting...');
    isQuitting = true;
    // Kill processes before relaunch
    forceKillAllOurProcesses();
    app.relaunch();
    app.exit(0);
});

ipcMain.on('install-update', () => {
    log('install-update IPC: Installing update...');
    isQuitting = true;
    // Ensure processes are killed before update installation starts
    forceKillAllOurProcesses();
    autoUpdater.quitAndInstall();
});

// IPC: Start downloading update (user confirmed)
ipcMain.on('download-update', () => {
    log('download-update IPC: User confirmed, starting download...');
    if (autoUpdater) {
        autoUpdater.downloadUpdate();
    }
});

ipcMain.handle('check-connectivity', async () => {
    log('check-connectivity IPC: Checking remote DB...');
    try {
        await verifyRemoteConnectivity(REMOTE_HOST, DB_PORT);
        return { success: true };
    } catch (e) {
        return { success: false, error: e.message };
    }
});

ipcMain.handle('discovery:start', async () => {
    return new Promise((resolve) => {
        listenForServers((server) => {
            resolve(server);
        });
        // Timeout after 5 seconds if no server found
        setTimeout(() => resolve({ timeout: true }), 5000);
    });
});

ipcMain.handle('config:save', async (event, config) => {
    log(`config:save IPC: Updating .env with mode=${config.mode}, host=${config.host}`);

    try {
        const envPath = path.join(resourcesPath, isDev ? '../.env' : '.env');
        let content = '';
        if (fs.existsSync(envPath)) {
            content = fs.readFileSync(envPath, 'utf8');
        }

        // Replace or Append vars
        const vars = {
            'APP_MODE': config.mode,
            'DB_HOST': config.host
        };
        // Only save port if provided (Client mode with dynamic port)
        if (config.port) {
            vars['DB_PORT'] = config.port;
        }

        let lines = content.split('\n');
        for (const [key, val] of Object.entries(vars)) {
            let found = false;
            lines = lines.map(line => {
                if (line.startsWith(`${key}=`)) {
                    found = true;
                    return `${key}=${val}`;
                }
                return line;
            });
            if (!found) lines.push(`${key}=${val}`);
        }

        fs.writeFileSync(envPath, lines.join('\n'));
        log('Config saved. Restarting...');

        app.relaunch();
        app.exit(0);
        return { success: true };
    } catch (e) {
        log(`Error saving config: ${e.message}`);
        return { success: false, error: e.message };
    }
});

// --- Enterprise Infrastructure: Secure User & UDP Discovery ---

async function createSecureUser() {
    log('Securing MariaDB: Creating enterprise user...');

    const setupScript = path.join(resourcesPath, isDev ? '../scripts/secure_db.php' : 'scripts/secure_db.php');

    // Create direct security script
    const scriptContent = `<?php
    $host = '127.0.0.1';
    $port = '${DB_PORT}';
    $user = 'root'; 
    $pass = ''; 
    
    try {
        $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
        $pdo->exec("CREATE USER IF NOT EXISTS '${DB_USER_SGEN}'@'%' IDENTIFIED BY '${DB_PASS_SGEN}'");
        $pdo->exec("GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER_SGEN}'@'%' WITH GRANT OPTION");
        $pdo->exec("GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER_SGEN}'@'localhost' IDENTIFIED BY '${DB_PASS_SGEN}'");
        $pdo->exec("FLUSH PRIVILEGES");
        echo "Database SECURED with enterprise user";
    } catch (Exception $e) { echo "Security Setup Warning: " . $e->getMessage(); }
    ?>`;

    const scriptDir = path.dirname(setupScript);
    if (!fs.existsSync(scriptDir)) fs.mkdirSync(scriptDir, { recursive: true });
    fs.writeFileSync(setupScript, scriptContent);

    return new Promise((resolve) => {
        const proc = spawn(PHP_EXE, [setupScript], {
            env: Object.assign({}, process.env, { PHPRC: path.join(BIN_PATH, 'php') }),
            windowsHide: true
        });
        proc.stdout.on('data', (d) => log(`[DB-Security] ${d.toString().trim()}`));
        proc.on('close', resolve);
    });
}

function startDiscoveryService() {
    log('Starting UDP Discovery Service...');
    try {
        discoveryServer = dgram.createSocket('udp4');
        discoveryServer.on('error', (err) => {
            log(`Discovery Socket Error: ${err.message}`);
            discoveryServer.close();
        });

        discoveryServer.on('message', (msg, rinfo) => {
            if (msg.toString() === 'SGEN_DISCOVER') {
                const os = require('os');
                const interfaces = os.networkInterfaces();
                let lanIp = '127.0.0.1';
                for (const iface in interfaces) {
                    for (const addr of interfaces[iface]) {
                        if (addr.family === 'IPv4' && !addr.internal) {
                            lanIp = addr.address;
                            break;
                        }
                    }
                }

                const response = JSON.stringify({
                    type: 'SGEN_SERVER',
                    host: lanIp,
                    port: DB_PORT,
                    name: os.hostname()
                });
                discoveryServer.send(response, rinfo.port, rinfo.address);
            }
        });

        discoveryServer.bind(DISCOVERY_PORT, () => {
            discoveryServer.setBroadcast(true);
            log(`Discovery Service active on port ${DISCOVERY_PORT}`);
        });
    } catch (e) { log('Discovery Error: ' + e.message); }
}

function listenForServers(callback) {
    log('Listening for SGEN Servers via Broadcast...');
    try {
        if (discoveryClient) discoveryClient.close();
        discoveryClient = dgram.createSocket('udp4');

        discoveryClient.on('message', (msg) => {
            try {
                const data = JSON.parse(msg.toString());
                if (data.type === 'SGEN_SERVER') {
                    log(`Found server: ${data.name} at ${data.host}`);
                    callback(data);
                }
            } catch (e) { }
        });

        discoveryClient.bind(() => {
            discoveryClient.setBroadcast(true);
            const message = Buffer.from('SGEN_DISCOVER');
            discoveryClient.send(message, DISCOVERY_PORT, '255.255.255.255');
        });
    } catch (e) { log('Client Discovery Error: ' + e.message); }
}
