var $getgeodata;
var $setgeodata;
var $serientermin;
var $dfxMap;
var marker = null;
var dfxMapSetInstance = null;
var url = window.url || '';
window.dfxFunctionsLoaded = true;

var dfxFlatpickrPromise = null;
function dfxLoadFlatpickr() {
	if (window.flatpickr) return Promise.resolve(window.flatpickr);
	if (dfxFlatpickrPromise) return dfxFlatpickrPromise;

	dfxFlatpickrPromise = new Promise(function (resolve) {
		dfxEnsureUrl();
		var baseUrl = url || window.location.origin;
		var cssHref = baseUrl + '/css/flatpickr/flatpickr.min.css';
		if (!document.querySelector('link[data-dfx-flatpickr-css="1"]')) {
			var css = document.createElement('link');
			css.rel = 'stylesheet';
			css.href = cssHref;
			css.setAttribute('data-dfx-flatpickr-css', '1');
			document.head.appendChild(css);
		}

		var ensureLocale = function () {
			if (!window.flatpickr) return resolve(null);
			if (!document.querySelector('script[data-dfx-flatpickr-l10n="de"]')) {
				var l10n = document.createElement('script');
				l10n.src = baseUrl + '/js/flatpickr/l10n/de.js';
				l10n.async = true;
				l10n.setAttribute('data-dfx-flatpickr-l10n', 'de');
				l10n.onload = function () { resolve(window.flatpickr); };
				l10n.onerror = function () { resolve(window.flatpickr); };
				document.head.appendChild(l10n);
				return;
			}
			resolve(window.flatpickr);
		};

		var existingScript = document.querySelector('script[data-dfx-flatpickr="1"]');
		if (existingScript) {
			var tries = 0;
			var timer = setInterval(function () {
				tries += 1;
				if (window.flatpickr || tries > 30) {
					clearInterval(timer);
					ensureLocale();
				}
			}, 100);
			return;
		}

		var script = document.createElement('script');
		script.src = baseUrl + '/js/flatpickr/flatpickr.min.js';
		script.async = true;
		script.setAttribute('data-dfx-flatpickr', '1');
		script.onload = ensureLocale;
		script.onerror = function (e) {
			if (window.DFX_DEBUG) console.log('flatpickr load error', e);
			resolve(null);
		};
		document.head.appendChild(script);
	});

	return dfxFlatpickrPromise;
}

function dfxInitDatePickers() {
	var inputs = document.querySelectorAll('.fxDatePicker');
	if (!inputs.length) return;
	dfxLoadFlatpickr().then(function (fp) {
		if (!fp) return;
		var locale = fp.l10ns && fp.l10ns.de ? fp.l10ns.de : null;
		inputs.forEach(function (input) {
			if (!input || input.id === 'termine_datumSerie') return;
			if (input._flatpickr) {
				try { input._flatpickr.destroy(); } catch (e) {}
			}
			fp(input, {
				dateFormat: 'Y-m-d',
				locale: locale || undefined,
				allowInput: true
			});
		});
	});
}
window.dfxInitDatePickers = dfxInitDatePickers;

function dfxInitDatumSeriePicker() {
	var input = document.getElementById('termine_datumSerie');
	if (!input) return;
	dfxLoadFlatpickr().then(function (fp) {
		if (!fp) return;
		if (input._flatpickr) {
			try { input._flatpickr.destroy(); } catch (e) {}
		}
		var locale = fp.l10ns && fp.l10ns.de ? fp.l10ns.de : null;
		fp(input, {
			mode: 'multiple',
			dateFormat: 'Y-m-d',
			conjunction: ', ',
			locale: locale || undefined,
			allowInput: false,
			clickOpens: true,
			closeOnSelect: false,
			onChange: function () {
				var datumVon = document.getElementById('termine_datum_von');
				if (datumVon) datumVon.required = false;
			}
		});
	});
}
window.dfxInitDatumSeriePicker = dfxInitDatumSeriePicker;

function dfxFadeIn(el) {
	if (!el) return;
	el.classList.add('dfx-fade');
	el.style.display = '';
	el.style.opacity = 0;
	requestAnimationFrame(function () { el.style.opacity = 1; });
}

