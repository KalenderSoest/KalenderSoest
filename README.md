# Kalender Soest

Kalender Soest ist eine Symfony-7.4-Anwendung für Veranstaltungs- und News-Kalender mit Adminbereich, Frontend-Ausgabe, API, Import/Export und Install-/Update-Assistent.

## Dokumentation

- [Installations- und Betriebshandbuch](docs/handbuch-installation.md)
- [Produktivbetrieb, Sicherheit, Backup und Skalierung](docs/betrieb.md)
- [Anwendungshandbuch](docs/handbuch-anwendung.md)
- [Entwicklerdokumentation](docs/development.md)
- [API-Dokumentation](docs/api.md) und [OpenAPI-Beschreibung](docs/openapi.yaml)
- [Teststrategie](docs/testing.md)
- [Aktueller Sicherheitsstatus der Abhängigkeiten](docs/sicherheitsstatus.md)
- [Lizenz](LICENSE) und [Lizenz-, Förder- und Markenhinweise](NOTICE.md)
- [Drittanbieter-Komponenten und Lizenzen](THIRD_PARTY_NOTICES.md)
- [Hinweise zur Mitarbeit](CONTRIBUTING.md)
- [Verhaltenskodex](CODE_OF_CONDUCT.md)
- [Sicherheitsrichtlinie und vertrauliche Meldungen](SECURITY.md)
- [Ansprechpartner](CONTACT.md)

## Lizenz

Der eigene Anwendungscode steht unter der GNU Affero General Public License,
Version 3 oder später (`AGPL-3.0-or-later`). Der vollständige Lizenztext steht
in [LICENSE](LICENSE).

Das Förderbanner und die darin enthaltenen Namen und Logos sind nicht unter
der AGPL lizenziert. Für sie gelten die gesonderten Marken- und
Förderhinweise in [NOTICE.md](NOTICE.md). Drittanbieter-Komponenten behalten
ihre jeweiligen Lizenzen; eine Übersicht steht in
[THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).

## Schnellstart

> **Sicherheitshinweis:** Der aktualisierte `composer.lock` besteht den
> Composer-Audit ohne bekannte Hinweise. Vor einem Produktivrelease bleiben
> die Plattform- und Staging-Prüfungen aus dem
> [Sicherheitsstatus der Abhängigkeiten](docs/sicherheitsstatus.md) erforderlich.

Voraussetzungen:

- PHP `>= 8.2`
- PHP-Erweiterungen `ctype`, `curl`, `fileinfo`, `gd`, `iconv` und `zip`
- Composer
- Datenbankzugang für MySQL/MariaDB
- Schreibrechte für `var/`, `web/images/`, `web/pdf/`

Projekt lokal starten:

```bash
composer install
composer check-platform-reqs
php -S 127.0.0.1:8000 -t web
```

Wichtige Konfigurationsdateien:

- `.env` oder `.env.local`
- `config/datefix.yaml`

Wenn die Grundkonfiguration noch fehlt, leitet der Bootstrap auf den Installer um:
- `web/install/index.php`
- danach weiter über `/installer/status`

## Installation und Update

Empfohlener Serverablauf:

1. kompletten neuen Codebestand deployen
2. `composer install --no-dev --optimize-autoloader`
3. `web/install/index.php` aufrufen
4. dann den Zustand über `/installer/status` prüfen

Wichtig:

- auf Zielsystemen `composer install`, nicht `composer update`
- bestehende Installationen werden nicht durch altes `src/` oder `vendor/` weiterverwendet
- maßgeblich sind Datenbank, `.env`, `config/datefix.yaml`, `web/images/`, `web/pdf/`

## Aktueller Install-/Update-Flow

Neuinstallation:

- Voraussetzungen in `web/install`
- `.env` und `config/datefix.yaml`
- Schema anlegen
- Basisdaten und Account anlegen

Bestandsupdate:

- Legacy-Prüfungen im Installer
- notwendige Vorabmigrationen ausführen, z. B. Medien- oder JSON-Migrationen
- individuelle Doctrine-Migration erzeugen
- Migration prüfen
- Migration ausführen
- Cache leeren

## Mitarbeit und Kontakt

Hinweise für Beiträge stehen in [CONTRIBUTING.md](CONTRIBUTING.md). Bitte
Sicherheitslücken nicht öffentlich melden, sondern nach
[SECURITY.md](SECURITY.md) vertraulich an `info@datefix.de` senden.

Technische Mitarbeit und Programmierung werden von Pool Online Internetservice
betreut. Projektverantwortliche Organisation ist das KulturBüro Soest im
Kulturhaus Alter Schlachthof Soest e.V. Die vollständigen Kontaktdaten stehen
in [CONTACT.md](CONTACT.md).

## Relevante Einstiegspunkte

- Frontend-Kalender: [src/Controller/DfxKalenderController.php](src/Controller/DfxKalenderController.php)
- Frontend-Termineingabe: [src/Controller/DfxKalenderTermineController.php](src/Controller/DfxKalenderTermineController.php)
- Admin-Termine: [src/Controller/DfxTermineController.php](src/Controller/DfxTermineController.php)
- Admin-News: [src/Controller/DfxNewsController.php](src/Controller/DfxNewsController.php)
- API: [src/Controller/DfxApiController.php](src/Controller/DfxApiController.php)
- Installer: [src/Controller/DfxInstallController.php](src/Controller/DfxInstallController.php)
