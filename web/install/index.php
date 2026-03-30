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
        <a class="navbar-brand" href="/"><img src="../images/logo_400.png" width="300" alt="Datefix Logo"></a>
    </div>
</nav>
    <!-- Page Content -->
    <div class="container install-shell">
        <!-- Page Heading/Breadcrumbs -->
        <?php
        $composerJson = is_file('../../composer.json') ? json_decode((string) file_get_contents('../../composer.json'), true) : null;
        $composerLock = is_file('../../composer.lock') ? json_decode((string) file_get_contents('../../composer.lock'), true) : null;
        $vendorAutoload = is_file('../../vendor/autoload.php');

        function dfx_parse_env_file($path) {
            $values = [];
            if (!is_file($path)) {
                return $values;
            }
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_starts_with($line, 'export ')) {
                    $line = substr($line, 7);
                }
                $parts = explode('=', $line, 2);
                if (count($parts) !== 2) {
                    continue;
                }
                $values[trim($parts[0])] = trim(trim($parts[1]), "\"'");
            }
            return $values;
        }

        function dfx_detect_existing_database($databaseUrl) {
            if (!$databaseUrl) {
                return ['configured' => false, 'has_tables' => false, 'error' => null];
            }

            $parts = parse_url($databaseUrl);
            if (!$parts || !isset($parts['host'], $parts['user'], $parts['path'])) {
                return ['configured' => true, 'has_tables' => false, 'error' => 'DATABASE_URL konnte nicht ausgewertet werden.'];
            }

            $dbName = ltrim($parts['path'], '/');
            $dbUser = urldecode($parts['user'] ?? '');
            $dbPass = urldecode($parts['pass'] ?? '');
            $dbHost = $parts['host'] ?? 'localhost';
            $dbPort = $parts['port'] ?? 3306;

            $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int) $dbPort);
            if ($mysqli->connect_error) {
                return ['configured' => true, 'has_tables' => false, 'error' => $mysqli->connect_error];
            }

            $result = $mysqli->query('SHOW TABLES');
            $tableCount = $result instanceof mysqli_result ? $result->num_rows : 0;
            if ($result instanceof mysqli_result) {
                $result->free();
            }
            $mysqli->close();

            return ['configured' => true, 'has_tables' => $tableCount > 0, 'error' => null];
        }

        $env = array_merge(
            dfx_parse_env_file('../../.env'),
            dfx_parse_env_file('../../.env.local')
        );
        $dbState = dfx_detect_existing_database($env['DATABASE_URL'] ?? null);
        $hasDatefixConfig = is_file('../../config/datefix.yaml');

        if(($dbState['configured'] && $dbState['has_tables']) || $hasDatefixConfig){
        ?>
            <div class="row">
                <div class="col-12">
                    <h1 class="border-bottom pb-2 mb-4">UPDATE
                        <small>Bestehende Installation erkannt.</small>
                    </h1>
                    <div class="install-panel">
                    <p>Der Installer erkennt eine bestehende Konfiguration oder eine bereits befüllte Datenbank.</p>
                    <p>Für eine Neuinstallation müssen Sie die Konfigurationsdateien <code>/.env</code> und <code>/config/datefix.yaml</code> entfernen und eine leere Datenbank verwenden.</p>
                    <p>Für ein Update wechseln Sie in die <a href="/installer/status">Statusprüfung</a>. Der alte <a href="/update">Update-Prozess</a> steht weiterhin direkt zur Verfügung.</p>
                    <?php if (!empty($dbState['error'])) { ?>
                        <div class="alert alert-warning">Die Datenbank konnte nicht vollständig geprüft werden: <?php echo htmlspecialchars($dbState['error']) ?></div>
                    <?php } ?>
                    </div>
                </div>
            </div>
        <?php
    }else{
        ?>
        <div class="row">
            <div class="col-12">
                <h1 class="border-bottom pb-2 mb-4">INSTALLATION
                    <small>Datenbank konfigurieren</small>
                </h1>
            </div>
        </div>
        <!-- /.row -->
        <div class="install-panel">
        <h2>Versions- und Dateicheck</h2>
        <h4>Composer / Projektstand</h4>
        <?php
        $requiredPhpVersion = is_array($composerJson) ? ($composerJson['require']['php'] ?? 'unbekannt') : 'unbekannt';

        if (is_array($composerJson)) {
            echo '<div class="alert alert-success">composer.json vorhanden</div>';
            echo '<div class="alert alert-info">PHP-Anforderung: '.htmlspecialchars($requiredPhpVersion).'</div>';
            $requiredPhpVersionForCompare = preg_match('/\d+(?:\.\d+)+/', (string) $requiredPhpVersion, $matches) ? $matches[0] : null;
            if ($requiredPhpVersionForCompare !== null && version_compare(phpversion(), $requiredPhpVersionForCompare, '<')) {
                echo '<div class="alert alert-danger" style="">Ihre PHP-Version: '.phpversion() .' - die Composer-Anforderung '.htmlspecialchars((string) $requiredPhpVersion).' wird damit nicht erfüllt.</div>';
                $error = true;
            } else {
                echo '<div class="alert alert-success" style="">Ihre PHP-Version: '.phpversion() .' - kompatibel zur Composer-Anforderung '.htmlspecialchars((string) $requiredPhpVersion).'.</div>';
            }
            echo '<div class="alert alert-info">Symfony-Anforderung: '.htmlspecialchars($composerJson['extra']['symfony']['require'] ?? 'unbekannt').'</div>';
        } else {
            echo '<div class="alert alert-danger">composer.json fehlt</div>';
            $error = true;
        }
        if (is_array($composerLock)) {
            echo '<div class="alert alert-success">composer.lock vorhanden</div>';
        } else {
            echo '<div class="alert alert-warning">composer.lock fehlt - Server-Deployment sollte mit festem Lockfile erfolgen</div>';
        }
        if ($vendorAutoload) {
            echo '<div class="alert alert-success">vendor/autoload.php vorhanden</div>';
            echo '<div class="alert alert-info">Der eigentliche Install-/Update-Ablauf laeuft ueber die <a href="/installer/status">Statuspruefung</a>. Diese Seite dient nur noch der Vorpruefung und Erzeugung der Grundkonfiguration.</div>';
        } else {
            echo '<div class="alert alert-danger">vendor/autoload.php fehlt. Bitte zuerst composer install --no-dev --optimize-autoloader ausführen.</div>';
            $error = true;
        }
        ?>
        <h4>Folgende Verzeichnisse müssen schreibbar sein</h4>
        <?php


            $arDirs = ["../../", "../../config","../../templates","../../var/cache", "../../var/cache/prod", "../../var/cache/dev", "../../var/log", "../../var/tmp", "../../var/tmp/mpdf", "../../var/sessions/dev",  "../../var/sessions/prod", "../../web/cache", "../../web/css", "../../web/css/own", "../../web/scss", "../../web/scss/own", "../../web/images/dfx", "../../web/pdf/dfx", "../../web/media/dfx", "../../web/exports", "../../migrations" ];

            $arDirsFiles = ["../../web/scss", "../../web/css"];

        $error = false;
        $filesWritable = true;
        foreach($arDirs as $dir){
        	if(is_writable($dir)){
        		echo '<div class="alert alert-success">'.substr($dir,5) .'</div>';
        	}else{
        		echo '<div class="alert alert-danger">'.substr($dir,5) .'</div>';
        		$error = true;
        	}
        }
        ?>
		<h4>Folgende Dateien müssen überschreibbar sein</h4>
		<?php
        foreach($arDirsFiles as $dir){
            $errorV = false;
        	echo '<div class="alert alert-info">Alle Dateien im Verzeichnis '.substr($dir,5) .':</div>';
        	$arFiles = scandir($dir);
        	foreach ($arFiles as $file) {
        		if(!str_starts_with($file, '.')) {
        			if(!is_writable($dir)){
        				echo '<div class="alert alert-danger" style="margin-left: 20px">'.$file .'</div>';
        				$error = true;
        				$errorV = true;
                        $filesWritable = false;
        			}
        		}
        	}
        	if(!$errorV)
        		echo '<div class="alert alert-success" style="margin-left: 20px">OK - alle Dateien sind schreibbar</div>';

        }

        ?>
<hr>
        <?php if (!$error && $filesWritable) { ?>
        <h2>Datenbank-Zugang</h2>
        <!-- Content Row -->
        <div class="row">
            <div class="col-md-6">
				<form name="form" method="post" action="step2.php" id="daba">
	                <div class="form-group">
                        <label class="required" for="form_dbHost">Datenbank Servername/IP</label>
                        <input type="text" id="form_dbHost" name="dbHost" required="required" class="form-control" />
   	                </div>
                    <div class="form-group">
                       <label class="required" for="form_dbName">Datenbank Name</label>
                       <input type="text" id="form_dbName" name="dbName" required="required" class="form-control" />
  	                </div>
                	<div class="form-group">
                        <label class="required" for="form_dbUser">Datenbank User</label>
                        <input type="text" id="form_dbUser" name="dbUser" required="required" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="" for="form_dbPassw">User Passwort</label>
                        <input type="text" id="form_dbPassw" name="dbPassw" class="form-control" />
   	                </div>
                    <div class="form-group">
                        <label class="required" for="form_dbPort">Datenbank Port</label>
                        <input type="text" id="form_dbPort" name="dbPort" required="required" value="3306" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="required" for="form_dbVersion">Datenbank Serverversion (MySQL nur Versionsnummer, also z.B. "5.7" oder "8.0" / Maria DB in der Form "10.3.38-MariaDB"</label>
                        <input type="text" id="form_dbVersion" name="dbVersion" required="required" class="form-control" />
                    </div>
   	            <hr>
                <h2>System-Konfiguration</h2>
   	            <div class="form-group">
                    <label class="" for="form_dfxMail">Mail-Absender des Systems</label>
                    <input type="text" id="form_dfxMail" name="dfxMail" class="form-control" />
   	            </div>
   	            <div class="form-group">
                    <label class="" for="form_dfxImpressum">Mail-Footer</label>
                    <textarea id="form_dfxImpressum" name="dfxImpressum" class="form-control" rows="6"></textarea>
                </div>
                <div class="form-group">
                    <label class="required" for="form_dfxUrl">Datefix Stamm-Url</label>
                    <input type="text" id="form_dfxUrl" name="dfxUrl" required="required" placeholder="http(s)://www.ihreDomain.de/datefixverzeichnis" class="form-control" />
   	            </div>
                <h2>E-Mail Versandsystem</h2>
                <div class="form-group">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="mailDSN_1" name="mailDSN" value="default" checked="checked" />
                        <label class="form-check-label" for="mailDSN_1">Sendmail</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="mailDSN_2" name="mailDSN" value="smtp" />
                        <label class="form-check-label" for="mailDSN_2">SMTP</label>
                    </div>
                </div>
                <div id="smtp" style="display:none">
                    <div class="form-group">
                        <label class="" for="form_smtpUser">E-Mail Username</label>
                        <input type="text" id="form_smtpUser" name="smtpUser" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="" for="form_smtpPw">E-Mail Passwort</label>
                        <input type="password" id="form_smtpPw" name="smtpPw" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="" for="form_smtpServer">E-Mail SMTP Server</label>
                        <input type="text" id="form_smtpServer" name="smtpServer" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="" for="form_smtpPort">E-Mail SMTP Port</label>
                        <input type="text" id="form_smtpPort" name="smtpPort" class="form-control" />
                    </div>
                </div>
            <h2>Lizenzbedingungen</h2>
                <div><input type="checkbox" id="form_lizenzAgree" name="lizenzAgree" /> &nbsp; Lizenzbedingungen wurden gelesen und akzeptiert<br><br></div>
                <div class="row">
                    <div class="col-md-6"><button type="submit" id="form_submit" name="submit" class="btn btn-primary">Konfigurationsdatei schreiben</button></div>
                    <div class="col-md-6"><button type="reset" id="form_reset" name="reset" class="btn btn-primary">zurücksetzen</button></div>
                </div>
            </form>
        </div>
    </div>
        <?php } else { ?>
        <div class="alert alert-warning">
            Das Installationsformular ist noch gesperrt. Bitte sorgen Sie dafuer, dass alle Pruefpunkte oben gruen sind und alle benoetigten Verzeichnisse und Dateien schreibbar sind.
        </div>
        <?php } ?>
        </div>
    <?php
    }
    ?>


        <hr>

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
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
    var mailTypeInputs = document.querySelectorAll("input[name='mailDSN']");
    var smtpSection = document.getElementById('smtp');

    function toggleSmtpSection(value) {
        smtpSection.style.display = value === 'smtp' ? 'block' : 'none';
    }

    mailTypeInputs.forEach(function (input) {
        input.addEventListener('click', function () {
            toggleSmtpSection(input.value);
        });
    });

    var selectedMailType = document.querySelector("input[name='mailDSN']:checked");
    toggleSmtpSection(selectedMailType ? selectedMailType.value : 'default');
});
</script>
</html>