function dfxGetJson(url, success) {
	fetch(url, { credentials: 'same-origin' })
		.then(function (res) { return res.json(); })
		.then(function (data) { if (success) success(data); })
		.catch(function () { if (success) success([]); });
}

function dfxJsonp(url, callbackName, success, error) {
	var finalCb = success;
	if (!finalCb && callbackName && typeof window[callbackName] === 'function') {
		finalCb = window[callbackName];
	}
	var cbName = 'dfx_jsonp_' + Date.now();
	var sep = url.indexOf('?') >= 0 ? '&' : '?';
	var src = url + sep + 'callback=' + encodeURIComponent(cbName);
	var script = document.createElement('script');
	script.src = src;
	script.async = true;
	if (window.DFX_DEBUG) console.log('dfxJsonp ->', src);
	var cleanup = function () {
		if (script.parentNode) script.parentNode.removeChild(script);
		try { delete window[cbName]; } catch (e) { window[cbName] = undefined; }
	};
	window[cbName] = function (data) {
        if (window.DFX_DEBUG) console.log('dfxJsonp erfolg', data);
        cleanup();
		if (finalCb) finalCb(data);
    };
	script.onerror = function (e) {
		cleanup();
		if (window.DFX_DEBUG) console.log('dfxJsonp error', e);
		if (error) error(null, 'error', e);
	};
	(document.head || document.documentElement).appendChild(script);
    if (window.DFX_DEBUG) console.log('return script', script);
	return script;
}
window.dfxJsonp = dfxJsonp;

function dfxEnsureUrl() {
	if (url) return;
	var urlEl = document.getElementById('dfx_be') || document.getElementById('dfx');
	if (urlEl) {
		url = urlEl.getAttribute('data-dfx-url') || url;
	}
	if (!url) {
		var konf = document.getElementById('dfx_konf');
		if (konf) {
			var fe = konf.getAttribute('data-frontend');
			if (fe) {
				try { url = new URL(fe, window.location.href).origin; } catch (e) {}
			}
		}
	}
	if (!url) {
		url = window.location.origin || url;
	}
}

function dfxSetGalleryIndex(gallery, nextIndex) {
	var slides = Array.prototype.slice.call(gallery.querySelectorAll('[data-dfx-gallery-slide]'));
	var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-dfx-gallery-thumb]'));

	if (!slides.length) return;

	var index = (nextIndex + slides.length) % slides.length;

	slides.forEach(function (slide, slideIndex) {
		var isActive = slideIndex === index;
		slide.classList.toggle('is-active', isActive);
		slide.setAttribute('aria-pressed', isActive ? 'true' : 'false');
	});

	thumbs.forEach(function (thumb, thumbIndex) {
		var isActive = thumbIndex === index;
		thumb.classList.toggle('is-active', isActive);
		thumb.setAttribute('aria-pressed', isActive ? 'true' : 'false');
	});

	gallery.setAttribute('data-gallery-index', String(index));
}

function dfxRenderGalleryLightbox(gallery) {
	var slides = Array.prototype.slice.call(gallery.querySelectorAll('[data-dfx-gallery-slide]'));
	var lightboxContent = gallery.querySelector('[data-dfx-gallery-lightbox-content]');
	var currentIndex = parseInt(gallery.getAttribute('data-gallery-index') || '0', 10);

	if (!lightboxContent || !slides[currentIndex]) return;

	lightboxContent.innerHTML = slides[currentIndex].innerHTML;
}

function dfxOpenGalleryLightbox(gallery) {
	var lightbox = gallery.querySelector('[data-dfx-gallery-lightbox]');

	if (!lightbox) return;

	dfxRenderGalleryLightbox(gallery);
	lightbox.hidden = false;
	document.body.classList.add('dfx-lightbox-open');
}

function dfxCloseGalleryLightbox(gallery) {
	var lightbox = gallery.querySelector('[data-dfx-gallery-lightbox]');

	if (!lightbox) return;

	lightbox.hidden = true;
	document.body.classList.remove('dfx-lightbox-open');
}

