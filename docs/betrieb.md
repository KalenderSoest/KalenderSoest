# Betrieb, Sicherheit und Wiederherstellung

Dieses Dokument ergänzt das [Installationshandbuch](handbuch-installation.md)
um die aus dem aktuellen Quellcode ableitbaren Anforderungen für einen
Produktivbetrieb. Konkrete Grenzwerte für Verfügbarkeit, Aufbewahrung,
Wiederanlaufzeit und Datenverlust müssen vom jeweiligen Betreiber festgelegt
und regelmäßig geprüft werden.

## Systemgrenze und Komponenten

| Komponente | Aufgabe | Betriebsrelevanter Zustand |
| --- | --- | --- |
| Webserver mit PHP-FPM oder Apache-Modul | TLS, Weiterleitung an `web/index.php`, Auslieferung öffentlicher Dateien | Webserver-Konfiguration |
| Symfony-Anwendung | Frontend, Administration, API, Import/Export und Installation | Quellcode, `.env`, `config/datefix.yaml` |
| MySQL oder MariaDB | Fach-, Benutzer- und Konfigurationsdaten | Datenbank |
| Lokales Dateisystem | Uploads, Customizing, Cache, Sitzungen, Logs und Exporte | siehe Datenklassen weiter unten |
| Mail-Transport | Versand über den in `MAILER_DSN` konfigurierten Transport | Zugangsdaten und Zustellbarkeit |

Das DocumentRoot des Webservers muss auf `web/` zeigen. Projektdateien wie
`.env`, `config/`, `src/` und `vendor/` dürfen nicht direkt über HTTP
ausgeliefert werden. Der eingebaute PHP-Webserver ist nur für lokale
Entwicklung vorgesehen.

## Externe Dienste und Datenflüsse

Die folgenden Verbindungen ergeben sich aus dem aktuellen Code und den
Templates:

| Ziel | Auslöser | Richtung und Hinweis |
| --- | --- | --- |
| MySQL/MariaDB aus `DATABASE_URL` | jeder fachliche Zugriff | Serverseitig; für API-Zugriffe werden auch Abrufzähler geschrieben |
| Mailserver oder lokales Sendmail aus `MAILER_DSN` | Benachrichtigungen und Systemmails | Serverseitig; Zugangsdaten als Geheimnis behandeln |
| OpenStreetMap-Tileserver aus `config/datefix.yaml` | Kartenansicht | Browserseitig; IP-Adresse und Kartenabruf gehen an den Tileserver |
| Google Maps | vom Benutzer betätigte Karten-/Routenlinks | Browserseitig; erst nach Benutzeraktion |
| Facebook, WhatsApp, X, LinkedIn und Reddit | vom Benutzer betätigte Teilen-Links | Browserseitig; erst nach Benutzeraktion |
| beliebige Remote-URLs beim PDF-Rendering | Dompdf mit aktivierter Remote-Option | Serverseitig; nur vertrauenswürdige URLs beziehungsweise Inhalte zulassen |

SunEditor wird lokal aus `web/vendor/suneditor/` ausgeliefert und verursacht
keine Verbindung zu einem externen CDN. Für einen besonders datenschutz- oder
ausfallsensiblen Betrieb sollte ein eigener oder vertraglich geregelter
Tileserver konfiguriert werden. Ausgehende Serververbindungen sollten per
Firewall auf tatsächlich benötigte Ziele begrenzt werden. Das gilt besonders
wegen des Remote-Zugriffs beim PDF-Rendering.

## Plattformanforderungen

Verbindlich aus `composer.json` ableitbar sind:

- PHP `>= 8.2`
- PHP-Erweiterungen `ctype`, `curl`, `fileinfo`, `gd`, `iconv` und `zip`
- Composer für reproduzierbare Installationen aus `composer.lock`
- MySQL oder MariaDB mit korrekt gesetztem `serverVersion` und `utf8mb4`

`gd` und `zip` werden insbesondere von PhpSpreadsheet benötigt. Weitere,
transitive Plattformanforderungen der konkret gesperrten Pakete werden vor dem
Deployment geprüft mit:

```bash
composer check-platform-reqs --no-dev
```

