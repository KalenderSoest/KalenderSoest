# Entwicklung

## Überblick

Datefix ist eine Symfony-7.4-Anwendung mit klassischem Twig-Frontend, Adminbereich und mehreren Funktionsblöcken:

- Kalender / Termine
- News
- Benutzer / Rechte
- Import / Export
- API / Custom-Ausgaben
- Installer / Updater

Das Web-Root ist über Composer auf `web/` gesetzt.

## Projektstruktur

Wichtige Verzeichnisse:

- `src/Controller`
  Controller für Frontend, Admin, Installer und API
- `src/Service`
  Fachlogik, Query-Factories, Render-Services, Install-Services
- `src/Form`
  Symfony-Formulare
- `src/Entity`
  Doctrine-Entities
- `config/doctrine`
  XML-Mappings
- `templates`
  Twig-Templates
- `web`
  Public Root, Assets, Legacy-Einstiege und Installer-Bootstrap
- `migrations`
  projektspezifische Doctrine-Migrationen

## Lokale Entwicklung

Voraussetzungen:

- PHP `>= 8.2`
- Composer
- MySQL oder MariaDB

Empfohlener Ablauf:

```bash
composer install
php -S 127.0.0.1:8000 -t web
```

Alternativ mit vorhandenem Webserver:
- DocumentRoot auf `web/`
- PHP-FPM / Apache / Nginx wie üblich

## Konfiguration

Datefix nutzt zwei Konfigurationsebenen:

1. Symfony-Umgebung
- `.env`
- `.env.local`

Relevante Werte:
- `APP_ENV`
- `APP_SECRET`
- `DATABASE_URL`
- `MAILER_DSN`

Die Standardvorlage für die vom Installer erzeugte `.env` liegt in
- [web/install/env_dist.yml](/mnt/c/htdocs/datefixDemoMulti/web/install/env_dist.yml)

Der Installer schreibt daraus die konkrete `.env` für die Zielinstallation.
Insbesondere `MAILER_DSN` sollte bei produktiven Installationen gegen die tatsächlichen Vorgaben des Mail-Accounts oder Hosters geprüft werden. `sendmail://default` ist nur ein pragmatischer Hosting-Default, kein universell richtiger Wert.

2. Datefix-spezifische Parameter
- `config/datefix.yaml`

Diese Datei enthält die projektspezifischen Parameter, die früher teilweise in `services.yaml` lagen.

## Bootstrap und Installer

Der Einstieg läuft bewusst zweistufig:

- `web/index.php`
- `web/install/index.php`

`web/index.php` startet Symfony nur, wenn die Mindestvoraussetzungen vorhanden sind:
- `vendor/autoload.php`
- `.env` oder `.env.local`
- `DATABASE_URL`
- `config/datefix.yaml`

Fehlt etwas davon, wird auf `web/install/index.php` umgeleitet.

Danach läuft der eigentliche Status- und Schrittprozess über:
- [src/Controller/DfxInstallController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxInstallController.php)
- Route `/installer/status`

## Install-/Update-Strategie

### Neuinstallation

Frische Installationen laufen über:

1. Voraussetzungen prüfen
2. `.env` und `config/datefix.yaml` bereitstellen
3. DB-Schema anlegen
4. Basisdaten und Account anlegen

### Bestandsupdate

Updates sind zustandsbasiert, nicht versionsbasiert.

Das bedeutet:
- Altstände werden über Datenbankstruktur und Dateninhalt erkannt
- notwendige Spezialmigrationen werden gezielt vorgeschaltet
- anschließend wird pro Installation eine individuelle Doctrine-Migration erzeugt

Aktueller Updatepfad:

1. Status prüfen
2. Legacy-/Spezialmigrationen ausführen
3. Migration erzeugen
4. Migration prüfen
5. Migration ausführen
6. Cache leeren

Wichtige Services:
- [src/Service/Install/InstallationStateService.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Install/InstallationStateService.php)
- [src/Service/Install/InstallationPlanService.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Install/InstallationPlanService.php)
- [src/Service/Install/InstallationExecutionService.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Install/InstallationExecutionService.php)
- [src/Service/Install/MigrationInspectionService.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Install/MigrationInspectionService.php)

## Wichtige Migrationssonderfälle

### Legacy-Medien in `pool_dfx_termine`

