var kid;
var url;
var filter = '';
var root;
var kalpath;
var frontendUrl = window.location.pathname;
var sign = '?';
var mapkey = '';
var map;
var marker = null;
var cChecked = false;
var pagetitle;
var $dfx;
var $datefix;
var $dfxKonf;
var $dfxKalender;
var $dfxTermine;
var $dfxDetailWrapper;
var $cCodeStatus;
var $filterForm;
var $setgeodata;
var $getgeodata;
var $formUmkreis;
var $formOrt;
var $formPlz;
var $formT;
var $formM;
var $formDatumVon;
var $formDatumBis;
var $formPills;
var $fbFrame;

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

var dfxJsonp = window.dfxJsonp || function (url, callbackName, success, fail) {
    var cbName = callbackName || ('dfx_jsonp_' + Date.now());
    var sep = url.indexOf('?') >= 0 ? '&' : '?';
    var src = url + sep + 'callback=' + encodeURIComponent(cbName);
    var script = document.createElement('script');
    script.src = src;
    script.async = true;
    var cleanup = function () {
        if (script.parentNode) script.parentNode.removeChild(script);
        try { delete window[cbName]; } catch (e) { window[cbName] = undefined; }
    };
    window[cbName] = function (data) {
        cleanup();
        if (success) success(data);
    };
    script.onerror = function (e) {
        cleanup();
        if (fail) fail(e);
    };
    document.head.appendChild(script);
};

