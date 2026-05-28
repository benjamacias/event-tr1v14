import { createRequire } from 'node:module';
import fs from 'node:fs/promises';

const require = createRequire('C:/Users/benja/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright/package.json');
const { chromium } = require('playwright');

const outDir = new URL('../docs/manual-assets-real/', import.meta.url);
await fs.mkdir(outDir, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({
  viewport: { width: 1440, height: 1000 },
  deviceScaleFactor: 1,
});
const page = await context.newPage();

async function screenshot(url, filename, options = {}) {
  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });
  await page.waitForTimeout(options.wait ?? 1200);
  await page.screenshot({
    path: new URL(filename, outDir).pathname,
    fullPage: options.fullPage ?? false,
  });
}

await screenshot('http://192.168.1.114:8016', '01_inicio_real.png', { fullPage: true });

await page.goto('http://192.168.1.114:8016/admin', { waitUntil: 'domcontentloaded', timeout: 20000 });
await page.waitForTimeout(1000);
if (await page.locator('input[type="email"]').count()) {
  await page.locator('input[type="email"]').fill('admin@ianus.local');
  await page.locator('input[type="password"]').fill('password');
  await page.locator('button[type="submit"]').click();
  await page.waitForLoadState('domcontentloaded', { timeout: 20000 }).catch(() => {});
  await page.waitForTimeout(1800);
}
await page.screenshot({ path: new URL('02_admin_real.png', outDir).pathname, fullPage: false });

await screenshot('http://192.168.1.114:8016/screen', '03_screen_real.png', { wait: 2200 });

await browser.close();
