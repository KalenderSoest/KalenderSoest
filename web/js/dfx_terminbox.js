window.addEventListener("load", function() {
    dfxTerminboxSolo();
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

function dfxTerminboxSolo(){
    var box = document.getElementById('dfxbox');
    if (!box) return false;

    var solo = document.getElementById('dfx-terminbox-solo');
    if (!solo) {
        solo = document.createElement('div');
        solo.id = 'dfx-terminbox-solo';
        box.insertAdjacentElement('afterend', solo);
    }

    var dfxurl = box.getAttribute('data-dfx-url') || '';
    var kid = box.getAttribute('data-kid') || '';
    if (dfxurl.indexOf('http') === -1) {
        dfxurl = 'https://' + dfxurl;
    }

    var filter = '';
    var filterRubrik = box.getAttribute('data-rubrik');
    var filterOrt = box.getAttribute('data-ort');
    var filterNat = box.getAttribute('data-nat');
    var filterPlz = box.getAttribute('data-plz');
    var filterLokal = box.getAttribute('data-lokal');
    var filterIdLocation = box.getAttribute('data-lid');
    var filterVeranstalter = box.getAttribute('data-veranstalter');
    var filterIdVeranstalter = box.getAttribute('data-vid');
    var filterIdRegion = box.getAttribute('data-rid');
    var filterZielgruppe = box.getAttribute('data-zielgruppe');

    var valid = function (value) {
        return typeof value !== 'undefined' && value !== false && value !== null && value !== '' && value !== 'null';
    };

    if (valid(filterRubrik)) {
        filter = '&form%5Brubrik%5D=' + encodeURIComponent(filterRubrik);
    }
    if (valid(filterZielgruppe)) {
        filter += '&form%5Bzielgruppe%5D=' + encodeURIComponent(filterZielgruppe);
    }
    if (valid(filterOrt)) {
        filter += '&form%5Bort%5D=' + encodeURIComponent(filterOrt);
    }
    if (valid(filterNat)) {
        filter += '&form%5Bnat%5D=' + encodeURIComponent(filterNat);
    }
    if (valid(filterPlz)) {
        filter += '&form%5Bplz%5D=' + encodeURIComponent(filterPlz);
    }
    if (valid(filterLokal)) {
        filter += '&form%5Blokal%5D=' + encodeURIComponent(filterLokal);
    }
    if (valid(filterIdLocation)) {
        filter += '&form%5BidLocation%5D=' + encodeURIComponent(filterIdLocation);
    }
    if (valid(filterVeranstalter)) {
        filter += '&form%5Bveranstalter%5D=' + encodeURIComponent(filterVeranstalter);
    }
    if (valid(filterIdVeranstalter)) {
        filter += '&form%5BidVeranstalter%5D=' + encodeURIComponent(filterIdVeranstalter);
    }
    if (valid(filterIdRegion)) {
        filter += '&form%5Bregion%5D=' + encodeURIComponent(filterIdRegion);
    }

    if (filter !== '') {
        filter = filter.substr(1);
    }

    dfxGet(dfxurl + "/widgets/terminbox/" + kid, filter, function(data) {
        solo.innerHTML = data;
    });
    return true;
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
