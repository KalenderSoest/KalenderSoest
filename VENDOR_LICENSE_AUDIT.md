# Vendor License Audit

Stand: 2026-04-17

Projektlizenz:
- Dieses Projekt ist in [composer.json](./composer.json) als `proprietary` gekennzeichnet.

Zusammenfassung der gefundenen Lizenztypen im `vendor`:

| Lizenz | Anzahl |
| --- | ---: |
| MIT | 131 |
| BSD-3-Clause | 27 |
| Apache-2.0 | 1 |
| LGPL-2.1 | 1 |
| LGPL-2.1-or-later | 2 |
| LGPL-3.0-or-later | 2 |
| OSL-3.0 | 1 |

Hinweis zur Zählung:
- Die Tabelle basiert auf `composer.lock`.
- Frontend-Abhängigkeiten, die direkt per CDN geladen werden, sind nicht Teil dieser Composer-Zählung.

Bewertung:
- `MIT`, `BSD-3-Clause` und `Apache-2.0` sind im vorliegenden Setup typischerweise unkritisch. Üblich ist vor allem die Pflicht, Copyright- und Lizenzhinweise beizubehalten.
- Relevante Prüfpositionen im Composer-Bestand sind aktuell vor allem mehrere produktiv installierte `LGPL`-Pakete, insbesondere im PDF-Umfeld rund um `dompdf`.
- Relevant bleibt ein externer CDN-Bezug von `SunEditor`; nach den offiziellen Paketangaben ist `SunEditor` MIT-lizenziert. Das ist lizenzseitig deutlich unkritischer, bleibt aber als externe Laufzeit-Abhängigkeit ein Supply-Chain- und Pinning-Thema.

## Relevante Pakete

| Paket | Lizenz | Einsatz | Risiko | Einschätzung / Handlung |
| --- | --- | --- | --- | --- |
| `dompdf/dompdf` | LGPL-2.1 | prod | mittel | Direkt produktiv eingebunden in `PdfResponseService`. Bei bloßer Nutzung meist beherrschbar; relevant werden vor allem lokale Änderungen an der Bibliothek selbst und die übliche Beibehaltung von Lizenzhinweisen. |
| `dompdf/php-font-lib` | LGPL-2.1-or-later | prod | mittel | Transitive Abhängigkeit im PDF-Umfeld; Bewertung wie bei anderen LGPL-Bibliotheken. |
| `dompdf/php-svg-lib` | LGPL-3.0-or-later | prod | mittel | Transitive Abhängigkeit; relevant, falls die Bibliothek selbst angepasst und verteilt wird. |
| `ezyang/htmlpurifier` | LGPL-2.1-or-later | prod | mittel | Produktiv installiert und nun serverseitig aktiv zur HTML-Bereinigung im Einsatz. Copyleft bezieht sich primär auf die Bibliothek selbst; relevant werden lokale Änderungen an der Bibliothek. |
| extern geladener `SunEditor` | MIT | prod | niedrig | Aktuell per jsDelivr-CDN eingebunden. Lizenzseitig deutlich unkritischer als der frühere TinyMCE-Einsatz. Praktisch relevant bleiben Pinning, Verfügbarkeit und ggf. Dokumentation der Drittanbieter-Komponente. |

## Konkrete Einschränkungen

### MIT / BSD-3-Clause / Apache-2.0
- Copyright- und Lizenzhinweise beibehalten.
- Keine Copyleft-Pflichten für den proprietären Anwendungscode.
- Bei `Apache-2.0` zusätzlich Patent- und NOTICE-Hinweise beachten, falls vorhanden.

### LGPL-2.1 / LGPL-3.0
- Bei bloßer Nutzung der Bibliothek in der Anwendung besteht typischerweise keine Pflicht, den gesamten Anwendungscode offenzulegen.
- Wenn die Bibliothek selbst geändert wird und diese geänderte Fassung weitergegeben oder ausgeliefert wird, müssen diese Änderungen unter der jeweiligen LGPL verfügbar gemacht werden.
- Lizenztext und Hinweise müssen erhalten bleiben.
- Bei `LGPL-3.0` sind zusätzliche Vorgaben relevant, wenn eine kombinierte Auslieferung die Ersetzung oder modifizierte Nutzung der Bibliothek praktisch verhindert.

## Empfehlung

1. Sicherstellen, dass keine lokalen Patches an `dompdf`, `dompdf/php-font-lib`, `dompdf/php-svg-lib` oder `ezyang/htmlpurifier` gepflegt werden, ohne diese gesondert zu dokumentieren.
2. Falls lokale Patches an Copyleft-Bibliotheken existieren, diese separat dokumentieren und gesondert lizenzrechtlich bewerten.
3. Für Auslieferungen und Deployments eine Third-Party-Notiz mit den verwendeten Lizenztexten bereithalten.
4. Den externen `SunEditor`-Bezug versioniert/pinning-sicher halten und mittelfristig prüfen, ob ein lokales Self-Hosting der Assets gewünscht ist.

## Nachweise

- Projektlizenz: [composer.json](./composer.json)
- Lockfile / Lizenzbasis: [composer.lock](./composer.lock)
- Direkte Produktivnutzung von `dompdf/dompdf`: [src/Service/Presentation/PdfResponseService.php](./src/Service/Presentation/PdfResponseService.php)
- Direkte Produktivnutzung von `ezyang/htmlpurifier`: [src/Service/Content/HtmlContentSanitizer.php](./src/Service/Content/HtmlContentSanitizer.php), [src/EventSubscriber/HtmlContentSanitizerSubscriber.php](./src/EventSubscriber/HtmlContentSanitizerSubscriber.php)
- `dompdf/dompdf`: [vendor/dompdf/dompdf/composer.json](./vendor/dompdf/dompdf/composer.json)
- `dompdf/php-font-lib`: [vendor/dompdf/php-font-lib/composer.json](./vendor/dompdf/php-font-lib/composer.json)
- `dompdf/php-svg-lib`: [vendor/dompdf/php-svg-lib/composer.json](./vendor/dompdf/php-svg-lib/composer.json)
- `ezyang/htmlpurifier`: [vendor/ezyang/htmlpurifier/composer.json](./vendor/ezyang/htmlpurifier/composer.json)
- SunEditor-Einbindung: [templates/base.html.twig](./templates/base.html.twig), [templates/Admin/base_admin.html.twig](./templates/Admin/base_admin.html.twig), [templates/DfxFrontend/base.html.twig](./templates/DfxFrontend/base.html.twig), [templates/Kalender/gast_termin_form.html.twig](./templates/Kalender/gast_termin_form.html.twig), [web/js/suneditor-init.js](./web/js/suneditor-init.js)
- SunEditor-Lizenzhinweis extern: https://www.npmjs.com/package/suneditor , https://suneditor.com/

Hinweis:
- Dies ist eine technische Lizenzprüfung und keine Rechtsberatung.
- Die aktuell auffälligsten Prüfpositionen im Composer-Bestand liegen im `LGPL`-Bereich rund um den produktiv eingesetzten PDF- und Sanitizer-Stack.
