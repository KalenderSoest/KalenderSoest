# HTTP-API

Kalender Soest stellt öffentliche, lesende JSON-Endpunkte bereit. Die Angaben
in diesem Dokument beschreiben den aktuellen Quellcode; die maschinenlesbare
Kurzbeschreibung steht in [openapi.yaml](openapi.yaml).

## Sicherheit und Freigabe

Die API verwendet derzeit keine eigene Authentifizierung. Sie darf deshalb nur
Informationen ausgeben, die bewusst öffentlich bereitgestellt werden. TLS und
ein Rate Limit am Reverse Proxy werden für den Produktivbetrieb empfohlen.

Die Kalender- und Newslisten sowie beide Detailendpunkte respektieren die
kalenderbezogene API-Freigabe. Detailendpunkte liefern nur grundsätzlich
veröffentlichte Inhalte; bei Gruppen- und Meta-Kalendern wird zusätzlich die
jeweilige Gruppen- beziehungsweise Meta-Freigabe geprüft.

## Endpunkte

| Methode | Pfad | Ergebnis |
| --- | --- | --- |
| `GET` | `/api/kalender/{kid}` | Liste von Terminen eines Kalenders |
| `GET` | `/api/detail/{tid}` | einzelner veröffentlichter Termin |
| `GET` | `/api/news/{kid}` | Liste von News eines Kalenders |
| `GET` | `/api/news/detail/{nfxid}` | einzelner veröffentlichter Newsbeitrag |

`kid`, `tid` und `nfxid` sind positive numerische IDs. Nicht vorhandene, durch
Symfony-Entity-Mapping gebundene Datensätze führen üblicherweise zu `404`.
Eine deaktivierte API beziehungsweise ein nicht freigegebener Newsbeitrag
liefert `403` als JSON-Fehlerantwort.

## Pagination

Die beiden Listenendpunkte unterstützen:

- `page`: Seite ab `1`
- `items`: Elemente pro Seite; nur zusammen mit `page` relevant

Ohne `page` werden bis zu dem für den Kalender konfigurierten API-Maximum
ausgegeben, standardmäßig bis zu 1000 Datensätze. Mit `page` gilt die
konfigurierte Seitengröße, standardmäßig 20, höchstens jedoch das API-Maximum.
Die Antwort enthält derzeit keine separaten Pagination-Metadaten.

Beispiel:

```text
GET /api/kalender/1?page=1&items=20
```

## Filter für Termine

Filter können direkt als Query-Parameter oder unter `form[...]` übergeben
werden. Unterstützt werden nach aktuellem Controller:

- Taxonomien: `rubrik`, `zielgruppe`, `filter1` bis `filter5`
- Veranstalter: `veranstalter`, `idVeranstalter`
- Ort: `lokal`, `idLocation`, `plz`, `ort`, `umkreis`
- Geografie: `bg`, `lg`, `nat`, `region`
- Suche und Zeit: `suche`, `m`, `t`, `datum_von`, `datum_bis`

Datumswerte verwenden `YYYY-MM-DD`. Boolesche Werte werden unter anderem als
`1`, `true`, `on` oder `yes` erkannt. Die konkrete Bedeutung der fachlichen
IDs richtet sich nach der Konfiguration des jeweiligen Kalenders.

```text
GET /api/kalender/1?datum_von=2026-09-17&datum_bis=2026-09-30&rubrik=3
```

## Filter für News

Unterstützt werden:

- `rubrik`, `zielgruppe`, `filter1` bis `filter5`
- `suche`
- `datum_von`, `datum_bis` im Format `YYYY-MM-DD`

```text
GET /api/news/1?suche=Kultur&datum_von=2026-09-01
```

## Antwortformat

Die Standardausgabe ist JSON-LD-nahe Schema.org-Struktur. Termine werden als
Ereignisobjekte, News als `NewsArticle` ausgegeben. Felder können abhängig von
Datensatz, Kalenderkonfiguration und Custom-Renderer fehlen oder erweitert
sein. Clients müssen unbekannte Felder ignorieren und optionale Felder
verkraften.

Kalenderspezifische oder globale Renderer können die Ausgabe ersetzen:

- `App\Service\Api\Custom\Kid{kid}\ApiPayloadRenderer`
- `App\Service\Api\Custom\ApiPayloadRenderer`

Die OpenAPI-Datei beschreibt deshalb den stabilen Transportvertrag und lässt
fachliche Antwortobjekte bewusst erweiterbar. Änderungen eines
Custom-Renderers müssen separat versioniert und mit dessen Verbrauchern
abgestimmt werden.

## Weitere projektspezifische JSON-Ausgaben

Daneben existieren ältere oder projektspezifische Ausgaben:

- `/js/kalender/{kid}/export/json`
- `/js/kalender/nms/json/{kid}`
- `/js/kalender/json/smartCity/{kid}`
- `/js/kalender/json/wms/{kid}`

Diese Routen sind nicht Teil des stabilen OpenAPI-Vertrags. Sie sind aktuell
öffentlich erreichbar, besitzen eigene Datenstrukturen und prüfen nicht
durchgängig dieselbe API-Freigabe. Vor externer Nutzung müssen ihre Inhalte,
Zugriffsregeln und Abwärtskompatibilität gesondert geprüft werden.

## Betriebshinweise

API-Aufrufe aktualisieren Abrufzähler in der Datenbank. Hohe Leselast erzeugt
damit auch Schreiblast. Der Betreiber sollte Antwortzeit, Fehlerrate und
Datenbanklast überwachen und am Webserver angemessene Request- und
Ratenbegrenzungen setzen. Personenbezogene Kontakt- oder Ortsangaben dürfen nur
ausgegeben werden, wenn ihre Veröffentlichung fachlich und rechtlich zulässig
ist.
