# Asset-Mapper Deployment

Dev (lokal):
- Stelle sicher, dass APP_ENV=dev und APP_DEBUG=1 gesetzt ist (z.B. in .env.local).

Build/Deploy (Staging/Prod):
- Assets kompilieren, damit sie unter web/assets bereitstehen:
  php bin/console asset-map:compile

Bundle-Assets:
- Bundle-Assets muessen in web/bundles liegen (Originaldateinamen, keine Hashes):
  php bin/console assets:install web

SunEditor:
- SunEditor wird aktuell direkt per CDN eingebunden.
- Die Initialisierung erfolgt lokal ueber:
  web/js/suneditor-init.js
- Relevante Templates/Bases sind:
  templates/base.html.twig
  templates/Admin/base_admin.html.twig
  templates/DfxFrontend/base.html.twig
  templates/Kalender/gast_termin_form.html.twig

Hinweis:
- public-dir ist in composer.json auf "web" gesetzt, daher muessen ausgelieferte Assets unter web/assets liegen.
- Die Datei web/js/dfx_ajax_functions.js stellt bei AJAX-nachgeladenen Formularen sicher, dass SunEditor ebenfalls initialisiert wird.
