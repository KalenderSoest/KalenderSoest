# Anwendungshandbuch

## Zielgruppe

Dieses Handbuch richtet sich an Redakteure und Administratoren, die Datefix im laufenden Betrieb verwenden.

Es beschreibt die tägliche Arbeit mit:
- Kalendern und Terminen
- News
- Benutzern und Rechten
- Import und Export
- Freigaben und Veröffentlichungen

Zusätzlich beschreibt es:
- den Einbau in Webseiten
- die zentrale Konfiguration des Kalenders
- Datenbanken für Locations, Veranstalter, Orte und Regionen
- Anmeldungen und Kartenreservierungen

## Einbau in Webseiten

### Veranstaltungskalender einbauen

Datefix kann grundsätzlich auf zwei Arten eingebunden werden:

- per JavaScript
- per `iframe`

Die empfohlene Variante ist die JavaScript-Einbindung. Ein `iframe` sollte nur verwendet werden, wenn wirklich kein JavaScript eingebunden werden kann.

Typischer JavaScript-Einbau:

```html
<script id="dfx" data-kid="IhreKalendernummer" data-dfx-url="https://ihre-datefix-url" src="https://ihre-datefix-url/js/dfx_ajax.js"></script>
<div id="datefix"></div>
```

Wichtige Punkte:
- `data-kid` muss zur Kalendernummer passen
- `data-dfx-url` muss auf die Datefix-Installation zeigen
- bei HTTPS muss auch die Einbindung per `https://` erfolgen

### Terminbox einbauen

Für eine kompakte Vorschau kommender Veranstaltungen steht eine Terminbox zur Verfügung.

Beispiel:

```html
<script id="dfxbox" data-kid="IhreKalendernummer" data-dfx-url="https://ihre-datefix-url" src="https://ihre-datefix-url/js/dfx_terminbox.js"></script>
```

Die Terminbox eignet sich besonders für:
- Startseiten
- Sidebars
- Landingpages

### Navigationselement getrennt einbauen

Das Kalenderelement mit Monatskalender und Filtern kann getrennt von der Terminliste eingebaut werden.

Dafür wird ein Container wie folgt gesetzt:

```html
<div id="datefix-kalender"></div>
```

Wichtig:
- dem umschließenden HTML-Element muss ausreichend Breite zur Verfügung stehen
- in der Konfiguration muss die Standardanzeige des Navigationselements deaktiviert werden, sonst erscheint es doppelt

### Frontend-URL korrekt setzen

Eine der wichtigsten Einstellungen ist die URL des Frontends.

Sie muss auf genau die Seite zeigen, auf der der Kalender im HTML-Umfeld eingebunden ist.

Diese URL wird verwendet für:
- Links aus Suchmaschinen
- soziale Netzwerke
- Terminbox
- Deeplinks zu Detailansichten

Wenn die Frontend-URL nicht korrekt gesetzt ist, zeigen Links oft nur auf die rohe Standardadresse oder auf das falsche Umfeld.

### Vorgefilterte Einbindung

Kalender und Terminbox können mit vorkonfigurierten Filtern eingebunden werden.

Typische `data-`-Attribute:
- `data-rubrik`
- `data-ort`
- `data-nat`
- `data-plz`
- `data-lokal`
- `data-lid`
- `data-veranstalter`
- `data-vid`
- `data-rid`

Wichtig:
- diese Filter begrenzen die ausgelieferten Daten
- sie schränken aber nicht automatisch alle Auswahlmöglichkeiten im Navigationselement ein
- für eine harte Datentrennung sind getrennte Kalender bzw. Multi-Strukturen besser geeignet

## Anmeldung

Der Einstieg in die redaktionelle Arbeit erfolgt über den Login-Bereich der Installation.

Nach dem Login stehen je nach Benutzerrolle unterschiedliche Bereiche zur Verfügung:
- Termine
- News
- Benutzer
- Konfiguration
- Import / Export

Die tatsächlich sichtbaren Funktionen hängen von den vergebenen Rollen ab.

## Rollen und Freigaben

Wichtige Rollen:

- `ROLE_ADMIN`
  allgemeiner Administrationszugriff im eigenen Kalender
