const test = require('node:test');
const assert = require('node:assert/strict');
const { insertAtSelection } = require('../../../public/js/canned-response.js');

function textarea(value, start, end = start) {
    return {
        value,
        selectionStart: start,
        selectionEnd: end,
        focused: false,
        setSelectionRange(selectionStart, selectionEnd) {
            this.selectionStart = selectionStart;
            this.selectionEnd = selectionEnd;
        },
        focus() {
            this.focused = true;
        },
    };
}

test('inserts a canned response into an empty textarea', () => {
    const target = textarea('', 0);

    insertAtSelection(target, 'Please restart your computer.');

    assert.equal(target.value, 'Please restart your computer.');
    assert.equal(target.selectionStart, 29);
    assert.equal(target.selectionEnd, 29);
    assert.equal(target.focused, true);
});

test('inserts a canned response at the cursor', () => {
    const target = textarea('Hello  Kind regards', 6);

    insertAtSelection(target, 'there.');

    assert.equal(target.value, 'Hello there. Kind regards');
    assert.equal(target.selectionStart, 12);
});

test('replaces selected text with a canned response', () => {
    const target = textarea('Hello old text.', 6, 14);

    insertAtSelection(target, 'new reply');

    assert.equal(target.value, 'Hello new reply.');
    assert.equal(target.selectionStart, 15);
});