Die im Installationshandbuch genannten MySQL- und MariaDB-Versionen sind
Beispiele für die Schreibweise von `serverVersion`, keine zugesicherte
Kompatibilitätsmatrix. Jede eingesetzte Hauptversion muss in einer
Staging-Umgebung mit Installation, Migrationen und den wichtigsten
Anwendungsfällen geprüft werden.

## Datenklassen und Schreibrechte

| Pfad oder Speicher | Bedeutung | Persistenz und Sicherung |
| --- | --- | --- |
| Datenbank | Fach-, Konto- und Konfigurationsdaten | zwingend persistent und zu sichern |
| `.env`, `.env.local` | Umgebung und Geheimnisse | sicher und verschlüsselt sichern; nicht veröffentlichen |
| `config/datefix.yaml` | anwendungsspezifische Konfiguration | persistent und zu sichern |
| `web/images/dfx/` | hochgeladene Bilder | persistent und zu sichern |
| `web/pdf/dfx/` | hochgeladene PDF-Dateien | persistent und zu sichern |
| `web/media/dfx/` | hochgeladene Medien | persistent und zu sichern |
| `web/css/own/`, `web/scss/own/` | generiertes oder angepasstes Design | sichern, wenn nicht reproduzierbar |
| `templates/custom/` und weitere eigene Templates | Betreiber-Customizing | als Code oder Konfiguration sichern |
| `migrations/` | installationsspezifische Migrationen | zusammen mit dem Release archivieren |
| `web/exports/` | erzeugte Exporte | abhängig vom Geschäftsbedarf; meist reproduzierbar |
| `var/cache/` | Symfony-Cache | flüchtig, nicht sichern |
| `var/log/` | Anwendungsprotokolle | nach Aufbewahrungsrichtlinie archivieren, nicht ins Restore einspielen |
| Sitzungsspeicher | angemeldete Sitzungen | flüchtig; bei Clusterbetrieb zentralisieren |

Nur die tatsächlich schreibenden Prozesse sollen Schreibrechte erhalten.
Insbesondere sollen `.env`, PHP-Code und `vendor/` für den Webserver-Prozess
nicht schreibbar sein. Symfony empfiehlt abgestimmte Rechte für `var/cache`
und `var/log`; auf Linux sind ACLs dafür die bevorzugte Lösung. Cache und Logs
sollten nicht auf NFS liegen.

## Produktionskonfiguration und Härtung

Der aktuelle Abhängigkeitsaudit ist in
[sicherheitsstatus.md](sicherheitsstatus.md) dokumentiert. Der aktualisierte
Lock-Stand enthält derzeit keine bekannten Composer-Sicherheitshinweise. Ein
Produktivrelease setzt zusätzlich erfolgreiche Plattform-, Staging- und
Smoke-Tests voraus.

Vor der Freigabe sind mindestens folgende Punkte zu prüfen:

1. `APP_ENV=prod` und `APP_DEBUG=0` sind wirksam.
2. `APP_SECRET`, Datenbank- und Mail-Zugangsdaten sind individuelle Geheimnisse
   und weder im Repository noch in öffentlich erreichbaren Backups enthalten.
3. Der Datenbankbenutzer besitzt nur die für den Betrieb notwendigen Rechte.
   Schemaänderungsrechte können auf ein separates Deployment-Konto begrenzt
   werden.
4. TLS ist erzwungen; sichere HTTP-Header und Cookie-Einstellungen werden am
   Reverse Proxy beziehungsweise Webserver gesetzt und getestet.
5. `trusted_proxies` und `trusted_headers` werden nur für bekannte Proxys
   konfiguriert. Ein pauschales Vertrauen in beliebige Absender ermöglicht
   manipulierte Host-, Schema- oder Client-IP-Angaben.
6. `/installer`, `/update` und `web/install/index.php` sind nach erfolgreicher
   Installation auf Webserver-Ebene für öffentliche Zugriffe gesperrt. Für
   Wartungen werden sie nur kontrolliert und zeitlich begrenzt freigegeben.
7. Der Adminbereich ist nur für notwendige Netze oder Konten erreichbar;
   Anmeldeversuche werden überwacht. Das aktuelle Symfony-Access-Control
   schützt global nur Pfade unter `/admin`; weitere schreibende oder
   installationsbezogene Routen benötigen eine zusätzliche Prüfung.
