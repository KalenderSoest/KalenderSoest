# Installationshandbuch

## Zweck

Dieses Dokument beschreibt die Installation und Aktualisierung einer Datefix-Instanz aus Betreiber-/Administrator-Sicht.

Es geht dabei nicht um die Weiterentwicklung des Projekts, sondern um:
- Bereitstellung auf einem Server
- Erstinstallation
- Aktualisierung bestehender Installationen
- typische Sonderfälle im Installationsprozess

## Voraussetzungen

Vor der Installation müssen folgende Voraussetzungen erfüllt sein:

- PHP `>= 8.2`
- Datenbankserver MySQL oder MariaDB
- Zugriff auf die Zieldatenbank
- Schreibrechte für relevante Verzeichnisse
- der vollständige Datefix-Codebestand liegt bereits auf dem Server

Wichtige Verzeichnisse mit Schreibrechten:
- `var/`
- `web/img/`
- `web/pdf/`
- je nach Hosting zusätzlich weitere von `web/install` geprüfte Verzeichnisse

## Code bereitstellen

Der empfohlene Ablauf ist:

1. den kompletten neuen Codebestand auf den Server übertragen
2. anschließend die PHP-Abhängigkeiten installieren

Empfohlen:

```bash
composer install --no-dev --optimize-autoloader
```

Wichtig:
- auf Zielsystemen `composer install`
- nicht `composer update`

Grund:
- `install` verwendet die getesteten Versionen aus `composer.lock`
- `update` würde neue Paketversionen nachziehen und ist nicht Teil eines stabilen Serverdeployments

## Sonderfall ohne Composer

Wenn auf dem Zielsystem kein Composer verfügbar ist oder Composer dort nicht ausgeführt werden darf, kann alternativ eine ZIP-Datei mit vollständig installiertem `vendor/`-Verzeichnis angefordert und zusammen mit dem Projektcode hochgeladen werden.

In diesem Fall gilt:
- der Server benötigt trotzdem die passenden PHP-Erweiterungen
- `vendor/autoload.php` muss nach dem Upload vorhanden sein
- der Codebestand und das mitgelieferte `vendor/` müssen zueinander passen

Der Installer prüft genau diesen Punkt. Fehlt `vendor/autoload.php`, bleibt die Installation auf der Vorprüfung stehen.

## Einstieg in die Installation

Die Installation beginnt über:

- `web/install/index.php`

Dieser Einstieg ist bewusst vor Symfony geschaltet. Das ist wichtig, weil Symfony ohne Grundkonfiguration nicht sauber starten kann.

`web/install/index.php` prüft unter anderem:
- `composer.json`
- `composer.lock`
- `vendor/autoload.php`
- PHP-Version
- Schreibrechte
- vorhandene Konfiguration
- vorhandene Tabellen in der Datenbank

## Konfigurationsdateien

Für den Betrieb werden zwei Konfigurationsebenen benötigt:

### 1. Symfony-Umgebung

- `.env`
- optional `.env.local`

Wichtige Werte:
- `APP_ENV`
- `APP_SECRET`
- `DATABASE_URL`
- `MAILER_DSN`

Die ausgelieferte Vorlage für die Symfony-Umgebung liegt in
- [web/install/env_dist.yml](/mnt/c/htdocs/datefixDemoMulti/web/install/env_dist.yml)

Sie wird vom Installer als Basis für die erzeugte `.env` verwendet.
Die dort gesetzten Standardwerte sind für typische Hosting-Umgebungen ausgelegt, müssen aber bei Bedarf an den tatsächlichen Server angepasst werden.

### 2. Datefix-Konfiguration

- `config/datefix.yaml`

Diese Datei enthält die Datefix-spezifischen Parameter.

## Erstinstallation

### Schritt 1: Vorprüfung

Aufruf:
- `web/install/index.php`

Die Seite prüft:
- PHP-Anforderung aus `composer.json`
- Vorhandensein von `vendor/autoload.php`
- Schreibbarkeit von Verzeichnissen und Dateien

Solange diese Voraussetzungen nicht vollständig erfüllt sind, wird das Konfigurationsformular nicht freigeschaltet.

### Schritt 2: Datenbank und System konfigurieren

