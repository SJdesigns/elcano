<?php
/* ---- elcano Explorer v3.0 - beta 2.3 ---- */

if (isset($_GET['token'])) {$userList = array(
    // LIST OF USERS
    // ----------------------------------------------------
    // type your users here in the format 'user' => 'password',
        'root' => 'admin',
    // ----------------------------------------------------
    );

    if (isset($_GET['token'])) {

        if (isset($_GET['user']) && isset($_GET['pass'])) {

            $user = htmlspecialchars($_GET['user']);
            $pass = htmlspecialchars($_GET['pass']);
            $token = htmlspecialchars($_GET['token']);

            $existentUser = 0;
            $credentialsCorrect = 0;

            foreach ($userList as $key => $valor) {
                if ($user == $key) {
                    $existentUser++;
                    if ($pass == sha1($valor)) {
                        $credentialsCorrect++;
                        $return['valor'] = $valor;
                    }
                }
            }

            if ($existentUser==0) {
                $return['status'] = 400;
                $return['token'] = $token;
                $return['message'] = 'Wrong User';
            } else {
                if ($credentialsCorrect>0) { // login successfull
                    $return['status'] = 200;
                    $return['token'] = $token;
                    $return['message'] = 'Access Granted';
                } else { // wrong password
                    $return['status'] = 400;
                    $return['token'] = $token;
                    $return['message'] = 'Wrong Credentials';
                }
            }

            /*echo '<pre>';
            print_r($return);
            echo '</pre>';*/

        } else { // missing login data
            $return['status'] = 400;
            $return['token'] = $token;
            $return['message'] = 'missing data';
        }

    } else { // missing token
        $return['status'] = 400;
        $return['token'] = 'null';
        $return['message'] = 'missing data';
    }

    echo json_encode($return);

} else if (isset($_POST['listDir'])) {
    // listDirectory.php

    function listDirectory($path) {
        $directories = [];
        $files = [];
        $return = [];

        $directorio = opendir($path); //ruta actual
        while ($archivo = readdir($directorio))
        {
            if (is_dir($path . $archivo)) {//verificamos si es o no un directorio
                // directories
                if ($archivo != "." && $archivo != ".." && $archivo != "explorerConf") {
                    $content = [];
                    $content['fileName'] = $archivo;
                    $content['filePath'] = $path . $archivo;
                    $content['fileType'] = 'folder';

                    array_push($directories, $content);
                }
            } else {
                // files

                // obtenemos la extensión del fichero
                $size = filesize($path . $archivo);
                if ($size > 1024) {
                    //es mayor que 1024 bytes
                    $size = ($size / 1024);
                    if ($size > 1024) {
                        //es mayor que 1024 kilobytes
                        $size = ($size / 1024);
                        if ($size > 1024) {
                            //es mayor que 1024 megabytes
                            $size = ($size / 1024);
                            $size = round($size,2,PHP_ROUND_HALF_DOWN) . " GB";
                        } else {
                            $size = round($size,2,PHP_ROUND_HALF_DOWN) . " MB";
                        }
                    } else {
                        $size = round($size,2,PHP_ROUND_HALF_DOWN) . " kB";
                    }
                } else { $size = $size . " B"; }

                // obtenemos el tipo de fichero
                if (strpos($archivo,'.') != null) {
                    $fileType = substr($archivo,strpos($archivo,'.')+1,strlen($archivo)-1);
                } else {
                    $fileType = 'unknown';
                }

                if ($archivo != "." && $archivo != ".." && ($archivo != "default.php")) {
                    $content = [];
                    $content['fileName'] = $archivo;
                    $content['filePath'] = $path . $archivo;
                    $content['fileType'] = $fileType;
                    $content['fileSize'] = $size;

                    array_push($files, $content);
                }
            }
        }

        $return['dir'] = $directories;
        $return['files'] = $files;

        echo json_encode($return);

        /*echo '<pre>';
        print_r($return);
        echo '</pre>';*/
    }

    function directoryError($code, $path='') {
        $return = [];
        $return['error']['code'] = $code;

        if ($code == '404') {
            $return['error']['message'] = 'Directory not found';
        } else if ($code == '403') {
            $return['error']['message'] = 'Access prohibited';
        }

        echo json_encode($return);
    }

    if (isset($_POST['ruta'])) {
        if (file_exists($_POST['ruta'])) {
            listDirectory($_POST['ruta']);
        } else {
            directoryError(404,$_POST['ruta']);
        }
    } else {
        directoryError(403);
    }

} else if (isset($_POST['dirTree'])) {
    // directoryTree.php

    function directoryTree($explRuta, $rutaGlobal) {
        //echo 'ruta global: ' . $rutaGlobal . '<br />'; // ruta raíz del arbol de directorios (normalmente ./)
        //echo 'ruta nivel: ' . $explRuta . '<br />'; // ruta actual a leer
        $sepRutaGlobal = explode("/", $rutaGlobal); // array con la ruta global segmentada
        $sepRutaNivel = explode("/", $explRuta); // array con la ruta actual segmentada
        $contGlobal = count($sepRutaGlobal) - 1; // cantidad de subdirectorios
        $contNivel = count($sepRutaNivel) - 2; // cantidad de subdirectorios

        $explDir = opendir($explRuta);

        while ($explFich = readdir($explDir)) {
            if (is_dir($explRuta . $explFich)) {
                if ($explFich != "." && $explFich != ".." && $explFich != "explorerConf") {
                    $compararGlobal = "./";
                    for ($i=1;$i<=$contNivel+1;$i++) {
                        if (array_key_exists($i, $sepRutaGlobal)) {
                            $compararGlobal .= $sepRutaGlobal[$i] . '/';
                        }
                        //echo '<p>contglobal: ' . $contGlobal . ', $i: ' . $i . '</p>';
                    }
                    //$compararGlobal .= $explFich;

                    //echo 'comp: ' . $compararGlobal . ' - ' . $explRuta.$explFich;

                    if ($compararGlobal == ($explRuta . $explFich . "/")) {
                        //echo '<p>' . $compararGlobal . '<b>igual </b> a ' . $explRuta . $explFich . '/<p>';
                        if ($rutaGlobal == $explRuta . $explFich . "/") {
                            echo '<details open id="activo">';
                        } else {
                            echo '<details open>';
                        }
                    } else {
                        //echo '<p>' . $compararGlobal . '<b>diferente </b> a ' . $explRuta . $explFich . '/<p>';
                        echo '<details>';
                    }

                    if (substr($explRuta,0,2) == '..') {
                        $explRuta2 = preg_replace("/\.\./", ".", $explRuta);
                    } else {
                        $explRuta2 = $explRuta;
                    }

                    echo '<summary><p class="linkTree" onclick="getFolder(\'' . $explRuta2 . $explFich . '/\')">' . $explFich . '</p></summary>';
                    directoryTree($explRuta . $explFich . "/", $rutaGlobal);
                    echo '</details>';
                }
            }
        }
    }

    if (isset($_POST['ruta'])) {
        $ruta = $_POST['ruta'];

        if ($ruta == '../') {
            $ruta = './';
        }
        directoryTree("./", $ruta);
    } else {
        echo '<p>Árbol de directorios</p><p>no disponible</p>';
    }

} else {
    // lang.php
    if (!isset($_COOKIE['elcano-lang'])) { setcookie('elcano-lang',substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2),time()+60*60*24*365,'/',"",false,false); $self = $_SERVER['PHP_SELF']; header("Location: $self"); }; $langTxt = [ 'es'=>[ 'langName'=>'Español','login'=>[ 'title'=>'Inicio de Sesión','user'=>'usuario','pass'=>'contraseña','submit'=>'Continuar' ],'header'=>[ 'headStartUp'=>'Ejecutar el índice del directorio','headDataBase'=>'Acceder a la base de datos','headFavorite'=>'Añadir a favoritos','headNotFavorite'=>'Quitar de favoritos','headView'=>'Cambiar la vista','headMore'=>'Más opciones','headViewMosaic'=>'Mosaico','headViewList'=>'Lista','headViewWall'=>'Muro','headMoreHistory'=>'Historial','headMoreHistoryPrev'=>'Atrás','headMoreHistoryNext'=>'Adelante','headMoreShowExpl'=>'Mostrar Explorador','headMoreHideExpl'=>'Ocultar Explorador','headMoreSettings'=>'Configuración','headMoreLogout'=>'Cerrar Sesión' ],'aside'=>[ 'asideFav'=>'Favoritos','asideDir'=>'Directorios',],'section'=>[ 'sectionFolder'=>'carpetas','sectionFiles'=>'archivos','noResults'=>'Esta carpeta está vacia',],'context'=>[ 'contextExplore'=>'Explorar','contextOpen'=>'Abrir','contextFavorites'=>'Agregar a favoritos',],'history'=>[ 'historyHome'=>'Página de Inicio',],'settings'=>[ 'general'=>'General','darkMode'=>'Activar el modo oscuro','showHiddenFiles'=>'Mostrar archivos ocultos','showfileExtensions'=>'Mostrar extensiones de los archivos','startUp'=>'Estado inicial','startUpDescrip'=>'Vista activa por defecto','startUpLast'=>'Último activo','hideFiles'=>'Omitir archivos','hideFilesDescrip'=>'Nombres de archivos y extensiones separados por comas','hideFilesPlaceholder'=>'ficheros ignorados','priority'=>'Prioridad de Índices','defaultPriority'=>'Índice predeterminado del sistema','priorityDescrip'=>'Lista de prioridad de ejecución para los directorios','priorityPlaceholder'=>'orden de prioridad de archivos','database'=>'Base de datos','databaseDescrip'=>'Ruta de acceso a la base de datos','databasePlaceholder'=>'ruta de la base de datos','lang'=>'Idioma','langDescrip'=>'Selecciona el idioma de la aplicación','default'=>'Configuración predeterminada','defaultButton'=>'Reestablecer','defaultDescrip'=>'Volver a la configuración por defecto' ],'error'=>[ ] ],'en'=>[ 'langName'=>'English','login'=>[ 'title'=>'Log In','user'=>'user','pass'=>'password','submit'=>'Continue' ],'header'=>[ 'headStartUp'=>'Run directory index','headDataBase'=>'Access database','headFavorite'=>'Add to favorites','headNotFavorite'=>'Remove from favorites','headView'=>'Change view','headMore'=>'More options','headViewMosaic'=>'Mosaic','headViewList'=>'List','headViewWall'=>'Wall','headMoreHistory'=>'History Review','headMoreHistoryPrev'=>'Prev','headMoreHistoryNext'=>'Next','headMoreShowExpl'=>'Show Explorer','headMoreHideExpl'=>'Hide Explorer','headMoreSettings'=>'Settings','headMoreLogout'=>'Log Out' ],'aside'=>[ 'asideFav'=>'Favorites','asideDir'=>'Directory Tree',],'section'=>[ 'sectionFolder'=>'folders','sectionFiles'=>'files','noResults'=>'This folder is empty',],'context'=>[ 'contextExplore'=>'Explore','contextOpen'=>'Open','contextFavorites'=>'Add to favorites',],'history'=>[ 'historyHome'=>'Homepage',],'settings'=>[ 'general'=>'General','darkMode'=>'Enable dark mode','showHiddenFiles'=>'Show hidden files','showfileExtensions'=>'Show file extensions','startUp'=>'Start Up','startUpDescrip'=>'Default active view','startUpLast'=>'Last active','hideFiles'=>'Ignore Files','hideFilesDescrip'=>'File names and extensions separated by commas','hideFilesPlaceholder'=>'ignored files','priority'=>'Index Priorities','defaultPriority'=>'Default System Index','priorityDescrip'=>'Execution priority list for directories','priorityPlaceholder'=>'file priority order','database'=>'Database','databaseDescrip'=>'Database path','databasePlaceholder'=>'database path','lang'=>'Language','langDescrip'=>'Select the app language','default'=>'Default Settings','defaultButton'=>'Reset','defaultDescrip'=>'Return to default settings' ],'error'=>[ ] ],'fr'=>[ 'langName'=>'Français','login'=>[ 'title'=>'Se Connecter','user'=>'Nom d\'utilisateur','pass'=>'mot de passe','submit'=>'Se Connecter' ],'header'=>[ 'headStartUp'=>'Exécuter l\'index du répertoire','headDataBase'=>'Accéder à la base de données','headFavorite'=>'Ajouter aux favoris','headNotFavorite'=>'Supprimer des favoris','headView'=>'Change de vue','headMore'=>'plus d\' options','headViewMosaic'=>'Mosaïque','headViewList'=>'Liste','headViewWall'=>'Mur','headMoreHistory'=>'Revue de l\' Histoire','headMoreHistoryPrev'=>'Prev','headMoreHistoryNext'=>'Prochain','headMoreShowExpl'=>'Montrer l\' explorateur','headMoreHideExpl'=>'Chacher explorateur','headMoreSettings'=>'réglages','headMoreLogout'=>'Se déconnecter' ],'aside'=>[ 'asideFav'=>'Favoris','asideDir'=>'Arborescence de directories',],'section'=>[ 'sectionFolder'=>'dossiers','sectionFiles'=>'fichiers','noResults'=>'ce dossier est vide',],'context'=>[ 'contextExplore'=>'Explorer','contextOpen'=>'Ouvrir','contextFavorites'=>'Ajouter aux favoris',],'history'=>[ 'historyHome'=>'Accueil',],'settings'=>[ 'general'=>'Général','darkMode'=>'Activer le mode sombre','showHiddenFiles'=>'montrer les fichiers cachés','showfileExtensions'=>'Afficher les extensions de fichier','startUp'=>'Start Up','startUpDescrip'=>'Vue active par défaut','startUpLast'=>'Dernier actif','hideFiles'=>'Fichiers ignorés','hideFilesDescrip'=>'Noms de fichiers et extensions séparés par des virgules','hideFilesPlaceholder'=>'fichiers ignorés','priority'=>'priorités d\'index','defaultPriority'=>'Index système par défaut','priorityDescrip'=>'Liste des priorités d\'exécution pour les dossiers','priorityPlaceholder'=>'ordre de priorité des fichiers','database'=>'base de données','databaseDescrip'=>'chemin de la base de données','databasePlaceholder'=>'chemin de la base de données','lang'=>'langue','langDescrip'=>'Sélectionnez la langue de l\'application','default'=>'paramètres par défaut','defaultButton'=>'réinitialiser','defaultDescrip'=>'Revenir aux paramètres par défaut' ],'error'=>[ ] ],]; if (isset($_COOKIE['elcano-lang'])) { if (!isset($langTxt[$_COOKIE['elcano-lang']])) { $_COOKIE['elcano-lang'] = 'en'; } } else { $_COOKIE['elcano-lang'] = 'en'; } $lang = $_COOKIE['elcano-lang']; $langJson = json_encode($langTxt);

    // html

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>elcano</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <style type="text/css">
        *{padding:0;margin:0;font-family:"Roboto",sans-serif}body{overflow:overlay}.screen{position:fixed;width:100%;height:100%;background-color:#fff;display:none}#startUp{display:block}#blocked{display:none;z-index:15}#blocked #blockedBack{position:absolute;width:100%;height:100%;display:flex;justify-content:center;align-items:center}#blocked #blockedBack #signIn{position:relative;width:340px;height:auto;box-shadow:2px 2px 8px rgba(0,0,0,.1)}#blocked #blockedBack #signIn #signInTitle{position:relative;width:100%;height:auto;padding:25px 0;text-align:center;color:#fff;background-color:#34515f}#blocked #blockedBack #signIn #signInTitle h1{font-weight:300}#blocked #blockedBack #signIn #signInBody{position:relative;width:100%;height:auto}#blocked #blockedBack #signIn #signInBody #signInError{position:relative;width:100%;min-height:30px;padding:15px 0;text-align:center;color:red}#blocked #blockedBack #signIn #signInBody .signInBodyItem{position:relative;margin:0 40px 28px}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass{width:100%;height:40px;font-size:13pt;padding-left:40px;border:0;outline:0;box-sizing:border-box}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:hover~.signInInputEffect,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:hover~.signInInputEffect{width:100%;left:0%;border-color:#ccc}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:focus~.signInInputEffect,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:focus~.signInInputEffect{width:100%;left:0%;border-color:#34515f}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:focus~.signInIcons,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:focus~.signInIcons{fill:#444}#blocked #blockedBack #signIn #signInBody .signInBodyItem .signInIcons{position:absolute;width:25px;height:25px;left:6px;top:8px;fill:#777}#blocked #blockedBack #signIn #signInBody .signInBodyItem .signInInputEffect{position:absolute;width:0%;left:50%;bottom:0;border-bottom:2px solid;transition:all 0.2s}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit{width:100%;height:50px;color:#34515f;font-size:13pt;margin-top:15px;text-align:center;border:0;outline:0;background-color:#fff}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit:hover,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit:focus{background-color:#f4f4f4}#blocked #blockedBack #loginLang{position:absolute;right:15px;bottom:12px}#blocked #blockedBack #loginLang select{width:150px;height:32px;color:#777;outline:0;font-size:13pt;padding:0 10px;border:0 solid #ccc}header{position:relative;width:100%;height:50px;top:0;background-color:#34515f;display:flex;justify-content:space-between;z-index:10;transition:background-color 0.4s}header div#headerTitle{position:relative;width:auto;height:100%;display:flex;padding-right:50px}header div#headerTitle #headerLogo{position:relative;width:40px;height:40px;top:5px;left:20px}header div#headerTitle h1{color:#fff;padding:4px 0;margin-left:30px;cursor:default;font-weight:300}header nav{position:relative;width:100%;height:100%;display:flex}header nav .navItem{position:relative;height:34px;color:#fff;margin:8px 0;padding:8px 10px;box-sizing:border-box;cursor:pointer}header nav .navItem:hover{background-color:rgba(50,150,255,.15)}header nav .navItem .navHomeItem{position:relative;display:flex}header nav .navItem .navHomeItem svg{width:25px;height:25px;margin-top:-3px;fill:#fff}header nav .navItem .navHomeItem i{margin-top:0;margin-left:5px;font-style:normal}header nav .navSeparator{position:relative;margin:8px 0;padding:8px 6px;font-size:11pt;color:#999;cursor:default}header div#options{position:relative;width:200px;height:100%;padding-left:50px;padding-right:20px;display:flex;flex-direction:row-reverse}header div#options .option{position:relative;width:50px;height:50px}header div#options .option .optionArea{position:absolute;width:0%;height:0%;left:50%;top:50%;border-radius:50%;background-color:rgba(255,255,255,.1);transition:all 0.2s}header div#options .option svg{position:relative;width:34px;height:34px;padding:8px 8px;fill:#fff;z-index:1}header div#options .option svg:hover~.optionArea{width:100%;height:100%;left:0%;top:0%;border-radius:0%}header div#options .optDropDown{position:absolute;width:260px;height:auto;right:0;top:50px;padding:8px 0;background-color:#fff;box-shadow:-2px 2px 8px rgba(0,0,0,.15);cursor:pointer;display:none}header div#options .optDropDown .optViewDesplActive{background-color:rgba(0,0,0,.1)}header div#options .optDropDown div.optDropDownItem{position:relative;width:100%;padding:12px 18px;text-align:center;box-sizing:border-box;display:flex}header div#options .optDropDown div.optDropDownItem:hover{background-color:rgba(0,0,0,.1)}header div#options .optDropDown div.optDropDownItem:hover .optChainDropdown{display:flex}header div#options .optDropDown div.optDropDownItem .optMoreDisabled{color:#aaa}header div#options .optDropDown div.optDropDownItem .optMoreDisabled:hover{background-color:#fff}header div#options .optDropDown div.optDropDownItem .optMoreDisabled svg{fill:#aaa}header div#options .optDropDown div.optDropDownItem .optMoreDisabled p small{color:#ddd}header div#options .optDropDown div.optDropDownItem svg{width:25px;height:25px;margin-right:12px}header div#options .optDropDown div.optDropDownItem p{margin-top:2px}header div#options .optDropDown div.optDropDownItem p small{color:#aaa;margin-left:12px}header div#options .optDropDown .optChainDropdown{position:absolute;width:220px;margin-left:-240px;top:-8px;padding:8px 0;background-color:#fff;box-shadow:2px 2px 8px rgba(0,0,0,.1);flex-direction:column;display:none}main{position:absolute;width:100%;height:100%;top:0;padding-top:50px;box-sizing:border-box}main #errorReporting{position:fixed;right:0;z-index:1}main #errorReporting .errorItem{position:relative;max-width:400px;padding:10px 35px;margin:10px 18px;background-color:rgba(255,68,68,.8);box-shadow:-2px 2px 8px rgba(0,0,0,.1);display:flex;justify-content:center}main #errorReporting .errorItem svg{width:30px;height:30px;fill:#fff}main #errorReporting .errorItem p{color:#fff;font-size:13pt;padding:5px 12px}main #shadow{position:fixed;width:100%;height:100%;background-color:rgba(0,0,0,.1);display:none;z-index:1}main #mainCenter{position:relative;width:100%;height:100%;display:flex}main #mainCenter aside{position:relative;width:320px;margin-left:0;height:100%;background-color:#8eacbc;display:flex;flex-direction:column;transition:margin-left 0.4s}main #mainCenter aside #asideFavorites{position:relative;width:100%;height:auto;max-height:190px;box-shadow:-2px 2px 4px rgba(120,144,156,.4)}main #mainCenter aside #asideFavorites #favTitle{justify-content:space-between}main #mainCenter aside #asideFavorites #favTitle div{display:flex;flex-direction:row}main #mainCenter aside #asideFavorites #favTitle #favCount{padding:0 15px}main #mainCenter aside #asideFavorites #asideFavBody{padding:4px 0 8px;overflow:auto;max-height:143px}main #mainCenter aside #asideFavorites #asideFavBody .favFolder{padding:3px 10px;box-sizing:border-box;cursor:pointer}main #mainCenter aside #asideFavorites #asideFavBody .favFolder:hover{background-color:#fff}main #mainCenter aside #asideFavorites #asideFavBody .favFolder small{font-size:10pt;font-weight:300;font-style:italic;margin-left:12px;color:#444}main #mainCenter aside #asideTree{position:relative;width:100%;height:auto;overflow:auto}main #mainCenter aside .asideTitle{position:relative;width:100%;height:30px;margin-top:8px;display:flex;flex-direction:row;cursor:default}main #mainCenter aside .asideTitle svg{width:25px;height:25px;fill:#222;margin:2px 8px}main #mainCenter aside .asideTitle p{margin:5px 0}main #mainCenter section{position:relative;width:100%;height:100%;overflow:auto}main #mainCenter section #itemArea{position:relative;width:100%;height:auto;display:flex;flex-wrap:wrap;color:#34515f}main #mainCenter section #itemArea #emptyFolder{padding:25px 50px}main #mainCenter section #itemArea .item{position:relative;padding:0 12px;overflow:hidden;box-sizing:border-box;box-shadow:2px 2px 8px rgba(0,0,0,.1);display:flex}main #mainCenter section #itemArea .item:hover{background-color:rgba(0,0,0,.1)}main #mainCenter section #itemArea .itemHidden{opacity:.4}main #mainCenter section #itemArea .itemActive{background-color:rgba(0,0,0,.1)}main #mainCenter section #itemArea .itemMosaic{width:16.6666666667%;height:60px}main #mainCenter section #itemArea .itemMosaic .itemLogo{position:relative;width:60px;height:100%;padding:0;display:flex;justify-content:center;align-items:center}main #mainCenter section #itemArea .itemMosaic .itemLogo svg,main #mainCenter section #itemArea .itemMosaic .itemLogo img{height:42px}main #mainCenter section #itemArea .itemMosaic .itemText{position:relative;width:100%;height:100%;display:flex;align-items:center;margin-left:12px}main #mainCenter section #itemArea .itemMosaic .itemText p{font-size:13pt;margin:8px 0;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemMosaic .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemMosaic .itemFiletype,main #mainCenter section #itemArea .itemMosaic .itemFilesize{display:none}main #mainCenter section #itemArea .itemList{width:100%;height:60px}main #mainCenter section #itemArea .itemList .itemLogo{position:relative;width:60px;height:60px;margin-left:4px;display:flex;justify-content:center;align-items:center}main #mainCenter section #itemArea .itemList .itemLogo svg,main #mainCenter section #itemArea .itemList .itemLogo img{height:42px}main #mainCenter section #itemArea .itemList .itemText{position:relative;width:100%;height:100%;margin-left:18px;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemText p{font-size:13pt;margin:8px 0;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemList .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemList .itemFiletype{position:relative;width:200px;height:100%;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemFiletype p{font-size:13pt;margin:8px 0;cursor:default}main #mainCenter section #itemArea .itemList .itemFilesize{position:relative;width:200px;height:100%;margin-right:20%;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemFilesize p{font-size:13pt;margin:8px 0;cursor:default}main #mainCenter section #itemArea .itemWall{width:auto;height:auto;flex-direction:column}main #mainCenter section #itemArea .itemWall .itemLogo{position:relative;width:50px;height:50px;padding:9px;display:flex;justify-content:center;margin:0 auto}main #mainCenter section #itemArea .itemWall .itemLogo svg,main #mainCenter section #itemArea .itemWall .itemLogo img{width:50px;height:50px}main #mainCenter section #itemArea .itemWall .itemText{position:relative;width:100%;height:100%;display:flex;align-items:center}main #mainCenter section #itemArea .itemWall .itemText p{font-size:13pt;margin:8px 0;margin:8px auto;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemWall .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemWall .itemFiletype,main #mainCenter section #itemArea .itemWall .itemFilesize{display:none}main #mainCenter section #folderInfo{position:fixed;right:0;bottom:0;font-size:11pt;padding:3px 7px 4px;background-color:rgba(0,0,0,.1)}details{margin:0;color:#444;padding:5px;cursor:default;-webkit-transition:all 0.1s}details[open]{animation-name:slideDown;animation-duration:200ms;animation-timing-function:ease-in}details:hover{background-color:#fff}details details{border:0;margin-left:12px}details summary p.linkTree{color:#333}summary p.linkTree{text-decoration:none}summary p.linkTree:hover{color:#2196f3}summary p.linkTree:focus{text-decoration:none}details#activo>summary>p.linkTree{color:#2196f3}summary{outline:0}details summary::-webkit-details-marker{display:none}summary::before{position:relative;float:left;content:"+";color:#444;margin-right:0;padding-right:12px;margin-top:-5px;font-size:18pt}summary:hover::before{color:#2196f3}details[open]>summary::before{position:relative;float:left;content:"-";margin-left:0;padding-left:2px;margin-right:0;padding-right:14px;margin-top:-10px;font-size:22pt}@keyframes slideDown{0%{opacity:0;height:0}100%{opacity:1;height:20px}}aside ::selection{background-color:transparent}.spinner{margin:100px auto;width:80px;height:80px;position:relative;text-align:center;-webkit-animation:sk-rotate 2s infinite linear;animation:sk-rotate 2s infinite linear}.dot1,.dot2{width:60%;height:60%;display:inline-block;position:absolute;top:0;background-color:#cfd8dc;border-radius:100%;-webkit-animation:sk-bounce 2s infinite ease-in-out;animation:sk-bounce 2s infinite ease-in-out}.dot2{top:auto;bottom:0;-webkit-animation-delay:-1s;animation-delay:-1s}@-webkit-keyframes sk-rotate{100%{-webkit-transform:rotate(360deg)}}@keyframes sk-rotate{100%{transform:rotate(360deg);-webkit-transform:rotate(360deg)}}@-webkit-keyframes sk-bounce{0%,100%{-webkit-transform:scale(0)}50%{-webkit-transform:scale(1)}}@keyframes sk-bounce{0%,100%{transform:scale(0);-webkit-transform:scale(0)}50%{transform:scale(1);-webkit-transform:scale(1)}}@media screen and (max-width:2100px){main #mainCenter section #itemArea .itemMosaic{width:20%}}@media screen and (max-width:1750px){main #mainCenter section #itemArea .itemMosaic{width:25%}}@media screen and (max-width:1300px){main #mainCenter section #itemArea .itemMosaic{width:33.33333333%}}@media screen and (max-width:1050px){main #mainCenter section #itemArea .itemMosaic{width:50%}}@media screen and (max-width:750px){main #mainCenter section #itemArea .itemMosaic{width:100%}}@media screen and (max-width:420px){main #mainCenter aside{margin-left:-320px}}#dialogBack{position:fixed;width:100%;height:100%;top:0;padding-top:25px;background-color:rgba(0,0,0,.1);align-items:center;display:none}#dialogBack .dialog{position:relative;width:950px;height:520px;margin:0 auto;background-color:#fff;box-shadow:2px 2px 8px rgba(0,0,0,.1);display:none}#dialogBack .dialog .dialogTitleBar{position:relative;width:100%;height:50px;box-shadow:2px 2px 8px rgba(0,0,0,.1);display:flex;justify-content:space-between;z-index:1}#dialogBack .dialog .dialogTitleBar h2{padding:10px 20px;font-weight:300;display:flex}#dialogBack .dialog .dialogTitleBar svg{width:30px;height:30px;fill:#777;padding:10px 20px}#dialogBack .dialog .dialogTitleBar svg:hover{fill:#000}#dialogBack .dialog .dialogBody{position:absolute;width:100%;top:0;height:100%;padding-top:50px;box-sizing:border-box}#dialogBack .dialog .dialogBody .dialogBodyCenter{position:relative;width:100%;height:100%;overflow:auto}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem{position:relative;width:100%;height:auto;padding:16px 24px;box-sizing:border-box}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem:hover{background-color:rgba(0,0,0,.04)}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem:hover input[type=text]{background-color:rgba(0,0,0,0)}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem h3{color:#777;margin-bottom:8px;font-weight:300;cursor:default}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem p,#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem label,#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem span{color:#333}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem label{cursor:pointer}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem select{width:200px;height:32px;font-size:13pt;color:#555;border:0;outline:0;padding:0 12px;margin:10px 0;background-color:transparent}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem select:focus{background-color:#fff}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div{padding:0 8px}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div input[type=checkbox],#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div input[type=radio]{vertical-align:middle;margin-right:8px}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div button{padding:6px 14px;margin:10px 5px;font-size:12pt;background-color:#ddd;border:0;outline:0;cursor:pointer}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div button:hover{background-color:#ccc}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsItemGeneral div{margin:6px 0}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsItemStartUp div form{margin:5px 0}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsItemStartUp div label{margin:0 10px}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsItemPriority p{margin:8px 0 5px}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText{width:50%;margin:6px 0}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText .settingsInputEffect{position:relative;width:0%;left:50%;padding:0;border-bottom:2px solid;transition:all 0.2s}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText input{width:100%;color:#333;height:40px;font-size:13pt;padding:0 10px;border:0;outline:0;box-sizing:border-box}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText input:hover~.settingsInputEffect{width:100%;left:0%;border-color:#ccc}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText input:focus~.settingsInputEffect{width:100%;left:0%;border-color:#34515f}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText input[disabled]{color:#999}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsVersion{position:relative;right:12px;bottom:4px;margin-top:8px;text-align:right;font-size:11pt;color:#777;cursor:default}#dialogBack .dialog #historyBody{position:absolute;width:100%;height:100%;top:0;padding-top:50px;box-sizing:border-box}#dialogBack .dialog #historyBody #historyPaths{position:relative;width:100%;height:100%;overflow:auto}#dialogBack .dialog #historyBody #historyPaths .historyItem{position:relative;width:100%;height:50px;padding:4px 20px;box-sizing:border-box;border-bottom:1px solid #f2f2f2;display:flex}#dialogBack .dialog #historyBody #historyPaths .historyItem p{min-width:130px;padding:12px 40px}#dialogBack .dialog #historyBody #historyPaths .historyItem p:last-child{color:#999}#dialogBack .dialog #historyBody #historyPaths .historyItem svg{position:absolute;right:30px;width:30px;height:30px;fill:#6f6f6f;padding:7px 25px}#dialogBack .dialog #historyBody #historyPaths .historyItem svg:hover{fill:#222}#dialogBack .dialog #historyBody #historyPaths .historyActive{color:#34515f;background-color:rgba(0,0,0,.06)}@media screen and (max-width:1080px){#dialogBack .dialog{width:92%}}@media screen and (max-width:800px){#dialogBack .dialog{width:100%}}@media screen and (max-height:620px){#dialogBack{padding-top:50px}#dialogBack .dialog{height:100%}}body.darkMode .screen{background-color:#212121;color:#fff}body.darkMode header{background-color:#484848}body.darkMode main #mainCenter aside{background:#303030;color:#fff}body.darkMode #mainCenter aside .asideTitle svg{fill:#fff}body.darkMode details summary p.linkTree{color:#fff}body.darkMode summary::before{color:#fff}body.darkMode header div#options .optDropDown{color:#000}body.darkMode #dialogBack .dialog{background-color:#444}body.darkMode main #mainCenter section #itemArea{color:#9eb9c6}body.darkMode main #mainCenter section #itemArea .item:hover{background-color:rgba(255,255,255,.06)}body.darkMode main #mainCenter section #folderInfo{background-color:rgba(255,255,255,.06)}body.darkMode main #mainCenter aside #asideFavorites{box-shadow:-2px 2px 4px rgba(120,120,120,.2)}body.darkMode main #mainCenter aside #asideFavorites #asideFavBody .favFolder small{color:#bbb}body.darkMode main #mainCenter aside #asideFavorites #asideFavBody .favFolder:hover{color:#222}body.darkMode main #mainCenter aside #asideFavorites #asideFavBody .favFolder:hover small{color:#777}body.darkMode #dialogBack .dialog .dialogTitleBar{background-color:#545454}body.darkMode #dialogBack .dialog .dialogTitleBar svg{fill:#888}body.darkMode #dialogBack .dialog .dialogTitleBar svg:hover{fill:#bbb}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem h3{color:#fff}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem p,body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem label,body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem span{color:#ddd}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem:hover input[type=text]{background-color:#606060}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem select{color:#eee}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem select:focus{color:#555}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsInputText input{color:#eee;background-color:#606060}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsInputText input:hover~.settingsInputEffect{border-color:#bbb}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsInputText input:focus~.settingsInputEffect{border-color:#fff}body.darkMode #dialogBack .dialog #historyBody #historyPaths .historyItem{border-bottom:1px solid #5f5f5f}body.darkMode #dialogBack .dialog #historyBody #historyPaths .historyActive{color:#2196f3}body.darkMode details:hover summary p.linkTree{color:#000}body.darkMode details:hover summary::before{color:#444}body.darkMode main #mainCenter section #itemArea .item{box-shadow:2px 2px 8px rgba(0,0,0,.15)}body.darkMode header div#options .optDropDown{color:#fff;background-color:#555}body.darkMode header div#options .optDropDown div.optDropDownItem svg{fill:#fff}body.darkMode header div#options .optDropDown div.optDropDownItem:hover{background-color:rgba(255,255,255,.1)}body.darkMode header div#options .optDropDown div.optDropDownItem:hover .optChainDropdown{background-color:#606060}body.darkMode #context{background-color:#555;box-shadow:2px 2px 7px #333}body.darkMode #context ul li{color:#f0f0f0}body.darkMode #context ul li:hover{background-color:#9eb9c6}#context{position:absolute;width:180px;height:auto;background-color:#fff;box-shadow:2px 2px 7px #ccc;overflow:hidden;opacity:.8;cursor:default;display:none;z-index:15;-webkit-transition:left 0.5s,top 0.5s,opacity 0.4s;-moz-transition:left 0.5s,top 0.5s,opacity 0.4s;-o-transition:left 0.5s,top 0.5s,opacity 0.4s;-ms-transition:left 0.5s,top 0.5s,opacity 0.4s}#context:hover{opacity:1}#context ul{position:relative;width:100%;height:auto;margin:5px 0;padding:0;list-style-type:none}#context ul li{position:relative;width:100%;height:auto;color:#282828;font-size:12pt;border-radius:50%;padding:12px 0;text-align:center;box-sizing:border-box}#context ul li:hover{border-radius:0;color:#fff;background-color:#2177ff}#context ul li.disabled{color:rgba(40,40,40,.5)}#context ul li.disabled:hover{color:#fff;background-color:#b4b4b4}::-webkit-scrollbar{width:10px;height:10px}::-webkit-scrollbar-thumb{background:#aaa}::-webkit-scrollbar-track{background:rgba(0,0,0,.05)}
    </style>
    <script type="text/javascript">
        var langs = <?php echo $langJson; ?>;
        var filename = '<?php echo substr(__FILE__,strrpos(__FILE__,'\\') + 1); ?>';
    </script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js">
    </script>
    <script type="text/javascript">
        /* ---- javascript code ---- */

        // global variables
        var version = '3.2.3';
        var allowedAccess = false; // indica si el usuario esta autenticado
        var path = './'; // ruta actual del explorador
        var favorites = []; // almacena las rutas favoritas
        var optMoreDespl = false; // estado del desplegable de más opciones
        var optViewDespl = false; // estado del desplegable de las vistas
        var timeline = []; // almacena todas las rutas accedidas anteriormente
        var posSelect = null; // posición del fichero o directorio seleccionado con las flechas de dirección
        var defaultDirectoryIndex = ['index.php','index.asp','index.html'];
        var ignoreFiles = [];
        var currentPathLaunch = false; // almacena el fichero ejecutable prioritario en el directorio actual (modificado por la funcion setLaunchOptions)
        var timelinePosition = 0; // es true cuando el directorio actual ha sido accedido volviendo atrás en el historial
        var defaultSettings = {'version':version,'tree':true,'view':'Mosaic','darkMode':false,'showHidden':false,'showExtensions':true,'defaultView':'last','debug':true,'ignoreFiles':ignoreFiles,'systemIndex':true,'directoryIndex':defaultDirectoryIndex,'dbpath':'http://localhost/phpmyadmin/','firstLoad':false}; // configuracion por defecto de la aplicacion
        var lang = getCookie('elcano-lang');

        if (localStorage.getItem('elcano-settings') == null) {
            var settings = defaultSettings;
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        } else {
            var settings = JSON.parse(localStorage.getItem('elcano-settings'));
        }

        if (localStorage.getItem('elcano-favorites') == null) {
            localStorage.setItem('elcano-favorites','[]');
            favorites = JSON.parse(localStorage.getItem('elcano-favorites'));
        } else {
            favorites = JSON.parse(localStorage.getItem('elcano-favorites'));
        }
        settings.firstLoad = true;

        $(function() { // init
            if (sessionStorage.getItem('elcano-access') != null) {
                storedAuth = JSON.parse(sessionStorage.getItem('elcano-access'));
                let storedUser = storedAuth.user;
                let storedPass = storedAuth.pass;
                authorize(storedUser,storedPass);
            } else {
                disableExplorer();
            }

            availableLanguages();

            $('#signInForm').on('submit',function(e) { // evento al rellenar el formulario de login
                e.preventDefault();
                let user = $('#signInUser').val();
                let pass = $('#signInPass').val();
                authorize(user,sha1(pass));
            });

            $('#loginLangSelect').val(lang);

            $('#loginLangSelect').on('change',function() {
                console.log('entra');
                if (langs[$('#loginLangSelect').val()] != null) {
                    changeLang($('#loginLangSelect').val());
                }
            });

            // aside tree loading circle
            $('#asideTreeBody').html('<div class="spinner"><div class="dot1"></div><div class="dot2"></div></div>');

            // options functionalities
            $('#optLaunch').on('click', function() {
                if (settings.systemIndex) {
                    window.open(path,'_blank');
                } else {
                    window.open(currentPathLaunch,'_blank');
                }
            });

            $('#optDatabase').on('click', function() {
                window.open(settings.dbpath,'_blank');
            });

            $('#optFavorite').on('click', function() {
                if (favorites.length>0) {
                    for (x in favorites) {
                        if (favorites[x].path == path) {
                            favorites.splice(x,1);
                            localStorage.setItem('elcano-favorites',JSON.stringify(favorites));
                        }
                    }
                    $('#optFavorite').hide();$('#optNotFavorite').show();
                    showFavorites();
                }
            });

            $('#optNotFavorite').on('click', function() {
                addFavorite();
            });

            $('.optDropDownItem').removeClass('optViewDesplActive');
            if (settings.view == 'Mosaic') {
                $('#optViewDesplMosaic').addClass('optViewDesplActive');
            } else if (settings.view == 'List') {
                $('#optViewDesplList').addClass('optViewDesplActive');
            } else if (settings.view == 'Wall') {
                $('#optViewDesplWall').addClass('optViewDesplActive');
            }

            $('#optView').on('click', function() {
                if (optViewDespl) {
                    $('#optViewDespl').slideUp(200);
                    $('#shadow').fadeOut(200);
                    optViewDespl = false;
                } else {
                    $('#optViewDespl').slideDown(200);
                    $('#shadow').fadeIn(200);
                    optViewDespl = true;
                    $('#optMoreDespl').slideUp(200);
                    optMoreDespl = false;
                }
            });

            $('#optMore').on('click', function() {
                if (optMoreDespl) {
                    $('#optMoreDespl').slideUp(200);
                    $('#shadow').fadeOut(200);
                    optMoreDespl = false;
                } else {
                    $('#optMoreDespl').slideDown(200);
                    $('#shadow').fadeIn(200);
                    optMoreDespl = true;
                    $('#optViewDespl').slideUp(200);
                    optViewDespl = false;
                }
            });

            if (settings.tree) { // set startup state of the aside tree
                $('#optMoreDesplExplorer p').html(langs[lang].header.headMoreHideExpl+'<small>alt+x</small>');
            } else {
                $('aside').css('margin-left','-320px');
                $('#optMoreDesplExplorer p').html(langs[lang].header.headMoreShowExpl+'<small>alt+x</small>');
            }

            $('#shadow').on('click', function() {
                $('#optViewDespl').slideUp(200);
                $('#shadow').fadeOut(200);
                optViewDespl = false;
                $('#optMoreDespl').slideUp(200);
                $('#shadow').fadeOut(200);
                optMoreDespl = false;
            })

            $('#optMoreHistory').on('click',function(e) {
                if (e.target.id == 'optMorePrevPath' || e.target.parentNode.id == 'optMorePrevPath' || e.target.parentNode.parentNode.id == 'optMorePrevPath') {
                    prevPath();
                } else if (e.target.id == 'optMoreNextPath' || e.target.parentNode.id == 'optMoreNextPath' || e.target.parentNode.parentNode.id == 'optMoreNextPath') {
                    nextPath();
                } else if (e.target.id == 'optMoreHistory' || e.target.parentNode.id == 'optMoreHistory' || e.target.parentNode.parentNode.id == 'optMoreHistory') {
                    showHistory(true);
                }
            });
        });

        function authorize(authUser,authPass) {
            // función que actúa tanto al rellenar el formulario de login como al cargar por primera vez la página si la contraseña está guardada en sessionStorage
            // la variable authPass contiene la contraseña a comprobar ya encriptada para enviar a PHP
            if (settings.debug){console.log('authorize')};

            var tokenCheck = Math.random();

            $.get( "", { user: authUser,pass: authPass,token: tokenCheck } )
                .done(function( data ) {
                    //console.log(data);
                    var response = JSON.parse(data);

                    if (response.token == tokenCheck) {
                        if (response.status == 200) {
                            accessDataJson = JSON.stringify({'user':authUser,'pass':authPass});
                            sessionStorage.setItem('elcano-access',accessDataJson);
                            enableExplorer();
                        } else {
                            disableExplorer();
                            $('#signInError').html('<p>'+response.message+'</p>');
                        }
                    } else {
                        disableExplorer();
                    }
            });
        }

        function logout() { // cierra la sesión iniciada volviendo a mostrar el formulario de login
            if (settings.debug){console.log('logout')};
            sessionStorage.removeItem('elcano-access');
            disableExplorer();
            $('#optMoreDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optMoreDespl = false;
        }

        function enableExplorer() {
            if (settings.debug){console.log('explorer enabled')};
            allowedAccess = true;
            changePath('./');
            showFavorites();
            loadTree();
            setSettings();
            $('.screen').hide();
            $('#explorer').fadeIn(200);
        }

        function disableExplorer() {
            if (settings.debug){console.log('explorer disabled')};
            allowedAccess = false;
            $('.screen').hide();
            $('#blocked').show();
            $('#signInUser,#signInPass').val('');
            $('#signInError').html('');
            $('#signInUser').focus();
        }

        function getFolder(url) { // recupera los ficheros y directorios existentes en la ruta que se le introduzca como parámetro
            if (allowedAccess) {
                if (settings.debug){console.log('getFolder(\''+url+'\')')};

                var coincidencia = false;
                if (favorites.length>0) {
                    for (x in favorites) {
                        if (favorites[x].path == url) {
                            coincidencia = true;
                        }
                    }
                }

                if (coincidencia) {
                    $('#optFavorite').show();
                    $('#optNotFavorite').hide();
                } else {
                    $('#optFavorite').hide();
                    $('#optNotFavorite').show();
                }

                $.post( "", { ruta: url, listDir: true } )
                    .done(function( data ) {
                        if (settings.debug){console.log(data)};
                        var response = JSON.parse(data);
                        if (settings.debug){console.log(response)};

                        if (response.error != null) {
                            if (settings.debug){console.log('Se ha producido un error al acceder al directorio')};
                            errorReporting(response);
                        } else {
                            path = url; // cambiamos la ruta general al confirmar la existencia del directorio
                            explodePath(url);
                            $('#itemArea').html('');

                            var files = 0;
                            var directories = 0;

                            if (response.dir.length == 0 && response.files.length == 0) {
                                $('#itemArea').html('<div id="emptyFolder"><p>'+langs[lang].section.noResults+'</p></div>');
                            }
                            for (i in response.dir) {
                                if (settings.ignoreFiles.indexOf(response.dir[i].fileName) == -1) {
                                    if (settings.showHidden) { // muestra los ficheros y directorios ocultos
                                        setFolderItems(response.dir[i].fileName,response.dir[i].filePath, response.dir[i].fileType);
                                        directories++;
                                    } else { // solo muestra los directorios y ficheros que son visibles
                                        if (response.dir[i].fileName.substring(0,1) != '.') {
                                            setFolderItems(response.dir[i].fileName,response.dir[i].filePath, response.dir[i].fileType);
                                            directories++;
                                        }
                                    }
                                }
                            }
                            for (i in response.files) {
                                if (!ignoreThisFile(path, response.files[i].fileName, filename)) {
                                    if (settings.ignoreFiles.indexOf(response.files[i].fileName) == -1) {
                                        if (settings.showExtensions) {
                                            setFolderItems(response.files[i].fileName,response.files[i].filePath,response.files[i].fileType,response.files[i].fileSize,true);
                                        } else {
                                            setFolderItems(response.files[i].fileName,response.files[i].filePath,response.files[i].fileType,response.files[i].fileSize,false);
                                        }
                                        files++;
                                    }
                                }
                            }

                            setLaunchOptions(response);

                            $('#folderInfo p').text(directories+' '+langs[lang].section.sectionFolder+', '+files+' '+langs[lang].section.sectionFiles);
                        }
                        settings.firstLoad = false;
                });
            }
        }

        function setFolderItems(name,path,type,size,extensions=true) {
            if (!extensions) {
                var extensionStart = name.lastIndexOf('.');
                var nameNoExt = name.substring(0,extensionStart);
            }

            if (settings.firstLoad) {
                if (settings.defaultView=='mosaic') { // detect default view
                    settings.view = 'Mosaic';
                } else if (settings.defaultView=='list') {
                    settings.view = 'List';
                } else if (settings.defaultView=='wall') {
                    settings.view = 'Wall';
                }
            }

            var html = '';
            if (settings.debug){console.log(type)};
            if (type=='folder') {
                if (name.substring(0,1) == '.') {
                    html += '<div class="item item'+settings.view+' itemDir itemHidden" onclick="changePath(\'' + path + '/\')">';
                } else {
                    html += '<div class="item item'+settings.view+' itemDir" onclick="changePath(\'' + path + '/\')">';
                }
            } else {
                html += '<div class="item item'+settings.view+' itemFich" onclick="readFich(\'' + path + '\')">';
            }
            html += '<div class="itemLogo">';
            html += setItemIcon(type,path);
            html += '</div>';
            html += '<div class="itemText">';
            if (extensions) {
                html += '<p split-lines>'+name+'</p>';
            } else {
                html += '<p split-lines>'+nameNoExt+'</p>';
            }
            html += '</div>';
            html += '<div class="itemFiletype">';
            if (type!='folder') {
                html += '<p>'+type+'</p>';
            }
            html += '</div>';
            html += '<div class="itemFilesize">';
            if (type!='folder') {
                html += '<p>'+size+'</p>';
            }
            html += '</div>';
            html += '</div>';
            $('#itemArea').append(html);
        }

        function changePath(url,tlPos = null) { // cambia la ruta actual. tlPos indica que se llama desde la funcion navigateHistory
            if (settings.debug){console.log('changePath to: '+url+' | tlPos = '+tlPos)};
            if (url.substring(0,2) == './' && url.indexOf('../') == -1) {
                getFolder(url);
                document.title = 'elcano ' + url;
                if (tlPos == null) {
                    if (timeline.length>0) {
                        if (timeline[timeline.length-1].path != url) {
                            timeline.push({'path':url});
                        }
                    } else {
                        timeline.push({'path':url});
                    }
                    reloadTimeline();
                } else {
                    reloadTimeline(tlPos);
                }
            } else {
                if (settings.debug){console.log('forbiden access to path '+url)};
            }
        }

        function readFich(url) {
            //console.log('reading ' + url);
            window.open(url,'_blank');
        }

        function explodePath(url) { // actualiza los directorios del nav
            var explode = url.split('/');
            $('nav').html('');

            for (i=0;i<explode.length;i++) {
                if (explode[i]=='') {
                    explode.splice(i,1);
                } else {
                    if (explode[i]=='.') {
                        //explode[i] = '<div class="navHomeItem"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 19v-5h4v5c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-7h1.7c.46 0 .68-.57.33-.87L12.67 3.6c-.38-.34-.96-.34-1.34 0l-8.36 7.53c-.34.3-.13.87.33.87H5v7c0 .55.45 1 1 1h3c.55 0 1-.45 1-1z"/></svg><i>Home</i></div>';
                        explode[i] = 'homePage';
                    }

                    var implode = ''; // permite asignar la url a cada item del nav
                    for (j=0;j<=i;j++) {
                        //if (explode[j] == '<div class="navHomeItem"><svg class="navHomeItem" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 19v-5h4v5c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-7h1.7c.46 0 .68-.57.33-.87L12.67 3.6c-.38-.34-.96-.34-1.34 0l-8.36 7.53c-.34.3-.13.87.33.87H5v7c0 .55.45 1 1 1h3c.55 0 1-.45 1-1z"/></svg><i>home</i></div>') {

                        if (explode[j] == 'homePage') {
                            implode += './';
                        } else {
                            implode += explode[j] + '/';
                        }
                    }

                    if (i != explode.length-1 && i != explode.length-2) {
                        if (explode[i] == 'homePage') {
                            $('nav').append('<div class="navItem" onclick="changePath(\'' + implode + '\')"><p><div class="navHomeItem"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 19v-5h4v5c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-7h1.7c.46 0 .68-.57.33-.87L12.67 3.6c-.38-.34-.96-.34-1.34 0l-8.36 7.53c-.34.3-.13.87.33.87H5v7c0 .55.45 1 1 1h3c.55 0 1-.45 1-1z"/></svg></div></p></div>');
                        } else {
                            $('nav').append('<div class="navItem" onclick="changePath(\'' + implode + '\')"><p>'+explode[i]+'</p></div>');
                        }
                        if (i != explode.length -1) {
                            $('nav').append('<div class="navSeparator"><p>/</p></div>');
                        }
                    } else if (explode[i] != '') {
                        if (explode[i] == 'homePage') {
                            $('nav').append('<div class="navItem"><p><div class="navHomeItem"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 19v-5h4v5c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-7h1.7c.46 0 .68-.57.33-.87L12.67 3.6c-.38-.34-.96-.34-1.34 0l-8.36 7.53c-.34.3-.13.87.33.87H5v7c0 .55.45 1 1 1h3c.55 0 1-.45 1-1z"/></svg><i>Home</i></div></p></div>');
                        } else {
                            $('nav').append('<div class="navItem"><p>'+explode[i]+'</p></div>');
                        }
                    }
                }
            }
        }

        function showFavorites() { // recarga la lista de favoritos en el aside
            var favCount = 0;
            if (favorites.length>0) {
                var favs = '';
                for (x in favorites) {
                    var filePath = location.pathname;
                    if (favorites[x].root == filePath) {
                        favs += '<div class="favFolder" onclick="changePath(\'' + favorites[x].path + '\')"><p>' + favorites[x].title + '<small>' + favorites[x].path + '</small></p></div>';
                        favCount++;
                    }
                }
                $('#favCount').text(favCount);
                $('#asideFavBody').html(favs);
            } else {
                $('#favCount').text('');
                $('#asideFavBody').html('<p style="text-align:center;color:#555">No hay favoritos</p>');
            }
        }

        function addFavorite(current=null) {
            console.log('addFavorite');
            var titFav = prompt('Título del favorito: ','');
            if (titFav!=null && titFav!='') {
                if (settings.debug){console.log(path)};
                var filePath = location.pathname;
                if (current==null) {
                    favorites.push({'root':filePath,'path':path,'title':titFav});
                } else {
                    favorites.push({'root':filePath,'path':current,'title':titFav});
                }
                localStorage.setItem('elcano-favorites',JSON.stringify(favorites));
                if (settings.debug){console.log(favorites)};
                if (current==null) { // cambia el icono de favorito de la barra superior solo si es en el directorio actual
                    $('#optFavorite').show();$('#optNotFavorite').hide();
                }
                showFavorites();
            }
        }

        function changeView(view) { // permite cambiar el layout de elementos en el directorio
            if (view != settings.view && (view=='Mosaic' || view=='List' || view=='Wall')) {
                $('.item').removeClass('itemMosaic');
                $('.item').removeClass('itemList');
                $('.item').removeClass('itemWall');

                if (view == 'Mosaic') {
                    $('.item').addClass('itemMosaic');
                    settings.view = 'Mosaic';
                    localStorage.setItem('elcano-settings',JSON.stringify(settings));
                    $('.optDropDownItem').removeClass('optViewDesplActive');
                    $('#optViewDesplMosaic').addClass('optViewDesplActive');
                } else if (view == 'List') {
                    $('.item').addClass('itemList');
                    settings.view = 'List';
                    localStorage.setItem('elcano-settings',JSON.stringify(settings));
                    $('.optDropDownItem').removeClass('optViewDesplActive');
                    $('#optViewDesplList').addClass('optViewDesplActive');
                } else if (view == 'Wall') {
                    $('.item').addClass('itemWall');
                    settings.view = 'Wall';
                    localStorage.setItem('elcano-settings',JSON.stringify(settings));
                    $('.optDropDownItem').removeClass('optViewDesplActive');
                    $('#optViewDesplWall').addClass('optViewDesplActive');
                }
                $('#optViewDespl').slideUp(200);
                $('#shadow').fadeOut(200);
                optViewDespl = false;
            }
        }

        function setItemIcon(type,path) {
            var standardSvgIcon = false; // indica si utiliza el icono svg estándar para un tipo concreto
            var paper='';var bend='';var text='';var size='36px';
            var image = 'default';
            if (type=='folder') { // folder
                image = '<svg class="fileicon" version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
            } else if (type=='zip' || type=='rar' || type=='7z') {
                image = '<svg class="fileicon" id="Capa_1" data-name="Capa 1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 384 384"><title>rar</title><image width="512" height="512" transform="scale(0.75)" xlink:href="data:image/png;base64,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"/></svg>';
            } else if (type=='exe') {
            } else if (type=='bat') {
                standardSvgIcon = true;
                paper='#424242';bend='#6d6d6d';text='BAT';
            } else if (type=='txt') {
                standardSvgIcon = true;
                paper='#607d8b';bend='#8eacbb';text='TXT';
            } else if (type=='pdf') { // PDF
                standardSvgIcon = true;
                paper='#f00';bend='#ff5252';text='PDF';
            } else if (type=='html' || type=='htm') { // HTML
                standardSvgIcon = true;
                paper='#f44336';bend='#ff7961';text='HTML';
            } else if (type=='css') { // CSS
                standardSvgIcon = true;
                paper='#1e88e5';bend='#6ab7ff';text='CSS';
            } else if (type=='sass' || type=='scss') { // SASS
                standardSvgIcon = true;
                paper='#ec407a';bend='#ff77a9';text='SASS';
            } else if (type=='js') { // JS
                standardSvgIcon = true;
                paper='#fdd835';bend='#ffff6b';text='JS';size='42px';
            } else if (type=='php') { // PHP
                standardSvgIcon = true;
                paper='#6a1b9a';bend='#9c4dcc';text='PHP';
            } else if (type=='py') {
                standardSvgIcon = true;
                paper='#1e88e5';bend='#fdd835';text='PY';size='36px';
            } else if (type=='c' || type=='cpp' || type=='cs') {
                standardSvgIcon = true;
                paper='#2196f3';bend='#6ec6ff';text='C';
            } else if (type=='asp') {
                standardSvgIcon = true;
                paper='#6cbeff';bend='#a1d9fe';text='ASP';
            } else if (type=='doc' || type=='docx' || type=='odt') {
                standardSvgIcon = true;
                paper='#1565c0';bend='#5e92f3';text='W';var size='50';
            } else if (type=='xls' || type=='xlsx' || type=='ods') {
                standardSvgIcon = true;
                paper='#2e7d32';bend='#60ad5e';text='X';var size='50';
            } else if (type=='ppt' || type=='pptx' || type=='odp') {
                standardSvgIcon = true;
                paper='#d84315';bend='#ff7543';text='P';var size='50';
            } else if (type=='accdb') {
                standardSvgIcon = true;
                paper='#c62828';bend='#ff5f52';text='A';var size='50';
            } else if (type=='jpg' || type=='jpeg' || type=='png' || type=='gif' || type=='svg' || type=='bmp' || type=='ico') {
                image = '<img src="'+path+'" />';
            } else if (type=='mp3' || type=='wav' || type=='ogg' || type=='wma' || type=='cda' || type=='mid' || type=='midi' || type=='ac3' || type=='ogm' || type=='aac' || type=='flac') { // audio types
                image = '<svg style="fill:#039be5" xmlns="http://www.w3.org/2000/svg" height="34" viewBox="0 0 24 24" width="34"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 5v8.55c-.94-.54-2.1-.75-3.33-.32-1.34.48-2.37 1.67-2.61 3.07-.46 2.74 1.86 5.08 4.59 4.65 1.96-.31 3.35-2.11 3.35-4.1V7h2c1.1 0 2-.9 2-2s-.9-2-2-2h-2c-1.1 0-2 .9-2 2z"/></svg>';
            } else if (type=='mp4' || type=='avi' || type=='wmv' || type=='rpm' || type=='mov' || type=='dvd' || type=='div' || type=='divx' || type=='asf' || type=='wm') { // video types
                image ='<svg style="fill:#039be5" xmlns="http://www.w3.org/2000/svg" height="34" viewBox="0 0 24 24" width="34"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l2.29 2.29c.63.63 1.71.18 1.71-.71V8.91c0-.89-1.08-1.34-1.71-.71L17 10.5z"/></svg>';
            } else if (type=='psd') {
                standardSvgIcon = true;
                paper='#0d47a1';bend='#5472d3';text='PS';var size='45';
            } else if (type=='ai') {
                standardSvgIcon = true;
                paper='#ff6f00';bend='#ffa040';text='AI';var size='45';
            } else {
                standardSvgIcon = true;
                paper='#eceff1';bend='#e0e0e0';text='';
            }

            if (standardSvgIcon) {
                image = '<svg class="fileicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 86 120"><title>'+text+'</title><g id="paper"><path d="M106,42v74a8,8,0,0,1-8,8H28a8,8,0,0,1-8-8V12a8,8,0,0,1,8-8H68L78,14V32H96Z" transform="translate(-20 -4)" style="fill:'+paper+'"/></g><text id="text" transform="translate(42 80.3)" style="text-anchor:middle;font-size:'+size+';fill:#fff;font-family:Calibri;cursor:default">'+text+'</text><polygon id="bend" points="86 38 48 38 48 0 86 38" style="fill:'+bend+'"/></svg>';
            }

            return image;
            //return '<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
        }

        function showTree() { // muestra u oculta el árbol de directorios lateral
            if (settings.debug){console.log('showTree')};
            if (settings.tree) {
                $('aside').css('margin-left','-320px');
                settings.tree = false;
                $('#optMoreDesplExplorer p').html(langs[lang].header.headMoreShowExpl+'<small>alt+x</small>');
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            } else {
                $('aside').css('margin-left','0px');
                settings.tree = true;
                $('#optMoreDesplExplorer p').html(langs[lang].header.headMoreHideExpl+'<small>alt+x</small>');
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            }
            $('#optMoreDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optMoreDespl = false;
        }

        function loadTree() { // carga los datos del árbol de directorios
            if (allowedAccess) {
                $.post( "", { ruta: path, dirTree: true } )
                    .done(function( data ) {
                        $('#asideTreeBody').html(data);
                        if (settings.debug){console.log("tree loaded")};
                });
            }
        }

        function ignoreThisFile(path,file,filename) {
            if (path == './') { // ruta base
                if (filename == file) { // son el mismo archivo
                    return true;
                }
            }
            return false;
        }

        function showSettings(op) { // muestra u oculta la ventana de configuración
            if (settings.debug){console.log('showSettings')};
            if (op) {
                $('#dialogBack').css('display','flex');
                $('.dialog').hide();
                $('#settings').fadeIn(200);
            } else {
                $('#settings').fadeOut(200);
                $('#dialogBack').css('display','none');
            }

            $('#optMoreDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optMoreDespl = false;
        }

        function prevPath() {
            if (settings.debug){console.log('prevPath')};

            if (timeline.length>1) {
                if (timeline[timelinePosition-1] != undefined) {
                    timelinePosition -= 1;
                    var url = timeline[timelinePosition].path;
                    changePath(url,timelinePosition);
                }
            }

            $('#optMoreDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optMoreDespl = false;
        }

        function nextPath() {
            if (settings.debug){console.log('nextPath')};

            if (timelinePosition < timeline.length-1) {
                if (timeline[timelinePosition+1] != undefined) {
                    timelinePosition += 1;
                    var url = timeline[timelinePosition].path;
                    changePath(url,timelinePosition);
                }

                $('#optMoreDespl').slideUp(200);
                $('#shadow').fadeOut(200);
                optMoreDespl = false;
            }
        }

        function showHistory(op) {
            if (settings.debug){console.log('showHistory')};
            if (op) {
                $('#dialogBack').css('display','flex');
                $('.dialog').hide();
                $('#history').fadeIn(200);
            } else {
                $('#history').fadeOut(200);
                $('#dialogBack').css('display','none');
            }

            $('#optMoreDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optMoreDespl = false;
        }

        function reloadTimeline(pos = null) {
            if (pos==null) {
                timelinePosition = timeline.length-1;
            } else {
                timelinePosition = pos;
            }

            if (timelinePosition == timeline.length-1) { // detectamos si la opcion nextPath debe estar disponible
                $('#optMoreNextPath').addClass('optMoreDisabled');
            } else {
                $('#optMoreNextPath').removeClass('optMoreDisabled');
            }

            var html = '';
            for (i in timeline) {
                var folderName = timeline[i].path.split('/');
                if (folderName.length==2) {
                    folderName2 = langs[lang].history.historyHome;
                } else {
                    folderName2 = folderName[folderName.length-2];
                }
                if (timelinePosition == i) {
                    html += '<div class="historyItem historyActive">';
                } else {
                    html += '<div class="historyItem">';
                }
                html += '<p>'+folderName2+'</p>';
                html += '<p>'+timeline[i].path+'</p>';
                if (timelinePosition > i) {
                    html += '<svg onclick="navigateHistory('+i+')" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L3.71 8.71C3.08 8.08 2 8.52 2 9.41V15c0 .55.45 1 1 1h5.59c.89 0 1.34-1.08.71-1.71l-1.91-1.91c1.39-1.16 3.16-1.88 5.12-1.88 3.16 0 5.89 1.84 7.19 4.5.27.56.91.84 1.5.64.71-.23 1.07-1.04.75-1.72C20.23 10.42 16.65 8 12.5 8z"/></svg>';
                } else if (timelinePosition < i) {
                    html += '<svg onclick="navigateHistory('+i+')" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z"/></svg>';
                } else {
                    html += '';
                }

                html += '</div>';
            }
            $('#historyPaths').html(html);
        }

        function navigateHistory(pos) {
            if (settings.debug){console.log('navigateHistory to: '+pos)};
            timelinePosition = pos;
            changePath(timeline[pos].path,pos);
        }

        function errorReporting(info) { // Muestra los mensajes de error por pantalla
            if (settings.debug){console.log('errorReporting')};

            var errorItemId = Math.floor(Math.random()*10000);

            var errorMsg = '<div class="errorItem" id="errorItem'+errorItemId+'">';
            errorMsg += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>';
            errorMsg += '<p>'+info.error.message+'</p>';
            errorMsg += '</div>';

            $('#errorReporting').append(errorMsg);

            setTimeout(function() {
                $('#errorItem'+errorItemId).remove();
            },5000);
        }

        function setLaunchOptions(data) { // indica el fichero ejecutable prioritario del directorio
            if (settings.debug){console.log(settings.directoryIndex)};
            var priority = settings.directoryIndex;
            var priorityFound = false;
            var launchPath = '';

            for (i in priority) {
                for (j in data.files) {
                    if (!priorityFound) {
                        if (data.files[j].fileName==priority[i]) {
                            launchPath = data.files[j].filePath;
                            priorityFound = true;
                        }
                    }
                }
            }

            if (priorityFound) {
                $('#optLaunch').show();
                currentPathLaunch = launchPath;
            } else {
                $('#optLaunch').hide();
                currentPathLaunch = false;
            }
        }

        function setSettings() {
            if (settings.debug){console.log('setSettings')};

            if (settings.version != version) {
                upgradeSettings();
            }

            // dark mode
            if (settings.darkMode) {
                $('body').removeClass('lightMode');
                $('body').addClass('darkMode');
                $('#darkModeCheckbox').prop('checked', true);
            } else {
                $('body').removeClass('darkMode');
                $('body').addClass('lightMode');
                $('#darkModeCheckbox').prop('checked', false);
            }

            $('#darkModeCheckbox').on('change',function() {
                if (settings.darkMode) {
                    console.log('set light mode');
                    $('body').removeClass('darkMode');
                    $('body').addClass('lightMode');
                    settings.darkMode = false;
                } else {
                    console.log('set dark mode');
                    $('body').removeClass('lightMode');
                    $('body').addClass('darkMode');
                    settings.darkMode = true;
                }
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            });

            // show hidden files
            if (settings.showHidden) {
                $('#showHiddenCheckbox').prop('checked', true);
            } else {
                $('#showHiddenCheckbox').prop('checked', false);
            }

            $('#showHiddenCheckbox').on('change',function() {
                if (settings.showHidden) {
                    settings.showHidden = false;
                } else {
                    settings.showHidden = true;
                }
                changePath(path);
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            });

            // show extensions
            if (settings.showExtensions) {
                $('#showExtensionCheckbox').prop('checked',true);
            } else {
                $('#showExtensionCheckbox').prop('checked',false);
            }

            $('#showExtensionCheckbox').on('change',function() {
                if (settings.showExtensions) {
                    settings.showExtensions = false;
                } else {
                    settings.showExtensions = true;
                }
                console.log(settings.showExtensions);
                changePath(path);
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            });

            // set default view
            if (settings.defaultView=='mosaic') {
                $('#defaultViewMosaic').prop('checked',true);
            } else if (settings.defaultView=='list') {
                $('#defaultViewList').prop('checked',true);
            } else if (settings.defaultView=='wall') {
                $('#defaultViewWall').prop('checked',true);
            } else if (settings.defaultView=='last') {
                $('#defaultViewLast').prop('checked',true);
            }

            $('.defaultView').on('change', function() {
                if ($(this).attr('id') == 'defaultViewMosaic') {
                    settings.defaultView = 'mosaic';
                } else if ($(this).attr('id') == 'defaultViewList') {
                    settings.defaultView = 'list';
                } else if ($(this).attr('id') == 'defaultViewWall') {
                    settings.defaultView = 'wall';
                } else {
                    settings.defaultView = 'last';
                }
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            });

            // ignore files
            $('#ignoreFilesInput').val((settings.ignoreFiles).join(','));

            $('#ignoreFilesInput').on('change',function() {
                console.log('ignoreFiles change');
                settings.ignoreFiles = ($('#ignoreFilesInput').val()).split(',');
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
                getFolder(path);
                console.log(settings.ignoreFiles);
            });

            // index priority
            if (settings.systemIndex) {
                $('#systemIndexPriority').attr('checked',true);
                $('#indexPriorityInput').attr('disabled',true);
            }

            $('#indexPriorityInput').val((settings.directoryIndex).join(','));

            $('#systemIndexPriority').on('change',function() {
                if (settings.systemIndex) {
                    settings.systemIndex = false;
                    $('#systemIndexPriority').attr('checked',false);
                    $('#indexPriorityInput').attr('disabled',false);
                } else {
                    settings.systemIndex = true;
                    $('#systemIndexPriority').attr('checked',true);
                    $('#indexPriorityInput').attr('disabled',true);
                }
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            });

            $('#indexPriorityInput').on('change',function() {
                console.log('indexPriority change');
                settings.directoryIndex = ($('#indexPriorityInput').val()).split(',');
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
                getFolder(path);
                console.log(settings.directoryIndex);
            });

            // database
            $('#databasePathInput').val(settings.dbpath);

            $('#databasePathInput').on('change',function() {
                settings.dbpath = $('#databasePathInput').val();
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            });

            // lang
            if (langs[lang] != null) {
                $('#settingsSelectLang').val(lang);
            }

            $('#settingsSelectLang').on('change',function() {
                console.log('entra');
                if (langs[$('#settingsSelectLang').val()] != null) {
                    changeLang($('#settingsSelectLang').val());
                }
            });

            // reset
            $('#resetSettingsButton').on('click',function() {
                console.log('reset settings');
                settings = defaultSettings;
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
                setSettings();
                changePath(path);
            });

            $('#settingsVersion').text('beta '+version);
        }

        function upgradeSettings() { // actualiza los datos del localStorage si el usuario tiene una versión diferente
            console.log('upgradeSettings');

            var newSettings = {};

            if (settings.version != version) {
                newSettings.version = version;


                if (settings.tree==true) { newSettings.tree = true; }
                else if (settings.tree==false) { newSettings.tree = false; }
                else { newSettings.tree = true; }

                if (settings.view == 'Mosaic' || settings.view == 'List' || settings.view == 'Wall') {
                    newSettings.view = settings.view;
                } else {
                    newSettings.view = 'Mosaic';
                }

                if (settings.darkMode == true) { newSettings.darkMode = true; }
                else if (settings.darkMode == false) { newSettings.darkMode = false; }
                else { newSettings.darkMode = false; }

                if (settings.showHidden == true) { newSettings.showHidden = true; }
                else if (settings.showHidden == false) { newSettings.showHidden = false; }
                else { newSettings.showHidden = false; }

                if (settings.showExtensions == true) { newSettings.showExtensions = true; }
                else if (settings.showExtensions == false) { newSettings.showExtensions = false; }
                else { newSettings.showExtensions = false; }

                if (settings.defaultView=='mosaic' || settings.defaultView=='list' || settings.defaultView=='wall' || settings.defaultView=='last') {
                    newSettings.defaultView = settings.defaultView;
                } else {
                    newSettings.defaultView = 'last';
                }

                if (settings.debug==true || settings.debug==false) { newSettings.debug = settings.debug; } else { newSettings.debug = false; }

                if (Array.isArray(settings.ignoreFiles)) {
                    newSettings.ignoreFiles = settings.ignoreFiles;
                } else {
                    newSettings.ignoreFiles = ignoreFiles;
                }

                if (settings.systemIndex=='true' || settings.systemIndex=='false') {
                    newSettings.systemIndex = settings.systemIndex;
                } else {
                    newSettings.systemIndex = true;
                }

                if (Array.isArray(settings.directoryIndex)) {
                    newSettings.directoryIndex = settings.directoryIndex;
                } else {
                    newSettings.directoryIndex = defaultDirectoryIndex;
                }

                if (!settings.dbpath) {
                    newSettings.dbpath = '';
                } else {
                    newSettings.dbpath = defaultSettings.dbpath;
                }

                if (settings.firstLoad == true || settings.firstLoad == false) {
                    newSettings.firstLoad = settings.firstLoad;
                } else {
                    newSettings.firstLoad = 'false';
                }

                localStorage.setItem('elcano-settings',JSON.stringify(newSettings));
                settings = newSettings;
            }
        }

        function availableLanguages() {
            if (settings.debug){console.log('availableLanguages')};

            var html = '';
            for (i in langs) {
                html += '<option value="'+i+'">'+langs[i].langName+'</option>';
            }
            $('#settingsSelectLang').html(html);
            $('#settingsSelectLang').val(lang);

            $('#loginLangSelect').html(html);
            $('#loginLangSelect').val(lang);
        }

        function changeLang(l) {
            if (settings.debug){console.log('changeLang')};

            setCookie('elcano-lang',l,3650);
            window.location.reload();
        }

        /* ---- app utils ---- */

        function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');

            for(var i = 0; i <ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return false;
        }

        function setCookie(name,value,days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "")  + expires + "; path=/";
        }

        function sha1 (str) {
            var hash
            try {
                var crypto = require('crypto')
                var sha1sum = crypto.createHash('sha1')
                sha1sum.update(str)
                hash = sha1sum.digest('hex')
            } catch (e) {
                hash = undefined
            }

            if (hash !== undefined) {
                return hash
            }

            var _rotLeft = function (n, s) {
                var t4 = (n << s) | (n >>> (32 - s))
                return t4
            }

            var _cvtHex = function (val) {
                var str = ''
                var i
                var v

                for (i = 7; i >= 0; i--) {
                    v = (val >>> (i * 4)) & 0x0f
                    str += v.toString(16)
                }
                return str
            }

            var blockstart
            var i, j
            var W = new Array(80)
            var H0 = 0x67452301
            var H1 = 0xEFCDAB89
            var H2 = 0x98BADCFE
            var H3 = 0x10325476
            var H4 = 0xC3D2E1F0
            var A, B, C, D, E
            var temp

            // utf8_encode
            str = unescape(encodeURIComponent(str))
            var strLen = str.length

            var wordArray = []
            for (i = 0; i < strLen - 3; i += 4) {
                j = str.charCodeAt(i) << 24 |
                str.charCodeAt(i + 1) << 16 |
                str.charCodeAt(i + 2) << 8 |
                str.charCodeAt(i + 3)
                wordArray.push(j)
            }

            switch (strLen % 4) {
                case 0:
                    i = 0x080000000
                    break
                case 1:
                    i = str.charCodeAt(strLen - 1) << 24 | 0x0800000
                    break
                case 2:
                    i = str.charCodeAt(strLen - 2) << 24 | str.charCodeAt(strLen - 1) << 16 | 0x08000
                    break
                case 3:
                    i = str.charCodeAt(strLen - 3) << 24 |
                    str.charCodeAt(strLen - 2) << 16 |
                    str.charCodeAt(strLen - 1) << 8 | 0x80
                    break
                }

                wordArray.push(i)

                while ((wordArray.length % 16) !== 14) {
                    wordArray.push(0)
                }

                wordArray.push(strLen >>> 29)
                wordArray.push((strLen << 3) & 0x0ffffffff)

                for (blockstart = 0; blockstart < wordArray.length; blockstart += 16) {
                    for (i = 0; i < 16; i++) {
                        W[i] = wordArray[blockstart + i]
                    }
                    for (i = 16; i <= 79; i++) {
                        W[i] = _rotLeft(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1)
                    }

                    A = H0
                    B = H1
                    C = H2
                    D = H3
                    E = H4

                    for (i = 0; i <= 19; i++) {
                        temp = (_rotLeft(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff
                        E = D
                        D = C
                        C = _rotLeft(B, 30)
                        B = A
                        A = temp
                    }

                    for (i = 20; i <= 39; i++) {
                        temp = (_rotLeft(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff
                        E = D
                        D = C
                        C = _rotLeft(B, 30)
                        B = A
                        A = temp
                    }

                    for (i = 40; i <= 59; i++) {
                        temp = (_rotLeft(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff
                        E = D
                        D = C
                        C = _rotLeft(B, 30)
                        B = A
                        A = temp
                    }

                    for (i = 60; i <= 79; i++) {
                        temp = (_rotLeft(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff
                        E = D
                        D = C
                        C = _rotLeft(B, 30)
                        B = A
                        A = temp
                    }

                    H0 = (H0 + A) & 0x0ffffffff
                    H1 = (H1 + B) & 0x0ffffffff
                    H2 = (H2 + C) & 0x0ffffffff
                    H3 = (H3 + D) & 0x0ffffffff
                    H4 = (H4 + E) & 0x0ffffffff
                }

                temp = _cvtHex(H0) + _cvtHex(H1) + _cvtHex(H2) + _cvtHex(H3) + _cvtHex(H4)
                return temp.toLowerCase()
        }

        /* ----- comandos de teclado ----- */

        document.onkeydown = function() {
            if (window.event.keyCode == 86) { if (event.altKey) { changeView('List') } } // alt+v
            if (window.event.keyCode == 88) { if (event.altKey) { showTree() } } // alt+x
            if (window.event.keyCode == 72) { if (event.altKey) { showHistory(true) } } // alt+h
            if (window.event.keyCode == 83) { if (event.altKey) { showSettings(true) } } // alt+s
            if (window.event.keyCode == 37) { if (event.altKey) { event.preventDefault(); prevPath() } } // alt+flecha izquierda
            if (window.event.keyCode == 39) { if (event.altKey) { event.preventDefault(); nextPath() } } // alt+flecha derecha

            if (window.event.keycode == 8) {
                if (settings.debug){console.log('backspace')};
            }

            // navegación por el directorio con las flechas de dirección

            // ATENCIÓN: NO MODIFICAR estos datos sin cambiar las media queries correspondientes
            // Cada variable indica la anchura máxima para cada cantidad de columnas
            cols1=750; cols2=1050; cols3=1300; cols4=1750; cols5=2100;

            if ($('#settings').css('display') == 'block') {
                if ($(':focus').attr('id') == undefined) {
                    if (window.event.keyCode == 88) {
                        showSettings(false);
                    }
                }
            } else if ($('#history').css('display') == 'block') {
                if (window.event.keyCode == 88) {
                    showHistory(false);
                }
            } else {
                if (window.event.keyCode == 37) { // flecha izquierda
                    $('.item').removeClass('itemActive');
                    if (posSelect<=0) {
                        posSelect=0;
                    } else if (posSelect>=$('.item').length-1) {
                        posSelect=$('.item').length-2;
                    } else {
                        posSelect--;
                    }
                    $($('.item')[posSelect]).addClass('itemActive');
                    if (settings.debug){console.log(posSelect)};
                }
                if (window.event.keyCode == 39) { // flecha derecha
                    $('.item').removeClass('itemActive');
                    if (posSelect==null) {
                        posSelect=0;
                    } else if (posSelect<=0) {
                        posSelect=1;
                    } else if (posSelect>=$('.item').length-1) {
                        posSelect=$('.item').length-1;
                    } else {
                        posSelect++;
                    }
                    $($('.item')[posSelect]).addClass('itemActive');
                    if (settings.debug){console.log(posSelect)};
                }
                if (window.event.keyCode == 38) { // fecha arriba
                    $('.item').removeClass('itemActive');
                    if (posSelect==null) {
                        posSelect=0;
                    } else if (posSelect<=0) {
                        posSelect=0;
                    } else {
                        if (window.innerWidth < cols1) { // 1 columna
                            if (posSelect-1>=0) { posSelect = posSelect-1; }
                        } else if (window.innerWidth < cols2) { // 2 columnas
                            if (posSelect-2>=0) { posSelect = posSelect-2; }
                        } else if (window.innerWidth < cols3) { // 3 columnas
                            if (posSelect-3>=0) { posSelect = posSelect-3; }
                        } else if (window.innerWidth < cols4) { // 4 columnas
                            if (posSelect-4>=0) { posSelect = posSelect-4; }
                        } else if (window.innerWidth < cols5) { // 5 columnas
                            if (posSelect-5>=0) { posSelect = posSelect-5; }
                        } else if (window.innerWidth >= cols5) { // 6 columnas
                            if (posSelect-6>=0) { posSelect = posSelect-6; }
                        }
                    }
                    $($('.item')[posSelect]).addClass('itemActive');
                    if (settings.debug){console.log(posSelect)};
                }
                if (window.event.keyCode == 40) { // flecha abajo
                    $('.item').removeClass('itemActive');
                    if (posSelect==null) {
                        posSelect=0;
                    } else if (posSelect>=$('.item').length-1) {
                        posSelect=$('.item').length-1;
                    } else {
                        if (window.innerWidth < cols1) { // 1 columna
                            if (posSelect+1<=$('.item').length-1) { posSelect = posSelect+1; }
                        } else if (window.innerWidth < cols2) { // 2 columnas
                            if (posSelect+2<=$('.item').length-1) { posSelect = posSelect+2; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                        } else if (window.innerWidth < cols3) { // 3 columnas
                            if (posSelect+3<=$('.item').length-1) { posSelect = posSelect+3; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                        } else if (window.innerWidth < cols4) { // 4 columnas
                            if (posSelect+4<=$('.item').length-1) { posSelect = posSelect+4; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                        } else if (window.innerWidth < cols5) { // 5 columnas
                            if (posSelect+5<=$('.item').length-1) { posSelect = posSelect+5; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                        } else if (window.innerWidth >= cols5) { // 6 columnas
                            if (posSelect+6<=$('.item').length-1) { posSelect = posSelect+6; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                        }
                    }
                    $($('.item')[posSelect]).addClass('itemActive');
                    if (settings.debug){console.log(posSelect)};
                }
                if (window.event.keyCode == 13) { // intro
                    if (posSelect!=null) {
                        $($('.item')[posSelect]).click();
                    }
                }
            }
            $('section').on('click',function() { // deseleccionar los items
                posSelect=null;
                $('.item').removeClass('itemActive');
            });
        }

        /* -------------------- context menu -------------------- */

        $(function() {
            //EVITAMOS que se muestre el MENU CONTEXTUAL del sistema operativo al hacer CLICK con el BOTON DERECHO del RATON
            $(document).bind("contextmenu", function(e){
                var target = $(e.target);

                //console.log(target.parents('.item').length); // booleano que indica si existe un elemento padre con una clase determinada
                //console.log(target.closest('.item').attr('onclick')); // obtener el primer elemento padre con una clase determinada
                //console.log(target.closest('.item').attr('onclick').substring(target.closest('.item').attr('onclick').indexOf('(')+1, target.closest('.item').attr('onclick').indexOf(')')));
                //console.log(target.closest('.item').attr('onclick').indexOf('('));
                //console.log(target.closest('.item').attr('onclick').indexOf(')'));

                if (target.parents('.item').length) { // detecta si elemento sobre el que se hace click es hijo de .item
                    $('#context>ul').html('');
                    if (target.parents('.itemDir').length) {
                        $('#context>ul').append('<li id="contextExplore" onclick="changePath('+target.closest('.item').attr('onclick')+')">'+langs[lang].context.contextExplore+'</li>'); // CLOSEST: obtener el primer elemento padre con una clase determinada
                        $('#context>ul').append('<li id="contextAddFav" onclick="addFavorite('+target.closest('.item').attr('onclick').substring(target.closest('.item').attr('onclick').indexOf('(')+1, target.closest('.item').attr('onclick').indexOf(')'))+')">'+langs[lang].context.contextFavorites+'</li>');
                    } else {
                        $('#context>ul').append('<li id="contextOpen" onclick="readFich('+target.closest('.item').attr('onclick')+')">'+langs[lang].context.contextOpen+'</li>');
                    }

                    menu.css({'display':'block', 'left':e.pageX, 'top':e.pageY});
                    return false;
                }
            });

            //variables de control
            var menuId = "context";
            var menu = $("#"+menuId);

            //Control sobre las opciones del menu contextual
            menu.click(function(e){
                //si la opcion esta desactivado, no pasa nada
                if(e.target.className == "disabled"){
                    return false;
                }
                //si esta activada, gestionamos cada una y sus acciones
                else{
                    switch(e.target.id){
                        case "contextExplore":
                            break;
                        case "contextAddFav":
                            break;
                        case "contextOpen":
                            break;
                    }
                    menu.css("display", "none");
                }
            });

            //controlamos ocultado de menu cuando esta activo
            //click boton principal raton
            $(document).click(function(e){
                if(e.button == 0 && e.target.parentNode.parentNode.id != menuId){
                    menu.css("display", "none");
                }
            });
            //pulsacion tecla escape
            $(document).keydown(function(e){
                if(e.keyCode == 27){
                    menu.css("display", "none");
                }
            });
        });

    </script>
</head>
<body class="lightMode">
    <div class="screen" id="startUp"></div>
    <div class="screen" id="blocked">
        <div id="blockedBack">
            <div id="signIn">
                <div id="signInTitle">
                    <h1><?php echo $langTxt[$lang]['login']['title']; ?></h1>
                </div>
                <div id="signInBody">
                    <div id="signInError"></div>
                    <form id="signInForm">
                        <div class="signInBodyItem">
                            <input id="signInUser" type="text" placeholder="<?php echo $langTxt[$lang]['login']['user']; ?>" autocomplete="off" spellcheck="false" autofocus />
                            <svg class="signInIcons" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1c0 .55.45 1 1 1h14c.55 0 1-.45 1-1v-1c0-2.66-5.33-4-8-4z"/></svg>
                            <div class="signInInputEffect"></div>
                        </div>
                        <div class="signInBodyItem">
                            <input id="signInPass" type="password" placeholder="<?php echo $langTxt[$lang]['login']['pass']; ?>" />
                            <svg class="signInIcons" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12.65 10C11.7 7.31 8.9 5.5 5.77 6.12c-2.29.46-4.15 2.29-4.63 4.58C.32 14.57 3.26 18 7 18c2.61 0 4.83-1.67 5.65-4H17v2c0 1.1.9 2 2 2s2-.9 2-2v-2c1.1 0 2-.9 2-2s-.9-2-2-2h-8.35zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                            <div class="signInInputEffect"></div>
                        </div>
                        <div class="signInBodyItem">
                            <input id="signInSubmit" type="submit" name="login" value="<?php echo $langTxt[$lang]['login']['submit']; ?>" />
                        </div>
                    </form>
                </div>
            </div>
            <div id="loginLang">
                <select id="loginLangSelect">
                </select>
            </div>
        </div>
    </div>
    <div class="screen" id="explorer">
        <header>
            <div id="headerTitle">
                <svg id="headerLogo" data-name="Capa 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1117.5 1117.5"><defs><style>.headerLogo-1{opacity:0.8;}.headerLogo-2{fill:none;stroke:#ffc107;stroke-miterlimit:10;stroke-width:80px;}.headerLogo-3{fill:#ffc107;}</style></defs><title>logo</title><g class="headerLogo-1"><circle class="headerLogo-2" cx="558.75" cy="558.75" r="518.75"/></g><circle class="headerLogo-3" cx="563.54" cy="563.54" r="372"/></svg>
                <h1>elcano</h1>
            </div>
            <nav>
                <!--<div class="navItem"><p>folder1</p></div>
                <div class="navSeparator"><p>/</p></div>
                <div class="navItem"><p>folder2</p></div>
                <div class="navSeparator"><p>/</p></div>
                <div class="navItem"><p>folder3</p></div>
                <div class="navSeparator"><p>/</p></div>
                <div class="navItem"><p>folder4</p></div>-->
            </nav>
            <div id="options">
                <div class="option" id="optMore" title="<?php echo $langTxt[$lang]['header']['headMore']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="optDropDown" id="optMoreDespl">
                    <div class="optDropDownItem" id="optMoreHistory">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                        <p><?php echo $langTxt[$lang]['header']['headMoreHistory']; ?><small>alt+h</small></p>
                        <div class="optChainDropdown">
                            <div class="optDropDownItem" id="optMorePrevPath">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L3.71 8.71C3.08 8.08 2 8.52 2 9.41V15c0 .55.45 1 1 1h5.59c.89 0 1.34-1.08.71-1.71l-1.91-1.91c1.39-1.16 3.16-1.88 5.12-1.88 3.16 0 5.89 1.84 7.19 4.5.27.56.91.84 1.5.64.71-.23 1.07-1.04.75-1.72C20.23 10.42 16.65 8 12.5 8z"/></svg>
                                <p><?php echo $langTxt[$lang]['header']['headMoreHistoryPrev']; ?><small>alt+flch izq</small></p>
                            </div>
                            <div class="optDropDownItem" id="optMoreNextPath">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z"/></svg>
                                <p><?php echo $langTxt[$lang]['header']['headMoreHistoryNext']; ?><small>alt+flch der</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="optDropDownItem" id="optMoreDesplExplorer" onclick="showTree()">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z"/></svg>
                        <p><?php echo $langTxt[$lang]['header']['headMoreShowExpl']; ?><small>alt+x</small></p>
                    </div>
                    <div class="optDropDownItem" id="optMoreSettings" onclick="showSettings(true)">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
                        <p><?php echo $langTxt[$lang]['header']['headMoreSettings']; ?><small>alt+s</small></p>
                    </div>
                    <div class="optDropDownItem" id="optMoreLogout" onclick="logout()">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10.79 16.29c.39.39 1.02.39 1.41 0l3.59-3.59c.39-.39.39-1.02 0-1.41L12.2 7.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L12.67 11H4c-.55 0-1 .45-1 1s.45 1 1 1h8.67l-1.88 1.88c-.39.39-.38 1.03 0 1.41zM19 3H5c-1.11 0-2 .9-2 2v3c0 .55.45 1 1 1s1-.45 1-1V6c0-.55.45-1 1-1h12c.55 0 1 .45 1 1v12c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1v3c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                        <p><?php echo $langTxt[$lang]['header']['headMoreLogout']; ?></p>
                    </div>
                </div>
                <div class="option" id="optView" title="<?php echo $langTxt[$lang]['header']['headView']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 11h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm0 7h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm6 0h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm6 0h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm-6-7h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm5-5v4c0 .55.45 1 1 1h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="optDropDown" id="optViewDespl">
                    <div class="optDropDownItem" id="optViewDesplMosaic" onclick="changeView('Mosaic')">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 11h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm0 7h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm6 0h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm6 0h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm-6-7h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm5-5v4c0 .55.45 1 1 1h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1z"/></svg>
                        <p><?php echo $langTxt[$lang]['header']['headViewMosaic']; ?></p>
                    </div>
                    <div class="optDropDownItem" id="optViewDesplList" onclick="changeView('List')">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none" opacity=".87"/><path d="M4 14h2c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zm0 5h2c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zM4 9h2c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zm5 5h10c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1H9c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zm0 5h10c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1H9c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zM8 6v2c0 .55.45 1 1 1h10c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1H9c-.55 0-1 .45-1 1z"/></svg>
                        <p><?php echo $langTxt[$lang]['header']['headViewList']; ?></p>
                    </div>
                    <div class="optDropDownItem" id="optViewDesplWall" onclick="changeView('Wall')">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 15h14c.55 0 1-.45 1-1s-.45-1-1-1H5c-.55 0-1 .45-1 1s.45 1 1 1zm0 4h14c.55 0 1-.45 1-1s-.45-1-1-1H5c-.55 0-1 .45-1 1s.45 1 1 1zm0-8h14c.55 0 1-.45 1-1s-.45-1-1-1H5c-.55 0-1 .45-1 1s.45 1 1 1zM4 6c0 .55.45 1 1 1h14c.55 0 1-.45 1-1s-.45-1-1-1H5c-.55 0-1 .45-1 1z"/></svg>
                        <p><?php echo $langTxt[$lang]['header']['headViewWall']; ?></p>
                    </div>
                </div>
                <div class="option" id="optFavorite" title="<?php echo $langTxt[$lang]['header']['headNotFavorite']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.35 20.13c-.76.69-1.93.69-2.69-.01l-.11-.1C5.3 15.27 1.87 12.16 2 8.28c.06-1.7.93-3.33 2.34-4.29 2.64-1.8 5.9-.96 7.66 1.1 1.76-2.06 5.02-2.91 7.66-1.1 1.41.96 2.28 2.59 2.34 4.29.14 3.88-3.3 6.99-8.55 11.76l-.1.09z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="option" id="optNotFavorite" title="<?php echo $langTxt[$lang]['header']['headFavorite']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19.66 3.99c-2.64-1.8-5.9-.96-7.66 1.1-1.76-2.06-5.02-2.91-7.66-1.1-1.4.96-2.28 2.58-2.34 4.29-.14 3.88 3.3 6.99 8.55 11.76l.1.09c.76.69 1.93.69 2.69-.01l.11-.1c5.25-4.76 8.68-7.87 8.55-11.75-.06-1.7-.94-3.32-2.34-4.28zM12.1 18.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="option" id="optDatabase" title="<?php echo $langTxt[$lang]['header']['headDataBase']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 13H5c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-4c0-1.1-.9-2-2-2zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM19 3H5c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM7 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="option" id="optLaunch" title="<?php echo $langTxt[$lang]['header']['headStartUp']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18 19H6c-.55 0-1-.45-1-1V6c0-.55.45-1 1-1h5c.55 0 1-.45 1-1s-.45-1-1-1H5c-1.11 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6c0-.55-.45-1-1-1s-1 .45-1 1v5c0 .55-.45 1-1 1zM14 4c0 .55.45 1 1 1h2.59l-9.13 9.13c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L19 6.41V9c0 .55.45 1 1 1s1-.45 1-1V3h-6c-.55 0-1 .45-1 1z"/></svg>
                    <div class="optionArea"></div>
                </div>
            </div>
        </header>
        <main>
            <div id="errorReporting"></div>
            <div id="shadow"></div>
            <div id="mainCenter">
                <aside>
                    <div id="asideFavorites">
                        <div id="favTitle" class="asideTitle">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19.66 3.99c-2.64-1.8-5.9-.96-7.66 1.1-1.76-2.06-5.02-2.91-7.66-1.1-1.4.96-2.28 2.58-2.34 4.29-.14 3.88 3.3 6.99 8.55 11.76l.1.09c.76.69 1.93.69 2.69-.01l.11-.1c5.25-4.76 8.68-7.87 8.55-11.75-.06-1.7-.94-3.32-2.34-4.28zM12.1 18.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/></svg>
                                <p><?php echo $langTxt[$lang]['aside']['asideFav']; ?></p>
                            </div>
                            <p id="favCount">0</p>
                        </div>
                        <div class="asideBody" id="asideFavBody"></div>
                    </div>
                    <div id="asideTree">
                        <div class="asideTitle">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
                            <p><?php echo $langTxt[$lang]['aside']['asideDir']; ?></p>
                        </div>
                        <div class="asideBody" id="asideTreeBody">
                            <div class="spinner">
                                <div class="dot1"></div>
                                <div class="dot2"></div>
                            </div>
                        </div>
                    </div>
                </aside>
                <section>
                    <div id="itemArea">
                        <!--<div class="item itemMosaic">
                            <div class="itemLogo">
                                <img src="img/typePDF.png" />
                            </div>
                            <div class="itemText">
                                <p split-lines>Fichero1</p>
                            </div>
                        </div>-->
                    </div>
                    <div id="folderInfo">
                        <p>? carpetas y ? archivos</p>
                    </div>
                </section>
            </div>
        </main>
        <div id="dialogBack">
            <div id="settings" class="dialog">
                <div class="dialogTitleBar">
                    <h2><?php echo $langTxt[$lang]['header']['headMoreSettings']; ?></h2>
                    <svg onclick="showSettings(false)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                </div>
                <div class="dialogBody">
                    <div class="dialogBodyCenter">
                        <div class="settingsItem" id="settingsItemGeneral">
                            <h3><?php echo $langTxt[$lang]['settings']['general']; ?></h3>
                            <div><label><input id="darkModeCheckbox" type="checkbox" /><?php echo $langTxt[$lang]['settings']['darkMode']; ?></label></div>
                            <div><label><input id="showHiddenCheckbox" type="checkbox" /><?php echo $langTxt[$lang]['settings']['showHiddenFiles']; ?></label></div>
                            <div><label><input id="showExtensionCheckbox" type="checkbox" /><?php echo $langTxt[$lang]['settings']['showfileExtensions']; ?></label></div>
                        </div>
                        <div class="settingsItem" id="settingsItemStartUp">
                            <h3><?php echo $langTxt[$lang]['settings']['startUp']; ?></h3>
                            <div>
                                <p><?php echo $langTxt[$lang]['settings']['startUpDescrip']; ?></p>
                                <form>
                                    <label><input class="defaultView" id="defaultViewMosaic" type="radio" name="viewOption" value="Mosaic" /><?php echo $langTxt[$lang]['header']['headViewMosaic']; ?></label>
                                    <label><input class="defaultView" id="defaultViewList" type="radio" name="viewOption" value="List" /><?php echo $langTxt[$lang]['header']['headViewList']; ?></label>
                                    <label><input class="defaultView" id="defaultViewWall" type="radio" name="viewOption" value="Icons" /><?php echo $langTxt[$lang]['header']['headViewWall']; ?></label>
                                    <label><input class="defaultView" id="defaultViewLast" type="radio" name="viewOption" value="last" /><?php echo $langTxt[$lang]['settings']['startUpLast']; ?></label>
                                </form>
                            </div>
                        </div>
                        <div class="settingsItem" id="settingsItemIgnore">
                            <h3><?php echo $langTxt[$lang]['settings']['hideFiles']; ?></h3>
                            <div>
                                <p><?php echo $langTxt[$lang]['settings']['hideFilesDescrip']; ?></p>
                                <div class="settingsInputText">
                                    <input id="ignoreFilesInput" type="text" name="" value="" spellcheck="false" autocomplete="off" placeholder="<?php echo $langTxt[$lang]['settings']['hideFilesPlaceholder'] ?>" />
                                    <div class="settingsInputEffect"></div>
                                </div>
                            </div>
                        </div>
                        <div class="settingsItem" id="settingsItemPriority">
                            <h3><?php echo $langTxt[$lang]['settings']['priority']; ?></h3>
                            <div>
                                <div><label><input id="systemIndexPriority" type="checkbox" /><?php echo $langTxt[$lang]['settings']['defaultPriority']; ?></label></div>
                                <p><?php echo $langTxt[$lang]['settings']['priorityDescrip']; ?></p>
                                <div class="settingsInputText">
                                    <input id="indexPriorityInput" type="text" name="" value="" spellcheck="false" autocomplete="off" placeholder="<?php echo $langTxt[$lang]['settings']['priorityPlaceholder'] ?>" />
                                    <div class="settingsInputEffect"></div>
                                </div>
                            </div>
                        </div>
                        <div class="settingsItem" id="settingsItemDatabase">
                            <h3><?php echo $langTxt[$lang]['settings']['database']; ?></h3>
                            <div>
                                <p><?php echo $langTxt[$lang]['settings']['databaseDescrip']; ?></p>
                                <div class="settingsInputText">
                                    <input id="databasePathInput" type="text" name="" value="" spellcheck="false" autocomplete="off" placeholder="<?php echo $langTxt[$lang]['settings']['databasePlaceholder'] ?>" />
                                    <div class="settingsInputEffect"></div>
                                </div>
                            </div>
                        </div>
                        <div class="settingsItem" id="settingsItemLang">
                            <h3><?php echo $langTxt[$lang]['settings']['lang'] ?></h3>
                            <div>
                                <p><?php echo $langTxt[$lang]['settings']['langDescrip'] ?></p>
                                <select id="settingsSelectLang">
                                </select>
                            </div>
                        </div>
                        <div class="settingsItem" id="settingsItemReset">
                            <h3><?php echo $langTxt[$lang]['settings']['default']; ?></h3>
                            <div>
                                <button id="resetSettingsButton"><?php echo $langTxt[$lang]['settings']['defaultButton']; ?></button>
                                <span><?php echo $langTxt[$lang]['settings']['defaultDescrip']; ?></span>
                            </div>
                        </div>
                        <p id="settingsVersion">beta v3.2.1</p>
                    </div>
                </div>
            </div>
            <div id="history" class="dialog">
                <div class="dialogTitleBar">
                    <h2><?php echo $langTxt[$lang]['header']['headMoreHistory']; ?></h2>
                    <svg onclick="showHistory(false)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                </div>
                <div class="dialogBody" id="historyBody">
                    <div id="historyPaths"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- inicio del menu contextual -->
    <div id="context" class="swing">
        <ul>
            <li id="context1">Abrir fichero / carpeta</li>
            <li id="context2" class="disabled">información</li>
            <li id="context3">Agregar a favoritos</li>
        </ul>
    </div>
    <!-- fin del menu contextual -->
</body>
</html>

<?php
}
?>