function dfxInitGallery(gallery) {
	if (!gallery || gallery.getAttribute('data-gallery-ready') === 'true') return;

	gallery.setAttribute('data-gallery-ready', 'true');
	dfxSetGalleryIndex(gallery, 0);

	var prevButton = gallery.querySelector('[data-dfx-gallery-prev]');
	if (prevButton) {
		prevButton.addEventListener('click', function () {
			dfxSetGalleryIndex(gallery, parseInt(gallery.getAttribute('data-gallery-index') || '0', 10) - 1);
		});
	}

	var nextButton = gallery.querySelector('[data-dfx-gallery-next]');
	if (nextButton) {
		nextButton.addEventListener('click', function () {
			dfxSetGalleryIndex(gallery, parseInt(gallery.getAttribute('data-gallery-index') || '0', 10) + 1);
		});
	}

	gallery.querySelectorAll('[data-dfx-gallery-thumb]').forEach(function (thumb) {
		thumb.addEventListener('click', function () {
			dfxSetGalleryIndex(gallery, parseInt(thumb.getAttribute('data-index') || '0', 10));
		});
	});

	gallery.querySelectorAll('[data-dfx-gallery-slide]').forEach(function (slide) {
		slide.addEventListener('click', function () {
			dfxSetGalleryIndex(gallery, parseInt(slide.getAttribute('data-index') || '0', 10));
			dfxOpenGalleryLightbox(gallery);
		});
	});

	var closeButton = gallery.querySelector('[data-dfx-gallery-lightbox-close]');
	if (closeButton) {
		closeButton.addEventListener('click', function () {
			dfxCloseGalleryLightbox(gallery);
		});
	}

	var lightboxPrev = gallery.querySelector('[data-dfx-gallery-lightbox-prev]');
	if (lightboxPrev) {
		lightboxPrev.addEventListener('click', function () {
			dfxSetGalleryIndex(gallery, parseInt(gallery.getAttribute('data-gallery-index') || '0', 10) - 1);
			dfxRenderGalleryLightbox(gallery);
		});
	}

	var lightboxNext = gallery.querySelector('[data-dfx-gallery-lightbox-next]');
	if (lightboxNext) {
		lightboxNext.addEventListener('click', function () {
			dfxSetGalleryIndex(gallery, parseInt(gallery.getAttribute('data-gallery-index') || '0', 10) + 1);
			dfxRenderGalleryLightbox(gallery);
		});
	}

	var lightbox = gallery.querySelector('[data-dfx-gallery-lightbox]');
	if (lightbox) {
		lightbox.addEventListener('click', function (event) {
			if (event.target && event.target.hasAttribute('data-dfx-gallery-lightbox')) {
				dfxCloseGalleryLightbox(gallery);
			}
		});
	}

	gallery.addEventListener('keydown', function (event) {
		if (event.key === 'ArrowLeft') {
			dfxSetGalleryIndex(gallery, parseInt(gallery.getAttribute('data-gallery-index') || '0', 10) - 1);
		}

		if (event.key === 'ArrowRight') {
			dfxSetGalleryIndex(gallery, parseInt(gallery.getAttribute('data-gallery-index') || '0', 10) + 1);
		}
	});
}

function dfxInitGalleries() {
	document.querySelectorAll('[data-dfx-gallery]').forEach(function (gallery) {
		dfxInitGallery(gallery);
	});
}
window.dfxInitGalleries = dfxInitGalleries;

