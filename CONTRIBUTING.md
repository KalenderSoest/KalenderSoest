# Mitarbeit an Kalender Soest

Beiträge zu Kalender Soest sind willkommen. Technische Mitarbeit und
Programmierung werden von Pool Online Internetservice betreut. Vor größeren
Änderungen sollte das Vorhaben über `info@datefix.de` abgestimmt werden, damit
Architektur, Kompatibilität und Projektziele frühzeitig geklärt werden können.

Es gelten der [Verhaltenskodex](CODE_OF_CONDUCT.md), die
[Sicherheitsrichtlinie](SECURITY.md) und die Lizenz- und Markenhinweise in
[NOTICE.md](NOTICE.md).

## Fehler und Vorschläge

Vor einer neuen Meldung bitte prüfen, ob das Thema bereits im verwendeten
Issue-Tracker behandelt wird. Eine gute Meldung enthält:

- eine kurze, eindeutige Zusammenfassung
- betroffene Version oder Commit
- PHP-, Datenbank- und Serverversion
- nachvollziehbare Schritte zum Reproduzieren
- erwartetes und tatsächliches Verhalten
- relevante Logauszüge ohne Passwörter, personenbezogene Daten oder andere
  Geheimnisse

Sicherheitslücken dürfen nicht als öffentliches Issue gemeldet werden. Dafür
gilt ausschließlich der vertrauliche Meldeweg aus [SECURITY.md](SECURITY.md).

## Entwicklungsumgebung

Die Voraussetzungen und der lokale Start sind in der
[Entwicklerdokumentation](docs/development.md) beschrieben. Grundsätzlich:

```bash
composer install
php -S 127.0.0.1:8000 -t web
```

Für einen vollständigen Anwendungsstart werden eine konfigurierte
MySQL-/MariaDB-Datenbank, `.env` beziehungsweise `.env.local` und
`config/datefix.yaml` benötigt.

## Anforderungen an Beiträge

- Änderungen klein, nachvollziehbar und auf ein Thema begrenzen.
- Bestehende Architektur und Symfony-Konventionen berücksichtigen.
- UTF-8, LF-Zeilenenden und die Regeln aus `.editorconfig` verwenden.
- Keine Zugangsdaten, produktiven Konfigurationen oder personenbezogenen Daten
  committen.
- Neue oder geänderte Konfigurationswerte dokumentieren.
- Datenbankänderungen über nachvollziehbare Doctrine-Migrationen bereitstellen.
- Verhalten und Dokumentation gemeinsam aktualisieren.
- Neue Abhängigkeiten begründen und deren Lizenz prüfen.
- Relevante Tests ergänzen oder dokumentieren, warum kein automatisierter Test
  möglich ist.

Soweit die jeweilige Umgebung vollständig eingerichtet ist, sollten mindestens
die betroffenen PHP-Dateien mit `php -l` geprüft und die vorhandenen Tests mit
folgendem Befehl ausgeführt werden:

```bash
php bin/phpunit
```

## Einreichung und Prüfung

Ein Beitrag sollte enthalten:

1. eine Beschreibung von Problem und Lösung
2. Hinweise auf Auswirkungen für Installation, Betrieb oder Datenmigration
3. die durchgeführten Prüfungen und deren Ergebnis
4. bei sichtbaren Änderungen geeignete Beispiele oder Bildschirmabbildungen
5. Hinweise auf verbleibende Risiken oder offene Arbeiten

Beiträge werden auf Funktion, Sicherheit, Wartbarkeit, Dokumentation,
Barrierefreiheit und Lizenzverträglichkeit geprüft.

## Rechte und Lizenzierung

Mit dem Einreichen eines Beitrags bestätigt die beitragende Person, dass sie
die erforderlichen Rechte daran besitzt und der Beitrag unter
`AGPL-3.0-or-later` veröffentlicht werden darf. Fremdcode und fremde Assets
müssen deutlich gekennzeichnet und lizenzrechtlich kompatibel sein.

Bei Verwendung generativer KI oder anderer Codegeneratoren muss der Beitrag
von der einreichenden Person fachlich und lizenzrechtlich geprüft werden. Eine
wesentliche Verwendung ist in der Beitragsbeschreibung offenzulegen.
