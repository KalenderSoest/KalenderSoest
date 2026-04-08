# Refactor-Plan Render-Service

## Ziel

Der Kalenderinhalt soll weiterhin auf zwei Wegen ausgeliefert werden:

1. per AJAX zum Einfügen in ein HTML-Element
2. serverseitig direkt im initialen HTML für Suchmaschinen und SSR

Beide Ausgabepfade sollen denselben fachlichen Code verwenden, aber ohne HTTP-Selbstaufruf.

## Problem der bisherigen Lösung

Die bisherige Variante erzeugt den HTML-Code doppelt, indem ein Controller per cURL erneut eine URL auf demselben Server aufruft. Das führt zu:

- unnötiger HTTP-Schleife innerhalb derselben Anwendung
- Abhängigkeit von Apache/TLS/FPM für interne Logik
- Deadlock-Risiko bei kleinem PHP-FPM-Pool
- schlechter Debugbarkeit

## Zielarchitektur

Statt:

- Controller A ruft per HTTP Controller B auf

Neu:

- Controller A und Controller B rufen denselben PHP-Service auf

## Vorschlag

### 1. Gemeinsamen Service einführen

Beispiel:

- `src/Service/KalenderRenderService.php`

Aufgabe des Services:

- Request-Parameter lesen oder übergeben bekommen
- Daten aus Repositories laden
- Filter anwenden
- den finalen Kalender-HTML-Block erzeugen

Der Service sollte entweder:

- strukturierte Daten zurückgeben, die dann in Twig gerendert werden

oder

- direkt den gerenderten HTML-String liefern

Für dieses Projekt ist beides machbar. Der HTML-String ist als Übergang pragmatisch, strukturierte Daten sind langfristig sauberer.

### 2. AJAX-Endpunkt auf denselben Service umstellen

Der bisherige Endpunkt unter `/js/kalender/...` soll:

- keine Frontend-URL mehr per cURL aufrufen
- stattdessen den neuen Service direkt nutzen
- das HTML-Fragment als Response zurückgeben

### 3. Frontend-Seite serverseitig auf denselben Service umstellen

`DefaultController::kalenderAction()` soll:

- nicht mehr `curl_init($dfxUrl)` verwenden
- stattdessen denselben `KalenderRenderService` aufrufen
- das Ergebnis direkt in das Twig-Template geben

### 4. AJAX nur noch für progressive Verbesserung verwenden

Die Seite sollte beim ersten Aufruf bereits serverseitig sinnvolles HTML enthalten. AJAX dient dann nur noch für:

- Nachladen
- Filterwechsel
- Interaktionen ohne kompletten Reload

## Minimale Übergangslösung

Wenn die große Umstellung zuerst zu aufwendig ist, ist als Zwischenstufe möglich:

- internen Subrequest in Symfony statt externem cURL verwenden

Beispielsweise über:

- `render(controller(...))`
- oder `HttpKernelInterface::SUB_REQUEST`

Das ist besser als HTTP auf die eigene Domain, aber immer noch schlechter als ein gemeinsamer Service.

## Konkrete Prüfpunkte im Code

Besonders prüfen:

- `src/Controller/DefaultController.php`
- Controller unter `/js/kalender/...`, vermutlich `src/Controller/DfxKalenderController.php`
- Stellen, die `datefix_url` verwenden
- Twig-Templates, die bisher `dfx_content` erwarten

## Erfolgsdefinition

Die Umstellung ist erfolgreich, wenn:

- kein `curl_init()` mehr zur eigenen Anwendung verwendet wird
- dieselbe HTML-Struktur sowohl für AJAX als auch SSR erzeugt wird
- `index.php` nicht mehr hängt
- die Seite mit kleinem FPM-Pool stabil bleibt

## Arbeitsauftrag für Codex lokal

Beispiel-Prompt für die lokale Entwicklungsumgebung:

```text
Bitte lies docs/server-debug-2026-04-07.md und docs/refactor-plan-render-service.md.
Analysiere DefaultController und die /js/kalender-Routen.
Baue die bestehende Selbst-cURL-Architektur auf einen gemeinsamen Render-Service um, sodass AJAX und serverseitige Ausgabe denselben PHP-Code verwenden.
Vermeide HTTP-Selbstaufrufe vollständig.
```