8. Uploadgrößen, erlaubte MIME-Typen und Dateiendungen werden sowohl in PHP
   (`upload_max_filesize`, `post_max_size`) als auch im Webserver begrenzt. Die
   in Formularen angezeigten 2 MB sind allein keine zentrale Sicherheitsgrenze.
9. Die Dompdf-Remote-Funktion bleibt nur aktiv, wenn externe Ressourcen in
   PDFs benötigt werden und deren URLs kontrolliert sind.
10. Die Anwendung wird regelmäßig mit `composer audit --locked --no-dev` und
    den Sicherheitshinweisen der eingesetzten Laufzeit geprüft.

Symfony-Umgebungsvariablen können beim Deployment für die Produktion
optimiert werden:

```bash
composer dump-env prod
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
```

Die erzeugte Datei `.env.local.php` enthält Konfigurationswerte und kann damit
Geheimnisse enthalten. Sie gehört nur auf das Zielsystem und nicht in
Repository oder öffentlich erreichbare Artefakte.

## Reproduzierbares Deployment

Ein Produktionsrelease wird zunächst in Staging geprüft und dann möglichst
atomar aktiviert:

1. Datenbank und persistente Dateien konsistent sichern.
2. Vollständigen Code aus einem markierten Commit bereitstellen.
3. `composer install --no-dev --optimize-autoloader` ausführen.
4. `composer check-platform-reqs --no-dev` und `composer audit --locked --no-dev`
   ausführen.
5. Anwendungstests und fachliche Smoke-Tests ausführen.
6. Assets installieren beziehungsweise kompilieren und den Produktionscache
   leeren/aufwärmen.
7. Die geprüften Datenbankmigrationen genau einmal ausführen.
8. Release aktivieren und HTTP-, Datenbank-, Mail- und Schreibzugriffe prüfen.
9. Installer und Updater wieder sperren.

`composer validate --strict` muss vor dem Release bestätigen, dass
`composer.lock` zu `composer.json` passt. Der aktuelle Stand erfüllt diese
Prüfung. Auf dem Produktivsystem darf dennoch kein unkontrolliertes
`composer update` ausgeführt werden.

Der projektspezifische, zustandsbasierte Updateablauf ist im
[Installationshandbuch](handbuch-installation.md) beschrieben. Eine dort
erzeugte Migration muss vor der Ausführung gelesen, archiviert und in Staging
mit einer Kopie der Produktionsdaten geprüft werden.

## Backup und Restore

Ein vollständiges Backup umfasst mindestens:

- einen konsistenten Datenbank-Dump,
- `.env`/`.env.local` und `config/datefix.yaml`,
- die persistenten Upload- und Customizing-Pfade aus der Tabelle oben,
- die zugehörige Anwendungsversion einschließlich `composer.lock` und der
  ausgeführten Migrationen.

Backups sind verschlüsselt, zugriffsbeschränkt und räumlich vom Produktivsystem
getrennt aufzubewahren. Der Betreiber legt Aufbewahrung, maximalen Datenverlust
(RPO) und maximale Wiederanlaufzeit (RTO) fest.

Empfohlener Wiederherstellungsablauf:

1. passendes, unverändertes Release und dessen Abhängigkeiten bereitstellen,
2. Datenbank in eine leere Instanz zurückspielen,
3. Konfiguration und persistente Dateien mit korrekten Rechten zurückspielen,
4. `APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear` ausführen,
5. Schema-/Migrationsstand kontrollieren, aber keine neue Diff-Migration blind
   erzeugen,
6. Anmeldung, Kalenderliste, Detailansicht, Upload, API und Mailversand prüfen,
7. erst danach den produktiven Verkehr umschalten.

Mindestens regelmäßig und nach wesentlichen Schemaänderungen ist ein Restore
auf einem getrennten System zu testen. Ein vorhandenes Backup ohne geprüften
Restore gilt nicht als belastbarer Wiederanlaufplan.

## Rollback

Ein Code-Rollback allein ist nach einer Datenbankmigration nicht automatisch
sicher. Vor jedem Release muss deshalb feststehen, ob die Migration rückwärts
kompatibel ist oder eine geprüfte Rückmigration beziehungsweise ein Restore
benötigt.