var dfxFadeIn = window.dfxFadeIn || function (el, duration) {
    if (!el) return;
    var d = duration || 300;
    el.style.display = '';
    el.style.opacity = 0;
    el.style.transition = 'opacity ' + d + 'ms';
    requestAnimationFrame(function () { el.style.opacity = 1; });
    setTimeout(function () { el.style.transition = ''; }, d + 50);
};
function dfxCurlInit() {
    // Original-Seitentitel für Listenansichten
    pagetitle = document.title;
    $dfx = document.getElementById('dfx');
    $datefix = document.getElementById('datefix');
    $dfxKonf = document.getElementById('dfx_konf');
    $dfxKalender = document.getElementById('dfx-kalender');
    $dfxTermine = document.getElementById('dfx-termine');
    $dfxDetailWrapper = document.getElementById('dfx_detail_wrapper');
    $cCodeStatus = document.getElementById('cCodeStatus');
    $filterForm = document.getElementById('filter');
    $setgeodata = document.getElementById('setgeodata');
    $getgeodata = document.getElementById('getgeodata');
    $formUmkreis = document.getElementById('form_umkreis');
    $formOrt = document.getElementById('form_ort');
    $formPlz = document.getElementById('form_plz');
    $formT = document.getElementById('form_t');
    $formM = document.getElementById('form_m');
    $formDatumVon = document.getElementById('form_datum_von');
    $formDatumBis = document.getElementById('form_datum_bis');
    $formPills = document.getElementById('formpills');
    $fbFrame = document.getElementById('fbFrame');

    if (!$dfx) return;
    kid = $dfx.getAttribute('data-kid');
    url = $dfx.getAttribute('data-dfx-url');

    kalpath = '/kalender/' + kid;
    console.log("Kalender-Url: " + url + kalpath);
    if ($dfxTermine) $dfxTermine.style.display = '';
    document.querySelectorAll('.dfx-nav').forEach(function (el) { el.style.display = ''; });

    if (window.location.search) {
        var arrFilter = ['dfxid', 'dfxpath', 'dfxp', 'dfx-rubrik', 'rubrik', 'zielgruppe', 'ort', 'nat', 'plz', 'lokal', 'lid', 'veranstalter', 'vid', 'rid', 'filter1', 'filter2', 'filter3', 'filter4', 'filter5'];
        var hash;
        var hashes;
        if (window.location.href.indexOf('_escaped_fragment_') > 0) {
            hashes = window.location.href.slice(window.location.href.indexOf('_escaped_fragment_') + 19).split('&');
        } else if (window.location.href.indexOf('#!') > 0) {
            hashes = window.location.href.slice(window.location.href.indexOf('#!') + 2).split('&');
            console.log(hashes);
        } else {
            hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        }
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            if (hash[1] !== '' && hash[1] !== 'null') {
                if (arrFilter.indexOf(hash[0]) >= 0 && hash[0].indexOf('form') === -1) {
                    filter += '&form%5B' + hash[0] + '%5D=' + hash[1];
                } else if (arrFilter.indexOf(hash[0]) >= 0 && hash[0].indexOf('form') === 0) {
                    filter += '&' + hash[0] + '=' + hash[1];
                } else {
                    filter += '&' + hash[0] + '=' + hash[1];
                }
                console.log('Vars: ' + hash[0] + ' / ' + hash[1]);
                console.log("InArray: " + arrFilter.indexOf(hash[0]));
                console.log("form vorhanden Pos: " + hash[0].indexOf('form'));
            }
        }
        console.log('Filter: ' + filter);
    }

    // Kalender wird serverseitig gerendert, keine clientseitige Initialisierung mehr

    if ($dfxKalender) {
        if (filter !== '') {
            filter = filter.slice(1);
            console.log('Filter: ' + filter);
        }
        dfxGet(url + "/js/kalender/widget/" + kid, filter, function (data) {
            dfxWidgetKalender(data);
        }, function (err) {
            alert('Ein Fehler (' + err + ') verhindert das Laden des Navigationselements. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx03');
        });
    }

    // Löschfunktion
    if ($datefix) {
        $datefix.addEventListener('click', function (event) {
            var del = event.target.closest('.delete');
            if (!del) return;
            if (!confirm(del.getAttribute('title') + '?')) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    // Lade Karte in Detailansicht
    if ((!document.querySelector('.dfx-map-open') || document.querySelector('.dfx-map-open-self')) && $dfxDetailWrapper && $dfxDetailWrapper.getAttribute('data-bg') > 0) {
        showKarte('', $dfxDetailWrapper.getAttribute('data-bg'), $dfxDetailWrapper.getAttribute('data-lg'), $dfxDetailWrapper.getAttribute('data-lokal'));
    }

    // Formulare aus der Detailseite
    if ($datefix) {
        $datefix.addEventListener("change", function (event) {
            var target = event.target;
            if (!target || !target.matches('#cCode input')) return;
            var arFormname = (target.getAttribute('id') || '').split('_');
            var formname = arFormname[0];
            var cCodeEl = document.getElementById(formname + "_cCode");
            var keyEl = document.getElementById(formname + "_key");
            var cCode = cCodeEl ? cCodeEl.value : '';
            var key = keyEl ? keyEl.value : '';
            cChecked = 'busy';
            if ($cCodeStatus) {
                $cCodeStatus.innerHTML = '<div style="text-align:center;"><img src="' + url + '/images/loader.gif" alt="Loader"><br>überprüfe Sicherheitscode</div>';
                dfxFadeIn($cCodeStatus);
            }
            fetch(url + "/js/kalender/check/" + cCode + "/" + key, { credentials: 'same-origin' })
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    if (data === 'ok') {
                        cChecked = data;
                        if ($cCodeStatus) {
                            $cCodeStatus.innerHTML = '<div style="text-align:center;">Sicherheitscode OK - Bitte Absendebutton betätigen</div>';
                            dfxFadeIn($cCodeStatus);
                        }
                    } else {
                        cChecked = 'error';
                        if ($cCodeStatus) {
                            $cCodeStatus.innerHTML = '<div style="text-align:center; color: red">Sicherheitscode falsch</div>';
                            dfxFadeIn($cCodeStatus);
                        }
                    }
                })
                .catch(function (err) {
                    alert("Fehler in Abfrage Sicherheitscode " + err);
                    cChecked = 'error';
                    if ($cCodeStatus) {
                        $cCodeStatus.innerHTML = '<div style="text-align:center; color: red">Sicherheitscode falsch</div>';
                        dfxFadeIn($cCodeStatus);
                    }
                });
        });
    }

    // Submit der Formulare
    var formSubmitHandler = function (event) {
        var form = event.target.closest('form');
        if (!form) return;
        var action = form.getAttribute('action') || '';
        if (action.indexOf('http') !== -1) {
            return true;
        }
        if (form.getAttribute('id') !== 'filter') {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'dfxpath';
            input.value = action;
            form.appendChild(input);
        }
        form.setAttribute('action', frontendUrl);
        return true;
    };
    if ($datefix) $datefix.addEventListener('submit', formSubmitHandler);
    if ($dfxKalender) $dfxKalender.addEventListener('submit', formSubmitHandler);

    // Reset der Suchmaske
    var resetHandler = function (event) {
        var form = event.target.closest('#filter');
        if (!form) return;
        event.preventDefault();
        clearFormInputs(form);
        console.log('reset');
        window.location.href = frontendUrl;
        return false;
    };
    if ($datefix) $datefix.addEventListener('reset', resetHandler);
    if ($dfxKalender) $dfxKalender.addEventListener('reset', resetHandler);

    if ($datefix) {
        $datefix.addEventListener("click", function (event) {
            var back = event.target.closest('.back-to-list');
            if (!back) return;
            event.preventDefault();
            history.back();
            console.log("Gehe Seite zurück");
            return false;
        });
    }

    if ($datefix) {
        $datefix.addEventListener("click", function (event) {
            var link = event.target.closest('#dfx-termine a');
            if (!link) return;
            var href = link.getAttribute('href') || '';
            if (link.classList.contains('dfx-map-open')) {
                event.preventDefault();
                showKarte(link.getAttribute('data-tid'), link.getAttribute('data-bg'), link.getAttribute('data-lg'), link.getAttribute('data-lokal'));
                return false;
            } else if (link.classList.contains('dfx-map-close')) {
                window.addEventListener('gMapsLoaded', function () {
                    closeKarte(link.getAttribute('data-tid'));
                });
            } else if (link.getAttribute('id') === 'getgeodata') {
                event.preventDefault();
                showAddress(link.getAttribute('data-feldstrasse'), link.getAttribute('data-formname'));
                return false;
            } else if (link.getAttribute('id') === 'setgeodata') {
                event.preventDefault();
                showKarteSet(link.getAttribute('data-formname'));
                return false;
            } else if (link.classList.contains('dfx-pdf')) {
                return true;
            } else if (link.classList.contains('dfx-print')) {
                event.preventDefault();
                dfxGet(href, false, function (data) {
                    printDatefix(data);
                }, function (err) {
                    alert('Ein Fehler  (' + err + ') verhindert das Laden Druckversion. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx_print 01');
                });
                return false;
            } else if (href.indexOf('dfxid=') > 0) {
                var titel = '';
                var dfxTitel = document.querySelector('.dfx-titel');
                var dfxDetailOrt = document.querySelector('.dfx-detail-ort');
                var dfxDetailLokal = document.querySelector('.dfx-detail-lokal');
                if (dfxTitel) {
                    titel += dfxTitel.textContent;
                }
                if (dfxDetailOrt) {
                    titel += ' | ' + dfxDetailOrt.textContent;
                }
                if (dfxDetailLokal) {
                    titel += ' | ' + dfxDetailLokal.textContent;
                }
            } else {
                // keine AJAX-Detailansicht in curl
            }
            return true;
        });
    }

    var responsiveCalendarHandler = function (event) {
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
    };
    if ($datefix) $datefix.addEventListener('click', responsiveCalendarHandler);
    if ($dfxKalender) $dfxKalender.addEventListener('click', responsiveCalendarHandler);

    if ($datefix) {
        $datefix.addEventListener('change', function (event) {
            var target = event.target;
            if (!target || !target.matches('#termine_ort')) return;
            if ($setgeodata) {
                if ($getgeodata) {
                    showAddress($getgeodata.getAttribute('data-feldstrasse'), $getgeodata.getAttribute('data-formname'));
                }
                return false;
            }
        });
    }

    if ($formUmkreis) {
        var formOrtPlzHandler = function (event) {
            var target = event.target;
            if (!target || !(target.matches('#form_ort') || target.matches('#form_plz'))) return;
            if ($formUmkreis.value.length > 0) {
                showAddress(null, 'form');
            }
        };
        if ($datefix) $datefix.addEventListener('change', formOrtPlzHandler);
        if ($dfxKalender) $dfxKalender.addEventListener('change', formOrtPlzHandler);
        if ($datefix) {
            $datefix.addEventListener('focus', function (event) {
                var target = event.target;
                if (!target || !target.matches('#form_umkreis')) return;
                if (($formPlz && $formPlz.value.length > 0) || ($formOrt && $formOrt.value.length > 0)) {
                    showAddress(null, 'form');
                }
            }, true);
        }
    }

    if ($formPills) {
        $formPills.addEventListener('click', function (e) {
            var link = e.target.closest('a');
            if (!link) return;
            e.preventDefault();
            console.log('Einzel-Serientermin');
            if (window.bootstrap && window.bootstrap.Tab) {
                window.bootstrap.Tab.getOrCreateInstance(link).show();
            }
        });
    }

    if ($datefix) {
        $datefix.addEventListener('click', function (e) {
            var link = e.target.closest('.sm-link');
            if (!link) return;
            document.querySelectorAll('.sm-box .sm-buttons').forEach(function (el) { el.style.display = 'none'; });
            var box = link.closest('.sm-box');
            if (box) {
                var buttons = box.querySelector('.sm-buttons');
                if (buttons) buttons.style.display = 'block';
            }
            if ($fbFrame) $fbFrame.setAttribute('src', url + kalpath + '/fb/' + link.getAttribute('data-tid'));
            e.stopPropagation();
        });

        $datefix.addEventListener('click', function (e) {
            var buttons = e.target.closest('.sm-buttons');
            if (buttons) e.stopPropagation();
        });
    }

    document.addEventListener('click', function () {
        document.querySelectorAll(".sm-buttons").forEach(function (el) { el.style.display = 'none'; });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', dfxCurlInit);
} else {
    dfxCurlInit();
}

function jsonTage() {
    return true;
}

function dfxWidgetKalender(data) {
    if ($dfxKalender) $dfxKalender.innerHTML = data;
    return true;
}

function closeKarte(tid) {
    var mapEl = document.getElementById("dfxMap" + tid);
    var closeEl = document.getElementById("dfxMapClose" + tid);
    if (mapEl) mapEl.style.display = 'none';
    if (closeEl) closeEl.style.display = 'none';
    return true;
}

var win = null;
function printDatefix(printContent) {
    win = window.open();
    self.focus();
    win.document.open();
    win.document.write('<' + 'html' + ' lang="de"><' + 'head><' + 'style>');
    win.document.write('body, td { font-family: Verdana; font-size: 12pt;}');
    win.document.write('<' + '/style><' + '/head><' + 'body>');
    win.document.write(printContent);
    win.document.write('<' + '/body><' + '/html>');
    win.document.close();
    win.print();
    win.close();
}

function getTermineTag(jahr, monat, tag) {
    if ($formT) $formT.value = jahr + "-" + monat + "-" + tag;
    window.location.href = frontendUrl + '?' + ($filterForm ? serializeForm($filterForm) : '');
    if ($formT) $formT.value = '';
    return true;
}

function getTermineMonat(jahr, monat) {
    if ($formM) $formM.value = jahr + "-" + monat;
    if ($formT) $formT.value = '';
    window.location.href = frontendUrl + '?' + ($filterForm ? serializeForm($filterForm) : '');
    if ($formM) $formM.value = '';
    return true;
}

// Calendar plugin removed (serverseitiges Rendering)
