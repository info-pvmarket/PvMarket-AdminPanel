const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function editor() {
    class Delta {}
    class Image {}
    const window = {};
    const Quill = { import: name => name === 'delta' ? Delta : Image, register() {} };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../public/js/admin-quill.js'), 'utf8'), { window, Quill });
    let matcher;
    const results = [];
    const quill = {
        root: { addEventListener() {} },
        getModule() { return null; },
        clipboard: {
            addMatcher(tag, fn) { matcher = fn; },
            dangerouslyPasteHTML(html) {
                for (const match of html.matchAll(/<img\b[^>]*src="([^"]*)"[^>]*>/g)) {
                    const delta = { image: match[1] };
                    results.push(matcher({ getAttribute: () => match[1] }, delta));
                }
            },
        },
    };
    window.PvQuill.attach(quill);
    return { quill, results, paste: html => quill.clipboard.dangerouslyPasteHTML(html) };
}

test('saved charts survive hydration, but later clipboard base64 stays blocked', () => {
    const { quill, results, paste } = editor();
    const html = '<p><img src="data:image/gif;base64,AAAA" alt="Chart"></p>';
    quill.pvLoadSavedContent(html);
    assert.equal(results[0].image, 'data:image/gif;base64,AAAA');
    paste(html);
    assert.equal(results[1].image, undefined);
    paste('<img src="https://example.test/chart.gif" alt="Chart">');
    assert.equal(results[2].image, 'https://example.test/chart.gif');
});

test('unsafe data formats are blocked even during hydration', () => {
    const { quill, results } = editor();
    quill.pvLoadSavedContent('<img src="data:image/svg+xml;base64,AAAA">');
    assert.equal(results[0].image, undefined);
});

test('hydration failure does not leave clipboard bypass enabled', () => {
    const { quill, results, paste } = editor();
    const original = quill.clipboard.dangerouslyPasteHTML;
    quill.clipboard.dangerouslyPasteHTML = () => { throw new Error('conversion failed'); };
    assert.throws(() => quill.pvLoadSavedContent('bad'), /conversion failed/);
    quill.clipboard.dangerouslyPasteHTML = original;
    paste('<img src="data:image/png;base64,AAAA">');
    assert.equal(results[0].image, undefined);
});