Frühere Spalten wie `imgSerie*`, `pdfSerie`, `mediaSerie` werden vor einem Schema-Update in die aktuellen Felder übernommen oder bereinigt.

Service:
- [src/Service/Calendar/TerminLegacyMediaMigrationService.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Calendar/TerminLegacyMediaMigrationService.php)

### `toGroup`

`toGroup` wurde von Altformaten auf JSON überführt.

Services:
- [src/Service/Install/KonfToGroupMigrationService.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Install/KonfToGroupMigrationService.php)
- [src/Service/Calendar/CalendarScopeResolver.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Calendar/CalendarScopeResolver.php)

### Array- auf JSON-Felder

Mehrere frühere Doctrine-`array`-Felder wurden auf `json` umgestellt.

Migration:
- [src/Service/Install/ArrayJsonMigrationService.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Install/ArrayJsonMigrationService.php)

Bereits angepasste Kernfelder:
- `DfxKonf.toGroup`
- `DfxKonf.rubriken`
- `DfxKonf.zielgruppen`
- `DfxTermine.rubrik`
- `DfxTermine.zielgruppe`
- `DfxNews.rubrik`
- `DfxNfxUser.roles`

## Architekturhinweise

### Controller

In den größeren Bereichen wurde Fachlogik schrittweise aus den Controllern gezogen.

Typische Aufteilung:
- Controller: HTTP-Orchestrierung
- FormFactory: Form-Erzeugung
- QueryFactory / QueryApplier: Listen- und Filterlogik
- WorkflowService: Schreiblogik
- NotificationService: Mail- und Benachrichtigungslogik
- RendererService: Ausgabeformate

### Kalender / Termine

Wichtige Komponenten:
- [src/Controller/DfxKalenderController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxKalenderController.php)
- [src/Controller/DfxKalenderTermineController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxKalenderTermineController.php)
- [src/Controller/DfxTermineController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxTermineController.php)
- [src/Service/Calendar/TerminWriteWorkflowService.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Calendar/TerminWriteWorkflowService.php)

### News

Wichtige Komponenten:
- [src/Controller/DfxNewsController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxNewsController.php)
- [src/Controller/DfxNewsFrontendController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxNewsFrontendController.php)

### API

Die API-Ausgabe läuft nicht mehr nur direkt aus dem Controller, sondern über Renderer.

Wichtige Komponenten:
- [src/Controller/DfxApiController.php](/mnt/c/htdocs/datefixDemoMulti/src/Controller/DfxApiController.php)
- [src/Service/Api/SchemaOrgApiPayloadRenderer.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Api/SchemaOrgApiPayloadRenderer.php)
- [src/Service/Api/ApiPayloadRendererResolver.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Api/ApiPayloadRendererResolver.php)

Custom-Renderer sind möglich über:
- `App\\Service\\Api\\Custom\\ApiPayloadRenderer`
- `App\\Service\\Api\\Custom\\Kid{kid}\\ApiPayloadRenderer`

## Eigene Templates

Nahezu alle Frontend-, Admin- und Mail-Templates können durch eigene Templates ersetzt werden.

Die Auflösung läuft zentral über:
- [src/Service/Presentation/TemplatePathResolver.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Presentation/TemplatePathResolver.php)

Grundprinzip:
- es gibt Standard-Templates unter dem normalen Domainpfad, z. B. `Kalender/...`, `News/...`, `DfxTermine/...`
- zusätzlich können globale Overrides im Unterordner `custom/` liegen
- außerdem sind in mehreren Bereichen kalenderspezifische Overrides pro `kid` möglich

### Allgemeine Template-Auflösung

Für viele Controllerpfade läuft die Suche über:

1. `{Domain}/custom/{datei}`
2. `{Domain}/{kid}_{datei}`
3. `{Domain}/{datei}`

Das betrifft z. B. Aufrufe wie:
- `resolve('DfxTermine', 'edit.html.twig', $konf)`
- `resolve('DfxNews', 'new.html.twig', $konf)`
- `resolve('DfxFrontend', 'index.html.twig', $konf)`

Wichtig:
- der globale Override liegt hier in `custom/`
- in diesem allgemeinen Resolver ist die kalenderspezifische Variante kein Unterordner, sondern ein Dateiname mit Präfix wie `1_edit.html.twig`

### Kalender-Listenansicht

Die Kalenderlistenansicht hat eine eigene Logik:

1. `Kalender/custom/liste_{tplVersion}_{tpl}.html.twig`
2. `Kalender/{kid}/liste.html.twig`
3. `Kalender/custom/liste.html.twig`
4. `Kalender/liste_{tplVersion}_{tpl}.html.twig`

Damit kann man:
- global eine bestimmte Listenvariante überschreiben
- oder für einen einzelnen Kalender eine eigene `liste.html.twig` bereitstellen

### Detailansichten von Kalender und News

Die Detailansichten laufen über `resolveDomainDetail(...)`.

Suchreihenfolge:

1. `{Domain}/{kid}/{ownDetailTemplate}.html.twig`
2. `{Domain}/custom/{detailTemplate}.html.twig`
3. `{Domain}/custom/{ownDetailTemplate}.html.twig`
4. `{Domain}/{detailTemplate}.html.twig`

Das wird unter anderem für:
- Kalender-Detailseiten
- News-Detailseiten

verwendet.

### Form-Templates und Basistemplates

Zusätzlich gibt es zwei Hilfsauflösungen:

- `resolveFormTemplatePrefix(...)`
- `resolveCustomBasePrefix(...)`

Sie werden vor allem dort verwendet, wo ein Template weitere Bausteine mit `include` oder `extends` nachlädt.

`resolveFormTemplatePrefix(...)` sucht:

1. `{Domain}/{kid}/form.html.twig`
2. `{Domain}/custom/form.html.twig`
3. Fallback `form.html.twig`

`resolveCustomBasePrefix(...)` sucht:

1. `{Domain}/{kid}/{baseTemplate}.html.twig`
2. `{Domain}/custom/{baseTemplate}.html.twig`
3. sonst kein Präfix

Das wird z. B. bei Kalender- und News-Detailseiten verwendet, damit auch Basistemplates wie `termine.html.twig` oder `base_detail.html.twig` übersteuerbar bleiben.

### E-Mail-Templates

Mails haben noch einmal eine eigene Suchreihenfolge:

1. `Emails/custom/{kid}/{template}`
2. `Emails/custom/{template}`
3. `Emails/{template}`

Damit sind sowohl globale Mail-Overrides als auch kalenderspezifische Mail-Templates möglich.

### Praktische Regeln

- Wenn nur ein globales Override gewünscht ist, Template unter `custom/` ablegen.
- Wenn nur ein einzelner Kalender angepasst werden soll, zuerst prüfen, ob der jeweilige Resolver einen `{kid}`-Pfad oder `{kid}_datei` unterstützt.
- Nicht jeder Pfad folgt exakt derselben Suchreihenfolge; maßgeblich ist immer der konkrete Resolver in [src/Service/Presentation/TemplatePathResolver.php](/mnt/c/htdocs/datefixDemoMulti/src/Service/Presentation/TemplatePathResolver.php).
- Bei neuen Controllerpfaden sollte nach Möglichkeit immer der `TemplatePathResolver` verwendet werden, damit der Custom-Mechanismus konsistent bleibt.

## Datenbank und Doctrine

Die Anwendung nutzt XML-Mappings unter `config/doctrine`.

Wichtig:
- Bestandsinstallationen können stark voneinander abweichen
- deshalb Updates nicht blind per `schema:update --force` fahren
- stattdessen individuelle Migration erzeugen, prüfen und ausführen

Für Neuinstallationen kann das Schema direkt aufgebaut werden.

## Rechte und Benutzer

Wichtige Rollen:
- `ROLE_ADMIN`
- `ROLE_DFX_ALL`
- `ROLE_DFX_PUB`
- `ROLE_DFX_META`
- `ROLE_DFX_GROUP`
- `ROLE_SUPER_ADMIN`

`roles` liegt inzwischen als JSON-Feld vor und wird in den relevanten Pfaden als Rollenarray behandelt.

## Entwicklungshinweise

- Auf Zielsystemen immer `composer install`, nicht `composer update`
- Bei Updates alte `src/`- oder `vendor/`-Bestände nicht weiterverwenden
- Relevante Bestandsdaten sind in der Regel:
  - Datenbank
  - `.env`
  - `config/datefix.yaml`
  - `web/img/`
  - `web/pdf/`

## Nächste Dokumente

Auf Basis dieser Entwicklerdoku können anschließend getrennt ergänzt werden:
- Installationshandbuch
- Anwendungshandbuch
- Betriebsdokumentation
