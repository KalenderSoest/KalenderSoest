var kid;
var url;
var root;
var kalpath;
var frontendUrlBase = window.location.pathname + window.location.search;
var frontendHash = window.location.hash || '';
var filterUrl = '';
var cChecked = false;
var lengthChecked = true;
var abstand;
var pagetitle;
var scriptloaded = false;
var dfxShowNewsDefault = false;
var dfxquery;
var pagequery;
var $dfxKonf;
var $dfxDetailWrapper;
var $dfxWrapper;
var $dfxTermine;
var $dfxTitel;
var $dfxKalender
var $dfx;
var $datefix
var $getgeodata;
var dfxTinymceLoading = false;
var dfxTinymceInited = false;

function dfxLoadScriptOnce(src, opts, cb) {
	if (!src) { if (cb) cb(); return; }
	var existing = document.querySelector('script[data-dfx-src="' + src + '"]');
	if (existing) { if (cb) cb(); return; }
	var script = document.createElement('script');
	script.src = src;
	script.async = true;
	if (opts && opts.type) script.type = opts.type;
	script.setAttribute('data-dfx-src', src);
	script.onload = function () { if (cb) cb(); };
	script.onerror = function () { if (cb) cb(new Error('load failed: ' + src)); };
	document.head.appendChild(script);
}

function dfxEnsureTinymce() {
	var hasEditor = document.querySelector('tinymce-editor');
	if (!hasEditor) return;
	if (window.customElements && window.customElements.get('tinymce-editor')) return;
	if (dfxTinymceLoading) return;
	dfxTinymceLoading = true;
	var base = url || window.location.origin || '';
	var baseOrigin = base;
	try { baseOrigin = new URL(base, window.location.href).origin; } catch (e) {}
	var sameOrigin = baseOrigin === window.location.origin;
	var tinymceMin = base + '/bundles/tinymce/ext/tinymce/tinymce.min.js';
	var tinymceSrc = base + '/bundles/tinymce/ext/tinymce/tinymce.js';
	var webcomponentSrc = base + '/bundles/tinymce/ext/tinymce-webcomponent.js';
	window.tinymceAdditionalConfig = window.tinymceAdditionalConfig || {};

	if (!sameOrigin) {
		// Fallback for cross-origin: replace <tinymce-editor> with <textarea> and init classic TinyMCE
		dfxLoadScriptOnce(tinymceMin, {}, function (err) {
			var loadClassic = function () {
				dfxLoadScriptOnce(tinymceSrc, {}, function () {
					dfxInitTinymceClassic();
					dfxTinymceLoading = false;
				});
			};
			if (err) {
				loadClassic();
				return;
			}
			dfxInitTinymceClassic();
			dfxTinymceLoading = false;
		});
		return;
	}

	dfxLoadScriptOnce(tinymceMin, {}, function (err) {
		if (err) {
			dfxLoadScriptOnce(tinymceSrc, {}, function () {
				dfxLoadScriptOnce(webcomponentSrc, { type: 'module' }, function () {
					dfxTinymceLoading = false;
				});
			});
			return;
		}
		dfxLoadScriptOnce(webcomponentSrc, { type: 'module' }, function () {
			dfxTinymceLoading = false;
		});
	});
}

function dfxInitTinymceClassic() {
	if (dfxTinymceInited) return;
	if (!window.tinymce) return;
	var editors = Array.prototype.slice.call(document.querySelectorAll('tinymce-editor'));
	if (!editors.length) return;
	editors.forEach(function (el) {
		if (el.getAttribute('data-dfx-tinymce') === '1') return;
		var textarea = document.createElement('textarea');
		var id = el.getAttribute('id');
		if (id) textarea.id = id;
		var name = el.getAttribute('name');
		if (name) textarea.name = name;
		var cls = el.getAttribute('class');
		if (cls) textarea.className = cls;
		textarea.value = el.textContent || '';
		el.setAttribute('data-dfx-tinymce', '1');
		el.parentNode.replaceChild(textarea, el);

		var cfg = window.tinymceAdditionalConfig ? Object.assign({}, window.tinymceAdditionalConfig) : {};
		var plugins = textarea.getAttribute('plugins') || el.getAttribute('plugins');
		var menubar = textarea.getAttribute('menubar') || el.getAttribute('menubar');
		var toolbar = textarea.getAttribute('toolbar') || el.getAttribute('toolbar');
		var height = textarea.getAttribute('height') || el.getAttribute('height');
		var skin = textarea.getAttribute('skin') || el.getAttribute('skin');

		if (plugins) cfg.plugins = plugins;
		if (typeof menubar !== 'undefined' && menubar !== null) cfg.menubar = menubar === 'true';
		if (toolbar) cfg.toolbar = toolbar;
		if (height) cfg.height = height;
		if (skin) cfg.skin = skin;
		cfg.target = textarea;
		window.tinymce.init(cfg);
	});
	dfxTinymceInited = true;
}

function dfxCancelFade(el) {
    if (!el) return;
    if (el._dfxFadeTimer) {
        clearTimeout(el._dfxFadeTimer);
        el._dfxFadeTimer = null;
    }
    el.style.display = 'block';
    el.style.opacity = 1;
    el.style.visibility = 'visible';
    el.style.transition = '';
}

var buildFrontendUrl = function(query) {
    var base = frontendUrlBase;
    var path = base;
    var paramsStr = '';
    if (base.indexOf('?') >= 0) {
        path = base.slice(0, base.indexOf('?'));
        paramsStr = base.slice(base.indexOf('?') + 1);
    }
    var params = {};
    var addParams = function(str) {
        if (!str) return;
        var s = str;
        if (s.charAt(0) === '?' || s.charAt(0) === '&') {
            s = s.slice(1);
        }
        if (!s.length) return;
        var parts = s.split('&');
        for (var i = 0; i < parts.length; i++) {
            if (!parts[i]) continue;
            var kv = parts[i].split('=');
            var k = kv[0];
            if (!k) continue;
            var v = kv.slice(1).join('=');
            if (v === '' && k === 'nfx') {
                v = 'true';
            }
            params[k] = v;
        }
    };
    addParams(paramsStr);
    addParams(query);
    var out = [];
    for (var key in params) {
        if (!params.hasOwnProperty(key)) continue;
        if (params[key] === '') {
            out.push(key);
        } else {
            out.push(key + '=' + params[key]);
        }
    }
    return path + (out.length ? '?' + out.join('&') : '') + frontendHash;
};

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

function extractFrontendDfxPath(href) {
    if (!href) return null;
    var match = href.match(/[?&]dfxpath=([^&]+)/);
    if (!match || !match[1]) return null;
    try {
        return decodeURIComponent(match[1]);
    } catch (e) {
        return match[1];
    }
}

