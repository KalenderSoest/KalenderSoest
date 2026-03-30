<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Der Veranstaltungskalender für Städte, Gemeinden, Theater, Kulturveranstalter, Vereine und Jedermann.">
<meta name="author" content="Pool Online Internetservice">
<meta name="copyright" content="Pool Online Internetservice, Nördlingen">
<meta name="keywords" content="Veranstaltungskalender, Eventkalender, Homepagetools, Webmastertools, Homepage, Webkalender">
<meta NAME="robots" content="INDEX, FOLLOW">


<title>Installation :: Datefix - Veranstaltungskalender</title>
<link href="../css/bootstrap.css" rel="stylesheet">
<link href="../css/datefix_fe.css" rel="stylesheet">
<link href="../fontawesome/css/all.min.css" rel="stylesheet">
<link href="../fontawesome/css/v4-shims.min.css" rel="stylesheet">
<style>
    .dfx-nav .navbar-brand img {
        margin-top: -12px;
    }

    body {
        padding-top: 96px;
    }

    .install-shell {
        padding-bottom: 32px;
    }

    .install-panel {
        margin-bottom: 24px;
        padding: 24px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #fff;
    }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light dfx-nav fixed-top" role="navigation">
    <div class="container">
        <a class="navbar-brand" href="/"><img src="../images/logo_400.png" alt="Logo" width="300" height=""></a>
    </div>
</nav>
    <!-- Page Content -->
    <div class="container install-shell">
        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-12">
                <h1 class="border-bottom pb-2 mb-4">INSTALLATION
                    <small>Datenbank konfigurieren</small>
                </h1>
            </div>
        </div>
        <!-- /.row -->
        <div class="install-panel">
        <h2>Schreibe Konfiguration</h2>
        <?php
        $vendorAutoload = is_file('../../vendor/autoload.php');

        if(!is_file('../../.env')){
            $dbHost = !empty($_POST['dbHost']) ? $_POST['dbHost'] : 'localhost';
            $dbName = $_POST['dbName'];
            $dbUser = $_POST['dbUser'];
            $dbPort = $_POST['dbPort'];
            $dbVersion = $_POST['dbVersion'];
            $dbPasswTest = $_POST['dbPassw'];
            $dbPassw = !empty($_POST['dbPassw']) ? urlencode((string) $_POST['dbPassw']) : '' ;
            $dfxUrl = $_POST['dfxUrl'];
            $dfxMail = $_POST['dfxMail'];
            $dfxImpressum = $_POST['dfxImpressum'];

            $mailDSN =  $_POST['mailDSN'];
            if($_POST['mailDSN'] == 'sendmail'){
                $mailLogin = 'default';
            }else{
                $mailLogin = $_POST['smtpUser'].':'.$_POST['smtpPw'].'@'.$_POST['smtpServer'].':'.$_POST['smtpPort'];
            }
            echo '<div class="alert alert-success">Testverbindung zur Datenbank herstellen</div>';

            // Datenbankverbindung hier mal testen

            $mysqli = new mysqli($dbHost, $dbUser, $dbPasswTest, $dbName);

            if ($mysqli->connect_error) {
                die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
            }else{
                echo '<div class="alert alert-success">Testverbindung zur Datenbank erfolgreich</div>';
            }

            $dbPassw = $dbPassw =='' ? null : $dbPassw;
            $secret = sha1($dbPassw.random_int(10000,1000000));
            $var_file=implode("",(@file("env_dist.yml")));
            $var_file=str_replace(["#dbHost#","#dbUser#","#dbPassw#","#dbName#","#dbPort#","#dbVersion#","#mailDsn#", "#mailLogin#", "#secret#"],[$dbHost,$dbUser,$dbPassw,$dbName,$dbPort,$dbVersion,$mailDSN,$mailLogin,$secret],$var_file);
            $konf=fopen("../../.env","w");
            fwrite($konf,$var_file);
            $ok=fclose($konf);

            if($ok)
               echo '<div class="alert alert-success">Datenbank-Konfiguration .... erfolgreich angelegt</div>';
            else
                echo '<div class="alert alert-danger">Fehler bei der Anlage der Datenbank-Konfiguration oder der Mail-Konfiguration</div>';

            $var_file=implode("",(@file("datefix_dist.yaml")));
            $var_file=str_replace("#dfxUrl#",$dfxUrl,$var_file);
            $var_file=str_replace("#dfxMail#",$dfxMail,$var_file);

            $konf=fopen("../../config/datefix.yaml","w");
            fwrite($konf,$var_file);
            $ok=fclose($konf);

            if($ok)
                echo '<div class="alert alert-success">Datefix-Konfiguration .... erfolgreich angelegt</div>';
            else
                echo '<div class="alert alert-danger">Fehler bei der Anlage der Datefix-Konfiguration</div>';

            $var_file=implode("",(@file("curl_index_dist.php")));
            $var_file=str_replace("#dfxUrl#",$dfxUrl,$var_file);

            $curldemo=fopen("../../web/curl_index.php","w");
            fwrite($curldemo,$var_file);
            $ok=fclose($curldemo);

            if($ok)
                echo '<div class="alert alert-success">Musterdatei für cURL .... erfolgreich angelegt</div>';
            else
                echo '<div class="alert alert-danger">Fehler bei der Anlage der Musterdatei für cURL</div>';


            $konf=fopen("../../templates/impressum.html.twig","w");
            fwrite($konf,nl2br((string) $dfxImpressum));
            $ok=fclose($konf);

            if($ok)
               echo '<div class="alert alert-success">Impressum .... erfolgreich angelegt</div>';
            else
               echo '<div class="alert alert-danger">Fehler bei der Anlage des Impressums</div>';

            ?>
            <hr>
            <?php if ($vendorAutoload) { ?>
                <div class="alert alert-info">Die Konfiguration ist geschrieben. Die weiteren Schritte laufen jetzt ueber den Symfony-Installer.</div>
                <a href="<?php echo $dfxUrl ?>/installer/status" class="btn btn-primary">Weiter zur Statuspruefung</a>
            <?php } else { ?>
                <div class="alert alert-warning">Die Konfiguration ist geschrieben. Bitte zuerst <code>composer install --no-dev --optimize-autoloader</code> ausfuehren und danach <code>/installer/status</code> aufrufen.</div>
            <?php } ?>
        <?php
        }else{
        ?>
            <p>Für eine Neuinstallation müssen Sie die Konfigurationsdateien /.env und /config/datefix.yaml vor dem Start löschen.</p>
            <p>Für ein Update wechseln Sie in die <a href="/installer/status">Statusprüfung</a> oder in den <a href="/update">Update-Prozess</a>.</p>
        <?php
        }
        ?>
        </div>


        <!-- Footer -->
        <footer>
            <div class="row">
                <div class="col-12">
                    <p>Copyright &copy; POOL ONLINE INTERNETSERVICE 1999 - <?php echo date('Y') ?></p>
                </div>
            </div>
        </footer>
     </div>

    <!-- /.container -->
</body>

</html>
