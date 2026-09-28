(function (root, factory) {
    'use strict';

    var api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }

    root.CannedResponsePicker = api;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', api.init);
    } else {
        api.init();
    }
}(typeof window !== 'undefined' ? window : globalThis, function () {
    'use strict';

    function insertAtSelection(textarea, text) {
        var start = Number.isInteger(textarea.selectionStart)
            ? textarea.selectionStart
            : textarea.value.length;
        var end = Number.isInteger(textarea.selectionEnd)
            ? textarea.selectionEnd
            : start;

        textarea.value = textarea.value.slice(0, start) + text + textarea.value.slice(end);

        var cursor = start + text.length;
        textarea.focus();
        textarea.setSelectionRange(cursor, cursor);
    }

    function init() {
        var select = document.getElementById('canned-response-select');
        var insertButton = document.getElementById('insert-canned-response');
        var textarea = document.getElementById('response');
        var data = document.getElementById('canned-response-data');

        if (!select || !insertButton || !textarea || !data) {
            return;
        }

        var responses = JSON.parse(data.textContent);

        select.addEventListener('change', function () {
            insertButton.disabled = select.value === '';
        });

        insertButton.addEventListener('click', function () {
            if (!Object.prototype.hasOwnProperty.call(responses, select.value)) {
                return;
            }

            insertAtSelection(textarea, responses[select.value]);
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            select.value = '';
            insertButton.disabled = true;
        });
    }

    return {
        init: init,
        insertAtSelection: insertAtSelection,
    };
}));