- `ROLE_DFX_PUB`
  Veröffentlichung und Deaktivierung von Terminen und News
- `ROLE_DFX_META`
  Freigaben für Meta- bzw. übergeordnete Kalender
- `ROLE_DFX_GROUP`
  Freigaben für Gruppenkalender
- `ROLE_SUPER_ADMIN`
  systemweiter Zugriff

Im Alltag wichtig:
- ein Eintrag kann intern angelegt sein, ohne bereits veröffentlicht zu sein
- zusätzliche Freigaben können je nach Installation für Meta- oder Gruppenkalender nötig sein

## Kalender / Termine

### Terminliste

In der Terminliste können vorhandene Einträge:
- gesucht
- gefiltert
- bearbeitet
- kopiert
- veröffentlicht
- deaktiviert
- gelöscht

Typische Filter:
- Rubrik
- Zielgruppe
- Ort
- Veranstalter
- Datum von / bis
- freie Suche

### Neuen Termin anlegen

Beim Anlegen eines Termins werden in der Regel folgende Bereiche gepflegt:

- Titel und optional Untertitel
- Datum und Uhrzeit
- Rubrik
- Zielgruppe
- Ort / Veranstaltungsstätte
- Veranstalter
- Kurztext und Beschreibung
- Bild, PDF oder weitere Medien
- Link, Ticketlink oder Anmeldelink

Je nach Installation können zusätzliche Felder eingeblendet sein, etwa:
- eigene Auswahlfelder
- Checkbox-Gruppen
- Mehrfachauswahlen
- Status- oder Freigabefelder

### Einzeltermin und Serie

Datefix unterstützt:
- Einzeltermine
- Serientermine

Bei Serienterminen wird ein gemeinsamer Code verwendet. Änderungen können je nach Bearbeitungspfad:
- nur einen Termin
- oder die ganze Serie betreffen

### Termin kopieren

Über die Kopierfunktion kann ein bestehender Termin als Vorlage verwendet werden.

Sinnvoll bei:
- wiederkehrenden Veranstaltungsformaten
- ähnlichen Veranstaltungsreihen
- neuen Terminen mit gleichen Stammdaten

Beim Kopieren werden Inhalte übernommen, aber nicht als alte Serie weitergeführt.

### Veröffentlichung

Je nach Benutzerrolle kann ein Termin:
- gespeichert, aber noch nicht veröffentlicht sein
- direkt veröffentlicht werden
- zusätzliche Meta- oder Gruppenfreigaben benötigen

Die sichtbaren Schaltflächen hängen von den Benutzerrechten und der Konfiguration des Kalenders ab.

## Freie Termineingabe im Frontend

Je nach Konfiguration kann ein Kalender eine freie Termineingabe im Frontend anbieten.

Dabei gilt:
- Besucher geben Termine über ein Frontend-Formular ein
- Sicherheitscode und Eingeberdaten werden separat erfasst
- die Einträge laufen anschließend in den redaktionellen oder administrativen Prüfprozess

Administratoren können solche Einträge anschließend im Backend bearbeiten, freigeben oder löschen.

## News

### Newsliste

Im News-Bereich können Artikel:
- gesucht
- gefiltert
- erstellt
- bearbeitet
- veröffentlicht
- deaktiviert
- gelöscht

Typische Felder:
- Titel
- Untertitel
- Rubrik
- Veröffentlichungszeitraum
- Beschreibung
- Bilder
- Links

### News veröffentlichen

Wie bei Terminen hängt die Veröffentlichung vom Rechtekonzept der Installation ab.

Ein News-Eintrag kann daher:
- nur gespeichert
- veröffentlicht
- für Meta- oder Gruppenfreigaben markiert

werden.

## Benutzerverwaltung

In der Benutzerverwaltung können je nach Berechtigung:
- neue Benutzer angelegt
- bestehende Benutzer bearbeitet
- Rechte vergeben
- Benutzer gelöscht

Typische Benutzerdaten:
- Name
- E-Mail
- Benutzername
- Passwort
- Rollen

Für Meta- und Gruppenkalender können zusätzliche Rollen vergeben werden.

## Kalender- und Systemkonfiguration

