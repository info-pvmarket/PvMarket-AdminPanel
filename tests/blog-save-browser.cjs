// Offline DOM test of the actual blog submission guard. No website is loaded.
const { chromium } = require('playwright');
const path = require('node:path');
const assert = require('node:assert/strict');

async function main() {
    const browser = await chromium.launch({ channel: 'msedge', headless: true });
    try {
        const context = await browser.newContext();
        await context.route('**/*', route => route.abort());
        const page = await context.newPage();
        await page.clock.install();
        await page.setContent('<form id="blogForm"><input id="title" required></form><button id="save" type="submit" form="blogForm"><span data-save-label>Save</span></button><p id="status" role="status" aria-live="polite"></p>');
        await page.addScriptTag({ path: path.join(__dirname, '../public/js/blog-save-state.js') });
        await page.evaluate(() => PvBlogSave.attach(document.querySelector('form'), document.querySelector('button'), document.querySelector('#status')));

        await page.evaluate(() => document.querySelector('form').requestSubmit());
        assert.equal(await page.locator('#save').isEnabled(), true, 'invalid required fields must not lock Save');
        console.log('PASS invalid form leaves Save available');

        const submitted = await page.evaluate(() => {
            document.querySelector('#title').value = 'Test';
            const form = document.querySelector('form');
            return [
                form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
                form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
            ];
        });
        assert.deepEqual(submitted, [true, false], 'second submit must be rejected');
        assert.equal(await page.locator('#save').isDisabled(), true);
        assert.equal(await page.locator('#save').textContent(), 'Saving…');
        assert.equal(await page.locator('#blogForm').getAttribute('aria-busy'), 'true');
        console.log('PASS repeated submit blocked and Saving state announced');

        await page.clock.fastForward(16000);
        assert.match(await page.locator('#status').textContent(), /check the Blogs list/);
        assert.equal(await page.locator('#save').isDisabled(), true, 'timeout must not invite another unsafe retry');
        console.log('PASS slow response explains how to check for a saved post');

        await page.evaluate(() => window.dispatchEvent(new PageTransitionEvent('pageshow', { persisted: true })));
        assert.equal(await page.locator('#save').isEnabled(), true);
        assert.equal(await page.locator('#save').textContent(), 'Save');
        assert.equal(await page.locator('#blogForm').getAttribute('aria-busy'), null);
        assert.equal(await page.locator('#status').textContent(), '');
        console.log('PASS browser Back restores usable Save controls');
    } finally {
        await browser.close();
    }
}

main().catch(error => { console.error(error); process.exitCode = 1; });
