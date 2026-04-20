(function () {
    function parseOptions(value) {
        if (!value) {
            return {};
        }

        try {
            var parsed = JSON.parse(value);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (error) {
            return {};
        }
    }

    function syncEditor(editor, textarea) {
        if (typeof editor.getContents === 'function') {
            textarea.value = editor.getContents();
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function sanitizeUrl(url) {
        var value = String(url || '').trim();
        if (!value) {
            return '';
        }

        if (/^(https?:|mailto:|tel:|#|\/)/i.test(value)) {
            return value;
        }

        return '';
    }

    function sanitizeTarget(target) {
        var value = String(target || '').trim().toLowerCase();
        if (value === '_blank' || value === '_self') {
            return value;
        }

        return '';
    }

    function sanitizeRel(target, rel) {
        var value = String(rel || '').trim();
        if (target === '_blank') {
            return value ? value : 'noopener noreferrer';
        }

        return value;
    }

    function sanitizeSimpleAttribute(value) {
        var normalized = String(value || '').trim();
        return normalized ? escapeHtml(normalized) : '';
    }

    function sanitizeDimension(value) {
        var normalized = String(value || '').trim();
        return /^(\d+|\d+%|\d+\.\d+%?)$/.test(normalized) ? normalized : '';
    }

    function sanitizeNumber(value) {
        var normalized = String(value || '').trim();
        return /^\d+$/.test(normalized) ? normalized : '';
    }

    function sanitizeTableStyle(value) {
        var normalized = String(value || '');
        var allowed = normalized.match(/(?:^|;)\s*(width|text-align|vertical-align|background-color|border(?:-top|-right|-bottom|-left)?|border-collapse|height)\s*:\s*[^;]+/gi);
        return allowed ? escapeHtml(allowed.map(function (part) { return part.replace(/^;\s*/, '').trim(); }).join('; ')) : '';
    }

    function sanitizePastedHtml(html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var allowedTags = new Set([
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup',
            'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'a', 'table', 'thead',
            'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr'
        ]);
        var dropWithChildren = new Set(['script', 'style', 'meta', 'link', 'xml', 'o:p']);

        function sanitizeNode(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                return escapeHtml(node.textContent || '');
            }

            if (node.nodeType !== Node.ELEMENT_NODE) {
                return '';
            }

            var tag = node.tagName.toLowerCase();
            if (dropWithChildren.has(tag)) {
                return '';
            }

            var children = Array.prototype.map.call(node.childNodes, sanitizeNode).join('');
            if (!allowedTags.has(tag)) {
                return children;
            }

            if (tag === 'br' || tag === 'hr') {
                return '<' + tag + '>';
            }

            var attrs = [];
            if (tag === 'a') {
                var href = sanitizeUrl(node.getAttribute('href'));
                var target = sanitizeTarget(node.getAttribute('target'));
                var rel = sanitizeRel(target, node.getAttribute('rel'));
                var title = sanitizeSimpleAttribute(node.getAttribute('title'));

                if (href) attrs.push(' href="' + escapeHtml(href) + '"');
                if (target) attrs.push(' target="' + target + '"');
                if (rel) attrs.push(' rel="' + escapeHtml(rel) + '"');
                if (title) attrs.push(' title="' + title + '"');
            } else if (tag === 'table') {
                var tableWidth = sanitizeDimension(node.getAttribute('width'));
                var tableBorder = sanitizeNumber(node.getAttribute('border'));
                var tableCellpadding = sanitizeNumber(node.getAttribute('cellpadding'));
                var tableCellspacing = sanitizeNumber(node.getAttribute('cellspacing'));
                var tableStyle = sanitizeTableStyle(node.getAttribute('style'));

                if (tableWidth) attrs.push(' width="' + escapeHtml(tableWidth) + '"');
                if (tableBorder) attrs.push(' border="' + tableBorder + '"');
                if (tableCellpadding) attrs.push(' cellpadding="' + tableCellpadding + '"');
                if (tableCellspacing) attrs.push(' cellspacing="' + tableCellspacing + '"');
                if (tableStyle) attrs.push(' style="' + tableStyle + '"');
            } else if (tag === 'thead' || tag === 'tbody' || tag === 'tfoot' || tag === 'tr') {
                var groupStyle = sanitizeTableStyle(node.getAttribute('style'));
                if (groupStyle) attrs.push(' style="' + groupStyle + '"');
            } else if (tag === 'th' || tag === 'td') {
                var colspan = sanitizeNumber(node.getAttribute('colspan'));
                var rowspan = sanitizeNumber(node.getAttribute('rowspan'));
                var scope = sanitizeSimpleAttribute(node.getAttribute('scope'));
                var width = sanitizeDimension(node.getAttribute('width'));
                var height = sanitizeDimension(node.getAttribute('height'));
                var cellStyle = sanitizeTableStyle(node.getAttribute('style'));

                if (colspan) attrs.push(' colspan="' + colspan + '"');
                if (rowspan) attrs.push(' rowspan="' + rowspan + '"');
                if (scope && tag === 'th') attrs.push(' scope="' + scope + '"');
                if (width) attrs.push(' width="' + escapeHtml(width) + '"');
                if (height) attrs.push(' height="' + escapeHtml(height) + '"');
                if (cellStyle) attrs.push(' style="' + cellStyle + '"');
            } else if (tag === 'colgroup' || tag === 'col') {
                var span = sanitizeNumber(node.getAttribute('span'));
                var colWidth = sanitizeDimension(node.getAttribute('width'));

                if (span) attrs.push(' span="' + span + '"');
                if (colWidth) attrs.push(' width="' + escapeHtml(colWidth) + '"');
            }

            return '<' + tag + attrs.join('') + '>' + children + '</' + tag + '>';
        }

        return Array.prototype.map.call(doc.body.childNodes, sanitizeNode).join('');
    }

    function insertHtmlAtCursor(html) {
        if (!html) {
            return;
        }

        if (document.queryCommandSupported && document.queryCommandSupported('insertHTML')) {
            document.execCommand('insertHTML', false, html);
            return;
        }

        var selection = window.getSelection();
        if (!selection || !selection.rangeCount) {
            return;
        }

        var range = selection.getRangeAt(0);
        range.deleteContents();
        var fragment = range.createContextualFragment(html);
        range.insertNode(fragment);
    }

    function insertPlainTextAtCursor(text) {
        var normalized = escapeHtml(String(text || '')).replace(/\r\n|\r|\n/g, '<br>');
        insertHtmlAtCursor(normalized);
    }

    function createEditor(textarea) {
        if (!window.SUNEDITOR || textarea.dataset.suneditorReady === '1') {
            return;
        }

        var options = parseOptions(textarea.getAttribute('data-suneditor-options'));
        var defaultOptions = {
            minHeight: '240px',
            width: '100%',
            resizingBar: true,
            buttonList: [
                ['undo', 'redo'],
                ['formatBlock', 'bold', 'italic', 'underline'],
                ['list', 'link'],
                ['removeFormat', 'codeView'],
                ['fullScreen', 'showBlocks']
            ]
        };

        var editor = window.SUNEDITOR.create(textarea, Object.assign({}, defaultOptions, options));
        textarea.dataset.suneditorReady = '1';
        textarea._suneditor = editor;

        editor.onPaste = function (event, cleanData) {
            var clipboard = event && (event.clipboardData || window.clipboardData);
            var html = clipboard ? clipboard.getData('text/html') : '';
            var text = clipboard ? clipboard.getData('text/plain') : '';

            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            if (html || cleanData) {
                insertHtmlAtCursor(sanitizePastedHtml(html || cleanData || ''));
            } else {
                insertPlainTextAtCursor(text);
            }

            return false;
        };

        if (typeof editor.onChange === 'function') {
            var originalOnChange = editor.onChange;
            editor.onChange = function (contents) {
                textarea.value = contents;
                return originalOnChange.apply(this, arguments);
            };
        } else {
            editor.onChange = function (contents) {
                textarea.value = contents;
            };
        }

        var form = textarea.form;
        if (form && !form.dataset.suneditorBound) {
            form.dataset.suneditorBound = '1';
            form.addEventListener('submit', function () {
                form.querySelectorAll('textarea[data-suneditor="1"]').forEach(function (field) {
                    if (field._suneditor) {
                        syncEditor(field._suneditor, field);
                    }
                });
            });

            form.addEventListener('reset', function () {
                window.setTimeout(function () {
                    form.querySelectorAll('textarea[data-suneditor="1"]').forEach(function (field) {
                        if (field._suneditor && typeof field._suneditor.setContents === 'function') {
                            field._suneditor.setContents(field.value || '');
                        }
                    });
                }, 0);
            });
        }
    }

    function initSuneditors() {
        document.querySelectorAll('textarea[data-suneditor="1"]').forEach(createEditor);
    }

    document.addEventListener('DOMContentLoaded', initSuneditors);
    document.addEventListener('turbo:load', initSuneditors);
    initSuneditors();
})();