function dfxInitFrontendHandlers() {
	dfxInitDatePickers();
	dfxInitGalleries();

	if (document.getElementById('termine_datumSerie')) {
		dfxInitDatumSeriePicker();
	}

	if (dfxInitFrontendHandlers._bound) return;
	dfxInitFrontendHandlers._bound = true;

	document.addEventListener('click', function (e) {
		var getBtn = e.target.closest('#getgeodata');
		if (getBtn) {
			e.preventDefault();
			dfxEnsureUrl();
			showAddress(getBtn.getAttribute('data-feldstrasse'), getBtn.getAttribute('data-formname'));
			return;
		}
		var setBtn = e.target.closest('#setgeodata');
		if (setBtn) {
			e.preventDefault();
			dfxEnsureUrl();
			showKarteSet(setBtn.getAttribute('data-formname'));
		}
	});

	document.addEventListener('change', function (e) {
		var target = e.target;
		if (!target) return;

			if (target.matches && target.matches('#termine_datumSerie')) return;

		if (target.matches && target.matches('#termine_lokalStrasse, #dfx_location_lokalStrasse, #veranstalter_lokalStrasse, #orte_lokalStrasse')) {
			var getBtnStreet = document.getElementById('getgeodata');
			if (getBtnStreet) {
				dfxEnsureUrl();
				showAddress(getBtnStreet.getAttribute('data-feldstrasse'), getBtnStreet.getAttribute('data-formname'));
			}
			return;
		}

		if (target.matches && target.matches('#termine_idLocation')) {
			var locId = parseInt(target.value || '0', 10);
			if (locId > 0) {
				dfxEnsureUrl();
				fetch(url + "/js/kalender/json/location/" + locId, { credentials: 'same-origin' })
					.then(function (res) {
						if (!res.ok) throw new Error('HTTP ' + res.status);
						return res.json();
					})
					.then(function (data) {
						window.datefixLocation(data);
					})
					.catch(function (err) {
						console.log("Fehler beim Laden der Location", err);
	});

	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Escape') return;

		document.querySelectorAll('[data-dfx-gallery-lightbox]').forEach(function (lightbox) {
			if (!lightbox.hidden) {
				lightbox.hidden = true;
				document.body.classList.remove('dfx-lightbox-open');
			}
		});
	});
}
			return;
		}

		if (target.matches && target.matches('#termine_idVeranstalter')) {
			var verId = parseInt(target.value || '0', 10);
			if (verId > 0) {
				dfxEnsureUrl();
				fetch(url + "/js/kalender/json/veranstalter/" + verId, { credentials: 'same-origin' })
					.then(function (res) {
						if (!res.ok) throw new Error('HTTP ' + res.status);
						return res.json();
					})
					.then(function (data) {
						window.datefixVeranstalter(data);
					})
					.catch(function (err) {
						console.log("Fehler beim Laden des Veranstalters", err);
					});
			}
			return;
		}

		if (target.matches && target.matches('#termine_ort, #dfx_location_ort, #veranstalter_ort, #orte_ort')) {
			var getBtn = document.getElementById('getgeodata');
			if (getBtn) {
				dfxEnsureUrl();
				showAddress(getBtn.getAttribute('data-feldstrasse'), getBtn.getAttribute('data-formname'));
			}
			return;
		}

		var isDays = target.matches && target.matches("input[name='termine[tage][]']");
		var isRange = target.matches && target.matches("#termine_datum_s_von, #termine_datum_s_bis");
		if (isDays || isRange) {
			dfxEnsureUrl();
			var tage7 = document.getElementById('termine_tage_7');
			var checked;
			if (tage7 && tage7.checked) {
				checked = '0,1,2,3,4,5,6';
			} else {
				checked = [];
				var checkedEls = document.querySelectorAll("input[name='termine[tage][]']:checked");
				checkedEls.forEach(function (el) { checked.push(parseInt(el.value, 10)); });
			}
			if (checked.length > 0) {
				var actionurl = url + '/js/kalender/json_kal/tage';
				var datumVonEl = document.getElementById('termine_datum_s_von');
				var datumBisEl = document.getElementById('termine_datum_s_bis');
				var strData = "wt=" + checked + "&datum_von=" + (datumVonEl ? datumVonEl.value : "") + "&datum_bis=" + (datumBisEl ? datumBisEl.value : "");
				fetch(actionurl + (actionurl.indexOf('?') >= 0 ? '&' : '?') + strData, { credentials: 'same-origin' })
					.then(function (res) {
						if (!res.ok) throw new Error('HTTP ' + res.status);
						return res.json();
					})
					.then(function (data) {
						var listeEl = document.getElementById('termine_datum_s_liste');
						if (listeEl) listeEl.value = data || '';
						var datumListe = document.getElementById('datum-liste');
						dfxFadeIn(datumListe);
						var datumVon = document.getElementById('termine_datum_von');
						if (datumVon) datumVon.required = false;
					})
					.catch(function (err) {
						console.log("Fehler in der Datumsberechnung", err);
					});
			}
			return;
		}

		if (target.matches && target.matches('#termine_datumSerie')) {
			var datumVon2 = document.getElementById('termine_datum_von');
			if (datumVon2) datumVon2.required = false;
		}
	});

	document.addEventListener('blur', function (e) {
		var target = e.target;
		if (!target || !target.matches) return;
		if (target.matches('#termine_lokalStrasse, #dfx_location_lokalStrasse, #veranstalter_lokalStrasse, #orte_lokalStrasse')) {
			var getBtnStreet = document.getElementById('getgeodata');
			if (getBtnStreet) {
				dfxEnsureUrl();
				showAddress(getBtnStreet.getAttribute('data-feldstrasse'), getBtnStreet.getAttribute('data-formname'));
			}
		}
	}, true);
}

