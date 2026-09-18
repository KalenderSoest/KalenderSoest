# Sicherheitsstatus der Abhängigkeiten

Stand: 17. September 2026

## Ergebnis nach der Aktualisierung

```bash
composer audit --locked --no-dev
```

meldet für den aktualisierten `composer.lock` keine bekannten
Sicherheitshinweise. Dasselbe gilt für den Audit einschließlich der
Entwicklungsabhängigkeiten:

```bash
composer audit --locked
```

Die zuvor gemeldeten 52 Hinweise in 15 Produktionspaketen wurden durch den
kontrollierten Lock-Abgleich behoben. Wesentliche aktualisierte Fixstände sind:

| Paket | vorher | jetzt |
| --- | --- | --- |
| `phpoffice/phpspreadsheet` | 1.30.2 | 1.30.7 |
| `twig/twig` | 3.23.0 | 3.28.0 |
| `dompdf/dompdf` | 3.1.4 | 3.1.6 |
| Symfony-Komponenten | überwiegend 7.4.0 bis 7.4.4 | aktuelle 7.4-Patchstände bis 7.4.19 |

Nicht mehr benötigte transitive Altkomponenten `spipu/html2pdf` und
`tecnickcom/tcpdf` wurden aus dem Lockfile entfernt. `composer.json` und
`composer.lock` sind wieder synchron; `composer validate --strict` ist ohne
Warnung erfolgreich.

## Noch erforderliche Freigabeprüfungen

Der Paket-Audit ist kein vollständiger Anwendungssicherheitstest. Vor einem
Produktivrelease bleiben deshalb erforderlich:

1. Auf jedem Zielsystem muss `composer check-platform-reqs --no-dev`
   erfolgreich sein. Die aktuelle Entwicklungsumgebung mit PHP 8.4.25 erfüllt
   alle Produktionsanforderungen einschließlich `ext-gd` und `ext-zip`.
2. Installations-, Update-, Import-, Export-, PDF-, Mail- und API-Abläufe sind
   in Staging mit produktionsnahen Daten zu prüfen.
3. Die Smoke-Tests aus [testing.md](testing.md) sind durchzuführen.
4. Öffentliche Schreib-, Import-, Installer- und Update-Routen bleiben gemäß
   [betrieb.md](betrieb.md) auf das erforderliche Maß beschränkt.

Die vorhandene Unit-Testsuite wurde mit PHPUnit 11.5.56 ausgeführt: elf Tests
mit 59 Assertions waren erfolgreich. Sie deckt die genannten fachlichen
Staging-Prüfungen noch nicht vollständig ab.

## Nachweis

Der Audit ist zeitabhängig. Vor jedem Release werden Datum und Ergebnis
dokumentiert. Die Prüfung lässt sich reproduzieren mit:

```bash
composer audit --locked --no-dev --format=plain
composer audit --locked --format=plain
```

Nach jeder Änderung von `composer.lock` sind auch die Versionen in
[THIRD_PARTY_NOTICES.md](../THIRD_PARTY_NOTICES.md) zu aktualisieren.
