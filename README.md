# Datefix

Datefix ist eine Symfony-7.4-Anwendung für Veranstaltungs- und News-Kalender mit Adminbereich, Frontend-Ausgabe, API, Import/Export und Install-/Update-Assistent.

## Schnellstart

Voraussetzungen:
- PHP `>= 8.2`
- Composer
- Datenbankzugang für MySQL/MariaDB
- Schreibrechte für `var/`, `web/img/`, `web/pdf/`

Projekt lokal starten:

```bash
composer install
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
- maßgeblich sind Datenbank, `.env`, `config/datefix.yaml`, `web/img/`, `web/pdf/`

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

## Entwicklerdoku

Die ausführlichere technische Dokumentation liegt in:
- [docs/development.md](/mnt/c/htdocs/datefixDemoMulti/docs/development.md)

## Relevante Einstiegspunkte

- Frontend-Kalender: [src/Controller/DfxKalenderController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxKalenderController.php)
- Frontend-Termineingabe: [src/Controller/DfxKalenderTermineController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxKalenderTermineController.php)
- Admin-Termine: [src/Controller/DfxTermineController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxTermineController.php)
- Admin-News: [src/Controller/DfxNewsController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxNewsController.php)
- API: [src/Controller/DfxApiController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxApiController.php)
- Installer: [src/Controller/DfxInstallController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxInstallController.php)