document.addEventListener('DOMContentLoaded', function () {
	dfxEnsureUrl();
	$serientermin = document.getElementById('serientermin');
	$getgeodata = document.getElementById('getgeodata');
	$setgeodata = document.getElementById('setgeodata');
	$dfxMap = document.getElementById('dfxMap');
	dfxInitFrontendHandlers();
});

window.dfxInitFrontendHandlers = dfxInitFrontendHandlers;

function datefixLocation(data) {
	if (window.DFX_DEBUG) console.log('datefixLocation', data);
	if (!data) return true;
	var setVal = function (id, val) {
		var el = document.getElementById(id);
		if (el && typeof val !== 'undefined' && val !== null) el.value = val;
	};
	setVal('termine_lokal', data.lokal);
	setVal('termine_lokalStrasse', data.lokalStrasse);
	setVal('termine_nat', data.nat);
	setVal('termine_plz', data.plz);
	setVal('termine_ort', data.ort);
	setVal('termine_lg', data.lg);
	setVal('termine_bg', data.bg);
	setVal('termine_region', data.rid);
	setVal('termine_idOrt', data.oid);
	var veranstalter = document.getElementById('termine_veranstalter');
	if (data.ver && veranstalter && veranstalter.value.length === 0) {
		setVal('termine_idVeranstalter', data.ver.id);
		setVal('termine_mail', data.ver.email);
		setVal('termine_veranstalter', data.ver.name);
		setVal('termine_text1', data.ver.ansprech);
		setVal('termine_text2', data.ver.telefon);
		setVal('termine_text3', data.ver.email);
		setVal('termine_text4', data.ver.ansprech);
		setVal('termine_text5', data.ver.telefon);
	}
	return true;
}
window.datefixLocation = datefixLocation;

function datefixVeranstalter(data) {
	if (window.DFX_DEBUG) console.log('datefixVeranstalter', data);
	if (!data) return true;
	var setVal = function (id, val) {
		var el = document.getElementById(id);
		if (el && typeof val !== 'undefined' && val !== null) el.value = val;
	};
	setVal('termine_mail', data.email);
	setVal('termine_veranstalter', data.name);
	setVal('termine_region', data.rid);
	var lokal = document.getElementById('termine_lokal');
	if (data.loc && lokal && lokal.value.length === 0) {
		setVal('termine_idLocation', data.loc.id);
		setVal('termine_lokal', data.loc.lokal);
		setVal('termine_lokalStrasse', data.loc.lokalStrasse);
		setVal('termine_nat', data.loc.nat);
		setVal('termine_plz', data.loc.plz);
		setVal('termine_ort', data.loc.ort);
		setVal('termine_lg', data.loc.lg);
		setVal('termine_bg', data.loc.bg);
		setVal('termine_region', data.loc.rid);
		setVal('termine_idOrt', data.loc.oid);
	}
	return true;
}
window.datefixVeranstalter = datefixVeranstalter;


function jsonTage(){
	return true;
}


