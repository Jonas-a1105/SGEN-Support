const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const fs = require('fs');
const http = require('http');
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
const DB_PORT = 3307;
const DB_NAME = 'sgen_db';

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
const USER_DATA_PATH = app.getPath('userData');
const DB_DATA_DIR = path.join(USER_DATA_PATH, 'mysql_data_v2');

function log(msg) {
    console.log(`[Main] ${msg}`);
}

// Helper: Wait for database to be ready by testing TCP connection
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
    log(`MYSQL_EXE exists: ${fs.existsSync(MYSQL_EXE)}`);
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

    const args = [
        '--console',
        `--port=${DB_PORT}`,
        `--datadir=${DB_DATA_DIR}`,
        '--skip-grant-tables',
        '--bind-address=127.0.0.1',
        '--default-storage-engine=InnoDB',
        '--general-log=0'
    ];

    log(`DB args: ${args.join(' ')}`);

    return new Promise((resolve, reject) => {
        let resolved = false;
        const tryResolve = () => {
            if (!resolved) {
                resolved = true;
                log('Database Promise resolved');
                resolve();
            }
        };

        dbProcess = spawn(MYSQL_EXE, args, {
            cwd: path.dirname(MYSQL_EXE),
            windowsHide: true,
            stdio: ['ignore', 'pipe', 'pipe']
        });
        log(`DB process spawned with PID: ${dbProcess.pid}`);

        const handleOutput = (data) => {
            const output = data.toString();
            console.log(`[DB] ${output}`);
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
            reject(err);
        });

        dbProcess.on('exit', (code) => {
            log(`DB process exited with code: ${code}`);
            if (!resolved) {
                reject(new Error(`Database exited prematurely with code ${code}`));
            }
        });

        // Fallback timeout - assume ready after 6 seconds
        setTimeout(() => {
            if (dbProcess && !dbProcess.killed) {
                log('Database startup timeout - assuming ready (process still running)');
                tryResolve();
            } else {
                log('Database startup timeout - process not running!');
                reject(new Error('Database process not running after timeout'));
            }
        }, 6000);
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
        DB_HOST: '127.0.0.1',
        DB_PORT: DB_PORT.toString(),
        DB_NAME: DB_NAME,
        DB_USER: 'root',
        DB_PASS: '',
        PHPRC: path.join(BIN_PATH, 'php'),
        APP_VERSION: pkg.version // Pass version to PHP
    });

    return new Promise((resolve, reject) => {
        let resolved = false;
        const tryResolve = (url) => {
            if (!resolved) {
                resolved = true;
                resolve(url);
            }
        };

        const routerPath = path.join(PUBLIC_ROOT, 'router.php');
        log(`Router path: ${routerPath}`);
        phpProcess = spawn(PHP_EXE, ['-S', '127.0.0.1:0', routerPath], {
            env,
            cwd: PUBLIC_ROOT
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
            DB_HOST: '127.0.0.1',
            DB_PORT: DB_PORT.toString(),
            DB_NAME: DB_NAME,
            DB_USER: 'root',
            DB_PASS: '',
            PHPRC: path.join(BIN_PATH, 'php')
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
        if (!isQuitting) {
            e.preventDefault();
            mainWindow.webContents.send('show-exit-confirm');
        }
    });

    // Check for updates (but don't download automatically)
    if (autoUpdater) autoUpdater.checkForUpdates();
}

// --- Process Lifecycle Management ---
const PID_FILE = path.join(USER_DATA_PATH, 'running_pids.json');

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
    const targets = ['mysqld.exe', 'php.exe'];
    const validRoots = [
        path.resolve(BIN_PATH).toLowerCase(), // e.g. .../resources/bin
    ];

    // In dev, sometimes binaries might be elsewhere? usually resources/bin is correct even in dev for this setup.
    log(`Cleanup Target Roots: ${JSON.stringify(validRoots)}`);

    for (const target of targets) {
        try {
            // WMIC is standard on Windows for querying process paths
            const cmd = `wmic process where "name='${target}'" get ProcessId,ExecutablePath /FORMAT:CSV`;
            const output = require('child_process').execSync(cmd, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });

            const lines = output.trim().split('\r\n');
            // CSV Format: Node,ExecutablePath,ProcessId
            // Skip headers (usually 2 lines or until empty line is passed)

            for (const line of lines) {
                if (!line.trim() || line.startsWith('Node,')) continue;

                // Parse CSV line simply
                const parts = line.split(',');
                if (parts.length < 3) continue;

                // Parts are usually: [NodeName, Path, PID]
                // But path might contain commas? WMIC CSV output is usually safeish but let's be careful.
                // Better approach: pop the last element as PID, join the rest as path (minus the first Node element)
                const pid = parts.pop();
                // Remove NodeName (first element)
                parts.shift();
                const exePath = parts.join(',').trim(); // Rejoin in case path had commas

                if (!exePath) continue;

                // Check if this process belongs to us
                const lowerExe = exePath.toLowerCase();
                const isOurs = validRoots.some(root => lowerExe.includes(root));

                if (isOurs) {
                    log(`Found zombie ${target} (PID: ${pid}) at ${exePath}`);
                    killProcess(pid);
                } else {
                    // log(`Skipping external process ${target} (PID: ${pid}) at ${exePath}`);
                }
            }
        } catch (e) {
            log(`Cleanup check failed for ${target}: ${e.message}`);
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

app.whenReady().then(async () => {
    log('=== APP READY - Starting initialization ===');

    // Step 0: Aggressive Cleanup
    cleanupZombies();
    await forceCleanupProcesses();

    try {
        log('Step 1: Starting database process...');
        await startDatabase();
        savePids(); // Save immediately
        log('Step 1 COMPLETE: Database process started');

        log('Step 2: Verifying database is accepting connections...');
        await waitForDatabaseReady(DB_PORT);
        log('Step 2 COMPLETE: Database verified ready');

        log('Step 2.5: Running migrations...');
        await runMigrations();
        log('Step 2.5 COMPLETE: Migrations executed');

        log('Step 3: Starting PHP server...');
        await startPhpServer();
        savePids(); // Update with PHP PID
        log('Step 3 COMPLETE: PHP server started');

        log('Step 4: Creating window...');
        createWindow();
        log('Step 4 COMPLETE: Window created');
    } catch (e) {
        console.error("FATAL STARTUP ERROR:", e);
        log(`FATAL ERROR: ${e.message}`);

        // Force cleanup on failure too
        if (dbProcess) killProcess(dbProcess.pid);
        if (phpProcess) killProcess(phpProcess.pid);

        const { dialog } = require('electron');
        dialog.showErrorBox('Error de Inicio', `No se pudo iniciar el servicio interno.\n${e.message}`);
        app.quit();
    }

    app.on('activate', function () {
        if (BrowserWindow.getAllWindows().length === 0) createWindow();
    });
});

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