Im Installationsformular werden mindestens eingetragen:
- Datenbank-Host
- Datenbank-Name
- Datenbank-Benutzer
- Datenbank-Passwort
- Datenbank-Port
- Datenbank-Version

#### Datenbank-Version korrekt ermitteln

Die Datenbank-Version wird für den Parameter `serverVersion` in `DATABASE_URL` benötigt.
Der Wert sollte zum tatsächlichen Datenbankserver passen, weil Doctrine und Symfony davon SQL-Eigenheiten ableiten.

Die Version lässt sich auf mehreren Wegen ermitteln:

- in phpMyAdmin auf der Startseite oder im Bereich `Server` / `Datenbank-Server`
- in vielen Hosting-Panels in den Angaben zur Datenbank
- an der Konsole mit:

```bash
mysql -u BENUTZER -p -h HOST -P PORT -e "SELECT VERSION();"
```

oder nach dem Login in die MySQL-/MariaDB-Konsole mit:

```sql
SELECT VERSION();
```

#### Schreibweise in `DATABASE_URL`

MySQL und MariaDB müssen in `serverVersion` nicht identisch geschrieben werden.

Beispiele:

- MySQL 8.0:
  `mysql://user:pass@localhost:3306/datefix?serverVersion=8.0.39&charset=utf8mb4`
- MySQL 5.7:
  `mysql://user:pass@localhost:3306/datefix?serverVersion=5.7.44&charset=utf8mb4`
- MariaDB 10.11:
  `mysql://user:pass@localhost:3306/datefix?serverVersion=10.11.6-MariaDB&charset=utf8mb4`
- MariaDB 10.6:
  `mysql://user:pass@localhost:3306/datefix?serverVersion=10.6.17-MariaDB&charset=utf8mb4`

Wichtig:
- bei MySQL reicht in der Regel die reine Versionsnummer
- bei MariaDB sollte der Zusatz `-MariaDB` enthalten sein
- der Wert muss nicht exakt auf die letzte Nachkommastelle perfekt sein, sollte aber Produkt und Hauptversion korrekt treffen
- im Zweifel ist der direkt vom Server gelieferte Wert aus `SELECT VERSION();` die beste Grundlage

Zusätzlich können Datefix-Basiswerte gesetzt werden, z. B.:
- System-Mailadresse
- Mail-Footer
- Stamm-URL der Installation

Nach erfolgreichem Speichern werden geschrieben:
- `.env`
- `config/datefix.yaml`

Hinweis zum Mailversand:
- die erzeugte `.env` basiert auf [web/install/env_dist.yml](/mnt/c/htdocs/datefixDemoMulti/web/install/env_dist.yml)
- `MAILER_DSN=sendmail://default` ist ein sinnvoller Standard für viele klassische Hostings mit lokalem Mailversand
- je nach Vorgaben des Mail-Accounts oder Hosters muss `MAILER_DSN` nach der Installation aber angepasst werden, zum Beispiel auf einen SMTP-Zugang

### Schritt 3: Symfony-Installer

Danach läuft der eigentliche Installationsprozess über:

- `/installer/status`

Dort erkennt Datefix den Installationszustand und zeigt den Ablaufplan.

Bei einer Neuinstallation sind die typischen Schritte:

1. Codebasis prüfen
2. Umgebung prüfen
3. Datenbank prüfen
4. Schema installieren
5. Basisdaten anlegen

### Schritt 4: Schema anlegen

Der Schritt `Schema anlegen` erstellt die notwendigen Datenbanktabellen.

Nach erfolgreichem Schema-Aufbau kehrt der Installer wieder auf die Statusseite zurück und schaltet die nächsten Schritte frei.

### Schritt 5: Basisdaten anlegen

Danach werden erzeugt:
- Webuser
- Kunde
- erster Account / Administrator
- Standardkonfiguration

### Meta-Kalender und zentrale Benutzer

Die letzten Schritte der Erstinstallation sind fachlich besonders wichtig.

Bei der Erstinstallation wird der erste Kalender als zentraler Ausgangskalender angelegt. Historisch entspricht das dem Meta-Kalender der Installation.

Dabei entstehen auch zentrale Benutzerkonten:

- Webuser für Frontend- und Systemeinstiege
- erster Administrator
- zentraler Superadmin

Für den praktischen Betrieb wichtig:
- der zentrale Superadmin darf nicht gelöscht werden
- mindestens ein globaler Verwaltungszugang muss immer erhalten bleiben
- bei Zweifeln sollte in der Benutzertabelle geprüft werden, ob die zentralen Benutzer nach der Installation vorhanden sind

Der heutige Installer legt diese Basisdaten nicht mehr als lose Einzelschritte wie in älteren Versionen an, sondern über den geführten Provisioning-Prozess.

### Erster Kunde und erster Kalender

Als erster Kunde sollten die Daten des eigentlichen Systembetreibers eingetragen werden.

Dieser erste Datensatz ist wichtig, weil er:
- die technische Ausgangsbasis der Installation bildet
- den ersten Kalender trägt
- mit dem ersten Administrator- und Superadmin-Zugang verknüpft ist

## Kalenderhierarchie

Datefix unterstützt drei funktionale Ebenen:

- Standard-Kalender
- Gruppen-Kalender
- Meta-Kalender

### Standard-Kalender

Ein Standard-Kalender zeigt die Daten eines einzelnen Accounts.

### Gruppen-Kalender

Ein Gruppen-Kalender bündelt ausgewählte Daten mehrerer untergeordneter Kalender.

### Meta-Kalender

Ein Meta-Kalender bündelt Daten aus allen oder aus vielen untergeordneten Kalendern einer Installation.

Typisches Einsatzszenario:
- ein Landkreis oder Dachsystem als Meta-Kalender
- Städte oder Gemeinden als Gruppen-Kalender
- Vereine, Veranstalter oder Einrichtungen als Standard-Kalender

Wichtig:
- alle Ebenen werden technisch gleich installiert
- die Zuordnung als Meta- oder Gruppen-Kalender ist eine administrative Aufgabe im laufenden Betrieb

## Der erste Kalender nach der Installation

Nach der erfolgreichen Erstinstallation ist der erste Kalender technisch vorhanden, aber in der Regel noch in einer Grundkonfiguration.

Deshalb sollten danach zeitnah geprüft oder ergänzt werden:
- Frontend-URL
- Rubriken und Zielgruppen
- Darstellungsvariante
- Filter und Navigationselement
- Freigabe- und Community-Funktionen

Die fachliche Konfiguration dieses Kalenders ist nicht mehr Teil des technischen Installers, sondern erfolgt anschließend im Backend.

## Weitere Kalender / Accounts anlegen

Neue Kalender können nach der Installation über die Kalender- bzw. Accountverwaltung angelegt werden.

Wichtig:
- es gibt einen geführten Registrierungs- und Provisioning-Prozess
- derselbe fachliche Kern wird sowohl im Installer als auch in der Accountanlage verwendet

Je nach Setup können neue Accounts:
- zentral durch den Betreiber angelegt werden
- oder über eine Registrierungsroute vorbereitet werden

Nach dem Anlegen eines Accounts sollten immer geprüft werden:
- Administratorzugang
- Frontend-URL
- Einbaucode
- Grundkonfiguration des neuen Kalenders

## Superadmin und Rollenmodell

### Superadmin

Der Superadmin ist der zentrale Verwalter der gesamten Installation.

Typische Aufgaben:
- Kalenderübersicht verwalten
- Meta- und Gruppenstatus festlegen
- in Accounts wechseln
- technische Wartungs- und Updatepfade starten
- globale Benutzer- und Rechtepflege

### Administratoren

Mit einem Kalender wird mindestens ein Administrator angelegt.

Dieser kann im Regelfall:
- den Kalender konfigurieren
- Termine und News verwalten
- Benutzer des eigenen Kalenders anlegen
- Freigaben im Rahmen seiner Rechte steuern

### Einfache Benutzer

Einfache Benutzer arbeiten standardmäßig eingeschränkt:
- nur eigene Einträge
- oft ohne direkte Veröffentlichung

Diese Einschränkungen können durch Administratoren erweitert werden.

## Historischer Hinweis