function resolveStateUrl(fe) {
    if (!fe) return fe;
    if (fe.indexOf('http://') === 0 || fe.indexOf('https://') === 0) {
        return fe;
    }
    if (fe.indexOf('/js/') === 0) {
        return url + fe;
    }
    if (fe.indexOf('/') === 0) {
        return root + fe;
    }
    return url + '/' + fe;
}

function buildNewsFrontendUrl(query) {
    var cleanQuery = query || '';
    if (cleanQuery.indexOf('nfx&') === 0) {
        cleanQuery = cleanQuery.slice(4);
    } else if (cleanQuery === 'nfx') {
        cleanQuery = '';
    }
    if (cleanQuery.charAt(0) === '&' || cleanQuery.charAt(0) === '?') {
        cleanQuery = cleanQuery.slice(1);
    }
    return root + '/news/' + kid + (cleanQuery ? '?' + cleanQuery : '') + frontendHash;
}

function dfxIsSameOrigin(targetUrl) {
    try {
        var u = new URL(targetUrl, window.location.href);
        return u.origin === window.location.origin;
    } catch (e) {
        return true;
    }
}

function dfxPushState(state, title, targetUrl) {
    if (!history.pushState || !targetUrl) {
        return false;
    }
    try {
        var resolved = new URL(targetUrl, window.location.href);
        if (resolved.origin !== window.location.origin) {
            return false;
        }
        history.pushState(state, title || '', resolved.pathname + resolved.search + resolved.hash);
        return true;
    } catch (e) {
        try {
            if (typeof targetUrl === 'string' && targetUrl.charAt(0) === '/') {
                history.pushState(state, title || '', targetUrl);
                return true;
            }
        } catch (ignoreError) {
        }
        return false;
    }
}