In der Konfiguration werden unter anderem gepflegt:
- Stammdaten des Kalenders
- Frontend-URL
- Navigations- und Darstellungsoptionen
- Rubriken
- Zielgruppen
- Karten- und Formularoptionen
- Freigabeeinstellungen
- Karten- und Listenoptionen

Außerdem können je nach Installation Template- und Darstellungsvarianten aktiv sein.

### Rubriken

Rubriken strukturieren den Datenbestand fachlich und dienen gleichzeitig als wichtige Filter.

Hinweise:
- Rubriken sollten vorausschauend geplant werden
- spätere Umbenennungen sind organisatorisch aufwendig
- in Meta- und Gruppenkalendern sollten dieselben Rubriken konsistent verwendet werden

### Gestaltung und Listenansichten

Datefix unterstützt unterschiedliche Listenansichten und Darstellungsvarianten.

Typische Stellschrauben:
- ein- bis mehrzeilige Listenansichten
- Raster- oder Linienvarianten
- Datum als Zwischenüberschrift oder pro Termin
- Anzahl der Termine pro Seite
- Blätterfunktionen oben und/oder unten
- Farben, Schriftgröße und Maximalbreite

Für die Praxis gilt:
- bei schmalen Layouts die Schriftgröße und Listenvariante bewusst wählen
- bei wenigen Terminen pro Tag kann das Datum im Termin statt als Zwischenüberschrift sinnvoller sein
- mindestens eine Pagination-Position sollte aktiv bleiben

### Kalender, Suchfunktion und Filterelemente

Das Navigationselement kann je nach Bedarf angepasst werden:
- Position seitlich oder oberhalb
- einzelne Filter ein- oder ausschalten
- Monatskalender anzeigen oder ausblenden

Filter sollten nur aktiviert werden, wenn die zugrunde liegenden Daten auch wirklich gepflegt werden.

Beispiele:
- kein Veranstalterfilter ohne Veranstalterpflege
- kein Regionsfilter ohne Orts-/Regionsdatenbank
- keine Karten-/Anmeldeanzeigen ohne entsprechende Prozesse

### Bildeingabe und Galerien

Ob Bilder genutzt werden sollen, sollte bewusst entschieden werden.

Wenn keine Bilder verwendet werden:
- Bildupload deaktivieren
- Vorschaubilder in Listen abschalten

Wenn Bilder verwendet werden:
- nur Webformate wie `.jpg`, `.png`, `.gif` hochladen
- optionale Galerie nur aktivieren, wenn sie redaktionell auch gepflegt wird

### Optionale Felder der Termineingabe

Viele Eingabefelder können in der Konfiguration deaktiviert werden.

Das ist sinnvoll, wenn:
- Felder fachlich nicht benötigt werden
- das Formular übersichtlicher werden soll
- Redakteure oder Frontendnutzer möglichst einfach eingeben sollen

Dabei ist zu beachten, dass einzelne Felder funktionale Abhängigkeiten haben, zum Beispiel:
- Ort und PLZ für Karten-/Standortfunktionen
- Veranstalter und E-Mail für Anmeldungen und Reservierungen
- Beschreibung und Kurztext für Detail- oder Listenansichten

### Dateneingabe aus dem Internet

Die freie Eingabe im Frontend kann aktiviert werden.

Dabei kann konfiguriert werden, ob:
- Einträge erst nach Freigabe sichtbar werden
- oder direkt veröffentlicht werden

In der Praxis ist die Freigabe durch die Redaktion meist die sicherere Standardvariante.

### Community-Funktionen

Optional können Funktionen aktiviert werden wie:
- Teilen in soziale Netzwerke
- Erinnerung per Mail
- Versand eines Hinweises per Mail
- Kalenderexport im iCal-Format

Diese Funktionen sollten nur aktiviert werden, wenn sie im konkreten Kalender auch gewünscht sind.

### Sprache und System

Zu den wichtigsten Systemeinstellungen gehören:
- Sprache
- Frontend-URL
- Titel des Kalenders
- Archivierungsregeln
- Template-Varianten

Besonders wichtig sind:
- die korrekte Frontend-URL
- die Archivierungsstrategie
- konsistente Rubriken und Zielgruppen

