const { app, BrowserWindow, globalShortcut } = require('electron');
const path = require('path');
const { exec } = require('child_process');
const fs = require('fs');

let win;
let phpServer;

function createWindow() {
  const configPath = path.join(__dirname, 'config.json');
  const config = JSON.parse(fs.readFileSync(configPath));
  const host = config.host || '127.0.0.1';

  const phpPath = path.join(__dirname, 'php', 'php.exe');
  phpServer = exec(`"${phpPath}" -S ${host}:8000 -t ./kiosk`);

  win = new BrowserWindow({
    width: 1024,
    height: 768,
    kiosk: true,
    autoHideMenuBar: true,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false
    }
  });

  win.loadURL(`http://${host}:8000/order.html`);

  globalShortcut.register('F2', () => {
    win.loadURL(`http://${host}:8000/config-ui.html`);
  });

  win.on('closed', () => {
    if (phpServer) phpServer.kill();
  });
}

app.whenReady().then(createWindow);

app.on('will-quit', () => {
  globalShortcut.unregisterAll();
  if (phpServer) phpServer.kill();
});