1. Schreibzugriffe stoppen oder die Anwendung in Wartung nehmen.
2. Fehlerzustand, Release-ID und Migrationsstand dokumentieren.
3. Bei kompatibler Datenbank das vorherige Release atomar reaktivieren.
4. Andernfalls Datenbank und persistente Dateien aus demselben konsistenten
   Sicherungspunkt wiederherstellen.
5. Cache leeren, Smoke-Tests durchführen und Verkehr wieder freigeben.

## Protokollierung und Monitoring

Die Produktionskonfiguration schreibt gepufferte Fehler nach
`var/log/prod.log`; 404- und 405-Antworten lösen den Fehlerpuffer nicht aus.
Für `var/log/*.log` ist deshalb eine betriebssystemseitige Rotation mit
Größen-, Zeit- und Aufbewahrungsgrenzen einzurichten. Protokolle können
personenbezogene Daten enthalten und benötigen passende Zugriffs- und
Löschregeln.

Zu überwachen sind mindestens:

- HTTP-Verfügbarkeit und Quote von 5xx-Antworten,
- Antwortzeit von Frontend, Adminbereich und API,
- fehlgeschlagene Anmeldungen und ungewöhnliche Adminzugriffe,
- Datenbankverbindungen, Fehler und Speicherwachstum,
- Mailfehler und Rückläufer,
- freie Kapazität und Inodes für Datenbank, Uploads, `var/` und Exporte,
- PHP-FPM-Auslastung, Arbeitsspeicher und Prozessabbrüche,
- fehlgeschlagene Deployments, Migrationen und Backups.

Es existiert derzeit kein dedizierter Health-Endpunkt. Ein externer Smoke-Test
sollte daher eine unkritische öffentliche Seite abrufen und zusätzlich
Datenbank- und Dateisystemzustand intern prüfen. Messenger-Transporte sind in
der aktuellen Konfiguration nicht aktiv; es ist daher kein Queue-Worker Teil
des heutigen Betriebs.

## Kapazitätsplanung und Skalierung

Eine allgemeingültige Mindestgröße lässt sich aus dem Quellcode nicht seriös
ableiten. Benötigter Speicher ist mindestens die Summe aus Datenbank,
persistenten Uploads/Customizing, Release-Artefakten, temporären Exporten,
Logs und ausreichender Reserve für Backup und Deployment. Diese Werte werden
auf der realen Instanz regelmäßig gemessen und anhand des Wachstums
fortgeschrieben.

Vor horizontaler Skalierung sind die aktuell lokalen Zustände zu behandeln:

- alle Knoten verwenden dieselbe Datenbank,
- Uploads und eigenes Design werden gemeinsam oder zuverlässig repliziert,
- Sitzungen werden zentral gespeichert oder durch nachweislich geeignete
  Sticky Sessions gebunden,
- Cache und Logs bleiben lokal und werden zentral ausgewertet,
- Releases und Konfiguration sind auf allen Knoten identisch,
- Migrationen laufen nur einmal und nicht parallel auf jedem Knoten.

Vertikale Skalierung und HTTP-/PHP-Caching sind meist der erste Schritt. Die
API führt auch bei Lesezugriffen Zähler-Schreibvorgänge aus; ihre Last betrifft
daher nicht nur Webserver, sondern auch die Datenbank. Ein Reverse Proxy sollte
für öffentliche Endpunkte Rate Limits setzen, solange die Anwendung selbst
keine solchen Grenzen erzwingt.

## Weiterführende Symfony-Dokumentation

- [Deploying a Symfony Application](https://symfony.com/doc/7.4/deployment.html)
- [Setting up or Fixing File Permissions](https://symfony.com/doc/7.4/setup/file_permissions.html)
- [How to Configure Symfony to Work behind a Load Balancer or a Reverse Proxy](https://symfony.com/doc/7.4/deployment/proxies.html)
- [Configuring Symfony](https://symfony.com/doc/7.4/configuration.html)
- [How to Keep Sensitive Information Secret](https://symfony.com/doc/7.4/configuration/secrets.html)
- [Logging](https://symfony.com/doc/current/logging.html)
