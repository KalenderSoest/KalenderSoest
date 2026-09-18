# Drittanbieter-Komponenten

Dieses Verzeichnis ergänzt [NOTICE.md](NOTICE.md) um eine konkrete Übersicht
der direkt verwendeten Fremdkomponenten. Es ersetzt nicht deren jeweilige
Lizenztexte. Bei Weitergabe des Projekts müssen die in den Paketen enthaltenen
Urheberrechts- und Lizenzhinweise erhalten bleiben.

## PHP-Abhängigkeiten für den Betrieb

Die folgende Momentaufnahme wurde aus `composer.json` und dem vorhandenen
`composer.lock` abgeleitet. `composer.lock` ist für Versionen und transitive
Abhängigkeiten maßgeblich.

| Paket | Version im Lock | Lizenz |
| --- | --- | --- |
| `beberlei/doctrineextensions` | 1.5.0 | BSD-3-Clause |
| `creof/doctrine2-spatial` | 1.2.0 | MIT |
| `doctrine/dbal` | 3.10.6 | MIT |
| `doctrine/doctrine-bundle` | 2.19.1 | MIT |
| `doctrine/doctrine-migrations-bundle` | 3.7.1 | MIT |
| `doctrine/orm` | 3.7.1 | MIT |
| `dompdf/dompdf` | 3.1.6 | LGPL-2.1 |
| `eluceo/ical` | 2.17.0 | MIT |
| `knplabs/knp-paginator-bundle` | 6.11.0 | MIT |
| `liip/imagine-bundle` | 2.17.2 | MIT |
| `phpdocumentor/reflection-docblock` | 5.6.7 | MIT |
| `phpstan/phpdoc-parser` | 2.3.5 | MIT |
| `roromix/spreadsheetbundle` | 3.0.0 | MIT |
| `scssphp/scssphp` | 2.1.0 | MIT |
| `symfony/asset` | 7.4.8 | MIT |
| `symfony/asset-mapper` | 7.4.19 | MIT |
| `symfony/console` | 7.4.19 | MIT |
| `symfony/doctrine-messenger` | 7.4.19 | MIT |
| `symfony/dotenv` | 7.4.18 | MIT |
| `symfony/expression-language` | 7.4.18 | MIT |
| `symfony/flex` | 2.11.0 | MIT |
| `symfony/form` | 7.4.19 | MIT |
| `symfony/framework-bundle` | 7.4.19 | MIT |
| `symfony/http-client` | 7.4.19 | MIT |
| `symfony/intl` | 7.4.17 | MIT |
| `symfony/mailer` | 7.4.19 | MIT |
| `symfony/mime` | 7.4.19 | MIT |
| `symfony/monolog-bundle` | 3.11.2 | MIT |
| `symfony/notifier` | 7.4.17 | MIT |
| `symfony/process` | 7.4.19 | MIT |
| `symfony/property-access` | 7.4.16 | MIT |
| `symfony/property-info` | 7.4.19 | MIT |
| `symfony/runtime` | 7.4.14 | MIT |
| `symfony/security-bundle` | 7.4.18 | MIT |
| `symfony/security-csrf` | 7.4.8 | MIT |
| `symfony/serializer` | 7.4.19 | MIT |
| `symfony/stimulus-bundle` | 2.36.0 | MIT |
| `symfony/string` | 7.4.19 | MIT |
| `symfony/translation` | 7.4.17 | MIT |
| `symfony/twig-bundle` | 7.4.19 | MIT |
| `symfony/ux-turbo` | 2.36.0 | MIT |
| `symfony/validator` | 7.4.19 | MIT |
| `symfony/web-link` | 7.4.8 | MIT |
| `symfony/yaml` | 7.4.18 | MIT |
| `symfonycasts/reset-password-bundle` | 1.25.0 | MIT |
| `twig/extra-bundle` | 3.24.0 | MIT |
| `twig/twig` | 3.28.0 | BSD-3-Clause |
| `wikimedia/less.php` | 5.5.1 | Apache-2.0 |

Die vollständige Liste einschließlich transitiver Produktionsabhängigkeiten
wird nach jeder Änderung des Locks neu erzeugt und geprüft:

```bash
composer licenses --no-dev
composer audit --locked --no-dev
```

Die Lizenzangabe wird aus den Paketmetadaten übernommen und ist vor einer
Veröffentlichung des Distributionsartefakts gegen die mitgelieferten
Lizenzdateien zu prüfen.

## Browser- und Asset-Komponenten

| Komponente | Version | Lizenz | Ablage oder Bezug |
| --- | --- | --- | --- |
| Bootstrap | 5.3.8 | MIT | lokal unter `web/css/` und `web/js/` |
| Font Awesome Free | 7.1.0 | Code MIT, Schriften SIL OFL 1.1, Icons CC BY 4.0 | lokal unter `web/fontawesome/`; Lizenztext in `web/fontawesome/LICENSE.txt` |
| Leaflet | 1.5.1 | BSD-2-Clause | lokal unter `web/js/leaflet/` |
| flatpickr | 4.6.13 | MIT | lokal unter `web/js/flatpickr/` und `web/css/flatpickr/` |
| SunEditor | 2.47.8 | MIT | lokal unter `web/vendor/suneditor/`; Lizenztext in `web/vendor/suneditor/LICENSE.txt` |
| Hotwire Stimulus | 3.2.2 | MIT | Symfony AssetMapper/importmap |
| Hotwire Turbo | 7.3.0 | MIT | Symfony AssetMapper/importmap |

Für lokal kopierte Komponenten ohne beiliegenden vollständigen Lizenztext ist
dieser vor der nächsten Weitergabe aus der unveränderten Upstream-Version zu
ergänzen. Ein Quellkommentar oder diese Tabelle allein erfüllt nicht zwingend
alle Bedingungen der jeweiligen Lizenz.

## Nicht von der Softwarelizenz erfasste Inhalte

Das Förderbanner und die enthaltenen Logos unterliegen den besonderen
Regelungen in [NOTICE.md](NOTICE.md). Von Benutzern hochgeladene Inhalte und
Betreiber-Customizing sind ebenfalls nicht automatisch unter der AGPL oder den
oben genannten Drittanbieterlizenzen freigegeben.
