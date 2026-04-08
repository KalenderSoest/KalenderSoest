# Server-Debug 2026-04-07

## Problem

Der Webserver wirkte von außen instabil. `service status` meldete Apache und MariaDB als laufend, trotzdem wurden Seiten nicht ausgeliefert.

## Befund

- Apache lief als Prozess und lauschte auf `*:80` und `*:443`.
- MariaDB lief ebenfalls und antwortete lokal.
- Statische Dateien wurden korrekt ausgeliefert.
- `https://kalender-soest.de/index.php` lief in einen Timeout.
- `php8.1-fpm` hatte alle `5` Worker belegt.

## Technische Analyse

Die Hänger entstehen nicht in Apache selbst und auch nicht primär in MariaDB, sondern in PHP-FPM.

Alle blockierten `php8.1-fpm`-Worker hielten gleichzeitig:

- eine Verbindung zu `127.0.0.1:3306`
- eine HTTPS-Verbindung zur eigenen Server-IP `172.22.3.23:443`

Das zeigt, dass die Anwendung sich selbst per HTTP/HTTPS aufruft.

Die relevante Stelle der alten Anwendung liegt in `src/Controller/DefaultController.php`. Dort wird aus `datefix_url + /js/kalender/...` eine URL gebaut und per `curl_init()` aufgerufen.

Gleichzeitig steht in `config/services.yaml`:

```yaml
parameters:
    datefix_url: https://kalender-soest.de
```

Damit passiert Folgendes:

1. Ein Frontend-Request trifft auf `kalender-soest.de`.
2. PHP startet die Bearbeitung in `DefaultController::kalenderAction()`.
3. Dieser Controller ruft per cURL wieder `https://kalender-soest.de/js/kalender/...` auf.
4. Dieser zweite Request landet wieder auf demselben Apache und demselben PHP-FPM-Pool.
5. Unter Last blockieren sich die Worker gegenseitig.
6. Sobald alle Worker belegt sind, hängt auch der ursprüngliche Request.

## Nachgewiesene Symptome

- `curl -k -I https://kalender-soest.de/test.html` lieferte sofort `200 OK`
- `curl -k -I https://kalender-soest.de/index.php` lief in den Timeout
- alle `php8.1-fpm`-Worker hatten `ESTABLISHED`-Verbindungen auf `172.22.3.23:443`

## Temporäre Sofortmaßnahme

In `/etc/php/8.1/fpm/pool.d/www.conf` vorübergehend:

```ini
pm = dynamic
pm.max_children = 15
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
```

Danach:

```bash
systemctl restart php8.1-fpm
systemctl restart apache2
```

Das behebt nur das Symptom und verschafft Luft. Es entfernt nicht die eigentliche Ursache.

## Eigentliche Ursache

Der Frontend-Controller ruft den eigenen Server erneut per cURL auf, um identischen HTML-Code zu erzeugen. Diese Architektur ist auf demselben Host und im selben FPM-Pool fehleranfällig und erzeugt Deadlocks.

## Weitere Log-Hinweise

Im Produktionslog der alten Instanz standen zusätzlich ältere Anwendungsfehler:

- Doctrine `SQLSTATE[HY000] [2002] Connection refused`
- mehrere `null`-/Typfehler in eigenen Controllern

Diese Fehler sind relevant, erklären aber nicht den aktuellen Komplett-Hänger. Der Hänger wird durch den HTTP-Selbstaufruf verursacht.

## Empfehlung

Kein `curl` mehr auf die eigene Frontend-URL. Stattdessen gemeinsame PHP-Logik in einen Service auslagern und sowohl für AJAX als auch für serverseitiges Rendern verwenden.
