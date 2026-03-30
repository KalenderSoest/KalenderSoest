# Asset-Mapper Deployment

Dev (lokal):
- Stelle sicher, dass APP_ENV=dev und APP_DEBUG=1 gesetzt ist (z.B. in .env.local).

Build/Deploy (Staging/Prod):
- Assets kompilieren, damit sie unter web/assets bereitstehen:
  php bin/console asset-map:compile

Bundle-Assets:
- Bundle-Assets muessen in web/bundles liegen (Originaldateinamen, keine Hashes):
  php bin/console assets:install web

TinyMCE:
- TinyMCE wird aktuell nicht aus web/assets geladen, sondern direkt aus web/bundles/tinymce.
- Relevante eingebundene Dateien sind insbesondere:
  /bundles/tinymce/ext/tinymce/tinymce.min.js
  /bundles/tinymce/ext/tinymce-webcomponent.js
- Der Asset-Mapper-Baum unter web/assets ist fuer die allgemeine Importmap-Auslieferung relevant, aber nicht fuer die aktive TinyMCE-Einbindung.

Checks:
- Beispiel-URL (sollte 200 liefern):
  /bundles/tinymce/ext/tinymce/plugins/link/plugin.min.js
  /bundles/tinymce/ext/tinymce/icons/default/icons.min.js

Hinweis:
- public-dir ist in composer.json auf "web" gesetzt, daher muessen ausgelieferte Assets unter web/assets liegen.
- Fuer TinyMCE sind folgende Twig-Overrides relevant:
  templates/bundles/TinymceBundle/twig/tinymce_scripts.html.twig
  templates/bundles/TinymceBundle/twig/tinymce_editor.html.twig
  templates/bundles/TinymceBundle/form/tinymce_type.html.twig
- Die Datei web/js/dfx_ajax_functions.js laedt TinyMCE bei Bedarf ebenfalls ueber /bundles/tinymce nach.
