<?php
    // Konfigurationsbereich - ist individuell anzupassen
    /* Kann hier entfallen, wenn die Vatriablen in der einbindenden Datei bereits definiert sind.
    // Die Url zu Ihrem Datefix Kalender in der Form https://IhrDatefix.de
    $cfgUrl ='';
    // Die Kalendernummer - Standard ist 1 für den Meta-Kalender und die Single-Version
    $cfgKid = 1;
    // Der Pfad zur DocumentRoot des Servers - Wird nur benötigt, wenn öffentliche Termineingabe mit Bild- und Dokumenteneingabe freigeschaltet ist
    $cfgRootPath = $_SERVER['DOCUMENT_ROOT'];
    // Ende Konfigurationsbereich
    */
    $err = NULL;
    $dfxContent = NULL;
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $myPost = $_POST;
        $method = "POST";
    } else {
        $myPost = $_GET;
        $method = "GET";
    }
    
    // Das Speichern von Bildern und Dokumenten ist aktuell nur möglich, wenn sich die Anwendung auf demselben Server wie Datefix befindet.
    // Eine Übertragung mittels CURLStringFile() ist für Datefix 8 geplant
    // Der Block File-Uploads sollte bei nicht aktiver öffentlicher Termin-Eingabe aus dem Web entfernt werden

    if (isset($_FILES['termine'])) {
        foreach ($_FILES['termine']['error'] as $key => $error) {
            // echo 'Hallo '.$key.' / '.UPLOAD_ERR_OK.' / '.$error['file'];
            if ($error === UPLOAD_ERR_OK) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $fileSize = $finfo->file($_FILES['termine']['tmp_name'][$key]);
                if ($fileSize === 0) {
                    $err .= 'Fehler beim Dateiupload: ' . $_FILES['termine']['tmp_name'][$key] . ' ist eine leere Datei';
                    continue;
                }
                
                if ($key === "pdfFile") {
                    $subdir = 'pdf';
                    $field = 'pdf';
                    if (false === $ext = array_search(
                            $finfo->file($_FILES['termine']['tmp_name'][$key]),
                            [
                                'pdf' => 'application/pdf'
                            ],
                            true
                        )) {
                        $err .= 'Fehler beim Dateiupload: Falsches Dateiformat (' . $_FILES['termine']['type'][$key] . ') für Pdf bei Datei ' . $_FILES['termine']['name'][$key] . '.<br>';
                    }
                } else if ($key === "mediaFile") {
                    $subdir = 'media';
                    $field = 'media';
                    if (false === $ext = array_search(
                            $finfo->file($_FILES['termine']['tmp_name'][$key]),
                            [
                                'video/mp4' => 'video/mp4'
                            ],
                            true
                        )) {
                        $err .= 'Fehler beim Dateiupload: Falsches Dateiformat  (' . $_FILES['termine']['type'][$key] . ')  für Media-Datei bei Datei ' . $_FILES['termine']['name'][$key] . '.<br>';
                    }
                } else {
                    $subdir = 'images';
                    $field = 'img';
                    $allowedTypes = [
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                    ];
                    if (false === $ext = array_search(
                            $finfo->file($_FILES['termine']['tmp_name'][$key]),
                            $allowedTypes,
                            true
                        )) {
                        $err .= 'Fehler beim Dateiupload: Falsches Dateiformat  (' . $_FILES['termine']['type'][$key] . ') für Bilder oder Grafiken bei Datei ' . $_FILES['termine']['name'][$key] . '.<br>';
                    }
                }
                
                if ($err === NULL) {
                    $prename = substr(md5(time()), 0, 10);
                    $fileName = $prename . '_' . str_replace(' ', '_', $_FILES['termine']['name'][$key]);
                    $fileData = 'data://application/octet-stream;base64,' . base64_encode(file_get_contents($_FILES['termine']['tmp_name'][$key]));
                    $cFile = new CURLFile($fileData, $finfo->file($_FILES['termine']['tmp_name'][$key]), $fileName);
                    $myPost['termine'][$field] = $cFile;
                }
            }
        }
    }
    
    // Block File-Uploads
    if (isset($myPost['dfxpath'])) {
        $dfxPath = $myPost['dfxpath'];
        $termin = null;
    } else if (isset($myPost['dfxid'])) {
        $dfxPath = '/js/kalender/' . $cfgKid . '/detail/' . $myPost['dfxid'];
    } else {
        $dfxPath = '/js/kalender/' . $cfgKid;
        $termin = null;
    }

    if ($method === 'GET') {
        $query = 'cb=all&' . $_SERVER['QUERY_STRING'];
    } else {
        $query = 'cb=all';
    }

    $dfxUrl = $cfgUrl.$dfxPath . "?" . $query;
    $ch = curl_init($dfxUrl);
    if ($ch === FALSE) {
        die ('Fehler bei der Initialisierung von CURL');
    }
    
    if ($method === 'POST') {
        unset($myPost['dfxpath']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($myPost));
    }
    
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

    // Set TCP timeout to 30 seconds
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Datefix','Connection: Close']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $dfx_content = curl_exec($ch);
    if($err !== NULL){
        $dfx_content .= $err;
    }
    
    echo $dfx_content;
    curl_close($ch);
?>
