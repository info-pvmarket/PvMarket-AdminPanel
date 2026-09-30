@php
    $faqRows = old('faqs', isset($record) ? ($record->faqs ?? []) : []);
    $linkRows = old('internal_links', isset($record) ? ($record->internal_links ?? []) : []);
@endphp

<hr class="section-divider">
<div class="section-title">FAQs</div>
<div id="seoFaqRows">
    @foreach($faqRows as $index => $faq)
        <div class="form-grid-2 seo-repeat-row" style="align-items:start;">
            <div class="form-group">
                <label class="form-label">Question</label>
                <input class="form-input" name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Answer</label>
                <textarea class="form-input" rows="2" name="faqs[{{ $index }}][answer]">{{ $faq['answer'] ?? '' }}</textarea>
                <button type="button" class="seo-remove-row" aria-label="Remove FAQ">Remove</button>
            </div>
        </div>
    @endforeach
</div>
<button type="button" class="btn-back" id="addSeoFaq" style="margin-bottom:20px;">+ Add FAQ</button>

<div class="section-title">Internal Links</div>
<div id="seoInternalLinkRows">
    @foreach($linkRows as $index => $link)
        <div class="form-grid-2 seo-repeat-row" style="align-items:start;">
            <div class="form-group">
                <label class="form-label">Link Title</label>
                <input class="form-input" name="internal_links[{{ $index }}][title]" value="{{ $link['title'] ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">URL</label>
                <input type="url" class="form-input" name="internal_links[{{ $index }}][url]" value="{{ $link['url'] ?? '' }}" placeholder="https://pv.market/...">
                <button type="button" class="seo-remove-row" aria-label="Remove internal link">Remove</button>
            </div>
        </div>
    @endforeach
</div>
<button type="button" class="btn-back" id="addSeoInternalLink">+ Add Internal Link</button>

@once
<style>
    .seo-remove-row { align-self:flex-end; margin-top:4px; border:0; background:transparent; color:#DC2626; cursor:pointer; font-size:12px; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function addRow(containerId, prefix, fields) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const index = container.querySelectorAll('.seo-repeat-row').length;
        const row = document.createElement('div');
        row.className = 'form-grid-2 seo-repeat-row';
        row.style.alignItems = 'start';
        row.innerHTML = fields.map(function (field) {
            const control = field.textarea
                ? '<textarea class="form-input" rows="2" name="' + prefix + '[' + index + '][' + field.key + ']"></textarea>'
                : '<input ' + (field.url ? 'type="url" ' : '') + 'class="form-input" name="' + prefix + '[' + index + '][' + field.key + ']">';
            return '<div class="form-group"><label class="form-label">' + field.label + '</label>' + control +
                (field.last ? '<button type="button" class="seo-remove-row">Remove</button>' : '') + '</div>';
        }).join('');
        container.appendChild(row);
    }

    document.getElementById('addSeoFaq')?.addEventListener('click', function () {
        addRow('seoFaqRows', 'faqs', [
            { key: 'question', label: 'Question' },
            { key: 'answer', label: 'Answer', textarea: true, last: true }
        ]);
    });
    document.getElementById('addSeoInternalLink')?.addEventListener('click', function () {
        addRow('seoInternalLinkRows', 'internal_links', [
            { key: 'title', label: 'Link Title' },
            { key: 'url', label: 'URL', url: true, last: true }
        ]);
    });
    document.addEventListener('click', function (event) {
        if (event.target.classList?.contains('seo-remove-row')) {
            event.target.closest('.seo-repeat-row')?.remove();
        }
    });
});
</script>
@endonce