Ältere Handbücher und Altinstallationen beziehen sich teilweise noch auf:
- Symfony 2.x
- `parameter.yml`
- `dfxnfx.yml`
- alte Bundle-Pfade unter `src/Pool/FxBundle`

Für den aktuellen Stand gilt stattdessen:
- `.env` / `.env.local`
- `config/datefix.yaml`
- Symfony 7.4
- `src/` und `templates/` in der heutigen Struktur

## Aktualisierung bestehender Installationen

### Grundprinzip

Bestehende Installationen werden nicht mehr blind über ein einfaches Schema-Update behandelt.

Stattdessen läuft das Update zustandsbasiert:
- vorhandene Datenbank analysieren
- notwendige Vorabmigrationen erkennen
- individuelle Migration für genau diese Installation erzeugen
- Migration prüfen
- Migration ausführen

### Einstieg

Wenn bereits Tabellen oder Konfigurationen erkannt werden, verweist `web/install/index.php` auf:

- `/installer/status`

### Typischer Update-Ablauf

1. Status prüfen
2. nötige Vorabmigrationen ausführen
3. individuelle Migration erzeugen
4. Migration vor Ausführung prüfen
5. Migration ausführen
6. Cache leeren

### Vorabmigrationen

Je nach Bestand können zusätzliche Schritte notwendig sein, zum Beispiel:

- `toGroup` auf den aktuellen JSON-Stand migrieren
- Legacy-Medien aus alten Serienspalten übernehmen
- frühere `array`-Felder auf `json` migrieren
- Passwort-/Login-Struktur prüfen

Diese Schritte werden im Installer nur dann angeboten, wenn sie für die aktuelle Installation tatsächlich benötigt werden.

## Warum keine pauschalen Updates?

Historisch gewachsene Installationen können sich stark unterscheiden.

Daher gilt:
- nicht jede Bestandsinstallation hat denselben Schema- oder Datenzustand
- die Datenbank ist oft die einzige wirklich verlässliche gemeinsame Basis
- individuelle Migrationen sind sicherer als ein pauschales, blindes Schema-Update

## Verhalten bei fehlender oder unvollständiger Konfiguration

Wenn eine oder mehrere Grundvoraussetzungen fehlen, startet Symfony nicht direkt.

Stattdessen leitet der Bootstrap auf `web/install/index.php` um, insbesondere wenn:
- `vendor/autoload.php` fehlt
- `.env` oder `.env.local` fehlt
- keine `DATABASE_URL` vorhanden ist
- `config/datefix.yaml` fehlt

## Wichtige Hinweise für Updates

- den kompletten neuen Codebestand bereitstellen
- alte `src/`- und `vendor/`-Bestände nicht weiterverwenden
- `web/img/` und `web/pdf/` bleiben erhalten
- die bestehende Datenbank wird übernommen und im Installer geprüft

## Typische Fehlerquellen

### `vendor/autoload.php` fehlt

Ursache:
- Composer wurde nicht ausgeführt
- oder es wurde kein Paket mit vollständigem `vendor/` hochgeladen

Lösung:
- `composer install --no-dev --optimize-autoloader`
- oder ZIP-Datei mit vollständigem `vendor/` anfordern und korrekt hochladen

### Symfony startet nicht direkt

Das ist bei einer frischen Installation normal, solange `.env`, `DATABASE_URL` oder `config/datefix.yaml` noch fehlen. In diesem Fall über `web/install/index.php` einsteigen.

### Bestehende Installation wird als Update erkannt

Das ist korrekt, wenn:
- bereits Tabellen in der Datenbank vorhanden sind
- oder Konfigurationsdateien bereits existieren

Für eine echte Neuinstallation wird benötigt:
- leere Datenbank
- keine Alt-Konfiguration in `.env` / `config/datefix.yaml`

## Abschluss

Nach erfolgreicher Installation oder Aktualisierung sollte geprüft werden:

- Login in den Adminbereich
- Frontend-Aufruf
- Kalenderliste
- Detailansichten
- Mailversand
- Schreibzugriff auf Bild- und PDF-Verzeichnisse

Dieses Dokument beschreibt den technischen Installationsprozess. Die eigentliche Bedienung der Anwendung wird im separaten Anwendungshandbuch beschrieben.
