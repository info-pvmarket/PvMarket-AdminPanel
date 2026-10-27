(function () {
    window.PvBlogSave = {
        attach: function (form, button, status) {
            var pending = false;
            var timer;
            var label = button.querySelector('[data-save-label]');

            function reset() {
                pending = false;
                window.clearTimeout(timer);
                button.disabled = false;
                label.textContent = 'Save';
                form.removeAttribute('aria-busy');
                status.textContent = '';
            }

            form.addEventListener('submit', function (event) {
                if (pending) {
                    event.preventDefault();
                    return;
                }
                if (event.defaultPrevented) return;
                pending = true;
                button.disabled = true;
                label.textContent = 'Saving…';
                form.setAttribute('aria-busy', 'true');
                status.textContent = 'Saving your blog. Please wait; do not submit again.';
                timer = window.setTimeout(function () {
                    status.textContent = 'Saving is taking longer than expected. Do not submit again. If this page cannot finish, check the Blogs list for your saved post before retrying.';
                }, 15000);
            });

            // Restore the control when returning through the browser back cache.
            window.addEventListener('pageshow', reset);
        }
    };
})();
