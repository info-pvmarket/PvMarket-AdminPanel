/**
 * Rich-text editor rules for the CMS (TASK-6.4):
 * - the image button uploads the file and inserts its URL, never base64;
 * - pasted base64 images are dropped; saved blog images survive hydration;
 * - every image carries alt text: asked for on insert, editable by clicking it.
 */
(function () {
    var Delta = Quill.import('delta');
    var BaseImage = Quill.import('formats/image');

    // An image blot that keeps its alt text through Quill's delta round trip.
    class ImageWithAlt extends BaseImage {
        static create(value) {
            var node = super.create(typeof value === 'string' ? value : value.src);
            if (value && typeof value === 'object' && value.alt) node.setAttribute('alt', value.alt);
            return node;
        }
        static value(node) {
            return { src: node.getAttribute('src'), alt: node.getAttribute('alt') || '' };
        }
    }
    ImageWithAlt.blotName = 'image';
    ImageWithAlt.tagName = 'IMG';
    Quill.register(ImageWithAlt, true);

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function askAlt(current) {
        var alt = window.prompt('Alt text (describe the image for screen readers and search engines):', current || '');
        return alt === null ? null : alt.trim();
    }

    function uploadImage(quill) {
        var input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/png,image/jpeg,image/webp,image/gif';
        input.onchange = function () {
            var file = input.files && input.files[0];
            if (!file) return;
            var alt = askAlt('');
            if (!alt) {
                window.alert('Alt text is required for every image.');
                return;
            }
            var body = new FormData();
            body.append('image', file);
            fetch(window.PV_EDITOR_IMAGE_UPLOAD_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                body: body,
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json().then(function (json) {
                    if (!response.ok) {
                        throw new Error((json.errors && json.errors.image && json.errors.image[0]) || json.message || 'Upload failed');
                    }
                    return json;
                });
            }).then(function (json) {
                var range = quill.getSelection(true);
                quill.insertEmbed(range.index, 'image', { src: json.url, alt: alt }, 'user');
                quill.setSelection(range.index + 1, 0, 'silent');
            }).catch(function (error) {
                window.alert('Image upload failed: ' + error.message);
            });
        };
        input.click();
    }

    window.PvQuill = {
        /** Apply the CMS image rules to an editor. Call right after new Quill(...). */
        attach: function (quill) {
            var loadingSavedContent = false;
            // Use only for hydrating stored content, never for clipboard paste.
            quill.pvLoadSavedContent = function (html) {
                loadingSavedContent = true;
                try {
                    quill.clipboard.dangerouslyPasteHTML(html);
                } finally {
                    loadingSavedContent = false;
                }
            };
            // New clipboard images remain blocked; explicit hydration is scoped above.
            quill.clipboard.addMatcher('IMG', function (node, delta) {
                var src = node.getAttribute('src') || '';
                if (!/^data:/i.test(src)) return delta;
                return loadingSavedContent && /^data:image\/(?:png|jpe?g|gif|webp);base64,/i.test(src)
                    ? delta : new Delta();
            });

            var toolbar = quill.getModule('toolbar');
            if (toolbar) toolbar.addHandler('image', function () { uploadImage(quill); });

            // Click an image to set or change its alt text.
            quill.root.addEventListener('click', function (event) {
                var img = event.target;
                if (!img || img.tagName !== 'IMG') return;
                var blot = Quill.find(img);
                if (!blot) return;
                var alt = askAlt(img.getAttribute('alt'));
                if (alt === null) return;
                if (!alt) {
                    window.alert('Alt text is required for every image.');
                    return;
                }
                var index = quill.getIndex(blot);
                // Formatting in place retains width, height and placement.
                quill.formatText(index, 1, 'alt', alt, 'user');
            });

            return quill;
        }
    };
})();