### Individuelle Templates

Datefix erlaubt globale und kalenderspezifische Template-Overrides.

Für Anwender wichtig:
- damit kann das Aussehen einzelner Kalender individuell angepasst werden
- Änderungen daran sind keine normale Redaktionsarbeit, sondern eine technische Anpassung

Die technische Programmlogik dazu ist beschrieben in:
- [docs/development.md](/mnt/c/htdocs/datefixDemoMulti/docs/development.md)

## Import / Export

Datefix bietet Import- und Exportfunktionen für redaktionelle und technische Zwecke.

Mögliche Exportformate:
- Excel
- XML
- Newsletter-Ausgaben
- API / JSON

Mögliche Importe:
- insbesondere Terminimporte aus Tabellenformaten

Beim Export können je nach Installation Filter verwendet werden, z. B.:
- Rubrik
- Zielgruppe
- Datum von / bis
- weitere Schalter für bestimmte Ausgabearten

## API und externe Ausgaben

Je nach Installation stehen externe Ausgaben bereit:
- JSON / API
- Widgets
- XML-Schnittstellen
- Newsletter-Ansichten

Diese Bereiche werden meist technisch eingerichtet und anschließend in Webseiten oder Drittsysteme eingebunden.

## Terminarten und korrekte Eingabe

Für eine saubere Darstellung ist die Unterscheidung der Terminarten wichtig.

### Einzeltermin an einem Tag

Ein klassischer Einzeltermin findet:
- an einem einzigen Tag
- mit oder ohne Uhrzeit

statt.

### Mehrtägiger Termin von/bis

Diese Variante ist für zusammenhängende Veranstaltungen gedacht, die fachlich ein einzelner Vorgang sind, zum Beispiel:
- Seminar
- Kongress
- mehrtägige Tagung

Wichtig:
- solche Einträge werden über ihren Starttermin einsortiert

### Regelmäßige Serientermine

Diese Variante ist für wiederkehrende Veranstaltungen gedacht, zum Beispiel:
- Ausstellungen mit regelmäßigen Öffnungstagen
- wöchentliche Treffen
- feste Veranstaltungsreihen

### Unregelmäßige Serientermine

Diese Variante ist für Folgen einzelner Termine gedacht, die nicht streng täglich oder wöchentlich stattfinden.

Beispiele:
- einzelne Vortragstermine
- unregelmäßige Reihen
- monatliche oder zweiwöchentliche Formate

### Praktischer Hinweis

Wenn Ausstellungen, Öffnungstage oder wiederkehrende Reihen falsch als einfacher von/bis-Termin angelegt werden, führt das häufig zu unerwünschten Einsortierungen. In solchen Fällen sollte statt eines einfachen von/bis-Termins eine Serie angelegt werden.

### Serien bearbeiten

Bei Serien gilt:
- Einzeltermine können einzeln geändert werden
- Änderungen an der ganzen Serie können Einzelanpassungen überschreiben

Bei größeren Änderungen ist es oft sinnvoller, eine Serie neu anzulegen statt sie lange nachzubearbeiten.

## Bilder, PDFs und Medien

In vielen Bereichen können hochgeladen oder referenziert werden:
- Bilder
- PDFs
- weitere Medien

Wichtig:
- Dateinamen und Dateitypen sollten sauber gehalten werden
- Bilder und PDFs werden meist kalenderspezifisch gespeichert
- bei Bestandsinstallationen können ältere Medienstände bereits aus Altversionen übernommen worden sein

## Datenbanken

### Locations und Veranstalter

Datefix bietet Datenbanken für:
- Veranstaltungsstätten / Locations
- Veranstalter

Vorteile:
- schnellere Dateneingabe
- einheitliche Stammdaten
- zusätzliche Detailinformationen über Verlinkungen

Wenn eine Location oder ein Veranstalter aus der Datenbank gewählt wird, können Felder im Terminformular automatisch vorbelegt werden.

### Orte und Regionen

Die Orts- und Regionen-Datenbank hilft dabei:
- Schreibweisen zu vereinheitlichen
- Regionen zu definieren
- zusätzliche Filtermöglichkeiten bereitzustellen

