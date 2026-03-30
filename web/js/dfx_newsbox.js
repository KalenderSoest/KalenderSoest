window.addEventListener("load", function() {
    dfxNewsboxSolo();
});

function dfxIsSameOrigin(targetUrl) {
    try {
        var u = new URL(targetUrl, window.location.href);
        return u.origin === window.location.origin;
    } catch (e) {
        return true;
    }
}

function dfxFetchText(url, options, success, fail) {
    var opts = options || {};
    if (!opts.credentials) {
        opts.credentials = dfxIsSameOrigin(url) ? 'same-origin' : 'omit';
    }
    fetch(url, opts)
        .then(function (res) {
            if (!res.ok) throw res;
            return res.text();
        })
        .then(function (data) { if (success) success(data); })
        .catch(function (err) { if (fail) fail(err); });
}

function dfxGet(url, query, success, fail) {
    var full = url;
    if (query) {
        full += (url.indexOf('?') >= 0 ? '&' : '?') + query;
    }
    dfxFetchText(full, {}, success, fail);
}

function dfxNewsboxSolo() {
    var box = document.getElementById('nfxbox') || document.getElementById('nffxbox');
    if (!box) return false;

    var solo = document.getElementById('dfx-newsbox-solo');
    if (!solo) {
        solo = document.createElement('div');
        solo.id = 'dfx-newsbox-solo';
        box.insertAdjacentElement('afterend', solo);
    }

    var dfxurl = box.getAttribute('data-dfx-url') || '';
    var kid = box.getAttribute('data-kid') || '';
    if (dfxurl.indexOf('http') === -1) {
        dfxurl = 'https://' + dfxurl;
    }

    var filter = '';
    var filterRubrik = box.getAttribute('data-rubrik');
    var filterRubrikAlt = box.getAttribute('data-dfx-rubrik');
    var valid = function (value) {
        return typeof value !== 'undefined' && value !== false && value !== null && value !== '' && value !== 'null';
    };

    if (valid(filterRubrik)) {
        filter = 'form%5Brubrik%5D=' + encodeURIComponent(filterRubrik);
    } else if (valid(filterRubrikAlt)) {
        filter = 'form%5Brubrik%5D=' + encodeURIComponent(filterRubrikAlt);
    }

    dfxGet(dfxurl + "/widgets/newsbox/" + kid, filter, function(data) {
        solo.innerHTML = data;
    });
    return true;
}
