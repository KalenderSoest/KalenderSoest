# Teststrategie

## Aktueller Stand

Das Projekt bringt PHPUnit und die Symfony-Testwerkzeuge als
Entwicklungsabhängigkeiten mit. Unter `tests/` existieren derzeit neben dem
Bootstrap Unit-Tests für den `FrontendBridgeService` und für die
Freigabeprüfung des API-Termin-Detailendpunkts. Eine breitere Abdeckung durch
Integrations- und Anwendungstests fehlt noch. Eine erfolgreiche Installation
oder ein manueller Seitenaufruf ersetzt daher gegenwärtig keine
Regressionstests.

`tests/` und `phpunit.xml.dist` sind für die Versionierung freigegeben. Die
XML-Konfiguration verwendet das mit der installierten PHPUnit-11-Version
gelieferte Schema. Der Test-Bootstrap erlaubt reine Unit-Tests auch ohne lokale
Produktionskonfiguration; Integrationstests benötigen weiterhin eine
ausdrücklich konfigurierte Testumgebung.

## Sichere Testumgebung

Tests dürfen niemals die Produktionsdatenbank verwenden. Die aktuelle
Bootstrap-Datei erwartet eine Basisdatei `.env`; testspezifische Werte gehören
in `.env.test` beziehungsweise die nicht versionierte `.env.test.local`.

Minimaler lokaler Ablauf:

```bash
composer install
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:schema:create
php bin/phpunit
```

Falls die Testdatenbank bewusst über Migrationen aufgebaut werden soll, wird
anstelle von `doctrine:schema:create` der geprüfte Migrationspfad verwendet.
Vor jedem Lauf ist zu kontrollieren, dass `DATABASE_URL` auf eine entbehrliche,
isolierte Testdatenbank zeigt.

Symfony unterscheidet zweckmäßig:

- Unit-Tests für reine Fachlogik ohne Kernel oder Datenbank,
- Integrationstests für Services, Doctrine-Mappings und Konfiguration,
- Anwendungstests über den HTTP-Kernel für Routen, Formulare und Rechte.

Tests müssen unabhängig voneinander sein und ihren Datenbestand selbst
erzeugen und bereinigen.

## Priorisierte automatisierte Tests

Die erste Testsuite sollte folgende Risiken in dieser Reihenfolge abdecken:

1. Zugriffsregeln: Admin-, Installer-, Update- und mutierende Routen sowie
   CSRF-Schutz.
2. API-Freigabe: Kalender- und Newslisten sowie beide Detailendpunkte dürfen
   nur freigegebene und veröffentlichte Inhalte liefern.
3. Installation und Migration: frische Datenbank, erkannte Altstände,
   generierte Migration und idempotenter Statusablauf.
4. Schreibabläufe für Termine und News einschließlich Validierung, Freigabe,
   Benachrichtigung und Rechteprüfung.
5. Uploads: Größen, MIME-Typen, Pfade, Dateinamen und unzulässige Inhalte.
6. Import/Export und Schema.org-Renderer einschließlich Custom-Renderer.
7. Template-Auflösung und kalenderbezogene Overrides.

Für behobene Fehler wird vor oder zusammen mit der Korrektur ein
Regressionstest ergänzt.

## Smoke-Tests für ein Release

Bis die automatisierte Abdeckung ausreicht, sind in einer isolierten
Staging-Umgebung mindestens manuell zu prüfen:

- Startseite sowie Kalenderliste und Termindetail,
- Login, Logout und ein repräsentativer Admin-Schreibvorgang,
- Newsliste und Newsdetail,
- Bild-, PDF- und Medienupload,
- die vier dokumentierten API-Endpunkte einschließlich gesperrter Inhalte,
- Mailversand,
- Export und PDF-Erzeugung,
- Installations-/Updateplan und Datenbankmigration,
- Darstellung und Erhalt des Förderbanners.

Das Ergebnis wird mit Commit, PHP-/Datenbankversion, Testdatum und bekannten
Abweichungen dokumentiert.

## Mindestprüfung in CI

Eine erste CI-Pipeline sollte auf einer unterstützten PHP- und Datenbankversion
mindestens ausführen:

```bash
composer validate --strict
composer install --no-interaction --prefer-dist
composer check-platform-reqs
composer audit --locked
php bin/console lint:yaml config
php bin/console lint:twig templates
php bin/phpunit
```

Die Matrix wird anschließend um die tatsächlich freigegebenen PHP-, MySQL-
und MariaDB-Hauptversionen erweitert. Ein Deployment ist nur bei erfolgreicher
Pipeline und bestandenem Staging-Smoke-Test zulässig.

Der derzeitige Audit-Befund und die Release-Sperre sind unter
[sicherheitsstatus.md](sicherheitsstatus.md) festgehalten.

## Referenz

- [Symfony: Testing](https://symfony.com/doc/7.4/testing.html)