Das ist besonders sinnvoll bei:
- größeren Gebieten
- mehreren Ortsteilen
- regional gegliederten Kalendern

## Karten, Anmeldungen und Bestellungen

Falls die Installation diese Module nutzt, können Termine zusätzlich mit:
- Anmeldungen
- Kartenreservierungen
- Platz- oder Kontingentverwaltung

verknüpft sein.

Dann stehen ergänzende Listen und Bearbeitungsbereiche bereit, um:
- Eingänge zu prüfen
- verfügbare Plätze zu überwachen
- Benachrichtigungen auszulösen

### Voraussetzungen in der Konfiguration

Damit Anmeldungen oder Kartenreservierungen funktionieren, müssen die Funktionen in der Konfiguration aktiviert sein.

Falls mit Kontingenten gearbeitet wird, muss zusätzlich die Platz- bzw. Kartenverwaltung aktiviert sein.

### Voraussetzungen bei der Termineingabe

Für einen konkreten Termin müssen dann zusätzlich gesetzt werden:
- Art der Funktion: Anmeldung oder Kartenreservierung
- Empfänger-E-Mail
- optional Platz- oder Kartenkontingent

Wenn Kontingente genutzt werden:
- `Plätze gesamt`
- `Plätze verfügbar`

Im laufenden Betrieb muss der Wert für verfügbare Plätze gepflegt bleiben, wenn Buchungen auch auf anderen Wegen erfolgen.

### Wirkung im Frontend

Bei aktivem Kontingent zeigt Datefix je nach Stand:
- verfügbare Plätze / Karten
- belegt
- ausverkauft

### Auswertung im Backend

Anmeldungen und Kartenbestellungen können im Backend aufgerufen und exportiert werden.

Typische Einsatzformen:
- Ausdruck als PDF
- Sichtung offener Bestellungen
- Weiterverarbeitung in internen Abläufen

## Typische Arbeitsabläufe

### Termin neu anlegen

1. Terminbereich öffnen
2. neuen Termin anlegen
3. Pflichtfelder ausfüllen
4. Inhalte und Medien ergänzen
5. speichern
6. je nach Rechten veröffentlichen oder zur Freigabe weitergeben

### Newsbeitrag veröffentlichen

1. Newsbereich öffnen
2. Beitrag erstellen oder bearbeiten
3. Veröffentlichungszeitraum prüfen
4. Inhalte und Bildmaterial ergänzen
5. speichern
6. veröffentlichen

### Benutzer anlegen

1. Benutzerbereich öffnen
2. neuen Benutzer anlegen
3. Stammdaten eingeben
4. Passwort setzen
5. Rollen vergeben
6. speichern

## Typische Probleme

### Eintrag ist gespeichert, aber nicht sichtbar

Mögliche Ursachen:
- nicht veröffentlicht
- Freigabe für Meta- oder Gruppenkalender fehlt
- Datumsbereich liegt außerhalb des aktuellen Anzeigezeitraums
- Filter im Frontend oder Backend sind aktiv

### Login funktioniert nicht

Mögliche Ursachen:
- Passwort falsch
- Benutzer deaktiviert oder falsch konfiguriert
- Passwort wurde nach einer Migration noch nicht neu gesetzt

### Bilder erscheinen nicht

Mögliche Ursachen:
- Datei wurde nicht korrekt hochgeladen
- Dateiname oder Pfad ist fehlerhaft
- Schreibrechte auf dem Server fehlen

## Hinweise für Redakteure

- Inhalte möglichst konsistent mit bestehenden Rubriken und Zielgruppen erfassen
- bei Kopien und Serien immer das Datum sorgfältig prüfen
- vor Veröffentlichung Links, Bilder und Orte kontrollieren
- Filter im Backend zurücksetzen, wenn Einträge scheinbar fehlen

## Abschluss

Dieses Handbuch beschreibt die Bedienung der Anwendung im laufenden Betrieb.

Für technische Bereitstellung und Updates siehe:
- [docs/handbuch-installation.md](/mnt/c/htdocs/datefixDemoMulti/docs/handbuch-installation.md)
- [docs/development.md](/mnt/c/htdocs/datefixDemoMulti/docs/development.md)