function showAddress(feldstrasse, formname){
    var plzEl = document.getElementById(formname + '_plz');
    var ortEl = document.getElementById(formname + '_ort');
    var plz = plzEl ? plzEl.value : '';
    var ort = ortEl ? ortEl.value : '';
	let address;
	if (feldstrasse != null) {
		var feld = document.getElementById(formname + '_' + feldstrasse);
		address = plz + '+' + ort + ',' + (feld ? feld.value : '');
	} else {
		address = plz + ' ' + ort;
	}
    dfxGetJson('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + address, function(data) {
        if (data && data.length && data[0] && data[0].lat) {
            var lg = document.getElementById(formname + '_lg');
            var bg = document.getElementById(formname + '_bg');
            if (lg) lg.value = data[0].lon;
            if (bg) bg.value = data[0].lat;
        } else {
            alert("Geocode aus der Adresse " + address + " nicht ermittelbar.");
        }
    });
}


function showKarteSet(formname){
	$dfxMap = $dfxMap || document.getElementById('dfxMap');
	if (!$dfxMap) {
		return;
	}
    $dfxMap.style.display = 'block';
    $dfxMap.style.visibility = 'visible';
    if ($dfxMap.offsetHeight === 0) {
        $dfxMap.style.height = '300px';
    }
	let lg;
	let bg;
	if (formname !== 'map') {
		var lgEl = document.getElementById(formname + '_lg');
		var bgEl = document.getElementById(formname + '_bg');
		lg = lgEl ? lgEl.value : '';
		bg = bgEl ? bgEl.value : '';
	} else {
		var lgEl2 = document.getElementById(formname + '_initLong');
		var bgEl2 = document.getElementById(formname + '_initLat');
		lg = lgEl2 ? lgEl2.value : '';
		bg = bgEl2 ? bgEl2.value : '';
		var initZoomEl = document.getElementById('map_initZoom');
		if (initZoomEl) $dfxMap.setAttribute('data-initzoom', initZoomEl.value);
	}
	let lgC;
	if (lg > 0) {
		lgC = lg;
	} else if ($dfxMap.getAttribute('data-initlg') > 0) {
		lgC = $dfxMap.getAttribute('data-initlg');
	} else {
		lgC = 8.8731;
	}

	let latC;
	if (lg > 0) {
		latC = bg;
	} else if ($dfxMap.getAttribute('data-initbg') > 0) {
		latC = $dfxMap.getAttribute('data-initbg');
	} else {
		latC = 49.980067
	}
    var initZoom = $dfxMap.getAttribute('data-initzoom') > 0 ? $dfxMap.getAttribute('data-initzoom') : 14;
    var mapkey = $dfxMap.getAttribute('data-mapkey');
    var tileserver = $dfxMap.getAttribute('data-tileserver');
    var mapset = $dfxMap.getAttribute('data-mapset');
    var copyright = $dfxMap.getAttribute('data-copyright');

    if (dfxMapSetInstance) {
        dfxMapSetInstance.remove();
        dfxMapSetInstance = null;
    }
    if ($dfxMap._leaflet_id) {
        $dfxMap._leaflet_id = null;
        $dfxMap.innerHTML = '';
    }

    var mymap = L.map('dfxMap').setView([latC, lgC], initZoom);
    dfxMapSetInstance = mymap;
    L.tileLayer(tileserver, {
        attribution: copyright,
        maxZoom: 18,
        id: mapset,
        accessToken: mapkey
    }).addTo(mymap);
    if(lg>0 && bg >0){
        if (marker) {
            mymap.removeLayer(marker);
        }
        marker = L.marker([latC, lgC],{
            draggable: true,
            title: "Klicken Sie auf die Karte oder verschieben Sie das Icon an die gewünschte Position"
        }).addTo(mymap);
        marker.on("dragend",function(e){
            var newPoint = e.target.getLatLng();
            mymap.setView(newPoint, 15);
            if(formname !== 'map'){
                var lgEl3 = document.getElementById(formname + '_lg');
                var bgEl3 = document.getElementById(formname + '_bg');
                if (lgEl3) lgEl3.value = newPoint.lng;
                if (bgEl3) bgEl3.value = newPoint.lat;
            }else{
                var lgEl4 = document.getElementById(formname + '_initLong');
                var bgEl4 = document.getElementById(formname + '_initLat');
                if (lgEl4) lgEl4.value = newPoint.lng;
                if (bgEl4) bgEl4.value = newPoint.lat;
            }

        });


    }else{
        mymap.on("click", function(e) {
            if (marker) {
                mymap.removeLayer(marker);
            }
            var newPoint = e.latlng;
            mymap.setView(newPoint, 15);
            marker = L.marker(newPoint,{
                draggable: true,
                title: "Klicken Sie auf die Karte oder verschieben Sie das Icon an die gewünschte Position"
            }).addTo(mymap);
            if(formname !== 'map'){
                var lgEl5 = document.getElementById(formname + '_lg');
                var bgEl5 = document.getElementById(formname + '_bg');
                if (lgEl5) lgEl5.value = newPoint.lng;
                if (bgEl5) bgEl5.value = newPoint.lat;
            }else{
                var lgEl6 = document.getElementById(formname + '_initLong');
                var bgEl6 = document.getElementById(formname + '_initLat');
                if (lgEl6) lgEl6.value = newPoint.lng;
                if (bgEl6) bgEl6.value = newPoint.lat;
            }
            marker.on("dragend",function(e){
                var newPoint = e.target.getLatLng();
                mymap.setView(newPoint, 15);
                if(formname !== 'map'){
                    var lgEl7 = document.getElementById(formname + '_lg');
                    var bgEl7 = document.getElementById(formname + '_bg');
                    if (lgEl7) lgEl7.value = newPoint.lng;
                    if (bgEl7) bgEl7.value = newPoint.lat;
                }else{
                    var lgEl8 = document.getElementById(formname + '_initLong');
                    var bgEl8 = document.getElementById(formname + '_initLat');
                    if (lgEl8) lgEl8.value = newPoint.lng;
                    if (bgEl8) bgEl8.value = newPoint.lat;
                }
            });
        });
        
    }
    setTimeout(function () { mymap.invalidateSize(); }, 0);
    setTimeout(function () { mymap.invalidateSize(); }, 150);
    setTimeout(function () { mymap.invalidateSize(); }, 400);
}
function showKarte(tid,bg,lg,name){

    if(tid){
            var mapEl = document.getElementById("dfxMap"+tid);
            if (mapEl) mapEl.style.display = 'block';
            console.log("Lade Karte für tid "+tid);
    }else{
            var mapWindows = document.querySelectorAll(".dfx-map-window");
            mapWindows.forEach(function (el) { el.style.display = 'block'; });
			$dfxMap = $dfxMap || document.getElementById('dfxMap');
			if (!$dfxMap) {
				return;
			}
            $dfxMap.style.display = 'block';
            tid='';
            console.log("Lade Karte in Detailansicht");

    }
    var closeEl = document.getElementById("dfxMapClose"+tid);
    if (closeEl) closeEl.style.display = '';
    $dfxKonf = $dfxKonf || document.getElementById("dfx_konf");
    var tileserver = $dfxKonf ? $dfxKonf.getAttribute('data-tileserver') : null;
    var copyright = $dfxKonf ? $dfxKonf.getAttribute('data-copyright') : null;
	var mapEl2 = document.getElementById('dfxMap'+tid);
	if (!mapEl2 || !tileserver || typeof L === 'undefined') {
		console.warn('Karte kann nicht geladen werden: Map-Element, Tile-Server oder Leaflet fehlt.');
		return;
	}
	if (mapEl2.offsetHeight === 0) {
		mapEl2.style.height = '300px';
	}
	// Reset Leaflet state if map was initialized before (e.g. reopen in list/detail)
	if (mapEl2._leaflet_id) {
		mapEl2._leaflet_id = null;
		mapEl2.innerHTML = '';
	}
	var initZoom = mapEl2 && mapEl2.getAttribute('data-initzoom') > 0 ? mapEl2.getAttribute('data-initzoom') : 15;
	var mymap = L.map('dfxMap'+tid).setView([bg, lg], initZoom);
    L.tileLayer(tileserver, {
        attribution: copyright,
        maxZoom: 18

    }).addTo(mymap);
	L.marker([bg, lg]).addTo(mymap);
	setTimeout(function () { mymap.invalidateSize(); }, 0);
	setTimeout(function () { mymap.invalidateSize(); }, 150);
	setTimeout(function () { mymap.invalidateSize(); }, 400);
}
