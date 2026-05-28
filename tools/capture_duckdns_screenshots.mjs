import fs from 'node:fs/promises';
import http from 'node:http';
import { spawn } from 'node:child_process';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const userDataDir = 'C:\\Users\\benja\\OneDrive\\Documentos\\New project\\docs\\chrome-duckdns-profile';
const outDir = new URL('../docs/manual-assets-duckdns/', import.meta.url);
const port = 9223;

await fs.mkdir(outDir, { recursive: true });
await fs.rm(userDataDir, { recursive: true, force: true }).catch(() => {});
await fs.mkdir(userDataDir, { recursive: true });

const chrome = spawn(chromePath, [
  '--headless=new',
  '--no-sandbox',
  '--disable-gpu',
  '--hide-scrollbars',
  `--remote-debugging-port=${port}`,
  `--user-data-dir=${userDataDir}`,
  '--window-size=1440,1000',
  'about:blank',
], { stdio: 'ignore', detached: false });

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function getJson(url) {
  return new Promise((resolve, reject) => {
    http.get(url, (res) => {
      let data = '';
      res.on('data', (chunk) => data += chunk);
      res.on('end', () => resolve(JSON.parse(data)));
    }).on('error', reject);
  });
}

function putJson(url) {
  return new Promise((resolve, reject) => {
    const request = http.request(url, { method: 'PUT' }, (res) => {
      let data = '';
      res.on('data', (chunk) => data += chunk);
      res.on('end', () => resolve(JSON.parse(data)));
    });
    request.on('error', reject);
    request.end();
  });
}

async function waitForVersion() {
  for (let i = 0; i < 80; i++) {
    try {
      return await getJson(`http://127.0.0.1:${port}/json/version`);
    } catch {
      await sleep(250);
    }
  }
  throw new Error('Chrome DevTools did not start.');
}

function cdpClient(url) {
  const ws = new WebSocket(url);
  let id = 0;
  const pending = new Map();
  ws.onmessage = (event) => {
    const msg = JSON.parse(event.data);
    if (msg.id && pending.has(msg.id)) {
      const { resolve, reject } = pending.get(msg.id);
      pending.delete(msg.id);
      if (msg.error) reject(new Error(msg.error.message));
      else resolve(msg.result);
    }
  };
  return new Promise((resolve, reject) => {
    ws.onopen = () => resolve({
      send(method, params = {}) {
        const callId = ++id;
        ws.send(JSON.stringify({ id: callId, method, params }));
        return new Promise((callResolve, callReject) => {
          pending.set(callId, { resolve: callResolve, reject: callReject });
        });
      },
      close() {
        ws.close();
      },
    });
    ws.onerror = () => reject(new Error('WebSocket connection failed.'));
  });
}

async function capture(cdp, url, filename, width, height, waitMs = 1600) {
  await cdp.send('Emulation.setDeviceMetricsOverride', {
    width,
    height,
    deviceScaleFactor: 1,
    mobile: false,
  });
  await cdp.send('Page.navigate', { url });
  await sleep(waitMs);
  const shot = await cdp.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
  await fs.writeFile(new URL(filename, outDir), Buffer.from(shot.data, 'base64'));
}

try {
  await waitForVersion();
  const target = await putJson(`http://127.0.0.1:${port}/json/new?about:blank`);
  const cdp = await cdpClient(target.webSocketDebuggerUrl);
  await cdp.send('Page.enable');
  await cdp.send('Runtime.enable');

  await capture(cdp, 'https://ianus-evento.duckdns.org', '01_inicio_duckdns.png', 900, 1400, 2500);
  await capture(cdp, 'https://ianus-evento.duckdns.org/admin/login', '02_login_duckdns.png', 1440, 1000, 2500);

  await cdp.send('Runtime.evaluate', {
    expression: `
      (async () => {
        document.querySelector('input[type="email"]').value = 'admin@ianus.local';
        document.querySelector('input[type="email"]').dispatchEvent(new Event('input', { bubbles: true }));
        document.querySelector('input[type="password"]').value = 'ianus-pass';
        document.querySelector('input[type="password"]').dispatchEvent(new Event('input', { bubbles: true }));
        document.querySelector('button[type="submit"]').click();
      })()
    `,
    awaitPromise: true,
  });
  await sleep(3500);
  const adminShot = await cdp.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
  await fs.writeFile(new URL('03_admin_duckdns.png', outDir), Buffer.from(adminShot.data, 'base64'));

  const adminTabs = [
    ['https://ianus-evento.duckdns.org/admin/answer-options', '05_admin_respuestas_duckdns.png'],
    ['https://ianus-evento.duckdns.org/admin/attempts', '06_admin_intentos_duckdns.png'],
    ['https://ianus-evento.duckdns.org/admin/participants', '07_admin_participantes_duckdns.png'],
    ['https://ianus-evento.duckdns.org/admin/provider-logos', '08_admin_publicidades_duckdns.png'],
    ['https://ianus-evento.duckdns.org/admin/questions', '09_admin_preguntas_duckdns.png'],
    ['https://ianus-evento.duckdns.org/admin/question-sets', '10_admin_sets_duckdns.png'],
    ['https://ianus-evento.duckdns.org/admin/settings', '11_admin_configuracion_duckdns.png'],
  ];
  for (const [url, filename] of adminTabs) {
    await capture(cdp, url, filename, 1440, 1000, 2600);
  }

  await capture(cdp, 'https://ianus-evento.duckdns.org/screen', '04_screen_duckdns.png', 1600, 900, 3000);
  cdp.close();
} finally {
  chrome.kill();
}
