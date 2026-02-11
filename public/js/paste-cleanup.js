(function () {
    'use strict';

    function htmlToPlainText(html) {
        var text = html;

        // Remove existing newlines (rebuild from HTML structure)
        text = text.replace(/\n/gi, '');

        // Strip style and script blocks entirely
        text = text.replace(/<style[\s\S]*?<\/style>/gi, '');
        text = text.replace(/<script[\s\S]*?<\/script>/gi, '');

        // Outlook Mso paragraphs → single line breaks, drop empty spacers
        text = text.replace(
            /<p[^>]*class="[^"]*(?:x_)?Mso[^"]*"[^>]*>([\s\S]*?)<\/p>/gi,
            function (match, inner) {
                var cleaned = inner
                    .replace(/<span[^>]*>([\s\S]*?)<\/span>/gi, '$1')
                    .replace(/&nbsp;|\xa0/g, ' ')
                    .trim();
                return cleaned ? cleaned + '\n' : '\n';
            }
        );

        // Remaining pure-spacer <p> tags → single newline (preserves visual breaks)
        text = text.replace(/<p[^>]*>(?:&nbsp;|\s*|\xa0)*<\/p>/gi, '\n');

        // Convert block-level elements to newlines
        text = text.replace(/<br\s*\/?>/gi, '\n');
        text = text.replace(/<\/p>/gi, '\n\n');
        text = text.replace(/<\/div>/gi, '\n');
        text = text.replace(/<li[^>]*>/gi, '  \u2022 ');
        text = text.replace(/<\/li>/gi, '\n');
        text = text.replace(/<\/(?:ul|ol)>/gi, '\n\n');
        text = text.replace(/<\/tr>/gi, '\n');
        text = text.replace(/<\/(?:td|th)>/gi, '\t');
        text = text.replace(/<\/h[1-6]>/gi, '\n\n');

        // Strip all remaining HTML tags
        text = text.replace(/<[^>]*>/g, '');

        // Decode common HTML entities
        text = text
            .replace(/&amp;/gi, '&')
            .replace(/&lt;/gi, '<')
            .replace(/&gt;/gi, '>')
            .replace(/&quot;/gi, '"')
            .replace(/&#039;/gi, "'")
            .replace(/&ldquo;|&rdquo;/g, '"')
            .replace(/&lsquo;|&rsquo;/g, "'")
            .replace(/&ndash;|&mdash;/g, '-')
            .replace(/&bull;/g, '\u2022');

        // Final normalization
        text = text
            .replace(/ +/g, ' ')
            .replace(/[ \t]+$/gm, '')
            .replace(/(\r?\n\s*){3,}/g, '\n\n')
            .trim();

        return text;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var textareas = document.querySelectorAll('textarea[data-paste-cleanup]');

        textareas.forEach(function (textarea) {
            textarea.addEventListener('paste', function (e) {
                var clipboardData = e.clipboardData || window.clipboardData;
                if (!clipboardData) {
                    return;
                }

                // Prefer HTML source for accurate structure, fall back to plain text
                var html = clipboardData.getData('text/html');
                var text = html
                    ? htmlToPlainText(html)
                    : clipboardData.getData('text/plain');

                if (!text) {
                    return;
                }

                e.preventDefault();

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

                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });
    });
})();
