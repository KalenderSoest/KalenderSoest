'use strict';

function asBlock(pElement) {
	pElement.style.display = 'block';
}

function asTable(pElement) {
	pElement.style.display = 'block';
}

function byId(pId) {
	return document.getElementById(pId);
}

function gone(pElement) {
	pElement.style.display = 'none';
}

function dfxSetGalleryIndex(gallery, nextIndex) {
	var slides = Array.prototype.slice.call(gallery.querySelectorAll('[data-dfx-gallery-slide]'));
	var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-dfx-gallery-thumb]'));
	if (!slides.length) {
		return;
	}
	var index = (nextIndex + slides.length) % slides.length;
	slides.forEach(function(slide, slideIndex) {
		var isActive = slideIndex === index;
		slide.classList.toggle('is-active', isActive);
		slide.setAttribute('aria-pressed', isActive ? 'true' : 'false');
	});
	thumbs.forEach(function(thumb, thumbIndex) {
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
	if (!lightboxContent || !slides[currentIndex]) {
		return;
	}
	lightboxContent.innerHTML = slides[currentIndex].innerHTML;
}

function dfxOpenGalleryLightbox(gallery) {
	var lightbox = gallery.querySelector('[data-dfx-gallery-lightbox]');
	if (!lightbox) {
		return;
	}
	dfxRenderGalleryLightbox(gallery);
	lightbox.hidden = false;
	document.body.classList.add('dfx-lightbox-open');
}

function dfxCloseGalleryLightbox(gallery) {
	var lightbox = gallery.querySelector('[data-dfx-gallery-lightbox]');
	if (!lightbox) {
		return;
	}
	lightbox.hidden = true;
	document.body.classList.remove('dfx-lightbox-open');
}

function dfxInitGallery(gallery) {
	if (!gallery || gallery.getAttribute('data-gallery-ready') === 'true') {
		return;
	}
	gallery.setAttribute('data-gallery-ready', 'true');
	dfxSetGalleryIndex(gallery, 0);

	var prevButton = gallery.querySelector('[data-dfx-gallery-prev]');
	if (prevButton) {
		prevButton.addEventListener('click', function() {
			var currentIndex = parseInt(gallery.getAttribute('data-gallery-index') || '0', 10);
			dfxSetGalleryIndex(gallery, currentIndex - 1);
		});
	}

	var nextButton = gallery.querySelector('[data-dfx-gallery-next]');
	if (nextButton) {
		nextButton.addEventListener('click', function() {
			var currentIndex = parseInt(gallery.getAttribute('data-gallery-index') || '0', 10);
			dfxSetGalleryIndex(gallery, currentIndex + 1);
		});
	}

	gallery.querySelectorAll('[data-dfx-gallery-thumb]').forEach(function(thumb) {
		thumb.addEventListener('click', function() {
			dfxSetGalleryIndex(gallery, parseInt(thumb.getAttribute('data-index') || '0', 10));
		});
	});

	gallery.querySelectorAll('[data-dfx-gallery-slide]').forEach(function(slide) {
		slide.addEventListener('click', function() {
			dfxSetGalleryIndex(gallery, parseInt(slide.getAttribute('data-index') || '0', 10));
			dfxOpenGalleryLightbox(gallery);
		});
	});

	var closeButton = gallery.querySelector('[data-dfx-gallery-lightbox-close]');
	if (closeButton) {
		closeButton.addEventListener('click', function() {
			dfxCloseGalleryLightbox(gallery);
		});
	}

	var lightboxPrev = gallery.querySelector('[data-dfx-gallery-lightbox-prev]');
	if (lightboxPrev) {
		lightboxPrev.addEventListener('click', function() {
			var currentIndex = parseInt(gallery.getAttribute('data-gallery-index') || '0', 10);
			dfxSetGalleryIndex(gallery, currentIndex - 1);
			dfxRenderGalleryLightbox(gallery);
		});
	}

	var lightboxNext = gallery.querySelector('[data-dfx-gallery-lightbox-next]');
	if (lightboxNext) {
		lightboxNext.addEventListener('click', function() {
			var currentIndex = parseInt(gallery.getAttribute('data-gallery-index') || '0', 10);
			dfxSetGalleryIndex(gallery, currentIndex + 1);
			dfxRenderGalleryLightbox(gallery);
		});
	}

	var lightbox = gallery.querySelector('[data-dfx-gallery-lightbox]');
	if (lightbox) {
		lightbox.addEventListener('click', function(event) {
			if (event.target && event.target.hasAttribute('data-dfx-gallery-lightbox')) {
				dfxCloseGalleryLightbox(gallery);
			}
		});
	}
}

(function() {
	var app = {
		navViewButtons: document.querySelectorAll('.app-nav-link[data-app-view]'),

		componentHome: byId('component-home'),
		componentCalendar: byId('component-calendar'),
		calendarMonthLabel: byId('calendar-month-label'),
		calendarDays: byId('calendar-days'),
		calendarPrev: byId('calendar-prev'),
		calendarNext: byId('calendar-next'),
		calendarApply: byId('calendar-apply'),
		calendarReset: byId('calendar-reset'),
		calendarFilterFields: document.querySelectorAll('[data-calendar-filter]'),

		containerEvents: byId('container-events'),
		containerEventsRows: byId('container-events-rows'),
		templateEventRow: byId('template-event-row'),
		componentEventsliste: byId('component-eventliste'),
		labelEventslisteEmpty: byId('label-eventliste-empty'),

		containerEvent: byId('container-event'),
		containerEventCard: byId('container-event-card'),
		templateEvent: byId('template-event'),
		componentEvent: byId('component-event'),
		labelEventEmpty: byId('label-event-empty'),

		componentNews: byId('component-news'),
		containerNews: byId('container-news'),
		containerNewsRows: byId('container-news-rows'),
		templateNewsRow: byId('template-news-row'),
		labelNewsEmpty: byId('label-news-empty'),
		spinnerLoadingNews: byId('spinner-loading-news'),

		componentNewsDetail: byId('component-news-detail'),
		containerNewsDetail: byId('container-news-detail'),
		containerNewsDetailCard: byId('container-news-detail-card'),
		templateNewsDetail: byId('template-news-detail'),
		labelNewsDetailEmpty: byId('label-news-detail-empty'),
		spinnerLoadingNewsDetail: byId('spinner-loading-news-detail'),

		paginationGroups: {
			events: {
				wrappers: document.querySelectorAll('#pagination-events-top, #pagination-events-bottom'),
				prevButtons: document.querySelectorAll('[data-pagination-prev="events"]'),
				nextButtons: document.querySelectorAll('[data-pagination-next="events"]'),
				pageLabels: document.querySelectorAll('[data-pagination-page="events"]')
			},
			news: {
				wrappers: document.querySelectorAll('#pagination-news-top, #pagination-news-bottom'),
				prevButtons: document.querySelectorAll('[data-pagination-prev="news"]'),
				nextButtons: document.querySelectorAll('[data-pagination-next="news"]'),
				pageLabels: document.querySelectorAll('[data-pagination-page="news"]')
			}
		},

		spinnerLoadingEvents: byId('spinner-loading-events'),
		spinnerLoadingEvent: byId('spinner-loading-event'),

		events: [],
		event: [],
		newsliste: [],
		news: [],
		eventMap: null,
		kid: null,
		items: 20,
		page: 1,
		activeView: 'events',
		calendarMonth: window.DFX_WEBAPP_CALENDAR_CONFIG && window.DFX_WEBAPP_CALENDAR_CONFIG.currentMonth ? window.DFX_WEBAPP_CALENDAR_CONFIG.currentMonth : '',
		filters: {}
	};

	app.getKid = function() {
		if (window.DFX_WEBAPP_CONFIG && window.DFX_WEBAPP_CONFIG.kid) {
			return String(window.DFX_WEBAPP_CONFIG.kid);
		}
		if (app.kid !== null) {
			return app.kid;
		}
		var params = new URLSearchParams(window.location.search);
		var kid = params.get('kid');
		if (kid !== null && kid !== '') {
			return kid;
		}
		return null;
	};

	app.getQueryParams = function() {
		return new URLSearchParams(window.location.search);
	};

	app.getViewFromQuery = function() {
		var params = app.getQueryParams();
		var view = params.get('view');
		return view === 'news' || view === 'calendar' ? view : 'events';
	};

	app.buildApiUrl = function(path, kid) {
		var base = window.location.origin;
		if (kid) {
			return base + path + '/' + kid;
		}
		return base + path;
	};

	app.updatePaginationUrl = function() {
		var params = app.getQueryParams();
		params.set('view', app.activeView);
		params.set('page', app.page);
		params.set('items', app.items);
		Object.keys(app.filters).forEach(function(key) {
			if (app.filters[key] !== null && app.filters[key] !== '') {
				params.set(key, app.filters[key]);
			} else {
				params.delete(key);
			}
		});
		var newUrl = window.location.pathname + '?' + params.toString();
		window.history.replaceState({}, '', newUrl);
	};

	app.updateViewButtons = function() {
		Array.prototype.forEach.call(app.navViewButtons, function(button) {
			var view = button.getAttribute('data-app-view');
			if (view === app.activeView) {
				button.classList.add('app-nav-link-active');
				button.classList.remove('w3-dark-grey');
			} else {
				button.classList.remove('app-nav-link-active');
				button.classList.remove('w3-dark-grey');
			}
		});
	};

	app.setActiveView = function(view) {
		var previousView = app.activeView;
		app.activeView = view === 'news' || view === 'calendar' ? view : 'events';
		if (app.activeView !== previousView) {
			app.page = 1;
		}
		app.updatePaginationUrl();
		app.updateViewButtons();

		if (app.activeView === 'calendar') {
			app.loadCalendar();
			return;
		}

		if (app.activeView === 'news') {
			app.loadNews();
			return;
		}

		app.loadEvents();
	};

	app.extractId = function(identifier) {
		if (!identifier) {
			return null;
		}
		if (identifier.indexOf('dfx-news-') === 0) {
			return identifier.slice(9);
		}
		if (identifier.indexOf('dfx-') === 0) {
			return identifier.slice(4);
		}
		return identifier;
	};

	app.extractParamFromUrl = function(url, paramName) {
		if (!url || !paramName) {
			return null;
		}
		var marker = paramName + '=';
		var idx = url.indexOf(marker);
		if (idx === -1) {
			return null;
		}
		return url.slice(idx + marker.length).split('&')[0];
	};

	app.extractIdFromUrl = function(url) {
		return app.extractParamFromUrl(url, 'dfxid');
	};

	app.getEventId = function(pEvent) {
		return app.extractId(pEvent.identifier) || app.extractIdFromUrl(pEvent.url);
	};

	app.getNewsId = function(pNews) {
		return app.extractId(pNews.identifier) || app.extractParamFromUrl(pNews.url, 'nfxid');
	};

	app.getDate = function(iso) {
		if (!iso) {
			return '';
		}
		var dateValue = iso.split('T')[0] || '';
		if (!dateValue) {
			return '';
		}
		var parts = dateValue.split('-');
		if (parts.length !== 3) {
			return dateValue;
		}
		return parts[2] + '.' + parts[1] + '.' + parts[0];
	};

	app.getMapUrl = function(location) {
		if (!location) {
			return '';
		}
		var latitude = location.latitude;
		var longitude = location.longitude;
		if (!latitude || !longitude) {
			return '';
		}
		return 'https://www.openstreetmap.org/?mlat=' + encodeURIComponent(latitude) + '&mlon=' + encodeURIComponent(longitude) + '#map=15/' + encodeURIComponent(latitude) + '/' + encodeURIComponent(longitude);
	};

	app.getWeekdayDate = function(iso) {
		if (!iso) {
			return '';
		}
		var dateValue = iso.split('T')[0] || '';
		if (!dateValue) {
			return '';
		}
		var date = new Date(dateValue + 'T00:00:00');
		if (Number.isNaN(date.getTime())) {
			return app.getDate(iso);
		}
		var weekdays = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
		return weekdays[date.getDay()] + ', ' + app.getDate(iso);
	};

	app.getMonthDate = function(monthValue) {
		var source = monthValue || app.calendarMonth || app.formatMonthValue(new Date());
		var parts = source.split('-');
		return new Date(Number(parts[0]), Number(parts[1]) - 1, 1);
	};

	app.formatMonthValue = function(date) {
		return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
	};

	app.formatMonthLabel = function(date) {
		return date.toLocaleDateString('de-DE', { month: 'long', year: 'numeric' });
	};

	app.collectFiltersFromQuery = function() {
		var params = app.getQueryParams();
		[
			'rubrik', 'zielgruppe', 'veranstalter', 'lokal', 'nat', 'region',
			'ort', 'suche', 'plz', 'umkreis', 'datum_von', 'datum_bis', 'm', 't'
		].forEach(function(key) {
			var value = params.get(key);
			if (value !== null && value !== '') {
				app.filters[key] = value;
			}
		});
		if (app.filters.m) {
			app.calendarMonth = app.filters.m;
		}
	};

	app.syncCalendarFilterInputs = function() {
		app.calendarFilterFields.forEach(function(field) {
			var name = field.getAttribute('data-calendar-filter');
			field.value = app.filters[name] || '';
		});
	};

	app.collectCalendarFilters = function() {
		app.calendarFilterFields.forEach(function(field) {
			var name = field.getAttribute('data-calendar-filter');
			app.filters[name] = field.value || '';
		});
	};

	app.buildFilterQuery = function(params) {
		Object.keys(app.filters).forEach(function(key) {
			if (app.filters[key] !== null && app.filters[key] !== '') {
				params.push(encodeURIComponent(key) + '=' + encodeURIComponent(app.filters[key]));
			}
		});
	};

	app.renderEventMap = function(container, location) {
		var latitude = location && location.latitude ? parseFloat(location.latitude) : 0;
		var longitude = location && location.longitude ? parseFloat(location.longitude) : 0;
		if (!container || !latitude || !longitude || typeof L === 'undefined') {
			if (container) {
				container.style.display = 'none';
				container.innerHTML = '';
			}
			if (app.eventMap) {
				app.eventMap.remove();
				app.eventMap = null;
			}
			return;
		}

		if (app.eventMap) {
			app.eventMap.remove();
			app.eventMap = null;
		}

		container.style.display = 'block';
		container.innerHTML = '';

		app.eventMap = L.map(container).setView([latitude, longitude], 15);
		L.tileLayer(window.DFX_WEBAPP_CONFIG.tileserver, {
			attribution: window.DFX_WEBAPP_CONFIG.copyright,
			maxZoom: 18,
			id: window.DFX_WEBAPP_CONFIG.mapset,
			accessToken: window.DFX_WEBAPP_CONFIG.mapkey
		}).addTo(app.eventMap);
		L.marker([latitude, longitude]).addTo(app.eventMap);

		setTimeout(function() {
			if (app.eventMap) {
				app.eventMap.invalidateSize();
			}
		}, 0);
		setTimeout(function() {
			if (app.eventMap) {
				app.eventMap.invalidateSize();
			}
		}, 200);
	};

	app.escapeHtml = function(value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	};

	app.getValidImages = function(pEvent) {
		if (!pEvent || !Array.isArray(pEvent.image)) {
			return [];
		}
		return pEvent.image.filter(function(image) {
			return image && image.contentUrl;
		});
	};

	app.getValidImageUrls = function(payload) {
		if (!payload || !Array.isArray(payload.image)) {
			return [];
		}
		return payload.image.map(function(image) {
			if (typeof image === 'string') {
				return image;
			}
			return image && image.contentUrl ? image.contentUrl : '';
		}).filter(function(urlValue) {
			return !!urlValue;
		});
	};

	app.getPrimaryImageMeta = function(payload) {
		if (!payload || !Array.isArray(payload.image) || !payload.image.length) {
			return { description: '', copyrightHolder: '' };
		}
		var firstImage = payload.image[0];
		if (!firstImage || typeof firstImage === 'string') {
			return { description: '', copyrightHolder: '' };
		}
		return {
			description: firstImage.description || '',
			copyrightHolder: firstImage.copyrightHolder || ''
		};
	};

	app.renderEventMedia = function(container, pEvent) {
		if (!container) {
			return;
		}
		var images = app.getValidImages(pEvent);
		if (!images.length) {
			container.innerHTML = '';
			container.style.display = 'none';
			return;
		}

		container.style.display = '';
		if (images.length === 1) {
			container.innerHTML = '<img class="bild app-single-image" src="' + app.escapeHtml(images[0].contentUrl) + '" alt="' + app.escapeHtml(images[0].description || pEvent.name || 'Eventbild') + '">';
			return;
		}

		var slides = '';
		var thumbs = '';
		images.forEach(function(image, index) {
			var isActive = index === 0 ? ' is-active' : '';
			var alt = app.escapeHtml(image.description || pEvent.name || ('Bild ' + (index + 1)));
			var src = app.escapeHtml(image.contentUrl);
			slides += '<button type="button" class="dfx-gallery-slide' + isActive + '" data-dfx-gallery-slide data-index="' + index + '" aria-label="Bild ' + (index + 1) + ' anzeigen"><img src="' + src + '" alt="' + alt + '"></button>';
			thumbs += '<button type="button" class="dfx-gallery-thumb' + isActive + '" data-dfx-gallery-thumb data-index="' + index + '" aria-label="Bild ' + (index + 1) + ' auswählen"><img src="' + src + '" alt="' + alt + '"></button>';
		});

		container.innerHTML = '<div class="dfx-gallery" data-dfx-gallery data-gallery-title="' + app.escapeHtml(pEvent.name || 'Bild') + '">' +
			'<div class="dfx-gallery-stage">' +
			'<button type="button" class="dfx-gallery-nav dfx-gallery-nav-prev" data-dfx-gallery-prev aria-label="Vorheriges Bild">&#8249;</button>' +
			'<div class="dfx-gallery-slides">' + slides + '</div>' +
			'<button type="button" class="dfx-gallery-nav dfx-gallery-nav-next" data-dfx-gallery-next aria-label="Nächstes Bild">&#8250;</button>' +
			'</div>' +
			'<div class="dfx-gallery-thumbs" data-dfx-gallery-thumbs>' + thumbs + '</div>' +
			'<div class="dfx-gallery-lightbox" data-dfx-gallery-lightbox hidden>' +
			'<button type="button" class="dfx-gallery-lightbox-close" data-dfx-gallery-lightbox-close aria-label="Lightbox schließen">&times;</button>' +
			'<button type="button" class="dfx-gallery-lightbox-nav dfx-gallery-lightbox-prev" data-dfx-gallery-lightbox-prev aria-label="Vorheriges Bild">&#8249;</button>' +
			'<div class="dfx-gallery-lightbox-content" data-dfx-gallery-lightbox-content></div>' +
			'<button type="button" class="dfx-gallery-lightbox-nav dfx-gallery-lightbox-next" data-dfx-gallery-lightbox-next aria-label="Nächstes Bild">&#8250;</button>' +
			'</div>' +
			'</div>';

	};

	app.renderNewsMedia = function(container, pNews) {
		if (!container) {
			return;
		}
		var imageUrls = app.getValidImageUrls(pNews);
		if (!imageUrls.length) {
			container.innerHTML = '';
			container.style.display = 'none';
			return;
		}

		container.style.display = '';
		if (imageUrls.length === 1) {
			container.innerHTML = '<img class="news-image app-single-image" src="' + app.escapeHtml(imageUrls[0]) + '" alt="' + app.escapeHtml(pNews.headline || 'Newsbild') + '">';
			return;
		}

		var slides = '';
		var thumbs = '';
		imageUrls.forEach(function(urlValue, index) {
			var isActive = index === 0 ? ' is-active' : '';
			var alt = app.escapeHtml(pNews.headline || ('Bild ' + (index + 1)));
			var src = app.escapeHtml(urlValue);
			slides += '<button type="button" class="dfx-gallery-slide' + isActive + '" data-dfx-gallery-slide data-index="' + index + '" aria-label="Bild ' + (index + 1) + ' anzeigen"><img src="' + src + '" alt="' + alt + '"></button>';
			thumbs += '<button type="button" class="dfx-gallery-thumb' + isActive + '" data-dfx-gallery-thumb data-index="' + index + '" aria-label="Bild ' + (index + 1) + ' auswählen"><img src="' + src + '" alt="' + alt + '"></button>';
		});

		container.innerHTML = '<div class="dfx-gallery" data-dfx-gallery data-gallery-title="' + app.escapeHtml(pNews.headline || 'Bild') + '">' +
			'<div class="dfx-gallery-stage">' +
			'<button type="button" class="dfx-gallery-nav dfx-gallery-nav-prev" data-dfx-gallery-prev aria-label="Vorheriges Bild">&#8249;</button>' +
			'<div class="dfx-gallery-slides">' + slides + '</div>' +
			'<button type="button" class="dfx-gallery-nav dfx-gallery-nav-next" data-dfx-gallery-next aria-label="Nächstes Bild">&#8250;</button>' +
			'</div>' +
			'<div class="dfx-gallery-thumbs" data-dfx-gallery-thumbs>' + thumbs + '</div>' +
			'<div class="dfx-gallery-lightbox" data-dfx-gallery-lightbox hidden>' +
			'<button type="button" class="dfx-gallery-lightbox-close" data-dfx-gallery-lightbox-close aria-label="Lightbox schließen">&times;</button>' +
			'<button type="button" class="dfx-gallery-lightbox-nav dfx-gallery-lightbox-prev" data-dfx-gallery-lightbox-prev aria-label="Vorheriges Bild">&#8249;</button>' +
			'<div class="dfx-gallery-lightbox-content" data-dfx-gallery-lightbox-content></div>' +
			'<button type="button" class="dfx-gallery-lightbox-nav dfx-gallery-lightbox-next" data-dfx-gallery-lightbox-next aria-label="Nächstes Bild">&#8250;</button>' +
			'</div>' +
			'</div>';

	};

	app.initGalleriesWithin = function(container) {
		if (!container) {
			return;
		}
		container.querySelectorAll('[data-dfx-gallery]').forEach(function(gallery) {
			dfxInitGallery(gallery);
		});
	};

	app.setLink = function(element, url, label) {
		if (!element) {
			return;
		}
		if (url) {
			element.href = url;
			element.textContent = label || element.textContent || '';
			element.style.display = '';
		} else {
			element.removeAttribute('href');
			element.style.display = 'none';
		}
	};

	app.getTime = function(iso) {
		if (!iso || iso.indexOf('T') === -1) {
			return '';
		}
		var timeValue = iso.split('T')[1] || '';
		if (timeValue.length >= 5) {
			return timeValue.slice(0, 5);
		}
		return timeValue;
	};

	app.getOfferText = function(offers) {
		if (!offers || typeof offers !== 'object') {
			return '';
		}
		if (offers.price) {
			return 'Eintritt: ' + offers.price;
		}
		return '';
	};

	app.buildOrganizerHtml = function(pEvent) {
		var blocks = [];
		var organizer = pEvent && pEvent.organizer ? pEvent.organizer : null;
		var address = organizer && organizer.address ? organizer.address : null;
		var contact = organizer && organizer.contactPoint ? organizer.contactPoint : null;

		if (organizer && organizer.name) {
			blocks.push(organizer.name);
		}
		if (address && address.streetAddress) {
			blocks.push(address.streetAddress);
		}
		if (address && (address.postalCode || address.addressLocality)) {
			blocks.push((address.postalCode || '') + ' ' + (address.addressLocality || ''));
		}
		if (contact && contact.telephone) {
			blocks.push('Telefon: ' + contact.telephone);
		}
		if (contact && contact.email) {
			blocks.push('<a href="mailto:' + contact.email + '">' + contact.email + '</a>');
		}
		if (organizer && organizer.url) {
			blocks.push('<a href="' + organizer.url + '" target="_blank" rel="noopener noreferrer">' + organizer.url + '</a>');
		}

		return blocks.join('<br>');
	};

	app.buildContactLinksHtml = function(pEvent) {
		var links = [];
		var organizer = pEvent && pEvent.organizer ? pEvent.organizer : null;
		var contact = organizer && organizer.contactPoint ? organizer.contactPoint : null;

		if (contact && contact.email) {
			links.push('<a href="mailto:' + contact.email + '">Mail an Veranstalter</a>');
		}
		if (pEvent && pEvent.url) {
			links.push('<a href="' + pEvent.url + '" target="_blank" rel="noopener noreferrer">Termine ' + (pEvent.name || '') + '</a>');
		}
		if (pEvent && pEvent.sameAs) {
			links.push('<a href="' + pEvent.sameAs + '" target="_blank" rel="noopener noreferrer">Weitere Informationen</a>');
		}

		return links.join('');
	};

	app.setHrefTextLink = function(element, url, label) {
		if (!element) {
			return;
		}
		if (url) {
			element.href = url;
			element.textContent = label || url;
			element.style.display = '';
		} else {
			element.removeAttribute('href');
			element.textContent = '';
			element.style.display = 'none';
		}
	};

	app.setText = function(element, text) {
		if (!element) {
			return;
		}
		if (text) {
			element.textContent = text;
			element.style.display = '';
		} else {
			element.textContent = '';
			element.style.display = 'none';
		}
	};

	app.setHtml = function(element, html) {
		if (!element) {
			return;
		}
		if (html) {
			element.innerHTML = html;
			element.style.display = '';
		} else {
			element.innerHTML = '';
			element.style.display = 'none';
		}
	};

	app.setImage = function(imageElement, url) {
		if (!imageElement) {
			return;
		}
		var wrapper = imageElement.parentElement;
		if (url) {
			imageElement.src = url;
			imageElement.style.display = '';
			if (wrapper) {
				wrapper.style.display = '';
			}
		} else {
			imageElement.removeAttribute('src');
			imageElement.style.display = 'none';
			if (wrapper) {
				wrapper.style.display = 'none';
			}
		}
	};

	Array.prototype.forEach.call(app.navViewButtons, function(button) {
		button.addEventListener('click', function(pEvent) {
			pEvent.preventDefault();
			var view = button.getAttribute('data-app-view');
			app.setActiveView(view);
			if (typeof closeSideNav === 'function') {
				closeSideNav();
			}
		});
	});

	if (app.containerEventsRows) {
		app.containerEventsRows.addEventListener('click', function(pEvent) {
			var button = pEvent.target.closest('.liste-detail-button');
			if (!button) {
				return;
			}
			var tid = button.getAttribute('data-tid') || button.id;
			if (!tid) {
				var row = button.closest('.event');
				tid = row ? row.getAttribute('data-tid') : null;
			}
			if (tid) {
				app.loadEvent(tid);
			}
		});
	}

	if (app.containerNewsRows) {
		app.containerNewsRows.addEventListener('click', function(pEvent) {
			var button = pEvent.target.closest('.news-detail-button');
			if (!button) {
				return;
			}
			var nfxid = button.getAttribute('data-nfxid');
			if (nfxid) {
				app.loadNewsDetail(nfxid);
			}
		});
	}

	if (app.containerEventCard) {
		app.containerEventCard.addEventListener('click', function(pEvent) {
			if (pEvent.target.closest('.back-button')) {
				gone(app.componentEvent);
				asBlock(app.componentEventsliste);
			}
		});
	}

	if (app.containerNewsDetailCard) {
		app.containerNewsDetailCard.addEventListener('click', function(pEvent) {
			if (pEvent.target.closest('.news-back-button')) {
				gone(app.componentNewsDetail);
				asBlock(app.componentNews);
			}
		});
	}

	if (app.calendarPrev) {
		app.calendarPrev.addEventListener('click', function() {
			var current = app.getMonthDate(app.calendarMonth);
			current.setMonth(current.getMonth() - 1);
			app.calendarMonth = app.formatMonthValue(current);
			app.filters.m = app.calendarMonth;
			delete app.filters.t;
			app.updatePaginationUrl();
			app.loadCalendar();
		});
	}

	if (app.calendarNext) {
		app.calendarNext.addEventListener('click', function() {
			var current = app.getMonthDate(app.calendarMonth);
			current.setMonth(current.getMonth() + 1);
			app.calendarMonth = app.formatMonthValue(current);
			app.filters.m = app.calendarMonth;
			delete app.filters.t;
			app.updatePaginationUrl();
			app.loadCalendar();
		});
	}

	if (app.calendarApply) {
		app.calendarApply.addEventListener('click', function() {
			app.collectCalendarFilters();
			app.calendarMonth = app.filters.m || app.calendarMonth || app.formatMonthValue(new Date());
			app.page = 1;
			app.setActiveView('events');
		});
	}

	if (app.calendarReset) {
		app.calendarReset.addEventListener('click', function() {
			app.filters = {};
			app.calendarMonth = window.DFX_WEBAPP_CALENDAR_CONFIG && window.DFX_WEBAPP_CALENDAR_CONFIG.currentMonth ? window.DFX_WEBAPP_CALENDAR_CONFIG.currentMonth : app.formatMonthValue(new Date());
			app.syncCalendarFilterInputs();
			app.updatePaginationUrl();
			app.loadCalendar();
		});
	}

	app.displayAppComponent = function(pComponent) {
		document.querySelectorAll('.app-component').forEach(function(component) {
			gone(component);
		});
		asBlock(pComponent);
	};

	app.initApp = function() {
		app.collectFiltersFromQuery();
		app.syncCalendarFilterInputs();
		var params = app.getQueryParams();
		var page = parseInt(params.get('page') || '1', 10);
		var items = parseInt(params.get('items') || '20', 10);
		app.page = page > 0 ? page : 1;
		app.items = items > 0 ? items : 20;
		app.setActiveView(app.getViewFromQuery());
	};

	app.loadEvents = function() {
		asBlock(app.spinnerLoadingEvents);
		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState === XMLHttpRequest.DONE) {
				if (request.status === 200) {
					app.events = JSON.parse(request.response);
					app.updateEventslisteUI(app.events);
				} else {
					app.setText(app.labelEventslisteEmpty, 'Events konnten nicht geladen werden (HTTP ' + request.status + ')');
					asBlock(app.labelEventslisteEmpty);
					gone(app.containerEvents);
					gone(app.spinnerLoadingEvents);
				}
			}
		};
		var kid = app.getKid();
		var params = ['items=' + encodeURIComponent(app.items), 'page=' + encodeURIComponent(app.page)];
		app.buildFilterQuery(params);
		var url = app.buildApiUrl('/api/kalender', kid) + '?' + params.join('&');
		request.open('GET', url);
		request.send();
	};

	app.loadNews = function() {
		asBlock(app.spinnerLoadingNews);
		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState === XMLHttpRequest.DONE) {
				if (request.status === 200) {
					app.newsliste = JSON.parse(request.response);
					app.updateNewslisteUI(app.newsliste);
				} else {
					app.setText(app.labelNewsEmpty, 'News konnten nicht geladen werden (HTTP ' + request.status + ')');
					asBlock(app.labelNewsEmpty);
					gone(app.containerNews);
					gone(app.spinnerLoadingNews);
				}
			}
		};
		var kid = app.getKid();
		var url = app.buildApiUrl('/api/news', kid) + '?items=' + encodeURIComponent(app.items) + '&page=' + encodeURIComponent(app.page);
		request.open('GET', url);
		request.send();
	};

	app.loadCalendar = function() {
		if (!app.calendarMonth) {
			app.calendarMonth = app.formatMonthValue(new Date());
		}
		app.filters.m = app.calendarMonth;
		delete app.filters.t;
		app.syncCalendarFilterInputs();

		var monthDate = app.getMonthDate(app.calendarMonth);
		if (app.calendarMonthLabel) {
			app.calendarMonthLabel.textContent = app.formatMonthLabel(monthDate);
		}

		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState === XMLHttpRequest.DONE) {
				if (request.status === 200) {
					app.renderCalendar(JSON.parse(request.response), monthDate);
				}
			}
		};
		var kid = app.getKid();
		var params = ['items=' + encodeURIComponent(window.DFX_WEBAPP_CONFIG.maxApiItems || 1000), 'page=1'];
		app.buildFilterQuery(params);
		var url = app.buildApiUrl('/api/kalender', kid) + '?' + params.join('&');
		request.open('GET', url);
		request.send();
		app.displayAppComponent(app.componentCalendar);
	};

	app.renderCalendar = function(events, monthDate) {
		if (!app.calendarDays) {
			return;
		}
		var eventDays = {};
		(events || []).forEach(function(item) {
			if (item && item.startDate) {
				eventDays[item.startDate.split('T')[0]] = true;
			}
		});
		app.calendarDays.innerHTML = '';
		var firstDay = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1);
		var startOffset = (firstDay.getDay() + 6) % 7;
		var visibleStart = new Date(firstDay);
		visibleStart.setDate(firstDay.getDate() - startOffset);

		for (var i = 0; i < 42; i += 1) {
			var day = new Date(visibleStart);
			day.setDate(visibleStart.getDate() + i);
			var y = day.getFullYear();
			var m = String(day.getMonth() + 1).padStart(2, '0');
			var d = String(day.getDate()).padStart(2, '0');
			var dateKey = y + '-' + m + '-' + d;
			var button = document.createElement('button');
			button.type = 'button';
			button.className = 'app-calendar-day' + (day.getMonth() !== monthDate.getMonth() ? ' is-outside' : '') + (eventDays[dateKey] ? ' is-active' : '') + (app.filters.t === dateKey ? ' is-selected' : '');
			button.textContent = String(day.getDate());
			if (!eventDays[dateKey]) {
				button.disabled = true;
			} else {
				button.addEventListener('click', function(selectedDate) {
					return function() {
						app.filters.t = selectedDate;
						app.page = 1;
						app.setActiveView('events');
					};
				}(dateKey));
			}
			app.calendarDays.appendChild(button);
		}
	};

	app.updateEventslisteUI = function(pEvents) {
		while (app.containerEventsRows.firstChild) {
			app.containerEventsRows.removeChild(app.containerEventsRows.firstChild);
		}

		if (pEvents.length > 0) {
			pEvents.forEach(function(pEvent) {
				var row = app.templateEventRow.cloneNode(true);
				row.removeAttribute('id');
				row.removeAttribute('hidden');
				var id = app.getEventId(pEvent);
				var imageUrl = pEvent.image && pEvent.image.length > 0 ? pEvent.image[0].contentUrl : '';
				app.setText(row.querySelector('.titel'), pEvent.name || '');
				row.querySelector('.titel').href = pEvent.url || '#';
				app.setImage(row.querySelector('.bild'), imageUrl || '');
				app.setText(row.querySelector('.subtitel'), pEvent.alternateName || '');
				app.setText(row.querySelector('.datumvon'), app.getDate(pEvent.startDate));
				app.setText(row.querySelector('.datumbis'), pEvent.endDate && app.getDate(pEvent.startDate) !== app.getDate(pEvent.endDate) ? 'bis ' + app.getDate(pEvent.endDate) : '');
				app.setText(row.querySelector('.zeitab'), app.getTime(pEvent.startDate));
				app.setText(row.querySelector('.zeitbis'), app.getTime(pEvent.endDate) ? 'bis ' + app.getTime(pEvent.endDate) : '');
				app.setText(row.querySelector('.metadate'), '');
				app.setText(row.querySelector('.lokal'), pEvent.location && pEvent.location.name ? pEvent.location.name : '');
				app.setText(row.querySelector('.plz'), pEvent.location && pEvent.location.address ? pEvent.location.address.postalCode || '' : '');
				app.setText(row.querySelector('.ort'), pEvent.location && pEvent.location.address ? pEvent.location.address.addressLocality || '' : '');
				app.setText(row.querySelector('.strasse'), pEvent.location && pEvent.location.address ? pEvent.location.address.streetAddress || '' : '');
				if (id) {
					row.setAttribute('data-tid', id);
				}
				var detailButton = row.querySelector('.liste-detail-button');
				if (id && detailButton) {
					detailButton.setAttribute('data-tid', id);
					detailButton.setAttribute('id', id);
				}
				app.containerEventsRows.appendChild(row);
			});

			gone(app.labelEventslisteEmpty);
			asTable(app.containerEvents);
		} else {
			asBlock(app.labelEventslisteEmpty);
			gone(app.containerEvents);
		}

		gone(app.spinnerLoadingEvents);
		app.updatePaginationUI(pEvents.length);
		app.displayAppComponent(app.componentEventsliste);
	};

	app.updateNewslisteUI = function(pNewsList) {
		while (app.containerNewsRows.firstChild) {
			app.containerNewsRows.removeChild(app.containerNewsRows.firstChild);
		}

		if (pNewsList.length > 0) {
			pNewsList.forEach(function(pNews) {
				var row = app.templateNewsRow.cloneNode(true);
				row.removeAttribute('id');
				row.removeAttribute('hidden');

				var newsId = app.getNewsId(pNews);
				var imageUrls = app.getValidImageUrls(pNews);

				app.setText(row.querySelector('.news-title'), pNews.headline || '');
				app.setText(row.querySelector('.news-subtitle'), pNews.alternativeHeadline || '');
				app.setText(row.querySelector('.news-date'), pNews.datePublished ? '(' + app.getDate(pNews.datePublished) + ')' : '');
				app.setHtml(row.querySelector('.news-excerpt'), pNews.description || pNews.articleBody || '');
				app.setImage(row.querySelector('.news-image'), imageUrls.length ? imageUrls[0] : '');

				var detailButton = row.querySelector('.news-detail-button');
				if (detailButton && newsId) {
					detailButton.setAttribute('data-nfxid', newsId);
					detailButton.style.display = '';
				} else if (detailButton) {
					detailButton.removeAttribute('data-nfxid');
					detailButton.style.display = 'none';
				}

				app.containerNewsRows.appendChild(row);
			});

			gone(app.labelNewsEmpty);
			asBlock(app.containerNews);
		} else {
			asBlock(app.labelNewsEmpty);
			gone(app.containerNews);
		}

		gone(app.spinnerLoadingNews);
		app.updatePaginationUI(pNewsList.length);
		app.displayAppComponent(app.componentNews);
	};

	app.loadNewsDetail = function(pNfxid) {
		if (!pNfxid) {
			return;
		}
		asBlock(app.spinnerLoadingNewsDetail);

		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState === XMLHttpRequest.DONE) {
				if (request.status === 200) {
					app.news = JSON.parse(request.response);
					app.showNewsDetailUI(app.news);
				} else {
					app.setText(app.labelNewsDetailEmpty, 'News-Details konnten nicht geladen werden (HTTP ' + request.status + ')');
					asBlock(app.labelNewsDetailEmpty);
					gone(app.containerNewsDetail);
					gone(app.spinnerLoadingNewsDetail);
				}
			}
		};
		request.open('GET', app.buildApiUrl('/api/news/detail', null) + '/' + pNfxid);
		request.send();
	};

	app.showNewsDetailUI = function(pNews) {
		while (app.containerNewsDetailCard.firstChild) {
			app.containerNewsDetailCard.removeChild(app.containerNewsDetailCard.firstChild);
		}

		if (pNews) {
			var row = app.templateNewsDetail.cloneNode(true);
			row.removeAttribute('id');
			row.removeAttribute('hidden');

			app.setText(row.querySelector('.news-title'), pNews.headline || '');
			app.setText(row.querySelector('.news-subtitle'), pNews.alternativeHeadline || '');
			app.setText(row.querySelector('.news-imgtext'), app.getPrimaryImageMeta(pNews).description);
			app.setText(row.querySelector('.news-imgcopyright'), app.getPrimaryImageMeta(pNews).copyrightHolder);
			app.setText(row.querySelector('.news-date-end'), pNews.datePublished ? '(' + app.getDate(pNews.datePublished) + ')' : '');
			app.setHtml(row.querySelector('.news-excerpt'), pNews.description || '');
			app.setHtml(row.querySelector('.news-body'), pNews.articleBody || '');
			app.renderNewsMedia(row.querySelector('.news-media-slot'), pNews);

			app.containerNewsDetailCard.appendChild(row);
			app.initGalleriesWithin(row);
			gone(app.labelNewsDetailEmpty);
			asBlock(app.containerNewsDetail);
		} else {
			asBlock(app.labelNewsDetailEmpty);
			gone(app.containerNewsDetail);
		}

		gone(app.spinnerLoadingNewsDetail);
		app.displayAppComponent(app.componentNewsDetail);
	};

	app.loadEvent = function(pTid) {
		asBlock(app.spinnerLoadingEvent);
		var request = new XMLHttpRequest();
		request.onreadystatechange = function() {
			if (request.readyState === XMLHttpRequest.DONE) {
				if (request.status === 200) {
					app.event = JSON.parse(request.response);
					app.showEventUI(app.event);
				} else {
					app.setText(app.labelEventEmpty, 'Event konnte nicht geladen werden (HTTP ' + request.status + ')');
					asBlock(app.labelEventEmpty);
					gone(app.containerEvent);
					gone(app.spinnerLoadingEvent);
				}
			}
		};
		request.open('GET', app.buildApiUrl('/api/detail', null) + '/' + pTid);
		request.send();
	};

	app.showEventUI = function(pEvent) {
		while (app.containerEventCard.firstChild) {
			app.containerEventCard.removeChild(app.containerEventCard.firstChild);
		}
		if (pEvent) {
			var row = app.templateEvent.cloneNode(true);
			row.removeAttribute('id');
			row.removeAttribute('hidden');
			app.setText(row.querySelector('.titel'), pEvent.name || '');
			app.renderEventMedia(row.querySelector('.app-media-slot'), pEvent);
			app.setText(row.querySelector('.imgtext'), pEvent.image && pEvent.image[0] ? pEvent.image[0].description || '' : '');
			app.setText(row.querySelector('.imgcopyright'), pEvent.image && pEvent.image[0] ? pEvent.image[0].copyrightHolder || '' : '');
			app.setHtml(row.querySelector('.beschreibung'), pEvent.description || '');
			app.setText(row.querySelector('.datumvon'), app.getWeekdayDate(pEvent.startDate));
			app.setText(row.querySelector('.datumbis'), pEvent.endDate && app.getDate(pEvent.startDate) !== app.getDate(pEvent.endDate) ? 'bis ' + app.getDate(pEvent.endDate) : '');
			app.setText(row.querySelector('.zeitab'), app.getTime(pEvent.startDate) ? app.getTime(pEvent.startDate) + ' Uhr' : '');
			app.setText(row.querySelector('.zeitbis'), app.getTime(pEvent.endDate) ? ' bis ' + app.getTime(pEvent.endDate) + ' Uhr' : '');
			app.setText(row.querySelector('.metadate'), '');
			app.setText(row.querySelector('.lokal'), pEvent.location && pEvent.location.name ? pEvent.location.name : '');
			app.setText(row.querySelector('.plz'), pEvent.location && pEvent.location.address ? pEvent.location.address.postalCode || '' : '');
			app.setText(row.querySelector('.ort'), pEvent.location && pEvent.location.address ? pEvent.location.address.addressLocality || '' : '');
			app.setText(row.querySelector('.strasse'), pEvent.location && pEvent.location.address ? pEvent.location.address.streetAddress || '' : '');
			app.setHrefTextLink(row.querySelector('.lokal-mail'), pEvent.location && pEvent.location.email ? 'mailto:' + pEvent.location.email : '', pEvent.location && pEvent.location.email ? pEvent.location.email : '');
			app.setHrefTextLink(row.querySelector('.lokal-web'), pEvent.location && pEvent.location.sameAs ? pEvent.location.sameAs : '', pEvent.location && pEvent.location.sameAs ? pEvent.location.sameAs : '');
			app.setHtml(row.querySelector('.veranstalter-info'), app.buildOrganizerHtml(pEvent));
			app.setHtml(row.querySelector('.kontakt-links'), app.buildContactLinksHtml(pEvent));
			app.setText(row.querySelector('.eintritt'), app.getOfferText(pEvent.offers));
			app.renderEventMap(row.querySelector('.app-map-canvas'), pEvent.location);
			app.containerEventCard.appendChild(row);
			app.initGalleriesWithin(row);

			gone(app.labelEventEmpty);
			asBlock(app.containerEvent);
		} else {
			asBlock(app.labelEventEmpty);
			gone(app.containerEvent);
		}
		gone(app.spinnerLoadingEvent);
		app.displayAppComponent(app.componentEvent);
	};

	app.updatePaginationUI = function(count) {
		Object.keys(app.paginationGroups).forEach(function(view) {
			var group = app.paginationGroups[view];
			if (!group) {
				return;
			}
			group.wrappers.forEach(function(wrapper) {
				if (view === app.activeView && app.activeView !== 'calendar') {
					asBlock(wrapper);
				} else {
					gone(wrapper);
				}
			});
			if (view !== app.activeView) {
				return;
			}
			group.pageLabels.forEach(function(label) {
				label.textContent = 'Seite ' + app.page;
			});
			group.prevButtons.forEach(function(button) {
				button.disabled = app.page <= 1;
			});
			group.nextButtons.forEach(function(button) {
				button.disabled = count < app.items;
			});
		});
	};

	document.querySelectorAll('[data-pagination-prev]').forEach(function(button) {
		button.addEventListener('click', function() {
			var view = button.getAttribute('data-pagination-prev');
			if (app.activeView !== view) {
				return;
			}
			if (app.page > 1) {
				app.page -= 1;
				app.updatePaginationUrl();
				if (view === 'news') {
					app.loadNews();
					return;
				}
				app.loadEvents();
			}
		});
	});

	document.querySelectorAll('[data-pagination-next]').forEach(function(button) {
		button.addEventListener('click', function() {
			var view = button.getAttribute('data-pagination-next');
			if (app.activeView !== view) {
				return;
			}
			app.page += 1;
			app.updatePaginationUrl();
			if (view === 'news') {
				app.loadNews();
				return;
			}
			app.loadEvents();
		});
	});

	app.initApp();

	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register('/webapp/service-worker.js');
	}
})();

