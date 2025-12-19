const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
    onUpdateAvailable: (callback) => ipcRenderer.on('update_available', (event, info) => callback(info)),
    onUpdateProgress: (callback) => ipcRenderer.on('update_progress', (event, progress) => callback(progress)),
    onUpdateDownloaded: (callback) => ipcRenderer.on('update_downloaded', (event, info) => callback(info)),
    onShowExitConfirm: (callback) => ipcRenderer.on('show-exit-confirm', callback),

    downloadUpdate: () => ipcRenderer.send('download-update'),
    installUpdate: () => ipcRenderer.send('install-update'),
    exitApp: () => ipcRenderer.send('exit-app'),
    restartApp: () => ipcRenderer.send('restart_app') // Legacy/Dev
});
