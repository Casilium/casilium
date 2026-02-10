(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var textareas = document.querySelectorAll('textarea[data-paste-cleanup]');

        textareas.forEach(function (textarea) {
            textarea.addEventListener('paste', function (e) {
                var clipboardData = e.clipboardData || window.clipboardData;
                if (!clipboardData) {
                    return;
                }

                var text = clipboardData.getData('text/plain');
                if (!text) {
                    return;
                }

                e.preventDefault();

                // Trim trailing whitespace per line, collapse 3+ newlines to 2
                var cleaned = text
                    .trim()
                    .replace(/[ \t]+$/gm, '')
                    .replace(/(\r?\n\s*){3,}/g, '\n\n');

                // Insert at cursor position
                var start = textarea.selectionStart;
                var end = textarea.selectionEnd;
                var value = textarea.value;

                textarea.value = value.substring(0, start) + cleaned + value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + cleaned.length;

                // Trigger input event so any listeners are notified
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });
    });
})();
