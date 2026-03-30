var kid;
var url;
var root;
var rooturl;
var kalpath;
var frontendUrl;
var filterUrl = '';
var sign = '?';
var scriptloaded = false;

function serializeForm(form) {
    if (!form) return '';
    var params = new URLSearchParams();
    new FormData(form).forEach(function (value, key) {
        if (typeof value === 'string' && value === '') {
            return;
        }
        params.append(key, value);
    });
    return params.toString();
}

function clearFormInputs(form) {
    if (!form) return;
    var inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(function (el) {
        var type = (el.getAttribute('type') || '').toLowerCase();
        if (type === 'button' || type === 'submit' || type === 'reset' || type === 'hidden' || type === 'checkbox' || type === 'radio') {
            return;
        }
        el.value = '';
    });
}

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

function dfxWidgetKalender(data, filter) {
    var kalender = document.getElementById('dfx-kalender');
    if (kalender) {
        kalender.innerHTML = data;
    }
    var responsive = document.querySelector('.responsive-calendar');
    if (responsive) {
        var cssFile = responsive.getAttribute('data-cssfile');
        if (cssFile) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url + '/css/' + cssFile;
            document.head.appendChild(link);
        }
    }
    var konf = document.getElementById('dfx_konf');
    frontendUrl = konf ? konf.getAttribute('data-frontend') : frontendUrl;
    if (frontendUrl) {
        sign = frontendUrl.indexOf('?') > -1 ? '&' : '?';
    }
    return true;
}

function getTermineTag(jahr, monat, tag) {
    var formT = document.getElementById('form_t');
    if (formT) formT.value = jahr + "-" + monat + "-" + tag;
    var filter = document.getElementById('filter');
    var query = filter ? serializeForm(filter) : '';
    if (frontendUrl) {
        window.location = frontendUrl + sign + "cb=all&" + query;
    }
}

function getTermineMonat(jahr, monat) {
    var formM = document.getElementById('form_m');
    var formT = document.getElementById('form_t');
    if (formM) formM.value = jahr + "-" + monat;
    if (formT) formT.value = '';
    var filter = document.getElementById('filter');
    var query = filter ? serializeForm(filter) : '';
    if (frontendUrl) {
        window.location = frontendUrl + sign + "cb=all&" + query;
    }
}

// Kalender wird serverseitig gerendert, keine clientseitige Initialisierung mehr

function dfxWidgetsInit() {
    var widgets = document.getElementById('dfx-widgets');
    if (!widgets) return;

    if (widgets.getAttribute('data-kid') > 0) {
        kid = widgets.getAttribute('data-kid');
    } else {
        kid = widgets.getAttribute('kid');
    }
    url = widgets.getAttribute('data-dfx-url');
    if (url.indexOf('://') !== -1) {
        var arUrl = url.split('://');
        if (arUrl[1].indexOf('/') > 0) {
            root = arUrl[0] + '://' + arUrl[1].substr(0, arUrl[1].indexOf('/'));
        } else {
            root = url;
        }
    } else {
        if (url.indexOf('/') > 0) {
            root = url.substr(0, url.indexOf('/'));
        } else {
            root = url;
        }
        if (url.indexOf('http') === -1) {
            url = 'http://' + url;
            root = 'http://' + root;
        }
    }
    console.log("Root: " + root);
    kalpath = '/js/kalender/' + kid;

    var filter = '';
    var filterQ = '?';
    var filterRubrikAlt = widgets.getAttribute('data-dfx-rubrik');
    var filterRubrik = widgets.getAttribute('data-rubrik');
    var filterZielgruppe = widgets.getAttribute('data-zielgruppe');
    var filterOrt = widgets.getAttribute('data-ort');
    var filterNat = widgets.getAttribute('data-nat');
    var filterPlz = widgets.getAttribute('data-plz');
    var filterLokal = widgets.getAttribute('data-lokal');
    var filterIdLocation = widgets.getAttribute('data-lid');
    var filterVeranstalter = widgets.getAttribute('data-veranstalter');
    var filterIdVeranstalter = widgets.getAttribute('data-vid');
    var filterIdRegion = widgets.getAttribute('data-rid');

    var valid = function (value) {
        return typeof value !== 'undefined' && value !== false && value !== null && value !== '' && value !== 'null';
    };

    if (valid(filterRubrik)) {
        filter = '&form%5Brubrik%5D=' + encodeURIComponent(filterRubrik);
    } else if (valid(filterRubrikAlt)) {
        filter = '&form%5Brubrik%5D=' + encodeURIComponent(filterRubrikAlt);
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
        filterQ = '?' + filter.substr(1) + '&';
        filterUrl = '?' + filter.substr(1);
    } else {
        filterUrl = '';
    }
    var ziel = url + kalpath + filterUrl;
    console.log("Ziel: " + ziel);

    if (filter !== '') {
        filter = filter.substr(1);
    }

    var kalenderEl = document.getElementById('dfx-kalender');
    if (kalenderEl) {
        dfxGet(url + "/js/kalender/widget/" + kid, filter, function (data) {
            dfxWidgetKalender(data, filter);
        }, function (err) {
            alert("Ein Fehler verhindert das Laden des Navigationselements. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer. " + err);
        });
    }

    var terminboxEl = document.getElementById('dfx-terminbox');
    if (terminboxEl && typeof dfxurl !== 'undefined') {
        dfxGet(dfxurl + "/widgets/terminbox/" + kid, filter, function (data) {
            terminboxEl.innerHTML = data;
        }, function (err) {
            alert("Ein Fehler verhindert das Laden des Vorschauliste. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe des Fehlers. " + err);
        });
    }

    if (kalenderEl) {
        kalenderEl.addEventListener('click', function (event) {
            var link = event.target.closest('.responsive-calendar a');
            if (!link) return;

            var go = link.getAttribute('data-go');
            var year = link.getAttribute('data-year');
            var month = link.getAttribute('data-month');
            var day = link.getAttribute('data-day');

            if (go && year && month) {
                event.preventDefault();
                getTermineMonat(year, month);
                return;
            }

            if (day && year && month) {
                event.preventDefault();
                getTermineTag(year, month, day);
            }
        });

        kalenderEl.addEventListener('submit', function (event) {
            var form = event.target.closest('#filter');
            if (!form) return;
            event.preventDefault();
            if (frontendUrl) {
                window.location = frontendUrl + sign + "cb=all&" + serializeForm(form);
            }
        });
    }

    document.addEventListener('reset', function (event) {
        var form = event.target.closest('#filter');
        if (!form) return;
        if (!(form.closest('#datefix') || form.closest('#dfx-kalender'))) return;
        event.preventDefault();
        clearFormInputs(form);
        var dfxquery = kalpath;
        var pagequery = frontendUrl;
        console.log('reset');
        return false;
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', dfxWidgetsInit);
} else {
    dfxWidgetsInit();
}

function addScript(file, callback) {
    var head = document.getElementsByTagName("head")[0];
    var script = document.createElement('script');
    script.src = file;
    script.type = 'text/javascript';
    //real browsers
    if (callback) {
        script.onload = callback;
        //Internet explorer
        script.onreadystatechange = function () {
            if (this.readyState === 'complete') {
                callback();
            }
        };
    }
    head.appendChild(script);
}
// Calendar plugin removed (serverseitiges Rendering)
