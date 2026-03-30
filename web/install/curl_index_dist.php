<?php
// Konfigurationsbereich - ist individuell anzupassen. Die Variablen werden auch im Include-Script verwendet.
// Die Url zu Ihrem Datefix Kalender in der Form https://IhrDatefix.de
$cfgUrl ='#datefixUrl#';
// Die Kalendernummer - Standard ist 1 für den Meta-Kalender und die Single-Version
$cfgKid = 1;
// Name der CSS-Datei - Den Namen (und ggf das Unterverzeichnis) der CSS-Datei finden Sie im Quellcode des Head-Bereichs Ihres Rohkalenders z.B unter https://IhrDatefix.de/kalender/1
$cfgCss = 'bootstrap.dro-r.css';
// Der Pfad zur DocumentRoot des Servers - Wird nur benötigt, wenn öffentliche Termineingabe mit Bild- und Dokumenteneingabe freigeschaltet ist
$cfgRootPath = $_SERVER['DOCUMENT_ROOT'];
// Ende Konfigurationsbereich
?>
<!DOCTYPE html>
<html lang="de">
<head>
   <title>Veranstaltungskalender</title>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta NAME="robots" content="INDEX, FOLLOW">
   <script src="<?php echo $cfgUrl ?>/js/bootstrap.bundle.min.js" type="text/javascript"></script>
   <script src="<?php echo $cfgUrl ?>/js/dfx_functions.js" type="text/javascript"></script>
   <script id="dfx" data-kid=<?php echo $cfgKid ?> data-dfx-url="<?php echo $cfgUrl ?>" src="<?php echo $cfgUrl ?>/js/dfx_functions_curl.js" type="text/javascript"></script>
   <link href="<?php echo $cfgUrl ?>/js/leaflet/leaflet.css" rel="stylesheet">
   <script src="<?php echo $cfgUrl ?>/js/leaflet/leaflet.js" type="text/javascript"></script>
   <link rel="stylesheet" href="<?php echo $cfgUrl ?>/css/<?php echo $cfgCss ?>">
</head>
 <body>
 <!-- Der Einbaucode -->
 <div id="datefix"><?php include('curl_include.php') ?></div>
 </body>
</html>
