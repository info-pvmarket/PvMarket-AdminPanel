// Offline integration test: uses the shipped Quill build, with all network
// requests blocked. Pass the evidence package's extracted root as argv[2].
// Requires an installed Playwright runtime and Microsoft Edge.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { chromium } = require('playwright');

async function main() {
    const evidenceRoot = process.argv[2];
    if (!evidenceRoot) throw new Error('Pass the extracted PVmarket_EOD_2026-10-24 directory.');
    const restoration = path.join(evidenceRoot, 'evidence/output/blog-image-audit/2026-10-23/restoration');
    const manifest = JSON.parse(fs.readFileSync(path.join(restoration, 'manifest.json.txt'), 'utf8'));
    const browser = await chromium.launch({ channel: 'msedge', headless: true });
    try {
        const context = await browser.newContext();
        await context.route('**/*', route => route.abort());
        const page = await context.newPage();
        const baseline = await context.newPage();
        await baseline.setContent('<div id="editor"></div>');
        await baseline.addScriptTag({ path: path.join(__dirname, '../public/assets/vendor/quill.min.js') });
        await baseline.addScriptTag({ content: execFileSync('git', ['show', 'HEAD:public/js/admin-quill.js'], {
            cwd: path.join(__dirname, '..'), encoding: 'utf8',
        }) });
        const firstReference = fs.readFileSync(path.join(restoration, manifest[0].reference_body), 'utf8');
        const baselineCounts = await baseline.evaluate(html => {
            const body = new DOMParser().parseFromString(html, 'text/html').querySelector('.blog-content');
            const editor = new Quill('#editor', { modules: { toolbar: false } });
            PvQuill.attach(editor);
            editor.clipboard.dangerouslyPasteHTML(body.innerHTML);
            return { before: body.querySelectorAll('img').length, after: editor.root.querySelectorAll('img').length };
        }, firstReference);
        assert.deepEqual(baselineCounts, { before: 1, after: 0 }, 'baseline must reproduce saved chart removal');
        console.log('PASS baseline reproduction: existing editor drops the battery chart on load');
        await baseline.close();
        await page.setContent('<div id="editor"></div>');
        await page.addScriptTag({ path: path.join(__dirname, '../public/assets/vendor/quill.min.js') });
        await page.addScriptTag({ path: path.join(__dirname, '../public/js/admin-quill.js') });
        await page.evaluate(() => {
            document.execCommand = () => false;
            window.editor = new Quill('#editor', { modules: { toolbar: false } });
            PvQuill.attach(window.editor);
        });

        for (const item of manifest) {
            const html = fs.readFileSync(path.join(restoration, item.reference_body), 'utf8');
            const result = await page.evaluate(async html => {
                function snapshot(root) {
                    return {
                        images: [...root.querySelectorAll('img')].map(img => ({
                            src: img.getAttribute('src'), alt: img.getAttribute('alt') || '',
                            width: img.getAttribute('width'), height: img.getAttribute('height'),
                        })),
                        links: [...root.querySelectorAll('a[href]')].map(a => a.getAttribute('href')).sort(),
                    };
                }
                const original = new DOMParser().parseFromString(html, 'text/html');
                // The package adds report navigation outside the actual article.
                const article = original.querySelector('.blog-content');
                if (!article) throw new Error('Article body missing from restoration reference');
                const before = snapshot(article);
                editor.pvLoadSavedContent(article.innerHTML);
                const loaded = snapshot(editor.root);
                const saved = editor.root.innerHTML;
                editor.setText('');
                editor.pvLoadSavedContent(saved);
                const reopened = snapshot(editor.root);
                const decoded = await Promise.all([...editor.root.querySelectorAll('img')].map(async img => {
                    try { await img.decode(); return img.naturalWidth > 0; }
                    catch { return false; }
                }));
                window.prompt = () => 'Updated chart description';
                editor.root.querySelector('img').click();
                const editedAlt = snapshot(editor.root);
                editor.pvLoadSavedContent(editor.root.innerHTML);
                const reopenedAlt = snapshot(editor.root);
                return { before, loaded, reopened, decoded, editedAlt, reopenedAlt };
            }, html);
            assert.equal(result.before.images.length, item.missing_images.length);
            assert.deepEqual(result.loaded, result.before, 'load must retain image attributes and links');
            assert.deepEqual(result.reopened, result.before, 'reopen must retain image attributes and links');
            assert.ok(result.decoded.every(Boolean), 'original chart files must decode');
            const expectedAlt = structuredClone(result.before);
            expectedAlt.images[0].alt = 'Updated chart description';
            assert.deepEqual(result.editedAlt, expectedAlt, 'alt editing must retain all other image attributes and links');
            assert.deepEqual(result.reopenedAlt, expectedAlt, 'edited alt must survive reopening');
            console.log(`PASS ${item.missing_images.join(', ')}: load/serialize/reopen, alt edits, dimensions, links, decode`);
        }
        const blocked = await page.evaluate(() => {
            editor.setText('');
            editor.clipboard.dangerouslyPasteHTML('<p><img src="data:image/png;base64,AAAA" alt="New paste"></p>');
            return editor.root.querySelectorAll('img').length;
        });
        assert.equal(blocked, 0, 'new clipboard base64 images must remain blocked');
        console.log('PASS new clipboard base64 remains blocked');
    } finally {
        await browser.close();
    }
}

main().catch(error => { console.error(error); process.exitCode = 1; });