function dfxReplaceState(state, title, targetUrl) {
    if (!history.replaceState || !targetUrl) {
        return false;
    }
    try {
        var resolved = new URL(targetUrl, window.location.href);
        if (resolved.origin !== window.location.origin) {
            return false;
        }
        history.replaceState(state, title || '', resolved.pathname + resolved.search + resolved.hash);
        return true;
    } catch (e) {
        try {
            if (typeof targetUrl === 'string' && targetUrl.charAt(0) === '/') {
                history.replaceState(state, title || '', targetUrl);
                return true;
            }
        } catch (ignoreError) {
        }
        return false;
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

function dfxPost(url, method, body, success, fail) {
    dfxFetchText(url, { method: method || 'POST', body: body }, success, fail);
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
    if (el._dfxFadeTimer) {
        clearTimeout(el._dfxFadeTimer);
        el._dfxFadeTimer = null;
    }
    var d = duration || 300;
    el.style.display = 'block';
    el.style.opacity = 0;
    el.style.transition = 'opacity ' + d + 'ms';
    requestAnimationFrame(function () { el.style.opacity = 1; });
    el._dfxFadeTimer = setTimeout(function () {
        el.style.opacity = 1;
        el.style.visibility = 'visible';
        el.style.transition = '';
        el._dfxFadeTimer = null;
    }, d + 50);
};
function addClasses(el, cls) {
    if (!el || !cls) return;
    cls.split(' ').forEach(function (c) { if (c) el.classList.add(c); });
}

function removeClasses(el, cls) {
    if (!el || !cls) return;
    cls.split(' ').forEach(function (c) { if (c) el.classList.remove(c); });
}

function ensureVisible(el) {
	if (!el) return;
	var display = window.getComputedStyle ? window.getComputedStyle(el).display : el.style.display;
	if (display === 'none') {
		el.style.display = 'block';
	}
	el.style.opacity = 1;
}

function scrollToDatefix() {
	if (!$datefix) return;
	var top = $datefix.getBoundingClientRect().top + window.pageYOffset - abstand;
	try {
		window.scrollTo({ top: top, behavior: 'smooth' });
	} catch (e) {
		window.scrollTo(0, top);
	}
}

function dfxAjaxInit() {
    if (window.__dfxAjaxInit) {
        return;
    }
    window.__dfxAjaxInit = true;
    // Original-Seitentitel für Listenansichten
    pagetitle = document.title;
    $dfx = document.getElementById('dfx');
    $datefix = document.getElementById('datefix');
	$dfxKalender = document.getElementById('dfx-kalender');


    // Überprüfe Browserfunktionen
    if(!history.pushState) {
         alert('Ihr Browser unterstützt einige aktuelle HTML5-Spezifikationen nicht. Die Navigation innerhalb des Veranstaltungskalenders ist deshalb nur eingeschränkt möglich');
    }


	kid = $dfx.getAttribute('data-kid');
    dfxShowNewsDefault = (($dfx.getAttribute('data-nfx') || '').toLowerCase() === 'true');
    if($dfx.getAttribute('data-abstand') > 0){
        abstand = $dfx.getAttribute('data-abstand');
    }else{
        abstand = 225;
    }
	url = $dfx.getAttribute('data-dfx-url');

    if(url.indexOf('://') !== -1){
        var arUrl = url.split('://');
        if(arUrl[1].indexOf('/') > 0){
            root = arUrl[0]+'://'+arUrl[1].slice(0,arUrl[1].indexOf('/'));
        }else{
            root = url;
        }
    }else{
        if(url.indexOf('/') > 0){
            root = url.slice(0,url.indexOf('/'));
        }else{
            root = url;
        }
        if(url.indexOf('http') === -1){
            url = 'http://'+url;
            root = 'http://'+root;
        }
    }
    kalpath = dfxShowNewsDefault ? '/js/news/' + kid : '/js/kalender/'+kid;
    if($datefix){
        // Funktionen nur Frontend
        var ziel='';
        var filter = '';
        var filterQ = '?';

        // checke Filter im Einbaucode auf Eintraege
        var filterSpecs = [
            { attr: 'data-rubrik', key: 'rubrik' },
            { attr: 'data-zielgruppe', key: 'zielgruppe' },
            { attr: 'data-ort', key: 'ort' },
            { attr: 'data-nat', key: 'nat' },
            { attr: 'data-plz', key: 'plz' },
            { attr: 'data-lokal', key: 'lokal' },
            { attr: 'data-lid', key: 'idLocation' },
            { attr: 'data-veranstalter', key: 'veranstalter' },
            { attr: 'data-vid', key: 'idVeranstalter' },
            { attr: 'data-rid', key: 'region' }
        ];

        for (var i = 0; i < filterSpecs.length; i++) {
            var spec = filterSpecs[i];
            var value = $dfx.getAttribute(spec.attr);
            if (typeof value === typeof undefined || value === false || value === null || value === 'null') {
                continue;
            }
            var part = '&form%5B' + spec.key + '%5D=' + encodeURIComponent(value);
            filter += part;
        }
        if(window.location.search){
            var arrFilter = ['dfxid', 'dfxpath', 'dfxp', 'nfx', 'nfxid', 'nfxp', 'rubrik','zielgruppe', 'ort', 'nat', 'plz', 'lokal', 'lid', 'veranstalter', 'vid', 'rid','filter1','filter2','filter3','filter4','filter5'];
            var href = window.location.href;
            if(href.indexOf('_escaped_fragment_') > 0){
                hashes = href.slice(href.indexOf('_escaped_fragment_') + 19).split('&');
            }else if(href.indexOf('#!') > 0){
                hashes = href.slice(href.indexOf('#!') + 2).split('&');
            }else{
                hashes = href.slice(href.indexOf('?') + 1).split('&');
            }

            var rebuildFilterUrl = function() {
                filterUrl = '?' + filter.slice(1);
            };
            var rebuildZielFromFilter = function() {
                ziel = url + kalpath + '?' + decodeURIComponent(filter.slice(1));
            };

            for(var i = 0; i < hashes.length; i++){
                hash = hashes[i].split('=');
                if(hash[1] !== '' && hash[1] !== 'null'){
                    var key = hash[0];
                    var val = hash[1];
                    if (key === 'nfx' && (typeof val === 'undefined' || val === '')) {
                        val = 'true';
                    }
                    var inFilterList = arrFilter.indexOf(key) >= 0;
                    var isFormParam = key.indexOf('form') === 0;

                    // pruefe ob Queryteil von Datefix generiert wurde - Fremdquery sollen immer vorne stehen
                    if(!inFilterList && key.indexOf('form') === -1){
                        if (ziel === '' || ziel.indexOf('/js/news/') === -1) {
                            ziel = url + kalpath + decodeURIComponent(filterUrl);
                        }
                        filter += '&' + key + '=' + val;
                        rebuildFilterUrl();
                        if (ziel.indexOf('/js/news/') > -1 && ziel.indexOf('/detail/') === -1) {
                            ziel = url + '/js/news/' + kid + decodeURIComponent(filterUrl);
                        }
                    }else if(key === 'dfxid'){
                        if(filter !== ''){
                            filterQ = '?' + filter.slice(1) + '&';
                            filterUrl = filterQ + 'dfxid=' + val;
                        }else{
                            filterUrl = '?' + 'dfxid=' + val;
                        }
                        ziel = url + kalpath + '/detail/' + val;
                    }else if(key === 'dfxpath'){
                        if(filter !== ''){
                            filterQ = '?' + filter.slice(1) + '&';
                            filterUrl = filterQ + 'dfxpath=' + val;
                        }else{
                            filterUrl = '?' + 'dfxpath=' + val;
                        }
                        ziel = root + decodeURIComponent(val);
                    }else if(key === 'dfxp'){
                        if(filter !== ''){
                            filterQ = '?' + filter.slice(1) + '&';
                            filterUrl = filterQ + 'dfxp=' + val;
                        }else{
                            filterUrl = '?' + 'dfxp=' + val;
                        }
                        ziel = url + kalpath + decodeURIComponent(filterUrl);
                    }else if(key === 'nfx'){
                        if(filter !== ''){
                            filterQ = '?' + filter.slice(1) + '&';
                            filterUrl = filterQ + 'nfx=' + val;
                        }else{
                            filterUrl = '?' + 'nfx=' + val;
                        }
                        ziel = url + '/js/news/' + kid + decodeURIComponent(filterUrl);
                    }else if(key === 'nfxid'){
                        if(filter !== ''){
                            filterQ = '?' + filter.slice(1) + '&';
                            filterUrl = filterQ + 'nfxid=' + val;
                        }else{
                            filterUrl = '?' + 'nfxid=' + val;
                        }
                        ziel = url + '/js/news/' + kid + '/detail/' + val;
                    }else if(key === 'nfxp'){
                        if(filter !== ''){
                            filterQ = '?' + filter.slice(1) + '&';
                            filterUrl = filterQ + 'nfxp=' + val;
                        }else{
                            filterUrl = '?' + 'nfxp=' + val;
                        }
                        ziel = url + '/js/news/' + kid + decodeURIComponent(filterUrl);
                    }else if(inFilterList && !isFormParam){
                        filter += '&form%5B' + key + '%5D=' + val;
                        rebuildFilterUrl();
                        rebuildZielFromFilter();
                    }else{
                        filter += '&' + key + '=' + val;
                        rebuildFilterUrl();
                        rebuildZielFromFilter();
                    }

                }
            }
        }else{
            if(filter !==''){
                filterUrl = '?'+filter.slice(1);
            }else{
                filterUrl = '';
            }
            ziel = url+kalpath+filterUrl;
        }
	        dfxGet(ziel, 'cb=all', function(data){
	                dfxContent(data, function(){
	                          dfxReplaceState({ fe: decodeURIComponent(ziel), cb: 'all' }, '', dfxShowNewsDefault ? buildNewsFrontendUrl(filterUrl) : buildFrontendUrl(filterUrl));
	                  });
	         }, function() {
                  alert("Ein Fehler verhindert das Laden des Veranstaltungskalenders. Sollte ein Reload der Seite das Problem nicht beheben, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx01" );
        });
        if($dfxKalender){
                if(filter !==''){
                        filter = filter.slice(1);
                }
                dfxGet(url+"/js/kalender/widget/"+kid, filter, function(data){
                        dfxWidgetKalender(data);
                }, function() {
                        alert("Ein Fehler verhindert das Laden des Navigationselements. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx03");
                });
        }
    }else{
		alert('Es wurde kein HTML-Elemnt mit der ID "datefix" im Quellcode gefunden');


	}

        // Vanilla handlers for filter/forms/reset/delete
        var datefixEl = document.getElementById('datefix');
        if (datefixEl) {
            datefixEl.addEventListener('click', function (event) {
                var del = event.target.closest('.delete');
                if (del) {
                    if (!confirm(del.getAttribute('title') + '?')) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                }
            });

            datefixEl.addEventListener('submit', function (event) {
                var form = event.target.closest('#filter');
                if (!form) return;
                event.preventDefault();
                var action = form.getAttribute('action') || '';
                dfxShowLoader();
                if (action.indexOf('/js/news/') > -1) {
                    dfxquery = '/js/news/' + kid;
                    var query = "cb=all&" + serializeForm(form);
                    var navParam = '';
                    var dfxKonfEl = document.getElementById('dfx_konf');
                    var navValue = dfxKonfEl ? dfxKonfEl.getAttribute('data-nav-liste') : null;
                    if (navValue !== null && navValue !== '') {
                        navParam = 'nav=' + navValue + '&';
                    }
                    dfxPushState({ fe: url + dfxquery, cb: 'nfx' }, '', dfxShowNewsDefault ? buildNewsFrontendUrl(navParam + serializeForm(form)) : buildFrontendUrl('nfx&' + navParam + serializeForm(form)));
                    dfxGet(url + dfxquery, query, function (data) {
                        dfxContent(data);
                    }, function () {
                        alert("Ein Fehler beim Setzen des News-Filters ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: nfx04 / bei Aufruf von " + root);
                    });
                    return;
                }

                dfxGet(url + kalpath, "cb=all&" + serializeForm(form), function (data) {
                    dfxContent(data);
                }, function () {
                    alert("Ein Fehler beim Setzen des Suchfilters ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx04 / bei Aufruf von " + root);
                });
            });

            datefixEl.addEventListener('submit', function (event) {
                var form = event.target.closest('#nfx-filter');
                if (!form) return;
                event.preventDefault();
                dfxShowLoader();
                dfxquery = '/js/news/' + kid;
                var query = "cb=all&" + serializeForm(form);
                dfxPushState({ fe: url + dfxquery, cb: 'nfx' }, '', dfxShowNewsDefault ? buildNewsFrontendUrl(serializeForm(form)) : buildFrontendUrl('nfx&' + serializeForm(form)));
                dfxGet(url + dfxquery, query, function (data) {
                    dfxContent(data);
                }, function () {
                    alert("Ein Fehler beim Setzen des News-Filters ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: nfx04 / bei Aufruf von " + root);
                });
            });

            datefixEl.addEventListener('reset', function (event) {
                var form = event.target.closest('#filter');
                if (!form) return;
                event.preventDefault();
                clearFormInputs(form);
                var action = form.getAttribute('action') || '';
                if (action.indexOf('/js/news/') > -1) {
                    var dfxquery = '/js/news/' + kid;
                    var navParam = '';
                    var dfxKonfEl = document.getElementById('dfx_konf');
                    var navValue = dfxKonfEl ? dfxKonfEl.getAttribute('data-nav-liste') : null;
                    if (navValue !== null && navValue !== '') {
                        navParam = 'nav=' + navValue;
                    }
                    dfxPushState({ fe: url + dfxquery, cb: 'nfx' }, '', dfxShowNewsDefault ? buildNewsFrontendUrl(navParam) : buildFrontendUrl('nfx' + (navParam ? '&' + navParam : '')));
                    dfxGet(url + dfxquery, 'cb=all', function (data) {
                        dfxContent(data);
                    }, function () {
                        alert("Ein Fehler in der Blätterfunktion der News-Liste ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: nfx06");
                    });
                    return;
                }
                var dfxquery2 = kalpath;
                dfxPushState({ fe: url + dfxquery2, cb: 'termine' }, '', buildFrontendUrl(''));
                dfxShowLoader();
                dfxGet(url + dfxquery2, 'cb=all', function (data) {
                    dfxContent(data);
                }, function () {
                    alert("Ein Fehler in der Blätterfunktion des Veranstaltungskalenders ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx06");
                });
            });

            datefixEl.addEventListener('reset', function (event) {
                var form = event.target.closest('#nfx-filter');
                if (!form) return;
                event.preventDefault();
                clearFormInputs(form);
                var dfxquery = '/js/news/' + kid;
                dfxPushState({ fe: url + dfxquery, cb: 'nfx' }, '', dfxShowNewsDefault ? buildNewsFrontendUrl('') : buildFrontendUrl('nfx'));
                dfxShowLoader();
                dfxGet(url + dfxquery, 'cb=all', function (data) {
                    dfxContent(data);
                }, function () {
                    alert("Ein Fehler in der Blätterfunktion der News-Liste ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: nfx06");
                });
            });
        }



		// Formulare aus der Detailseite
	    // Überprüfung Sicherheitscode

		if ($datefix) {
			$datefix.addEventListener('change', function (event) {
				var target = event.target;
				if (!target || !target.matches('#cCode input')) return;
				var arFormname = (target.getAttribute('id') || '').split('_');
				var formname = arFormname[0];
				var cCodeEl = document.getElementById(formname + "_cCode");
				var keyEl = document.getElementById(formname + "_key");
				var cCode = cCodeEl ? cCodeEl.value : '';
				var key = keyEl ? keyEl.value : '';
				cChecked = 'busy';
				var statusEl = document.getElementById('cCodeStatus');
				if (statusEl) {
					statusEl.innerHTML = '<div style="text-align:center;"><img src="' + url + '/images/loader.gif" alt="Loader"><br>überprüfe Sicherheitscode</div>';
					dfxFadeIn(statusEl);
				}
				fetch(url + "/js/kalender/check/" + cCode + "/" + key, { credentials: 'same-origin' })
					.then(function (res) {
						if (!res.ok) throw new Error('HTTP ' + res.status);
						return res.json();
					})
					.then(function (data) {
						if (data === 'ok') {
							cChecked = data;
							if (statusEl) {
								statusEl.innerHTML = '<div style="text-align:center;">Sicherheitscode OK - Bitte Absendebutton betätigen</div>';
								dfxFadeIn(statusEl);
							}
						} else {
							cChecked = 'error';
							if (statusEl) {
								statusEl.innerHTML = '<div style="text-align:center; color: red">Sicherheitscode falsch</div>';
								dfxFadeIn(statusEl);
							}
						}
					})
					.catch(function (err) {
						alert("Fehler in Abfrage Sicherheitscode " + err);
						cChecked = 'error';
						if (statusEl) {
							statusEl.innerHTML = '<div style="text-align:center; color: red">Sicherheitscode falsch</div>';
							dfxFadeIn(statusEl);
						}
					});
				return cChecked;
			});
		}

		// Versand
		if ($datefix) {
			$datefix.addEventListener("submit", function (event) {
				var obj = event.target.closest("#dfx-termine form") || event.target.closest("#dfx_detail_wrapper form") || event.target.closest("form");
				if (!obj) return;
				var actionAttr = obj.getAttribute("action") || "";
				var dfxpath = extractFrontendDfxPath(actionAttr);
				var ajaxPath = dfxpath || actionAttr;
				var isAjaxTarget = ajaxPath.indexOf('/js/kalender/') > -1 || ajaxPath.indexOf('/js/news/') > -1;
				if (!isAjaxTarget) return;
				var method = (obj.getAttribute("method") || "get").toLowerCase();
				var objData = null;
				if (method === 'post') {
					objData = new FormData(obj);
				}
				// actionAttr already captured above
				if (actionAttr.slice(0, 7) === 'http://' || actionAttr.slice(0, 8) === 'https://') {
					if (actionAttr.indexOf(url) !== 0 && actionAttr.indexOf(root) !== 0) {
						return true;
					}
				}

				event.preventDefault();
				// warte Überprüfung Sicherheitscode ab
				setTimeout(function () {
					var strData = serializeForm(obj);
					var actionurl = ajaxPath;
					if (ajaxPath.slice(0, 7) !== 'http://' && ajaxPath.slice(0, 8) !== 'https://') {
						actionurl = root + ajaxPath;
					}
					if (cChecked === 'ok' && lengthChecked === true) {
						dfxShowLoader();
						if (method === 'post') {
							dfxPost(actionurl, method, objData, function (data) {
								dfxDetail(data);
							}, function (err) {
								alert("Ein Fehler verhindert das Versenden eines Formulars mit der Url " + actionurl + ". Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx05 / post / " + actionurl);
							});
						} else {
							dfxGet(actionurl, strData, function (data) {
								dfxDetail(data);
							}, function () {
								alert("Ein Fehler verhindert das Versenden eines Formulars mit der Url " + actionurl + ". Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx05 / get / " + actionurl);
							});
						}
						return false;
					} else if (lengthChecked === false) {
						var statusEl = document.getElementById('cCodeStatus');
						if (statusEl) statusEl.innerHTML = '<div style="color: red">Texteingaben zu lang.</div>';
					} else if (cChecked === 'error') {
						var statusErr = document.getElementById('cCodeStatus');
						if (statusErr) statusErr.innerHTML = '<div style="color: red">Sicherheitscode falsch.</div>';
					} else {
						var statusWait = document.getElementById('cCodeStatus');
						if (statusWait) {
							statusWait.innerHTML = '<div style="color: red">Status: ' + cChecked + '. Warte auf Überprüfung des Sicherheitscodes - Bitte Absendebutton nochmals betätigen</div>';
							dfxFadeIn(statusWait);
						}
					}
				}, 1000);
			});
		}


		// Klick auf Link in Terminliste - Weiche Map u. andere Links


        if ($datefix) {
            $datefix.addEventListener("click", function (event) {
                var calendarLink = event.target.closest('.responsive-calendar a');
                if (calendarLink) {
                    var filterForm = document.getElementById('filter');
                    if (!filterForm) return;
                    var formT = document.getElementById('form_t');
                    var formM = document.getElementById('form_m');
                    var go = calendarLink.getAttribute('data-go');
                    var year = calendarLink.getAttribute('data-year');
                    var month = calendarLink.getAttribute('data-month');
                    var day = calendarLink.getAttribute('data-day');

                    event.preventDefault();
                    if (go) {
                        if (formM) formM.value = year + '-' + month;
                        if (formT) formT.value = '';
                    } else if (day) {
                        var paddedMonth = String(month).padStart(2, '0');
                        var paddedDay = String(day).padStart(2, '0');
                        if (formT) formT.value = year + '-' + paddedMonth + '-' + paddedDay;
                    } else {
                        return;
                    }

                    var query = serializeForm(filterForm);
                    dfxPushState({ fe: url + kalpath, cb: 'termine' }, '', buildFrontendUrl(query));
                    dfxShowLoader();
                    dfxGet(url + kalpath, 'cb=all&' + query, function (data) {
                        dfxContent(data);
                    }, function () {
                        alert("Ein Fehler in der Kalendernavigation ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx_kal_nav");
                    });
                    return;
                }

                var link = event.target.closest("#dfx-termine a, #dfx-termine button");
                if (!link) return;
                if (link.closest('#formpills') || link.getAttribute('data-bs-toggle') === 'tab') {
                    return;
                }
				var href = link.getAttribute("href") || "";
				// "Termin hinzufügen" -> AJAX into #dfx-termine
				var isFormHref = (href.indexOf('dfxpath=') > -1 && href.indexOf('/new') > -1) ||
					(href.indexOf('/js/kalender/') > -1 && href.indexOf('/new') > -1);
				if (isFormHref) {
					event.preventDefault();
					event.stopPropagation();
					var dfxpathMatch = href.match(/dfxpath=([^&]+)/);
					var dfxpath = null;
					if (dfxpathMatch && dfxpathMatch[1]) {
						dfxpath = decodeURIComponent(dfxpathMatch[1]);
					} else {
						var newMatch = href.match(/(\/js\/kalender\/\d+\/new)/);
						if (newMatch && newMatch[1]) {
							dfxpath = newMatch[1];
						}
					}
					if (dfxpath) {
							dfxPushState({ fe: root + dfxpath, cb: 'termine' }, '', buildFrontendUrl('dfxpath=' + dfxpath));
						dfxShowLoader();
						dfxGet(root + dfxpath, false, function (data) {
							dfxDetail(data);
						}, function () {
							alert("Ein Fehler verhindert das Laden des Formulars. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx_form_new / " + dfxpath);
						});
					}
					return;
				}
				if (link.classList.contains('dfx-map-open')) {
					event.preventDefault();
					showKarte(link.getAttribute("data-tid"), link.getAttribute("data-bg"), link.getAttribute("data-lg"), link.getAttribute("data-lokal"));
					return false;
				} else if (link.classList.contains('dfx-map-close')) {
					event.preventDefault();
					window.addEventListener('gMapsLoaded', function () {
						closeKarte(link.getAttribute("data-tid"));
					});
					return false;
				} else if (link.getAttribute("id") === 'getgeodata') {
					event.preventDefault();
					showAddress(link.getAttribute('data-feldstrasse'), link.getAttribute('data-formname'));
					return false;
				} else if (link.getAttribute("id") === 'setgeodata') {
					event.preventDefault();
					showKarteSet(link.getAttribute('data-formname'));
					return false;
				} else if (link.classList.contains('dfx-print')) {
					event.preventDefault();
					dfxGet(href, false, function (data) {
						printDatefix(data);
					}, function () {
						alert("Ein Fehler verhindert das Laden Druckversion. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx_print 01 ");
					});
					return false;
				} else if (link.classList.contains('dfx-pdf')) {
					return true;
				} else if (link.classList.contains('dfx-nojson') || href.slice(0, 7) === 'mailto:') {
					return true;
				} else if (link.closest('ul') && link.closest('ul').classList.contains('pagination')) {
					event.preventDefault();
					if (href.indexOf('dfxp=') > 0) {
						dfxquery = kalpath + '?' + href.slice(href.indexOf('dfxp=')).replace('?', '&');
						pagequery = buildFrontendUrl(href.slice(href.indexOf('dfxp=')));
					} else {
						dfxquery = href;
						pagequery = buildFrontendUrl('');
					}
						dfxPushState({ fe: url + dfxquery, cb: 'termine' }, '', pagequery);
					dfxShowLoader();
					dfxGet(url + dfxquery, 'cb=termine', function (data) {
						dfxDetail(data);
					}, function () {
						alert("Ein Fehler in der Blätterfunktion des Veranstaltungskalenders ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx06");
					});
				} else if (href.indexOf('dfxid=') > 0) {
                    event.preventDefault();
					var titel = '';
					$dfxTitel = document.querySelector(".dfx-titel");
					if ($dfxTitel) {
						titel += $dfxTitel.textContent;
					}
					var detailOrt = document.querySelector(".dfx-detail-ort");
					if (detailOrt) {
						titel += ' | ' + detailOrt.textContent;
					}
					var detailLokal = document.querySelector(".dfx-detail-lokal");
					if (detailLokal) {
						titel += ' | ' + detailLokal.textContent;
					}
					dfxquery = kalpath + '/detail/' + href.slice(href.indexOf('dfxid=') + 6);
						dfxPushState({ fe: url + dfxquery, cb: 'termine' }, titel, buildFrontendUrl('dfxid=' + href.slice(href.indexOf('dfxid=') + 6)));
					dfxShowLoader();
                    dfxGet(url + dfxquery, false, function (data) {
                        dfxDetail(data);
                    }, function () {
						alert("Ein Fehler verhindert das Laden einer Detailseite des Veranstaltungskalenders. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx08 / " + dfxquery);
					});
				} else if (link.closest('div') && link.closest('div').getAttribute('id') === 'dfx-social') {
					return true;
				} else if (link.closest('div') && link.closest('div').classList.contains('back-to-list')) {
					event.preventDefault();
					var ajaxNewsListUrl = link.getAttribute('data-dfx-ajax-url');
					if (ajaxNewsListUrl) {
						dfxPushState({ fe: ajaxNewsListUrl, cb: 'all' }, '', href);
						dfxShowLoader();
						dfxGet(ajaxNewsListUrl, 'cb=all', function (data) {
							dfxContent(data);
						}, function () {
							alert("Ein Fehler verhindert das Zurückblättern der News-Liste. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: nfx07");
						});
					} else if (href.indexOf('/news/') > -1 || href.indexOf('nfx') > -1) {
						var newsListUrl = url + '/js/news/' + kid;
						dfxPushState({ fe: newsListUrl, cb: 'all' }, '', href);
						dfxShowLoader();
						dfxGet(newsListUrl, 'cb=all', function (data) {
							dfxContent(data);
						}, function () {
							alert("Ein Fehler verhindert das Zurückblättern der News-Liste. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: nfx07");
						});
					} else if (history.pushState) {
						history.back();
					} else {
						dfxGet(href, "cb=Termine", function (data) {
							dfxDetail(data);
						}, function () {
							alert("Ein Fehler verhindert das Zurückblättern Veranstaltungskalenders. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx07");
						});
					}
					return false;
				} else if ((href.slice(0, 7) === 'http://' || href.slice(0, 8) === 'https://') && href.indexOf("dfxpath") === -1 && href.indexOf("dfxid") === -1) {
					return true;
				} else if (href.slice(0, 1) === '#') {
					return true;
				} else {
					event.preventDefault();
					var dfxqueryDetail = href.slice(href.indexOf('dfxpath=') + 8);
						dfxPushState({ fe: root + dfxqueryDetail, cb: 'termine' }, '', buildFrontendUrl('dfxpath=' + dfxqueryDetail));
					dfxShowLoader();
					dfxGet(root + dfxqueryDetail, false, function (data) {
						dfxDetail(data);
					}, function () {
						alert("Ein Fehler verhindert das Laden einer Detailseite des Veranstaltungskalenders. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx08 / " + root + dfxqueryDetail);
					});
				}
				return false;
			});
		}

		// Klick auf Link in News-Detail/News-Liste (#nfx-item) -> Artikel-Detail per AJAX
		if ($datefix) {
			$datefix.addEventListener("click", function (event) {
				var link = event.target.closest(".nfx-item a");
				if (!link) return;
				var href = link.getAttribute("href") || "";
				if (link.closest('ul') && link.closest('ul').classList.contains('pagination')) {
					event.preventDefault();
					if (href.indexOf('nfxp=') > -1) {
						dfxquery = '/js/news/' + kid + '?' + href.slice(href.indexOf('nfxp='));
						pagequery = buildFrontendUrl(href.slice(href.indexOf('nfxp=')));
					} else {
						dfxquery = href;
						pagequery = buildFrontendUrl('');
					}
						dfxPushState({ fe: url + dfxquery, cb: 'nfx' }, '', dfxShowNewsDefault ? buildNewsFrontendUrl(href.slice(href.indexOf('nfxp='))) : pagequery);
					dfxShowLoader();
					dfxGet(url + dfxquery, 'cb=nfx', function (data) {
						dfxDetail(data);
					}, function () {
						alert("Ein Fehler beim Blättern in der News-Liste ist aufgetreten. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: nfx06");
					});
					return false;
				}
				if (href.indexOf('nfxid=') > -1) {
					event.preventDefault();
					var artikelId = href.slice(href.indexOf('nfxid=') + 6);
					dfxquery = '/js/news/' + kid + '/detail/' + artikelId;
						dfxPushState({ fe: url + dfxquery, cb: 'nfx' }, '', dfxShowNewsDefault ? buildNewsFrontendUrl('nfxid=' + artikelId) : buildFrontendUrl('nfxid=' + artikelId));
					dfxShowLoader();
					dfxGet(url + dfxquery, false, function (data) {
						dfxDetail(data);
					}, function () {
						alert("Ein Fehler verhindert das Laden einer Detailseite der News. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: nfx08 / " + dfxquery);
					});
					return false;
				}
				return true;
			});
		}
		 window.addEventListener('popstate', function(event) {
			    // if the event has our history data on it, load the page fragment with AJAX
			    var state = event.state;
	    if (state) {
	    		if(state.cb){
	    			var query = 'cb='+state.cb;
	    		}else{
	    			var query = false;
	    		}
	    		dfxShowLoader();
	    		var fetchUrl = resolveStateUrl(state.fe);
	    		dfxGet(fetchUrl, query, function(data){
	    			var isDetailState = false;
	    			if (typeof fetchUrl === 'string') {
	    				isDetailState = fetchUrl.indexOf('/detail/') > -1 || fetchUrl.indexOf('dfxpath=') > -1 || fetchUrl.indexOf('dfxid=') > -1 || fetchUrl.indexOf('nfxid=') > -1;
	    			}
	    			if(!isDetailState){
	    				dfxContent(data);
	    			}else{
	    				dfxDetail(data);
	    			}
	    		}, function() {
		    			alert("Ein Fehler verhindert das Blättern im Veranstaltungskalender. Sollte ein Reload der Seite das Problem nicht beseitigen, infomieren Sie Bitte den Webmaster dieser Seite unter Angabe der Fehlernummer: dfx10 / "+fetchUrl );
		    		});
			    }

		});


		// Klick auf Tag im Kalender wird serverseitig per Link behandelt


		if ($datefix) {
			$datefix.addEventListener('change', function (event) {
				var target = event.target;
				if (!target || !target.matches('#termine_ort')) return;
				if (document.getElementById('setgeodata')) {
					if ($getgeodata) {
						showAddress($getgeodata.getAttribute('data-feldstrasse'), $getgeodata.getAttribute('data-formname'));
					}
					return false;
				}
			});
		}

		if (document.getElementById('form_umkreis')) {
			var bindFormOrtPlz = function (event) {
				var target = event.target;
				if (!target || !(target.matches('#form_ort') || target.matches('#form_plz'))) return;
				var umkreisEl = document.getElementById('form_umkreis');
				if (umkreisEl && umkreisEl.value.length > 0) {
					showAddress(null, 'form');
				}
			};
			if ($datefix) $datefix.addEventListener('change', bindFormOrtPlz);
			if ($dfxKalender) $dfxKalender.addEventListener('change', bindFormOrtPlz);
			if ($datefix) {
				$datefix.addEventListener('focus', function (event) {
					var target = event.target;
					if (!target || !target.matches('#form_umkreis')) return;
					var plzEl = document.getElementById('form_plz');
					var ortEl = document.getElementById('form_ort');
					if ((plzEl && plzEl.value.length > 0) || (ortEl && ortEl.value.length > 0)) {
						showAddress(null, 'form');
					}
				}, true);
			}
		}

		if ($datefix) {
			$datefix.addEventListener('click', function (e) {
				var link = e.target.closest('#formpills a');
				if (!link) return;
				e.preventDefault();
				var targetSel = link.getAttribute('data-bs-target') || link.getAttribute('href');
				if (!targetSel) return;
				var target = document.querySelector(targetSel);
				if (!target) return;
				document.querySelectorAll('#formpills .nav-link').forEach(function (el) {
					el.classList.remove('active');
					el.setAttribute('aria-selected', 'false');
				});
				document.querySelectorAll('.tab-content .tab-pane').forEach(function (el) {
					el.classList.remove('active');
					el.classList.remove('show');
				});
				link.classList.add('active');
				link.setAttribute('aria-selected', 'true');
				target.classList.add('active');
				target.classList.add('show');
			});
		}

		if ($datefix) {
			$datefix.addEventListener('click', function (e) {
				var link = e.target.closest('.sm-link');
				if (!link) return;
				document.querySelectorAll('.sm-box .sm-buttons').forEach(function (el) { el.style.display = 'none'; });
				var box = link.closest('.sm-box');
				if (box) {
					var buttons = box.querySelector(".sm-buttons");
					if (buttons) buttons.style.display = 'block';
				}
				var fbFrame = document.getElementById('fbFrame');
				if (fbFrame) fbFrame.setAttribute('src', url + kalpath + '/fb/' + link.getAttribute('data-tid'));
				e.stopPropagation();
			});
		}

		if ($datefix) {
			$datefix.addEventListener('click', function (e) {
				var buttons = e.target.closest(".sm-buttons");
				if (buttons) e.stopPropagation();
			});
		}

	 	 document.addEventListener('click', function(){
	         var buttons = document.querySelectorAll(".sm-buttons");
	         buttons.forEach(function (el) { el.style.display = 'none'; });
	     });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', dfxAjaxInit);
} else {
    dfxAjaxInit();
}

function dfxParseResponse(data) {
	var html = data || '';
	if (!html || typeof html !== 'string') {
		return { html: html, doc: null, nav: null, wrapper: null, detail: null };
	}
	try {
		var doc = new DOMParser().parseFromString(html, 'text/html');
		var termineEl = doc.querySelector('#dfx-termine');
		var detailEl = doc.querySelector('#dfx_detail_wrapper');
		var navEl = doc.querySelector('.dfx-nav');
		var wrapperEl = doc.querySelector('#dfx_wrapper');
		return {
			html: termineEl ? termineEl.innerHTML : (detailEl ? detailEl.outerHTML : html),
			doc: doc,
			nav: navEl,
			wrapper: wrapperEl,
			detail: detailEl,
			konf: doc.querySelector('#dfx_konf')
		};
	} catch (e) {
		return { html: html, doc: null, nav: null, wrapper: null, detail: null, konf: null };
	}
}

function dfxApplyResponse(parsed, opts) {
	var options = opts || {};
	var target = document.getElementById('dfx-termine');
	var html = (parsed && typeof parsed.html === 'string') ? parsed.html : '';
	if (!html && parsed && parsed.html) {
		html = String(parsed.html);
	}
	if (!target) {
		var datefix = document.getElementById('datefix');
		if (datefix && parsed && parsed.wrapper) {
			datefix.innerHTML = parsed.wrapper.outerHTML;
		} else if (datefix && !document.getElementById('dfx_wrapper')) {
			var wrapper = document.createElement('div');
			wrapper.id = 'dfx_wrapper';
			wrapper.className = 'container-fluid';
			var row = document.createElement('div');
			row.className = 'row';
			var content = document.createElement('div');
			content.id = 'dfx-termine';
			content.className = 'col-12';
			row.appendChild(content);
			wrapper.appendChild(row);
			datefix.innerHTML = '';
			datefix.appendChild(wrapper);
		}
		target = document.getElementById('dfx-termine');
	}
	if (target) {
		target.innerHTML = html || '';
		dfxFadeIn(target);
		ensureVisible(target);
	}
	if (options.updateNav && parsed.nav) {
		var navEls = document.querySelectorAll('.dfx-nav');
		if (navEls.length) {
			navEls.forEach(function (el) {
				el.innerHTML = parsed.nav.innerHTML;
				ensureVisible(el);
			});
		}
	}
}

function dfxShowLoader() {
	var target = document.getElementById('dfx-termine');
	if (!target) return;
	target.innerHTML = '<div style="text-align:center; margin-top: 50px"><img src="' + url + '/images/loader.gif" alt="Loader"></div>';
	dfxFadeIn(target);
	ensureVisible(target);
}


//Lade kompletten Datefix-Container HTML
function dfxContent(data,callback) {
	var parsed = dfxParseResponse(data);
	dfxApplyResponse(parsed, { updateNav: true });
	$dfxTermine = document.getElementById("dfx-termine");
	$dfxTitel = document.querySelector(".dfx-titel");
	$getgeodata = document.getElementById("getgeodata");
	$dfxWrapper = document.getElementById("dfx_wrapper");
	$dfxDetailWrapper = document.getElementById("dfx_detail_wrapper");
	$dfxKonf = document.getElementById("dfx_konf");
	dfxEnsureTinymce();
	document.querySelectorAll(".dfx-nav").forEach(function (el) { ensureVisible(el); });
	if (scriptloaded === false) {
		var konfEl = document.getElementById('dfx_konf');
		var cssFile = null;
		if (konfEl && konfEl.getAttribute('data-cssfile')) {
			cssFile = konfEl.getAttribute('data-cssfile');
		} else if ($dfxWrapper && $dfxWrapper.getAttribute("data-cssfile")) {
			cssFile = $dfxWrapper.getAttribute("data-cssfile");
		} else if ($dfxDetailWrapper && $dfxDetailWrapper.getAttribute("data-cssfile")) {
			cssFile = $dfxDetailWrapper.getAttribute("data-cssfile");
		}
		if (cssFile) {
			var link = document.createElement('link');
			link.rel = 'stylesheet';
			link.href = url + '/css/' + cssFile;
			document.head.appendChild(link);
			scriptloaded = true;
		}
	}
	// Kalender wird serverseitig gerendert, keine clientseitige Initialisierung mehr

	if((!document.querySelector('.dfx-map-open') || document.querySelector('.dfx-map-open-self')) && $dfxDetailWrapper && $dfxDetailWrapper.getAttribute('data-bg') > 0){
		showKarte('', $dfxDetailWrapper.getAttribute('data-bg'), $dfxDetailWrapper.getAttribute('data-lg'), $dfxDetailWrapper.getAttribute('data-lokal'));

	}
	dfxPostRender();

	if (typeof callback == "function") {
	   callback();
	}

	dfxEnsureVisible();

	return true;
}
function jsonTage(){
	return true;
}
function dfxWidgetKalender(data) {
	$dfxKalender = document.getElementById("dfx-kalender");
	if ($dfxKalender) {
		$dfxKalender.innerHTML = data;
	}
	// $.get(url+"/js/kalender/json/widgets/"+kid, false, false,'jsonp');
	return true;
}
//Lade Terminbereich HTML

//Detailbereich und Formulare HTML

//Lade Terminbereich HTML
function dfxDetail(data) {
	// $dfxTermine.fadeOut('slow');
	var parsed = dfxParseResponse(data);
	dfxApplyResponse(parsed, { updateNav: false });
	$dfxTermine = document.getElementById("dfx-termine");
	$dfxTitel = document.querySelector(".dfx-titel");
	$getgeodata = document.getElementById("getgeodata");
	$dfxDetailWrapper = document.getElementById("dfx_detail_wrapper");
	$dfxKonf = document.getElementById("dfx_konf");
	document.querySelectorAll(".dfx-nav").forEach(function (el) { ensureVisible(el); });
	dfxEnsureTinymce();
	dfxPostRender();
	if((!document.querySelector('.dfx-map-open') || document.querySelector('.dfx-map-open-self')) && $dfxDetailWrapper && $dfxDetailWrapper.getAttribute('data-bg') > 0){
            showKarte('', $dfxDetailWrapper.getAttribute('data-bg'), $dfxDetailWrapper.getAttribute('data-lg'), $dfxDetailWrapper.getAttribute('data-lokal'));
	}
	scrollToDatefix();
	if ($datefix) $datefix.getBoundingClientRect().top;
	dfxEnsureVisible();
	return true;
}

function dfxPostRender() {
	$dfxKonf = document.getElementById("dfx_konf");
	$dfxTitel = document.querySelector(".dfx-titel");
	if (window.dfxInitFrontendHandlers) {
		window.dfxInitFrontendHandlers();
	}
	if ($dfxTitel) {
		var detailOrt = document.querySelector(".dfx-detail-ort");
		var detailLokal = document.querySelector(".dfx-detail-lokal");
		document.title = $dfxTitel.textContent + ' | ' + (detailOrt ? detailOrt.textContent : '') + ' | ' + (detailLokal ? detailLokal.textContent : '');
	} else {
		var header = document.querySelector(".dfx-header");
		document.title = header ? header.textContent : document.title;
	}
	if (typeof FormData != 'function' && typeof FormData != 'object'){
		document.querySelectorAll(".dfx-termin-add-link").forEach(function (el) { el.style.display = 'none'; });
	}
	if (document.getElementById("galerie")){
		if (window.bootstrap && window.bootstrap.Carousel) {
			document.querySelectorAll('.carousel').forEach(function (el) {
				window.bootstrap.Carousel.getOrCreateInstance(el, { interval: 4000 });
			});
		}
	}
	if (document.getElementById('termine_datumSerie') && window.dfxInitDatumSeriePicker) {
		window.dfxInitDatumSeriePicker();
	}
}

function dfxEnsureVisible() {
	if ($dfxTermine) ensureVisible($dfxTermine);
	if ($datefix) ensureVisible($datefix);
	if ($dfxWrapper) ensureVisible($dfxWrapper);
	document.querySelectorAll(".dfx-nav").forEach(function (el) { ensureVisible(el); });
	if ($dfxTermine) dfxCancelFade($dfxTermine);
	document.querySelectorAll(".dfx-nav").forEach(function (el) { dfxCancelFade(el); });
	setTimeout(function () {
		if ($dfxTermine) dfxCancelFade($dfxTermine);
		document.querySelectorAll(".dfx-nav").forEach(function (el) { dfxCancelFade(el); });
	}, 350);
}
function closeKarte(tid){
	var mapEl = document.getElementById("dfxMap" + tid);
	var closeEl = document.getElementById("dfxMapClose" + tid);
	if (mapEl) mapEl.style.display = 'none';
	if (closeEl) closeEl.style.display = 'none';
}


var win=null;
function printDatefix(printContent){
    win = window.open();
    self.focus();
    win.document.open();
    win.document.write('<'+'html'+'><'+'head'+'><'+'style'+'>');
    win.document.write('body, td { font-family: Verdana; font-size: 12pt;}');
    win.document.write('<'+'/'+'style'+'><'+'/'+'head'+'><'+'body'+'>');
    win.document.write(printContent);
    win.document.write('<'+'/'+'body'+'><'+'/'+'html'+'>');
    win.document.close();
    win.print();
    win.close();
}

// Calendar plugin removed (serverseitiges Rendering)
