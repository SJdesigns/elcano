<?php
/* ---- elcano Explorer v3.2.5 ---- */

if (isset($_GET['token'])) {
    // auth.php

    $userList = array(
    // LIST OF USERS
    // --------------------------------------------------------
    // type your users here in the format 'user' => 'password',
        'user1' => 'pass1',
    // --------------------------------------------------------
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
                $return['message'] = 'WrongUser';
            } else {
                if ($credentialsCorrect>0) { // login successfull
                    $return['status'] = 200;
                    $return['token'] = $token;
                    $return['message'] = 'AccessGranted';
                } else { // wrong password
                    $return['status'] = 400;
                    $return['token'] = $token;
                    $return['message'] = 'WrongCredentials';
                }
            }

        } else { // missing login data
            $return['status'] = 400;
            $return['token'] = $token;
            $return['message'] = 'missingData';
        }

    } else { // missing token
        $return['status'] = 400;
        $return['token'] = 'null';
        $return['message'] = 'missingData';
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

    function directoryTree($root) {
        $treeData = [];

        $dirs = treePath($root);

        $code = json_encode($dirs);

        print_r($code);
    }

    function treePath($path) { // lee directorios de una ruta concreta
        $directories = [];

        $stream = opendir($path);
        while ($fich = readdir($stream)) {
            if (is_dir($path.'/'.$fich)) {
                if ($fich != "." && $fich != "..") {
                    //echo '<p>'.$fich.' <small>('.$path.$fich.')</small></p>';
                    $item = [];
                    $item['type'] = 'dir';
                    $item['path'] = $path.$fich;
                    $item['name'] = $fich;
                    $item['content'] = treePath($path.$fich.'/');
                    array_push($directories,$item);
                }
            } else {
                $item = [];
                $item['type'] = 'file';
                $item['path'] = $path.''.$fich;
                $item['name'] = $fich;
                array_push($directories, $item);
            }
        }

        return $directories;
    }

    directoryTree('./');

} else if (isset($_POST['ruta'])) {
    // readFich.php

    $rutaArchivo = $_POST['ruta'];

    // Verificar si el archivo existe y es legible
    if (file_exists($rutaArchivo) && is_readable($rutaArchivo)) {
        // Abrir el archivo en modo lectura
        $archivo = fopen($rutaArchivo, 'r');

        // Leer el archivo línea por línea y almacenar en un array
        $lineas = [];
        while (($linea = fgets($archivo)) !== false) {
            $lineas[] = $linea; // Eliminar espacios en blanco al inicio y final de cada línea
        }
        fclose($archivo);

        // Codificar el array como JSON para enviar a JavaScript
        $json = json_encode($lineas);

        // Enviar la respuesta como JSON
        header('Content-Type: application/json');
        echo $json;
    } else {
        // Si el archivo no existe o no se puede leer, enviar un mensaje de error
        echo json_encode(['error' => 'No se pudo leer el archivo']);
    }

} else {
    // lang.php

    if(!isset($_COOKIE['elcano-lang'])){setcookie('elcano-lang',substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2),time()+60*60*24*365,'/',"",false,false);$self=$_SERVER['PHP_SELF'];header("Location: $self");};$langTxt=['es'=>['langName'=>'Español','login'=>['title'=>'Inicio de Sesión','user'=>'usuario','pass'=>'contraseña','submit'=>'Continuar','error'=>['WrongUser'=>'Usuario erroneo','AccessGranted'=>'Acceso permitido','WrongCredentials'=>'Contraseña erronea','MissingData'=>'Falta Informacion',],],'header'=>['headSearch'=>'Buscar ficheros','headStartUp'=>'Ejecutar el índice del directorio','headDataBase'=>'Acceder a la base de datos','headFavorite'=>'Añadir a favoritos','headNotFavorite'=>'Quitar de favoritos','headView'=>'Cambiar la vista','headMore'=>'Más opciones','headViewMosaic'=>'Mosaico','headViewList'=>'Lista','headViewWall'=>'Muro','headMoreHistory'=>'Historial','headMoreHistoryPrev'=>'Atrás','headMoreHistoryNext'=>'Adelante','headMoreShowExpl'=>'Mostrar Explorador','headMoreHideExpl'=>'Ocultar Explorador','headMoreSettings'=>'Configuración','headMoreLogout'=>'Cerrar Sesión'],'aside'=>['asideFav'=>'Favoritos','asideDir'=>'Directorios',],'section'=>['sectionFolder'=>'carpetas','sectionFiles'=>'archivos','noResults'=>'Esta carpeta está vacia','searchResultsTitle'=>'Resultados de la búsqueda para','searchNoResults'=>'Ningún fichero contiene el término de búsqueda',],'context'=>['contextExplore'=>'Explorar','contextOpen'=>'Abrir','contextFavorites'=>'Agregar a favoritos',],'history'=>['historyHome'=>'Página de Inicio',],'viewer'=>['imageViewerTitle'=>'Visor de imágenes','textfileViewerTitle'=>'Visor de texto plano',],'settings'=>['general'=>'General','darkMode'=>'Activar el modo oscuro','showHiddenFiles'=>'Mostrar archivos ocultos','showfileExtensions'=>'Mostrar extensiones de los archivos','startUp'=>'Estado inicial','startUpDescrip'=>'Vista activa por defecto','startUpLast'=>'Último activo','hideFiles'=>'Omitir archivos','hideFilesDescrip'=>'Nombres de archivos y extensiones separados por comas','hideFilesPlaceholder'=>'ficheros ignorados','priority'=>'Prioridad de Índices','defaultPriority'=>'Índice predeterminado del sistema','priorityDescrip'=>'Lista de prioridad de ejecución para los directorios','priorityPlaceholder'=>'orden de prioridad de archivos','database'=>'Base de datos','databaseDescrip'=>'Ruta de acceso a la base de datos','databasePlaceholder'=>'ruta de la base de datos','videoplayer'=>'Reproductor de vídeo','videoplayerDescrip'=>'Configura la url del reproductor uniplayer','videoplayerPlaceholder'=>'ruta del reproductor','lang'=>'Idioma','langDescrip'=>'Selecciona el idioma de la aplicación','default'=>'Configuración predeterminada','defaultButton'=>'Reestablecer','defaultDescrip'=>'Volver a la configuración por defecto'],'error'=>[]],'en'=>['langName'=>'English','login'=>['title'=>'Log In','user'=>'user','pass'=>'password','submit'=>'Continue','error'=>['WrongUser'=>'Wrong user','AccessGranted'=>'Access granted','WrongCredentials'=>'Wrong credentials','MissingData'=>'Missing data',],],'header'=>['headSearch'=>'Search files','headStartUp'=>'Run directory index','headDataBase'=>'Access database','headFavorite'=>'Add to favorites','headNotFavorite'=>'Remove from favorites','headView'=>'Change view','headMore'=>'More options','headViewMosaic'=>'Mosaic','headViewList'=>'List','headViewWall'=>'Wall','headMoreHistory'=>'History Review','headMoreHistoryPrev'=>'Prev','headMoreHistoryNext'=>'Next','headMoreShowExpl'=>'Show Explorer','headMoreHideExpl'=>'Hide Explorer','headMoreSettings'=>'Settings','headMoreLogout'=>'Log Out'],'aside'=>['asideFav'=>'Favorites','asideDir'=>'Directory Tree',],'section'=>['sectionFolder'=>'folders','sectionFiles'=>'files','noResults'=>'This folder is empty','searchResultsTitle'=>'Search results for','searchNoResults'=>'Your search didn\'t match any file',],'context'=>['contextExplore'=>'Explore','contextOpen'=>'Open','contextFavorites'=>'Add to favorites',],'history'=>['historyHome'=>'Homepage',],'viewer'=>['imageViewerTitle'=>'Image viewer','textfileViewerTitle'=>'Plain text viewer',],'settings'=>['general'=>'General','darkMode'=>'Enable dark mode','showHiddenFiles'=>'Show hidden files','showfileExtensions'=>'Show file extensions','startUp'=>'Start Up','startUpDescrip'=>'Default active view','startUpLast'=>'Last active','hideFiles'=>'Ignore Files','hideFilesDescrip'=>'File names and extensions separated by commas','hideFilesPlaceholder'=>'ignored files','priority'=>'Index Priorities','defaultPriority'=>'Default System Index','priorityDescrip'=>'Execution priority list for directories','priorityPlaceholder'=>'file priority order','database'=>'Database','databaseDescrip'=>'Database path','databasePlaceholder'=>'database path','videoplayer'=>'Video player','videoplayerDescrip'=>'Configure the path of uniplayer','videoplayerPlaceholder'=>'path of the player','lang'=>'Language','langDescrip'=>'Select the app language','default'=>'Default Settings','defaultButton'=>'Reset','defaultDescrip'=>'Return to default settings'],'error'=>[]],'fr'=>['langName'=>'Français','login'=>['title'=>'Se Connecter','user'=>'Nom d\'utilisateur','pass'=>'mot de passe','submit'=>'Se Connecter','error'=>['WrongUser'=>'Utilisateur inexistant','AccessGranted'=>'Accès autorisé','WrongCredentials'=>'mot de passe incorrect','MissingData'=>'Manque des informations',],],'header'=>['headSearch'=>'Rechercher des fichers','headStartUp'=>'Exécuter l\'index du répertoire','headDataBase'=>'Accéder à la base de données','headFavorite'=>'Ajouter aux favoris','headNotFavorite'=>'Supprimer des favoris','headView'=>'Change de vue','headMore'=>'plus d\' options','headViewMosaic'=>'Mosaïque','headViewList'=>'Liste','headViewWall'=>'Mur','headMoreHistory'=>'Revue de l\' Histoire','headMoreHistoryPrev'=>'Prev','headMoreHistoryNext'=>'Prochain','headMoreShowExpl'=>'Montrer l\' explorateur','headMoreHideExpl'=>'Chacher l\' explorateur','headMoreSettings'=>'réglages','headMoreLogout'=>'Se déconnecter'],'aside'=>['asideFav'=>'Favoris','asideDir'=>'Arbre des dossiers',],'section'=>['sectionFolder'=>'dossiers','sectionFiles'=>'fichiers','noResults'=>'ce dossier est vide','searchResultsTitle'=>'Résultats de recherche pour','searchNoResults'=>'Votre recherche n\'a donné aucun résultat',],'context'=>['contextExplore'=>'Explorer','contextOpen'=>'Ouvrir','contextFavorites'=>'Ajouter aux favoris',],'history'=>['historyHome'=>'Accueil',],'viewer'=>['imageViewerTitle'=>'Visionneuse d\'images','textfileViewerTitle'=>'Visionneuse de texte brut',],'settings'=>['general'=>'Général','darkMode'=>'Activer le mode sombre','showHiddenFiles'=>'montrer les fichiers cachés','showfileExtensions'=>'Afficher les extensions de fichier','startUp'=>'Start Up','startUpDescrip'=>'Vue active par défaut','startUpLast'=>'Dernier actif','hideFiles'=>'Fichiers ignorés','hideFilesDescrip'=>'Noms de fichiers et extensions séparés par des virgules','hideFilesPlaceholder'=>'fichiers ignorés','priority'=>'priorités d\'index','defaultPriority'=>'Index système par défaut','priorityDescrip'=>'Liste des priorités d\'exécution pour les dossiers','priorityPlaceholder'=>'ordre de priorité des fichiers','database'=>'base de données','databaseDescrip'=>'chemin de la base de données','databasePlaceholder'=>'chemin de la base de données','videoplayer'=>'lecteur vidéo','videoplayerDescrip'=>'Configurer le chemin de uniplayer','videoplayerPlaceholder'=>'chemin du lecteur vidéo','lang'=>'langue','langDescrip'=>'Sélectionnez la langue de l\'application','default'=>'paramètres par défaut','defaultButton'=>'réinitialiser','defaultDescrip'=>'Revenir aux paramètres par défaut'],'error'=>[]],];if(isset($_COOKIE['elcano-lang'])){if(!isset($langTxt[$_COOKIE['elcano-lang']])){$_COOKIE['elcano-lang']='en';}}else{$_COOKIE['elcano-lang']='en';}$lang=$_COOKIE['elcano-lang'];$langJson=json_encode($langTxt);

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
        :root{--primary:#0288D1;--asideColor:#f4f4f4;--sectionColor:#f8f8f8;--shadow:rgba(0,0,0, .1);--lightSeparator:#ccc;--itemHover:rgba(2,136,209,.18);--itemTxt:#34515f;--darkPrimary:#1a1625;--darkAside:#2f2b3a;--darkSection:#2f2b3a;--darkPrimary:#101214;--darkAside:#1D2125;--darkSection:#1D2125;--darkShadow:rgba(255,255,255,.15);--darkSeparator:#333;--darkItemHover:rgba(255,255,255,.08);--darkDropdown:#282828;--darkDialogShadow:rgba(0,255,255,.1)}*{padding:0;margin:0;font-family:"Roboto",sans-serif}body{overflow:overlay}.screen{position:fixed;width:100%;height:100%;background-color:#fff;display:none}#startUp{display:block}#blocked{display:none;z-index:15}#blocked #blockedBack{position:absolute;width:100%;height:100%;display:flex;flex-direction:column;justify-content:center;align-items:center}#blocked #blockedBack #signInBrand{width:340px;padding:24px 0;display:flex;margin-top:-30px;align-items:center;justify-content:center}#blocked #blockedBack #signInBrand #signInLogo{position:relative;width:40px;height:40px}#blocked #blockedBack #signInBrand #signInLogo #brandBack{fill:#fff0}#blocked #blockedBack #signInBrand #signInLogo #brandBorder{fill:var(--primary)}#blocked #blockedBack #signInBrand h1{color:var(--primary);margin-left:20px;cursor:default;font-size:32pt;font-weight:400}#blocked #blockedBack #signIn{position:relative;width:340px;height:auto;box-shadow:2px 2px 8px rgb(0 0 0 / .1)}#blocked #blockedBack #signIn #signInTitle{position:relative;width:100%;height:auto;padding:25px 0;text-align:center;color:#fff;background-color:var(--primary)}#blocked #blockedBack #signIn #signInTitle h1{font-weight:300}#blocked #blockedBack #signIn #signInBody{position:relative;width:100%;height:auto}#blocked #blockedBack #signIn #signInBody #signInError{position:relative;width:100%;min-height:30px;padding:15px 0;text-align:center;color:red}#blocked #blockedBack #signIn #signInBody .signInBodyItem{position:relative;margin:0 40px 28px}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass{width:100%;height:40px;font-size:13pt;padding-left:40px;border:0;outline:0;box-sizing:border-box}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:hover~.signInInputEffect,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:hover~.signInInputEffect{width:100%;left:0%;border-color:#ccc}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:focus~.signInInputEffect,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:focus~.signInInputEffect{width:100%;left:0%;border-color:var(--primary)}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:focus~.signInIcons,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:focus~.signInIcons{fill:#444}#blocked #blockedBack #signIn #signInBody .signInBodyItem .signInIcons{position:absolute;width:25px;height:25px;left:6px;top:8px;fill:#777}#blocked #blockedBack #signIn #signInBody .signInBodyItem .signInInputEffect{position:absolute;width:0%;left:50%;bottom:0;border-bottom:2px solid;transition:all 0.2s}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit{width:100%;height:50px;color:var(--primary);font-size:13pt;margin-top:15px;border-radius:12px;text-align:center;border:0;outline:0;background-color:#fff}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit:hover,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit:focus{background-color:var(--itemHover)}#blocked #blockedBack #loginLang{position:absolute;right:15px;bottom:12px}#blocked #blockedBack #loginLang select{width:150px;height:32px;color:#777;outline:0;font-size:13pt;padding:0 10px;border:0 solid #ccc}header{position:relative;width:100%;height:50px;top:0;background-color:var(--primary);display:flex;justify-content:space-between;z-index:10;transition:background-color 0.4s}header div#headerTitle{position:relative;width:auto;height:100%;display:flex;align-items:center;padding-right:50px}header div#headerTitle #headerLogo{position:relative;width:28px;height:28px;left:20px}header div#headerTitle #headerLogo #logoBack{fill:#fff0}header div#headerTitle #headerLogo #logoBorder{fill:#ffb300;fill:#fff}header div#headerTitle h1{color:#fff;margin-left:30px;cursor:default;font-weight:400}header nav{position:relative;width:100%;height:100%;display:flex}header nav .navItem{position:relative;height:34px;color:#fff;border-radius:8px;font-weight:300;margin:8px 0;padding:8px 10px;box-sizing:border-box;cursor:pointer}header nav .navItem:hover{background-color:var(--shadow)}header nav .navItem .navHomeItem{position:relative;display:flex}header nav .navItem .navHomeItem svg{width:25px;height:25px;margin-top:-3px;fill:#fff}header nav .navItem .navHomeItem i{margin-top:0;margin-left:5px;font-style:normal}header nav .navSeparator{position:relative;margin:8px 0;padding:8px 6px;font-size:11pt;color:#fff;cursor:default}header div#options{position:relative;width:200px;height:100%;padding-left:50px;padding-right:20px;display:flex;flex-direction:row-reverse}header div#options #searchBar{position:relative;width:auto;height:50px;padding:0 10px;display:flex;align-items:center}header div#options #searchBar #searchBox{width:240px;font-size:13pt;padding:0 10px 0 28px;box-sizing:border-box;border:0;color:#ddd;outline:0;height:32px;background-color:#fff0}header div#options #searchBar #searchBox:placeholder-shown~#searchBarIcon,header div#options #searchBar #searchBox:placeholder-shown~#searchBarIcon{fill:rgb(255 255 255 / .5)}header div#options #searchBar #searchBox:not(:placeholder-shown)~#searchBarIcon,header div#options #searchBar #searchBox:not(:placeholder-shown)~#searchBarIcon{fill:#ddd}header div#options #searchBar label{position:absolute;width:22px;height:22px}header div#options #searchBar label #searchBarIcon{position:absolute;width:22px;height:22px;fill:rgb(255 255 255 / .5);cursor:text}header div#options .option{position:relative;width:50px;height:50px}header div#options .option .optionArea{position:absolute;width:0%;height:0%;left:50%;top:50%;border-radius:50%;background-color:var(--shadow);transition:all 0.2s}header div#options .option svg{position:relative;width:34px;height:34px;padding:8px 8px;fill:#fff;z-index:1}header div#options .option svg:hover~.optionArea{width:100%;height:100%;left:0%;top:0%;border-radius:0%}header div#options .optDropDown{position:absolute;width:260px;height:auto;right:0;top:50px;padding:8px 0;background-color:#fff;box-shadow:-2px 2px 8px rgb(0 0 0 / .15);cursor:pointer;display:none}header div#options .optDropDown .optViewDesplActive{background-color:rgb(0 0 0 / .1)}header div#options .optDropDown div.optDropDownItem{position:relative;width:100%;padding:12px 14px;text-align:center;box-sizing:border-box;display:flex}header div#options .optDropDown div.optDropDownItem:hover{background-color:rgb(0 0 0 / .1)}header div#options .optDropDown div.optDropDownItem:hover .optChainDropdown{display:flex}header div#options .optDropDown div.optDropDownItem .optMoreDisabled{color:#aaa}header div#options .optDropDown div.optDropDownItem .optMoreDisabled:hover{background-color:#fff}header div#options .optDropDown div.optDropDownItem .optMoreDisabled svg{fill:#aaa}header div#options .optDropDown div.optDropDownItem .optMoreDisabled p small{color:#ddd}header div#options .optDropDown div.optDropDownItem svg{width:25px;height:25px;margin-right:12px}header div#options .optDropDown div.optDropDownItem p{margin-top:2px}header div#options .optDropDown div.optDropDownItem p small{color:#aaa;margin-left:12px}header div#options .optDropDown .optChainDropdown{position:absolute;width:220px;margin-left:-236px;top:-8px;padding:8px 0;background-color:#fff;box-shadow:2px 2px 8px rgb(0 0 0 / .1);flex-direction:column;display:none}#searchBox::-webkit-input-placeholder{color:rgb(255 255 255 / .5)}main{position:absolute;width:100%;height:100%;top:0;padding-top:50px;box-sizing:border-box}main #errorReporting{position:fixed;right:0;z-index:1}main #errorReporting .errorItem{position:relative;max-width:400px;padding:10px 35px;margin:10px 18px;background-color:rgb(255 68 68 / .8);box-shadow:-2px 2px 8px rgb(0 0 0 / .1);display:flex;justify-content:center}main #errorReporting .errorItem svg{width:30px;height:30px;fill:#fff}main #errorReporting .errorItem p{color:#fff;font-size:13pt;padding:5px 12px}main #shadow{position:fixed;width:100%;height:100%;background-color:var(--shadow);display:none;z-index:2}main #mainCenter{position:relative;width:100%;height:100%;display:flex}main #mainCenter aside{position:relative;width:320px;margin-left:0;height:100%;background-color:#8eacbc;background-color:var(--asideColor);display:flex;flex-direction:column;transition:margin-left 0.4s;box-shadow:2px 2px 8px rgb(0 0 0 / .08);z-index:1}main #mainCenter aside #asideFavorites{position:relative;width:100%;height:auto;max-height:190px;border-bottom:1px solid var(--lightSeparator)}main #mainCenter aside #asideFavorites #favTitle{justify-content:space-between}main #mainCenter aside #asideFavorites #favTitle div{display:flex;flex-direction:row}main #mainCenter aside #asideFavorites #favTitle #favCount{padding:0 15px}main #mainCenter aside #asideFavorites #asideFavBody{padding:4px 0 8px;overflow:auto;max-height:143px}main #mainCenter aside #asideFavorites #asideFavBody .favFolder{padding:3px 10px;box-sizing:border-box;cursor:pointer}main #mainCenter aside #asideFavorites #asideFavBody .favFolder:hover{background-color:var(--shadow)}main #mainCenter aside #asideFavorites #asideFavBody .favFolder small{font-size:10pt;font-weight:300;font-style:italic;margin-left:12px;color:#444}main #mainCenter aside #asideTree{position:relative;width:100%;height:auto;overflow:auto}main #mainCenter aside .asideTitle{position:relative;width:100%;height:30px;margin-top:8px;display:flex;flex-direction:row;cursor:default}main #mainCenter aside .asideTitle svg{width:25px;height:25px;fill:#222;margin:2px 8px}main #mainCenter aside .asideTitle p{margin:5px 0}main #mainCenter section{position:relative;width:100%;height:100%;overflow:auto;background-color:var(--sectionColor)}main #mainCenter section #itemArea{position:relative;width:100%;height:auto;display:flex;flex-wrap:wrap;color:var(--itemTxt);padding-bottom:40px}main #mainCenter section #itemArea #emptyFolder{padding:30px 60px;cursor:default}main #mainCenter section #itemArea .item{position:relative;padding:0 12px;overflow:hidden;box-sizing:border-box;display:flex}main #mainCenter section #itemArea .item:hover{background-color:var(--itemHover)}main #mainCenter section #itemArea .itemHidden{opacity:.4}main #mainCenter section #itemArea .itemActive{background-color:var(--itemHover)}main #mainCenter section #itemArea .itemMosaic{width:16.6666666667%;height:60px}main #mainCenter section #itemArea .itemMosaic .itemLogo{position:relative;width:60px;height:100%;padding:0;display:flex;justify-content:center;align-items:center}main #mainCenter section #itemArea .itemMosaic .itemLogo svg,main #mainCenter section #itemArea .itemMosaic .itemLogo img{height:42px}main #mainCenter section #itemArea .itemMosaic .itemText{position:relative;width:100%;height:100%;display:flex;align-items:center;margin-left:12px}main #mainCenter section #itemArea .itemMosaic .itemText p{font-size:13pt;margin:8px 0;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemMosaic .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemMosaic .itemFiletype,main #mainCenter section #itemArea .itemMosaic .itemFilesize{display:none}main #mainCenter section #itemArea .itemList{width:100%;height:60px}main #mainCenter section #itemArea .itemList .itemLogo{position:relative;width:60px;height:60px;margin-left:4px;display:flex;justify-content:center;align-items:center}main #mainCenter section #itemArea .itemList .itemLogo svg,main #mainCenter section #itemArea .itemList .itemLogo img{height:42px}main #mainCenter section #itemArea .itemList .itemText{position:relative;width:100%;height:100%;margin-left:18px;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemText p{font-size:13pt;margin:8px 0;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemList .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemList .itemFiletype{position:relative;width:200px;height:100%;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemFiletype p{font-size:13pt;margin:8px 0;cursor:default}main #mainCenter section #itemArea .itemList .itemFilesize{position:relative;width:200px;height:100%;margin-right:20%;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemFilesize p{font-size:13pt;margin:8px 0;cursor:default}main #mainCenter section #itemArea .itemWall{width:auto;height:auto;flex-direction:column}main #mainCenter section #itemArea .itemWall .itemLogo{position:relative;width:50px;height:50px;padding:9px;display:flex;justify-content:center;margin:0 auto}main #mainCenter section #itemArea .itemWall .itemLogo svg,main #mainCenter section #itemArea .itemWall .itemLogo img{width:50px;height:50px}main #mainCenter section #itemArea .itemWall .itemText{position:relative;width:100%;height:100%;display:flex;align-items:center}main #mainCenter section #itemArea .itemWall .itemText p{font-size:13pt;margin:8px 0;margin:8px auto;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemWall .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemWall .itemFiletype,main #mainCenter section #itemArea .itemWall .itemFilesize{display:none}main #mainCenter section #searchArea{position:absolute;width:100%;height:100%;top:0;font-size:13pt;background-color:var(--sectionColor);display:none;flex-direction:column}main #mainCenter section #searchArea #searchAreaHeader{position:relative;width:100%;min-height:80px;padding:0 100px;overflow:auto;box-sizing:border-box;border-bottom:1px solid #ddd;display:flex;align-items:center;justify-content:space-between}main #mainCenter section #searchArea #searchAreaHeader svg{width:28px;height:28px}main #mainCenter section #searchArea #searchAreaHeader svg:hover{fill:red}main #mainCenter section #searchArea #searchAreaContent{position:relative;width:100%;flex:1}main #mainCenter section #searchArea #searchAreaContent #searchNoResults{padding:26px 50px}main #mainCenter section #searchArea #searchAreaContent .item{position:relative;padding:0 12px;overflow:hidden;box-sizing:border-box;display:flex;cursor:default}main #mainCenter section #searchArea #searchAreaContent .item:hover{background-color:var(--itemHover)}main #mainCenter section #searchArea #searchAreaContent .item .itemLogo{position:relative;width:60px;height:100%;padding:0;display:flex;justify-content:center;align-items:center}main #mainCenter section #searchArea #searchAreaContent .item .itemLogo svg{height:42px}main #mainCenter section #searchArea #searchAreaContent .item .itemText{position:relative;min-width:300px;height:100%;display:flex;align-items:center;margin-left:12px}main #mainCenter section #searchArea #searchAreaContent .item .itemPath{position:relative;width:400px;height:100%;display:flex;align-items:center;color:#888}main #mainCenter section #searchArea #searchAreaContent .itemList{width:100%;height:60px}main #mainCenter section #folderBackground{position:fixed;width:500px;height:500px;right:10px;bottom:0}main #mainCenter section #folderBackground svg{position:absolute;width:500px;height:500px;right:20px;bottom:14px;fill:#f2f2f2}main #mainCenter section #folderInfo{position:fixed;right:14px;bottom:8px;border-radius:8px;font-size:11pt;padding:3px 7px 4px;background-color:rgb(0 0 0 / .07);cursor:default}details{margin:0;color:#444;padding:5px;cursor:default;-webkit-transition:all 0.1s}details[open]{animation-name:slideDown;animation-duration:200ms;animation-timing-function:ease-in}details:hover{background-color:#ddd}details details{border:0;margin-left:12px}details summary p.linkTree{color:#333}summary p.linkTree{text-decoration:none}summary p.linkTree:focus{text-decoration:none}details#activo>summary>p.linkTree{color:#2196f3}summary{outline:0}details summary::-webkit-details-marker{display:none;list-style:none}details>summary{list-style:none}summary::before{position:relative;float:left;content:"+";color:#444;margin-right:0;padding-right:12px;margin-top:-5px;font-size:18pt}details[open]>summary::before{position:relative;float:left;content:"-";margin-left:0;padding-left:2px;margin-right:0;padding-right:14px;margin-top:-10px;font-size:22pt}@keyframes slideDown{0%{opacity:0;height:0}100%{opacity:1;height:20px}}aside ::selection{background-color:#fff0}.spinner{margin:100px auto;width:80px;height:80px;position:relative;text-align:center;-webkit-animation:sk-rotate 2s infinite linear;animation:sk-rotate 2s infinite linear}.dot1,.dot2{width:60%;height:60%;display:inline-block;position:absolute;top:0;background-color:#cfd8dc;background-color:var(--primary);border-radius:100%;-webkit-animation:sk-bounce 2s infinite ease-in-out;animation:sk-bounce 2s infinite ease-in-out}.dot2{top:auto;bottom:0;-webkit-animation-delay:-1s;animation-delay:-1s}@-webkit-keyframes sk-rotate{100%{-webkit-transform:rotate(360deg)}}@keyframes sk-rotate{100%{transform:rotate(360deg);-webkit-transform:rotate(360deg)}}@-webkit-keyframes sk-bounce{0%,100%{-webkit-transform:scale(0)}50%{-webkit-transform:scale(1)}}@keyframes sk-bounce{0%,100%{transform:scale(0);-webkit-transform:scale(0)}50%{transform:scale(1);-webkit-transform:scale(1)}}@media screen and (max-width:2100px){main #mainCenter section #itemArea .itemMosaic{width:20%}}@media screen and (max-width:1750px){main #mainCenter section #itemArea .itemMosaic{width:25%}}@media screen and (max-width:1300px){main #mainCenter section #itemArea .itemMosaic{width:33.33333333%}}@media screen and (max-width:1050px){main #mainCenter section #itemArea .itemMosaic{width:50%}}@media screen and (max-width:750px){main #mainCenter section #itemArea .itemMosaic{width:100%}}@media screen and (max-width:420px){main #mainCenter aside{margin-left:-320px}}#dialogBack{position:fixed;width:100%;height:100%;top:0;background-color:rgb(0 0 0 / .1);align-items:center;justify-content:center;z-index:12;display:none}#dialogBack #imageViewer{height:560px}#dialogBack .dialog{position:relative;width:950px;height:520px;border-radius:8px;margin-top:50px;background-color:#fff;box-shadow:2px 2px 8px rgb(0 0 0 / .1);display:none}#dialogBack .dialog .dialogTitleBar{position:relative;width:100%;height:50px;box-shadow:2px 2px 8px rgb(0 0 0 / .1);display:flex;align-items:center;justify-content:space-between;z-index:1}#dialogBack .dialog .dialogTitleBar div{display:flex;align-items:center}#dialogBack .dialog .dialogTitleBar div .settingsTitleIcon{width:30px;height:30px;fill:#555;padding-left:26px}#dialogBack .dialog .dialogTitleBar div h2{padding:0 14px;font-weight:400;display:flex;cursor:default;align-items:center}#dialogBack .dialog .dialogTitleBar div h2 #imageViewerUrl,#dialogBack .dialog .dialogTitleBar div h2 #textfileViewerUrl{color:#aaa;font-size:15pt;margin-left:20px}#dialogBack .dialog .dialogTitleBar .settingsClose{width:32px;height:32px;fill:#888;padding:10px 20px}#dialogBack .dialog .dialogTitleBar .settingsClose:hover{fill:red}#dialogBack .dialog .dialogBody{position:absolute;width:100%;top:0;height:100%;padding-top:50px;box-sizing:border-box}#dialogBack .dialog .dialogBody .dialogBodyCenter{position:relative;width:100%;height:100%;overflow:auto}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem{position:relative;width:100%;height:auto;padding:16px 24px;box-sizing:border-box}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem:hover{background-color:rgb(0 0 0 / .04)}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem:hover input[type=text]{background-color:#fff0}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem h3{color:#777;margin-bottom:8px;font-weight:300;cursor:default}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem p,#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem label,#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem span{color:#333}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #settFormStartUp{display:flex}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem label{cursor:pointer}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem select{width:200px;height:32px;font-size:13pt;color:#555;border:0;outline:0;padding:0 12px;margin:10px 0;background-color:#fff0}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem select:focus{background-color:#fff}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem .hiddenCheckbox{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #darkModeCheckbox:checked~div #settCheckLabelDarkMode .settCheckboxIconOff{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #darkModeCheckbox:checked~div #settCheckLabelDarkMode .settCheckboxIconOn{display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #showHiddenCheckbox:checked~div #settCheckLabelShowHidden .settCheckboxIconOff{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #showHiddenCheckbox:checked~div #settCheckLabelShowHidden .settCheckboxIconOn{display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #showExtensionCheckbox:checked~div #settCheckLabelShowExt .settCheckboxIconOff{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #showExtensionCheckbox:checked~div #settCheckLabelShowExt .settCheckboxIconOn{display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #systemIndexPriority:checked~div #settCheckLabelIndexPriority .settCheckboxIconOff{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #systemIndexPriority:checked~div #settCheckLabelIndexPriority .settCheckboxIconOn{display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #defaultViewMosaic:checked~#settRadioLabelMosaic .settRadioIconOff{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #defaultViewMosaic:checked~#settRadioLabelMosaic .settRadioIconOn{display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #defaultViewList:checked~#settRadioLabelList .settRadioIconOff{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #defaultViewList:checked~#settRadioLabelList .settRadioIconOn{display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #defaultViewWall:checked~#settRadioLabelWall .settRadioIconOff{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #defaultViewWall:checked~#settRadioLabelWall .settRadioIconOn{display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #defaultViewLast:checked~#settRadioLabelLast .settRadioIconOff{display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem #defaultViewLast:checked~#settRadioLabelLast .settRadioIconOn{display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div{padding:0 8px}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div .settCheckLabel{width:320px;display:flex;align-items:center}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div .settRadioLabel{width:auto;display:flex;align-items:center}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div .settCheckboxIconOff,#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div .settRadioIconOff{margin-right:6px;display:block}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div .settCheckboxIconOn,#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div .settRadioIconOn{margin-right:6px;fill:var(--primary);display:none}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div input[type=checkbox],#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div input[type=radio]{vertical-align:middle;margin-right:8px}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div button{padding:8px 16px;margin:10px 5px;font-size:12pt;background-color:#ddd;border-radius:8px;border:0;outline:0;cursor:pointer}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsItem div button:hover{background-color:#ccc}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsItemGeneral div{margin:6px 0}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsItemStartUp div form{margin:5px 0}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsItemStartUp div label{margin:0 10px}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsItemPriority p{margin:8px 0 5px}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText{width:50%;margin:6px 0}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText .settingsInputEffect{position:relative;width:0%;left:50%;padding:0;border-bottom:2px solid;transition:all 0.2s}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText input{width:100%;color:#333;height:40px;font-size:13pt;padding:0 10px;border:0;outline:0;box-sizing:border-box}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText input:hover~.settingsInputEffect{width:100%;left:0%;border-color:#ccc}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText input:focus~.settingsInputEffect{width:100%;left:0%;border-color:#34515f}#dialogBack .dialog .dialogBody .dialogBodyCenter .settingsInputText input[disabled]{color:#999}#dialogBack .dialog .dialogBody .dialogBodyCenter #settingsVersion{position:relative;right:12px;bottom:4px;margin-top:8px;text-align:right;font-size:11pt;color:#777;cursor:default}#dialogBack .dialog #historyBody{position:absolute;width:100%;height:100%;top:0;padding-top:50px;box-sizing:border-box}#dialogBack .dialog #historyBody #historyPaths{position:relative;width:100%;height:100%;overflow:auto}#dialogBack .dialog #historyBody #historyPaths .historyItem{position:relative;width:100%;height:50px;padding:4px 20px;box-sizing:border-box;border-bottom:1px solid #f2f2f2;display:flex}#dialogBack .dialog #historyBody #historyPaths .historyItem p{min-width:130px;padding:12px 40px}#dialogBack .dialog #historyBody #historyPaths .historyItem p:last-child{color:var(--primary)}#dialogBack .dialog #historyBody #historyPaths .historyItem svg{position:absolute;right:30px;width:30px;height:30px;fill:#6f6f6f;padding:7px 25px;cursor:pointer}#dialogBack .dialog #historyBody #historyPaths .historyItem svg:hover{fill:var(--primary)}#dialogBack .dialog #historyBody #historyPaths .historyActive{color:var(--primary);background-color:var(--itemHover);border-top:1px solid var(--primary);border-bottom:1px solid var(--primary)}#dialogBack .dialog #imageViewerBody,#dialogBack .dialog #textfileViewerBody{position:absolute;width:100%;height:100%;top:0;padding-top:50px;box-sizing:border-box;display:flex;justify-content:center;align-items:center}#dialogBack .dialog #imageViewerBody #imageViewerImageCanvas,#dialogBack .dialog #textfileViewerBody #imageViewerImageCanvas{position:relative;width:100%;height:100%;padding:20px 28px;box-sizing:border-box;display:flex;align-items:center;justify-content:center}#dialogBack .dialog #imageViewerBody #imageViewerImageCanvas img,#dialogBack .dialog #textfileViewerBody #imageViewerImageCanvas img{max-width:100%;max-height:100%;object-fit:contain}#dialogBack .dialog #imageViewerBody #textfileViewerCanvas,#dialogBack .dialog #textfileViewerBody #textfileViewerCanvas{position:relative;width:100%;height:100%;padding:10px 18px;box-sizing:border-box;overflow:auto}#dialogBack .dialog #imageViewerBody #imageViewerOptions,#dialogBack .dialog #imageViewerBody #textfileViewerOptions,#dialogBack .dialog #textfileViewerBody #imageViewerOptions,#dialogBack .dialog #textfileViewerBody #textfileViewerOptions{position:absolute;right:28px;bottom:16px}#dialogBack .dialog #imageViewerBody #imageViewerOptions svg,#dialogBack .dialog #imageViewerBody #textfileViewerOptions svg,#dialogBack .dialog #textfileViewerBody #imageViewerOptions svg,#dialogBack .dialog #textfileViewerBody #textfileViewerOptions svg{width:28px;height:28px;fill:#999}#dialogBack .dialog #imageViewerBody #imageViewerOptions svg:hover,#dialogBack .dialog #imageViewerBody #textfileViewerOptions svg:hover,#dialogBack .dialog #textfileViewerBody #imageViewerOptions svg:hover,#dialogBack .dialog #textfileViewerBody #textfileViewerOptions svg:hover{fill:var(--primary)}@media screen and (max-width:1080px){#dialogBack .dialog{width:92%}#dialogBack #imageViewer{width:92%}}@media screen and (max-width:800px){#dialogBack{padding:20px;box-sizing:border-box}#dialogBack .dialog{width:100%}#dialogBack #imageViewer{width:100%}}@media screen and (max-height:620px){#dialogBack{padding:20px;box-sizing:border-box}#dialogBack .dialog{height:100%;margin-top:0}#dialogBack #imageViewer{height:100%}}body.darkMode .screen{background-color:var(--darkSection);color:#fff}body.darkMode #dialogBack{background-color:rgb(0 0 0 / .5)}body.darkMode #dialogBack .dialog #textfileViewerBody #textfileViewerCanvas{color:#ccc}body.darkMode header{background-color:var(--darkPrimary)}body.darkMode header #headerTitle #headerLogo #logoBorder{fill:var(--primary)}body.darkMode header nav .navItem:hover{background-color:var(--darkShadow)}body.darkMode header div#options .option .optionArea{background-color:var(--darkShadow)}body.darkMode main #mainCenter aside{background:var(--darkAside);border-right:1px solid var(--darkSeparator);color:#fff}body.darkMode main #mainCenter aside .asideTitle svg{fill:#fff}body.darkMode main #mainCenter aside #asideFavorites{border-bottom:1px solid var(--darkSeparator)}body.darkMode main #mainCenter aside #asideFavorites #asideFavBody .favFolder small{color:#aaa}body.darkMode main #mainCenter aside #asideFavorites #asideFavBody .favFolder:hover{background-color:#555;color:#fff}body.darkMode main #mainCenter aside #asideFavorites #asideFavBody .favFolder:hover small{color:#ccc}body.darkMode details:hover{color:#fff;background-color:#555}body.darkMode details summary p.linkTree{color:#fff}body.darkMode summary::before{color:#fff}body.darkMode header div#options .optDropDown{color:#000}body.darkMode #dialogBack .dialog{background-color:#333}body.darkMode main #mainCenter section #itemArea{color:#9eb9c6}body.darkMode main #mainCenter section #itemArea .item:hover{background-color:var(--darkItemHover)}body.darkMode main #mainCenter section #itemArea .itemText{color:#bbb}body.darkMode main #mainCenter section #folderInfo{color:#ddd;background-color:rgb(255 255 255 / .07)}body.darkMode main #mainCenter section{background-color:var(--darkSection)}body.darkMode main #mainCenter section #folderBackground svg{fill:#22272c}body.darkMode main #mainCenter section #itemArea #emptyFolder{color:#ccc}body.darkMode main #mainCenter section #searchArea{background-color:var(--darkSection)}body.darkMode main #mainCenter section #searchArea #searchAreaHeader{border-bottom:1px solid var(--darkSeparator)}body.darkMode main #mainCenter section #searchArea #searchAreaHeader #searchAreaHeaderLeft{color:#bbb}body.darkMode main #mainCenter section #searchArea #searchAreaHeader #searchAreaHeaderRight{fill:#ddd}body.darkMode main #mainCenter section #searchArea #searchAreaContent #searchNoResults{color:#bbb}body.darkMode main #mainCenter section #searchArea #searchAreaContent .item .itemText{color:#bbb}body.darkMode #dialogBack .dialog .dialogTitleBar{border-radius:8px 8px 0 0;background-color:var(--darkDropdown)}body.darkMode #dialogBack .dialog .dialogTitleBar .settingsTitleIcon{fill:#fff}body.darkMode #dialogBack .dialog .dialogTitleBar .settingsClose{fill:#888}body.darkMode #dialogBack .dialog .dialogTitleBar .settingsClose:hover{fill:#FF1744}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem:hover{background-color:var(--darkDialogShadow)}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem h3{color:#fff}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem p,body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem label,body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem span{color:#ddd}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem div .settCheckboxIconOff,body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem div .settRadioIconOff{fill:#ddd}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem:hover input[type=text]{background-color:#fff0}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem:hover input[type=text]:hover{background-color:rgb(0 255 255 / .05)}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem:hover select:hover{background-color:rgb(0 255 255 / .05)}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem:hover select:focus{background-color:#fff}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem select{color:#eee}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsItem select:focus{color:#555}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsInputText input{color:#eee;background-color:#333}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsInputText input:hover~.settingsInputEffect{border-color:#bbb}body.darkMode #dialogBack .dialog .dialogBodyCenter .settingsInputText input:focus~.settingsInputEffect{border-color:#fff}body.darkMode #dialogBack .dialog .dialogBodyCenter ::-webkit-input-placeholder{color:#aaa}body.darkMode #dialogBack .dialog #historyBody #historyPaths .historyItem{color:#ccc;border-bottom:1px solid #444}body.darkMode #dialogBack .dialog #historyBody #historyPaths .historyItem p:last-child{color:#ddd}body.darkMode #dialogBack .dialog #historyBody #historyPaths .historyItem svg{fill:#bbb}body.darkMode #dialogBack .dialog #historyBody #historyPaths .historyItem svg:hover{fill:var(--primary)}body.darkMode #dialogBack .dialog #historyBody #historyPaths .historyActive{color:#ddd;background-color:var(--darkDialogShadow);border-top:1px solid var(--darkDialogShadow);border-bottom:1px solid var(--darkDialogShadow)}body.darkMode details:hover summary p.linkTree{color:#fff}body.darkMode details:hover summary::before{color:#fff}body.darkMode header div#options .optDropDown{color:#fff;background-color:var(--darkDropdown)}body.darkMode header div#options .optDropDown div.optDropDownItem svg{fill:#fff}body.darkMode header div#options .optDropDown div.optDropDownItem:hover{background-color:rgb(255 255 255 / .1)}body.darkMode header div#options .optDropDown div.optDropDownItem:hover .optChainDropdown{background-color:var(--darkDropdown)}body.darkMode #context{background-color:var(--darkDropdown);box-shadow:2px 2px 7px rgb(0 0 0 / .15);border:1px solid #383838}body.darkMode #context ul li{color:#fff}body.darkMode #context ul li:hover{background-color:rgb(255 255 255 / .12)}body.darkMode #blocked #blockedBack #signIn{border:1px solid #444}body.darkMode #blocked #blockedBack #signIn #signInTitle{background-color:#333}body.darkMode #blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser,body.darkMode #blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass{color:#ddd;background-color:#fff0}body.darkMode #blocked #blockedBack #signIn #signInBody svg{fill:#ddd}body.darkMode #blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:focus~.signInInputEffect,body.darkMode #blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:focus~.signInInputEffect{border-color:#fff}body.darkMode #blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit{color:#ddd;background-color:rgb(255 255 255 / .1)}body.darkMode #blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit:hover,body.darkMode #blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit:focus{background-color:rgb(255 255 255 / .4)}body.darkMode #blocked #blockedBack #loginLang select{color:#ddd;background-color:#fff0}body.darkMode #blocked #blockedBack #loginLang select:focus{color:#222;background-color:#fff}#context{position:absolute;width:180px;height:auto;background-color:#fff;box-shadow:2px 2px 7px #ccc;overflow:hidden;opacity:.8;cursor:default;display:none;z-index:15;-webkit-transition:left 0.5s,top 0.5s,opacity 0.4s;-moz-transition:left 0.5s,top 0.5s,opacity 0.4s;-o-transition:left 0.5s,top 0.5s,opacity 0.4s;-ms-transition:left 0.5s,top 0.5s,opacity 0.4s}#context:hover{opacity:1}#context ul{position:relative;width:100%;height:auto;margin:5px 0;padding:0;list-style-type:none}#context ul li{position:relative;width:100%;height:auto;color:#282828;font-size:12pt;border-radius:50%;padding:12px 0;text-align:center;box-sizing:border-box}#context ul li:hover{border-radius:0;color:#fff;background-color:#2177ff}#context ul li.disabled{color:rgb(40 40 40 / .5)}#context ul li.disabled:hover{color:#fff;background-color:#b4b4b4}::-webkit-scrollbar{width:10px;height:10px}::-webkit-scrollbar-thumb{background:#aaa}::-webkit-scrollbar-track{background:rgb(0 0 0 / .05)}
    </style>
    <script type="text/javascript">
        var langs = <?php echo $langJson; ?>;
        var filename = '<?php echo substr(__FILE__,strrpos(__FILE__,'\\') + 1); ?>';
        console.log(filename);
    </script>
    <script type="text/javascript">
        /*! jQuery v3.1.1 | (c) jQuery Foundation | jquery.org/license */
!function(a,b){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=a.document?b(a,!0):function(a){if(!a.document)throw new Error("jQuery requires a window with a document");return b(a)}:b(a)}("undefined"!=typeof window?window:this,function(a,b){"use strict";var c=[],d=a.document,e=Object.getPrototypeOf,f=c.slice,g=c.concat,h=c.push,i=c.indexOf,j={},k=j.toString,l=j.hasOwnProperty,m=l.toString,n=m.call(Object),o={};function p(a,b){b=b||d;var c=b.createElement("script");c.text=a,b.head.appendChild(c).parentNode.removeChild(c)}var q="3.1.1",r=function(a,b){return new r.fn.init(a,b)},s=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,t=/^-ms-/,u=/-([a-z])/g,v=function(a,b){return b.toUpperCase()};r.fn=r.prototype={jquery:q,constructor:r,length:0,toArray:function(){return f.call(this)},get:function(a){return null==a?f.call(this):a<0?this[a+this.length]:this[a]},pushStack:function(a){var b=r.merge(this.constructor(),a);return b.prevObject=this,b},each:function(a){return r.each(this,a)},map:function(a){return this.pushStack(r.map(this,function(b,c){return a.call(b,c,b)}))},slice:function(){return this.pushStack(f.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},eq:function(a){var b=this.length,c=+a+(a<0?b:0);return this.pushStack(c>=0&&c<b?[this[c]]:[])},end:function(){return this.prevObject||this.constructor()},push:h,sort:c.sort,splice:c.splice},r.extend=r.fn.extend=function(){var a,b,c,d,e,f,g=arguments[0]||{},h=1,i=arguments.length,j=!1;for("boolean"==typeof g&&(j=g,g=arguments[h]||{},h++),"object"==typeof g||r.isFunction(g)||(g={}),h===i&&(g=this,h--);h<i;h++)if(null!=(a=arguments[h]))for(b in a)c=g[b],d=a[b],g!==d&&(j&&d&&(r.isPlainObject(d)||(e=r.isArray(d)))?(e?(e=!1,f=c&&r.isArray(c)?c:[]):f=c&&r.isPlainObject(c)?c:{},g[b]=r.extend(j,f,d)):void 0!==d&&(g[b]=d));return g},r.extend({expando:"jQuery"+(q+Math.random()).replace(/\D/g,""),isReady:!0,error:function(a){throw new Error(a)},noop:function(){},isFunction:function(a){return"function"===r.type(a)},isArray:Array.isArray,isWindow:function(a){return null!=a&&a===a.window},isNumeric:function(a){var b=r.type(a);return("number"===b||"string"===b)&&!isNaN(a-parseFloat(a))},isPlainObject:function(a){var b,c;return!(!a||"[object Object]"!==k.call(a))&&(!(b=e(a))||(c=l.call(b,"constructor")&&b.constructor,"function"==typeof c&&m.call(c)===n))},isEmptyObject:function(a){var b;for(b in a)return!1;return!0},type:function(a){return null==a?a+"":"object"==typeof a||"function"==typeof a?j[k.call(a)]||"object":typeof a},globalEval:function(a){p(a)},camelCase:function(a){return a.replace(t,"ms-").replace(u,v)},nodeName:function(a,b){return a.nodeName&&a.nodeName.toLowerCase()===b.toLowerCase()},each:function(a,b){var c,d=0;if(w(a)){for(c=a.length;d<c;d++)if(b.call(a[d],d,a[d])===!1)break}else for(d in a)if(b.call(a[d],d,a[d])===!1)break;return a},trim:function(a){return null==a?"":(a+"").replace(s,"")},makeArray:function(a,b){var c=b||[];return null!=a&&(w(Object(a))?r.merge(c,"string"==typeof a?[a]:a):h.call(c,a)),c},inArray:function(a,b,c){return null==b?-1:i.call(b,a,c)},merge:function(a,b){for(var c=+b.length,d=0,e=a.length;d<c;d++)a[e++]=b[d];return a.length=e,a},grep:function(a,b,c){for(var d,e=[],f=0,g=a.length,h=!c;f<g;f++)d=!b(a[f],f),d!==h&&e.push(a[f]);return e},map:function(a,b,c){var d,e,f=0,h=[];if(w(a))for(d=a.length;f<d;f++)e=b(a[f],f,c),null!=e&&h.push(e);else for(f in a)e=b(a[f],f,c),null!=e&&h.push(e);return g.apply([],h)},guid:1,proxy:function(a,b){var c,d,e;if("string"==typeof b&&(c=a[b],b=a,a=c),r.isFunction(a))return d=f.call(arguments,2),e=function(){return a.apply(b||this,d.concat(f.call(arguments)))},e.guid=a.guid=a.guid||r.guid++,e},now:Date.now,support:o}),"function"==typeof Symbol&&(r.fn[Symbol.iterator]=c[Symbol.iterator]),r.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(a,b){j["[object "+b+"]"]=b.toLowerCase()});function w(a){var b=!!a&&"length"in a&&a.length,c=r.type(a);return"function"!==c&&!r.isWindow(a)&&("array"===c||0===b||"number"==typeof b&&b>0&&b-1 in a)}var x=function(a){var b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u="sizzle"+1*new Date,v=a.document,w=0,x=0,y=ha(),z=ha(),A=ha(),B=function(a,b){return a===b&&(l=!0),0},C={}.hasOwnProperty,D=[],E=D.pop,F=D.push,G=D.push,H=D.slice,I=function(a,b){for(var c=0,d=a.length;c<d;c++)if(a[c]===b)return c;return-1},J="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",K="[\\x20\\t\\r\\n\\f]",L="(?:\\\\.|[\\w-]|[^\0-\\xa0])+",M="\\["+K+"*("+L+")(?:"+K+"*([*^$|!~]?=)"+K+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+L+"))|)"+K+"*\\]",N=":("+L+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+M+")*)|.*)\\)|)",O=new RegExp(K+"+","g"),P=new RegExp("^"+K+"+|((?:^|[^\\\\])(?:\\\\.)*)"+K+"+$","g"),Q=new RegExp("^"+K+"*,"+K+"*"),R=new RegExp("^"+K+"*([>+~]|"+K+")"+K+"*"),S=new RegExp("="+K+"*([^\\]'\"]*?)"+K+"*\\]","g"),T=new RegExp(N),U=new RegExp("^"+L+"$"),V={ID:new RegExp("^#("+L+")"),CLASS:new RegExp("^\\.("+L+")"),TAG:new RegExp("^("+L+"|[*])"),ATTR:new RegExp("^"+M),PSEUDO:new RegExp("^"+N),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+K+"*(even|odd|(([+-]|)(\\d*)n|)"+K+"*(?:([+-]|)"+K+"*(\\d+)|))"+K+"*\\)|)","i"),bool:new RegExp("^(?:"+J+")$","i"),needsContext:new RegExp("^"+K+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+K+"*((?:-\\d)?\\d*)"+K+"*\\)|)(?=[^-]|$)","i")},W=/^(?:input|select|textarea|button)$/i,X=/^h\d$/i,Y=/^[^{]+\{\s*\[native \w/,Z=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,$=/[+~]/,_=new RegExp("\\\\([\\da-f]{1,6}"+K+"?|("+K+")|.)","ig"),aa=function(a,b,c){var d="0x"+b-65536;return d!==d||c?b:d<0?String.fromCharCode(d+65536):String.fromCharCode(d>>10|55296,1023&d|56320)},ba=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,ca=function(a,b){return b?"\0"===a?"\ufffd":a.slice(0,-1)+"\\"+a.charCodeAt(a.length-1).toString(16)+" ":"\\"+a},da=function(){m()},ea=ta(function(a){return a.disabled===!0&&("form"in a||"label"in a)},{dir:"parentNode",next:"legend"});try{G.apply(D=H.call(v.childNodes),v.childNodes),D[v.childNodes.length].nodeType}catch(fa){G={apply:D.length?function(a,b){F.apply(a,H.call(b))}:function(a,b){var c=a.length,d=0;while(a[c++]=b[d++]);a.length=c-1}}}function ga(a,b,d,e){var f,h,j,k,l,o,r,s=b&&b.ownerDocument,w=b?b.nodeType:9;if(d=d||[],"string"!=typeof a||!a||1!==w&&9!==w&&11!==w)return d;if(!e&&((b?b.ownerDocument||b:v)!==n&&m(b),b=b||n,p)){if(11!==w&&(l=Z.exec(a)))if(f=l[1]){if(9===w){if(!(j=b.getElementById(f)))return d;if(j.id===f)return d.push(j),d}else if(s&&(j=s.getElementById(f))&&t(b,j)&&j.id===f)return d.push(j),d}else{if(l[2])return G.apply(d,b.getElementsByTagName(a)),d;if((f=l[3])&&c.getElementsByClassName&&b.getElementsByClassName)return G.apply(d,b.getElementsByClassName(f)),d}if(c.qsa&&!A[a+" "]&&(!q||!q.test(a))){if(1!==w)s=b,r=a;else if("object"!==b.nodeName.toLowerCase()){(k=b.getAttribute("id"))?k=k.replace(ba,ca):b.setAttribute("id",k=u),o=g(a),h=o.length;while(h--)o[h]="#"+k+" "+sa(o[h]);r=o.join(","),s=$.test(a)&&qa(b.parentNode)||b}if(r)try{return G.apply(d,s.querySelectorAll(r)),d}catch(x){}finally{k===u&&b.removeAttribute("id")}}}return i(a.replace(P,"$1"),b,d,e)}function ha(){var a=[];function b(c,e){return a.push(c+" ")>d.cacheLength&&delete b[a.shift()],b[c+" "]=e}return b}function ia(a){return a[u]=!0,a}function ja(a){var b=n.createElement("fieldset");try{return!!a(b)}catch(c){return!1}finally{b.parentNode&&b.parentNode.removeChild(b),b=null}}function ka(a,b){var c=a.split("|"),e=c.length;while(e--)d.attrHandle[c[e]]=b}function la(a,b){var c=b&&a,d=c&&1===a.nodeType&&1===b.nodeType&&a.sourceIndex-b.sourceIndex;if(d)return d;if(c)while(c=c.nextSibling)if(c===b)return-1;return a?1:-1}function ma(a){return function(b){var c=b.nodeName.toLowerCase();return"input"===c&&b.type===a}}function na(a){return function(b){var c=b.nodeName.toLowerCase();return("input"===c||"button"===c)&&b.type===a}}function oa(a){return function(b){return"form"in b?b.parentNode&&b.disabled===!1?"label"in b?"label"in b.parentNode?b.parentNode.disabled===a:b.disabled===a:b.isDisabled===a||b.isDisabled!==!a&&ea(b)===a:b.disabled===a:"label"in b&&b.disabled===a}}function pa(a){return ia(function(b){return b=+b,ia(function(c,d){var e,f=a([],c.length,b),g=f.length;while(g--)c[e=f[g]]&&(c[e]=!(d[e]=c[e]))})})}function qa(a){return a&&"undefined"!=typeof a.getElementsByTagName&&a}c=ga.support={},f=ga.isXML=function(a){var b=a&&(a.ownerDocument||a).documentElement;return!!b&&"HTML"!==b.nodeName},m=ga.setDocument=function(a){var b,e,g=a?a.ownerDocument||a:v;return g!==n&&9===g.nodeType&&g.documentElement?(n=g,o=n.documentElement,p=!f(n),v!==n&&(e=n.defaultView)&&e.top!==e&&(e.addEventListener?e.addEventListener("unload",da,!1):e.attachEvent&&e.attachEvent("onunload",da)),c.attributes=ja(function(a){return a.className="i",!a.getAttribute("className")}),c.getElementsByTagName=ja(function(a){return a.appendChild(n.createComment("")),!a.getElementsByTagName("*").length}),c.getElementsByClassName=Y.test(n.getElementsByClassName),c.getById=ja(function(a){return o.appendChild(a).id=u,!n.getElementsByName||!n.getElementsByName(u).length}),c.getById?(d.filter.ID=function(a){var b=a.replace(_,aa);return function(a){return a.getAttribute("id")===b}},d.find.ID=function(a,b){if("undefined"!=typeof b.getElementById&&p){var c=b.getElementById(a);return c?[c]:[]}}):(d.filter.ID=function(a){var b=a.replace(_,aa);return function(a){var c="undefined"!=typeof a.getAttributeNode&&a.getAttributeNode("id");return c&&c.value===b}},d.find.ID=function(a,b){if("undefined"!=typeof b.getElementById&&p){var c,d,e,f=b.getElementById(a);if(f){if(c=f.getAttributeNode("id"),c&&c.value===a)return[f];e=b.getElementsByName(a),d=0;while(f=e[d++])if(c=f.getAttributeNode("id"),c&&c.value===a)return[f]}return[]}}),d.find.TAG=c.getElementsByTagName?function(a,b){return"undefined"!=typeof b.getElementsByTagName?b.getElementsByTagName(a):c.qsa?b.querySelectorAll(a):void 0}:function(a,b){var c,d=[],e=0,f=b.getElementsByTagName(a);if("*"===a){while(c=f[e++])1===c.nodeType&&d.push(c);return d}return f},d.find.CLASS=c.getElementsByClassName&&function(a,b){if("undefined"!=typeof b.getElementsByClassName&&p)return b.getElementsByClassName(a)},r=[],q=[],(c.qsa=Y.test(n.querySelectorAll))&&(ja(function(a){o.appendChild(a).innerHTML="<a id='"+u+"'></a><select id='"+u+"-\r\\' msallowcapture=''><option selected=''></option></select>",a.querySelectorAll("[msallowcapture^='']").length&&q.push("[*^$]="+K+"*(?:''|\"\")"),a.querySelectorAll("[selected]").length||q.push("\\["+K+"*(?:value|"+J+")"),a.querySelectorAll("[id~="+u+"-]").length||q.push("~="),a.querySelectorAll(":checked").length||q.push(":checked"),a.querySelectorAll("a#"+u+"+*").length||q.push(".#.+[+~]")}),ja(function(a){a.innerHTML="<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";var b=n.createElement("input");b.setAttribute("type","hidden"),a.appendChild(b).setAttribute("name","D"),a.querySelectorAll("[name=d]").length&&q.push("name"+K+"*[*^$|!~]?="),2!==a.querySelectorAll(":enabled").length&&q.push(":enabled",":disabled"),o.appendChild(a).disabled=!0,2!==a.querySelectorAll(":disabled").length&&q.push(":enabled",":disabled"),a.querySelectorAll("*,:x"),q.push(",.*:")})),(c.matchesSelector=Y.test(s=o.matches||o.webkitMatchesSelector||o.mozMatchesSelector||o.oMatchesSelector||o.msMatchesSelector))&&ja(function(a){c.disconnectedMatch=s.call(a,"*"),s.call(a,"[s!='']:x"),r.push("!=",N)}),q=q.length&&new RegExp(q.join("|")),r=r.length&&new RegExp(r.join("|")),b=Y.test(o.compareDocumentPosition),t=b||Y.test(o.contains)?function(a,b){var c=9===a.nodeType?a.documentElement:a,d=b&&b.parentNode;return a===d||!(!d||1!==d.nodeType||!(c.contains?c.contains(d):a.compareDocumentPosition&&16&a.compareDocumentPosition(d)))}:function(a,b){if(b)while(b=b.parentNode)if(b===a)return!0;return!1},B=b?function(a,b){if(a===b)return l=!0,0;var d=!a.compareDocumentPosition-!b.compareDocumentPosition;return d?d:(d=(a.ownerDocument||a)===(b.ownerDocument||b)?a.compareDocumentPosition(b):1,1&d||!c.sortDetached&&b.compareDocumentPosition(a)===d?a===n||a.ownerDocument===v&&t(v,a)?-1:b===n||b.ownerDocument===v&&t(v,b)?1:k?I(k,a)-I(k,b):0:4&d?-1:1)}:function(a,b){if(a===b)return l=!0,0;var c,d=0,e=a.parentNode,f=b.parentNode,g=[a],h=[b];if(!e||!f)return a===n?-1:b===n?1:e?-1:f?1:k?I(k,a)-I(k,b):0;if(e===f)return la(a,b);c=a;while(c=c.parentNode)g.unshift(c);c=b;while(c=c.parentNode)h.unshift(c);while(g[d]===h[d])d++;return d?la(g[d],h[d]):g[d]===v?-1:h[d]===v?1:0},n):n},ga.matches=function(a,b){return ga(a,null,null,b)},ga.matchesSelector=function(a,b){if((a.ownerDocument||a)!==n&&m(a),b=b.replace(S,"='$1']"),c.matchesSelector&&p&&!A[b+" "]&&(!r||!r.test(b))&&(!q||!q.test(b)))try{var d=s.call(a,b);if(d||c.disconnectedMatch||a.document&&11!==a.document.nodeType)return d}catch(e){}return ga(b,n,null,[a]).length>0},ga.contains=function(a,b){return(a.ownerDocument||a)!==n&&m(a),t(a,b)},ga.attr=function(a,b){(a.ownerDocument||a)!==n&&m(a);var e=d.attrHandle[b.toLowerCase()],f=e&&C.call(d.attrHandle,b.toLowerCase())?e(a,b,!p):void 0;return void 0!==f?f:c.attributes||!p?a.getAttribute(b):(f=a.getAttributeNode(b))&&f.specified?f.value:null},ga.escape=function(a){return(a+"").replace(ba,ca)},ga.error=function(a){throw new Error("Syntax error, unrecognized expression: "+a)},ga.uniqueSort=function(a){var b,d=[],e=0,f=0;if(l=!c.detectDuplicates,k=!c.sortStable&&a.slice(0),a.sort(B),l){while(b=a[f++])b===a[f]&&(e=d.push(f));while(e--)a.splice(d[e],1)}return k=null,a},e=ga.getText=function(a){var b,c="",d=0,f=a.nodeType;if(f){if(1===f||9===f||11===f){if("string"==typeof a.textContent)return a.textContent;for(a=a.firstChild;a;a=a.nextSibling)c+=e(a)}else if(3===f||4===f)return a.nodeValue}else while(b=a[d++])c+=e(b);return c},d=ga.selectors={cacheLength:50,createPseudo:ia,match:V,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(a){return a[1]=a[1].replace(_,aa),a[3]=(a[3]||a[4]||a[5]||"").replace(_,aa),"~="===a[2]&&(a[3]=" "+a[3]+" "),a.slice(0,4)},CHILD:function(a){return a[1]=a[1].toLowerCase(),"nth"===a[1].slice(0,3)?(a[3]||ga.error(a[0]),a[4]=+(a[4]?a[5]+(a[6]||1):2*("even"===a[3]||"odd"===a[3])),a[5]=+(a[7]+a[8]||"odd"===a[3])):a[3]&&ga.error(a[0]),a},PSEUDO:function(a){var b,c=!a[6]&&a[2];return V.CHILD.test(a[0])?null:(a[3]?a[2]=a[4]||a[5]||"":c&&T.test(c)&&(b=g(c,!0))&&(b=c.indexOf(")",c.length-b)-c.length)&&(a[0]=a[0].slice(0,b),a[2]=c.slice(0,b)),a.slice(0,3))}},filter:{TAG:function(a){var b=a.replace(_,aa).toLowerCase();return"*"===a?function(){return!0}:function(a){return a.nodeName&&a.nodeName.toLowerCase()===b}},CLASS:function(a){var b=y[a+" "];return b||(b=new RegExp("(^|"+K+")"+a+"("+K+"|$)"))&&y(a,function(a){return b.test("string"==typeof a.className&&a.className||"undefined"!=typeof a.getAttribute&&a.getAttribute("class")||"")})},ATTR:function(a,b,c){return function(d){var e=ga.attr(d,a);return null==e?"!="===b:!b||(e+="","="===b?e===c:"!="===b?e!==c:"^="===b?c&&0===e.indexOf(c):"*="===b?c&&e.indexOf(c)>-1:"$="===b?c&&e.slice(-c.length)===c:"~="===b?(" "+e.replace(O," ")+" ").indexOf(c)>-1:"|="===b&&(e===c||e.slice(0,c.length+1)===c+"-"))}},CHILD:function(a,b,c,d,e){var f="nth"!==a.slice(0,3),g="last"!==a.slice(-4),h="of-type"===b;return 1===d&&0===e?function(a){return!!a.parentNode}:function(b,c,i){var j,k,l,m,n,o,p=f!==g?"nextSibling":"previousSibling",q=b.parentNode,r=h&&b.nodeName.toLowerCase(),s=!i&&!h,t=!1;if(q){if(f){while(p){m=b;while(m=m[p])if(h?m.nodeName.toLowerCase()===r:1===m.nodeType)return!1;o=p="only"===a&&!o&&"nextSibling"}return!0}if(o=[g?q.firstChild:q.lastChild],g&&s){m=q,l=m[u]||(m[u]={}),k=l[m.uniqueID]||(l[m.uniqueID]={}),j=k[a]||[],n=j[0]===w&&j[1],t=n&&j[2],m=n&&q.childNodes[n];while(m=++n&&m&&m[p]||(t=n=0)||o.pop())if(1===m.nodeType&&++t&&m===b){k[a]=[w,n,t];break}}else if(s&&(m=b,l=m[u]||(m[u]={}),k=l[m.uniqueID]||(l[m.uniqueID]={}),j=k[a]||[],n=j[0]===w&&j[1],t=n),t===!1)while(m=++n&&m&&m[p]||(t=n=0)||o.pop())if((h?m.nodeName.toLowerCase()===r:1===m.nodeType)&&++t&&(s&&(l=m[u]||(m[u]={}),k=l[m.uniqueID]||(l[m.uniqueID]={}),k[a]=[w,t]),m===b))break;return t-=e,t===d||t%d===0&&t/d>=0}}},PSEUDO:function(a,b){var c,e=d.pseudos[a]||d.setFilters[a.toLowerCase()]||ga.error("unsupported pseudo: "+a);return e[u]?e(b):e.length>1?(c=[a,a,"",b],d.setFilters.hasOwnProperty(a.toLowerCase())?ia(function(a,c){var d,f=e(a,b),g=f.length;while(g--)d=I(a,f[g]),a[d]=!(c[d]=f[g])}):function(a){return e(a,0,c)}):e}},pseudos:{not:ia(function(a){var b=[],c=[],d=h(a.replace(P,"$1"));return d[u]?ia(function(a,b,c,e){var f,g=d(a,null,e,[]),h=a.length;while(h--)(f=g[h])&&(a[h]=!(b[h]=f))}):function(a,e,f){return b[0]=a,d(b,null,f,c),b[0]=null,!c.pop()}}),has:ia(function(a){return function(b){return ga(a,b).length>0}}),contains:ia(function(a){return a=a.replace(_,aa),function(b){return(b.textContent||b.innerText||e(b)).indexOf(a)>-1}}),lang:ia(function(a){return U.test(a||"")||ga.error("unsupported lang: "+a),a=a.replace(_,aa).toLowerCase(),function(b){var c;do if(c=p?b.lang:b.getAttribute("xml:lang")||b.getAttribute("lang"))return c=c.toLowerCase(),c===a||0===c.indexOf(a+"-");while((b=b.parentNode)&&1===b.nodeType);return!1}}),target:function(b){var c=a.location&&a.location.hash;return c&&c.slice(1)===b.id},root:function(a){return a===o},focus:function(a){return a===n.activeElement&&(!n.hasFocus||n.hasFocus())&&!!(a.type||a.href||~a.tabIndex)},enabled:oa(!1),disabled:oa(!0),checked:function(a){var b=a.nodeName.toLowerCase();return"input"===b&&!!a.checked||"option"===b&&!!a.selected},selected:function(a){return a.parentNode&&a.parentNode.selectedIndex,a.selected===!0},empty:function(a){for(a=a.firstChild;a;a=a.nextSibling)if(a.nodeType<6)return!1;return!0},parent:function(a){return!d.pseudos.empty(a)},header:function(a){return X.test(a.nodeName)},input:function(a){return W.test(a.nodeName)},button:function(a){var b=a.nodeName.toLowerCase();return"input"===b&&"button"===a.type||"button"===b},text:function(a){var b;return"input"===a.nodeName.toLowerCase()&&"text"===a.type&&(null==(b=a.getAttribute("type"))||"text"===b.toLowerCase())},first:pa(function(){return[0]}),last:pa(function(a,b){return[b-1]}),eq:pa(function(a,b,c){return[c<0?c+b:c]}),even:pa(function(a,b){for(var c=0;c<b;c+=2)a.push(c);return a}),odd:pa(function(a,b){for(var c=1;c<b;c+=2)a.push(c);return a}),lt:pa(function(a,b,c){for(var d=c<0?c+b:c;--d>=0;)a.push(d);return a}),gt:pa(function(a,b,c){for(var d=c<0?c+b:c;++d<b;)a.push(d);return a})}},d.pseudos.nth=d.pseudos.eq;for(b in{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})d.pseudos[b]=ma(b);for(b in{submit:!0,reset:!0})d.pseudos[b]=na(b);function ra(){}ra.prototype=d.filters=d.pseudos,d.setFilters=new ra,g=ga.tokenize=function(a,b){var c,e,f,g,h,i,j,k=z[a+" "];if(k)return b?0:k.slice(0);h=a,i=[],j=d.preFilter;while(h){c&&!(e=Q.exec(h))||(e&&(h=h.slice(e[0].length)||h),i.push(f=[])),c=!1,(e=R.exec(h))&&(c=e.shift(),f.push({value:c,type:e[0].replace(P," ")}),h=h.slice(c.length));for(g in d.filter)!(e=V[g].exec(h))||j[g]&&!(e=j[g](e))||(c=e.shift(),f.push({value:c,type:g,matches:e}),h=h.slice(c.length));if(!c)break}return b?h.length:h?ga.error(a):z(a,i).slice(0)};function sa(a){for(var b=0,c=a.length,d="";b<c;b++)d+=a[b].value;return d}function ta(a,b,c){var d=b.dir,e=b.next,f=e||d,g=c&&"parentNode"===f,h=x++;return b.first?function(b,c,e){while(b=b[d])if(1===b.nodeType||g)return a(b,c,e);return!1}:function(b,c,i){var j,k,l,m=[w,h];if(i){while(b=b[d])if((1===b.nodeType||g)&&a(b,c,i))return!0}else while(b=b[d])if(1===b.nodeType||g)if(l=b[u]||(b[u]={}),k=l[b.uniqueID]||(l[b.uniqueID]={}),e&&e===b.nodeName.toLowerCase())b=b[d]||b;else{if((j=k[f])&&j[0]===w&&j[1]===h)return m[2]=j[2];if(k[f]=m,m[2]=a(b,c,i))return!0}return!1}}function ua(a){return a.length>1?function(b,c,d){var e=a.length;while(e--)if(!a[e](b,c,d))return!1;return!0}:a[0]}function va(a,b,c){for(var d=0,e=b.length;d<e;d++)ga(a,b[d],c);return c}function wa(a,b,c,d,e){for(var f,g=[],h=0,i=a.length,j=null!=b;h<i;h++)(f=a[h])&&(c&&!c(f,d,e)||(g.push(f),j&&b.push(h)));return g}function xa(a,b,c,d,e,f){return d&&!d[u]&&(d=xa(d)),e&&!e[u]&&(e=xa(e,f)),ia(function(f,g,h,i){var j,k,l,m=[],n=[],o=g.length,p=f||va(b||"*",h.nodeType?[h]:h,[]),q=!a||!f&&b?p:wa(p,m,a,h,i),r=c?e||(f?a:o||d)?[]:g:q;if(c&&c(q,r,h,i),d){j=wa(r,n),d(j,[],h,i),k=j.length;while(k--)(l=j[k])&&(r[n[k]]=!(q[n[k]]=l))}if(f){if(e||a){if(e){j=[],k=r.length;while(k--)(l=r[k])&&j.push(q[k]=l);e(null,r=[],j,i)}k=r.length;while(k--)(l=r[k])&&(j=e?I(f,l):m[k])>-1&&(f[j]=!(g[j]=l))}}else r=wa(r===g?r.splice(o,r.length):r),e?e(null,g,r,i):G.apply(g,r)})}function ya(a){for(var b,c,e,f=a.length,g=d.relative[a[0].type],h=g||d.relative[" "],i=g?1:0,k=ta(function(a){return a===b},h,!0),l=ta(function(a){return I(b,a)>-1},h,!0),m=[function(a,c,d){var e=!g&&(d||c!==j)||((b=c).nodeType?k(a,c,d):l(a,c,d));return b=null,e}];i<f;i++)if(c=d.relative[a[i].type])m=[ta(ua(m),c)];else{if(c=d.filter[a[i].type].apply(null,a[i].matches),c[u]){for(e=++i;e<f;e++)if(d.relative[a[e].type])break;return xa(i>1&&ua(m),i>1&&sa(a.slice(0,i-1).concat({value:" "===a[i-2].type?"*":""})).replace(P,"$1"),c,i<e&&ya(a.slice(i,e)),e<f&&ya(a=a.slice(e)),e<f&&sa(a))}m.push(c)}return ua(m)}function za(a,b){var c=b.length>0,e=a.length>0,f=function(f,g,h,i,k){var l,o,q,r=0,s="0",t=f&&[],u=[],v=j,x=f||e&&d.find.TAG("*",k),y=w+=null==v?1:Math.random()||.1,z=x.length;for(k&&(j=g===n||g||k);s!==z&&null!=(l=x[s]);s++){if(e&&l){o=0,g||l.ownerDocument===n||(m(l),h=!p);while(q=a[o++])if(q(l,g||n,h)){i.push(l);break}k&&(w=y)}c&&((l=!q&&l)&&r--,f&&t.push(l))}if(r+=s,c&&s!==r){o=0;while(q=b[o++])q(t,u,g,h);if(f){if(r>0)while(s--)t[s]||u[s]||(u[s]=E.call(i));u=wa(u)}G.apply(i,u),k&&!f&&u.length>0&&r+b.length>1&&ga.uniqueSort(i)}return k&&(w=y,j=v),t};return c?ia(f):f}return h=ga.compile=function(a,b){var c,d=[],e=[],f=A[a+" "];if(!f){b||(b=g(a)),c=b.length;while(c--)f=ya(b[c]),f[u]?d.push(f):e.push(f);f=A(a,za(e,d)),f.selector=a}return f},i=ga.select=function(a,b,c,e){var f,i,j,k,l,m="function"==typeof a&&a,n=!e&&g(a=m.selector||a);if(c=c||[],1===n.length){if(i=n[0]=n[0].slice(0),i.length>2&&"ID"===(j=i[0]).type&&9===b.nodeType&&p&&d.relative[i[1].type]){if(b=(d.find.ID(j.matches[0].replace(_,aa),b)||[])[0],!b)return c;m&&(b=b.parentNode),a=a.slice(i.shift().value.length)}f=V.needsContext.test(a)?0:i.length;while(f--){if(j=i[f],d.relative[k=j.type])break;if((l=d.find[k])&&(e=l(j.matches[0].replace(_,aa),$.test(i[0].type)&&qa(b.parentNode)||b))){if(i.splice(f,1),a=e.length&&sa(i),!a)return G.apply(c,e),c;break}}}return(m||h(a,n))(e,b,!p,c,!b||$.test(a)&&qa(b.parentNode)||b),c},c.sortStable=u.split("").sort(B).join("")===u,c.detectDuplicates=!!l,m(),c.sortDetached=ja(function(a){return 1&a.compareDocumentPosition(n.createElement("fieldset"))}),ja(function(a){return a.innerHTML="<a href='#'></a>","#"===a.firstChild.getAttribute("href")})||ka("type|href|height|width",function(a,b,c){if(!c)return a.getAttribute(b,"type"===b.toLowerCase()?1:2)}),c.attributes&&ja(function(a){return a.innerHTML="<input/>",a.firstChild.setAttribute("value",""),""===a.firstChild.getAttribute("value")})||ka("value",function(a,b,c){if(!c&&"input"===a.nodeName.toLowerCase())return a.defaultValue}),ja(function(a){return null==a.getAttribute("disabled")})||ka(J,function(a,b,c){var d;if(!c)return a[b]===!0?b.toLowerCase():(d=a.getAttributeNode(b))&&d.specified?d.value:null}),ga}(a);r.find=x,r.expr=x.selectors,r.expr[":"]=r.expr.pseudos,r.uniqueSort=r.unique=x.uniqueSort,r.text=x.getText,r.isXMLDoc=x.isXML,r.contains=x.contains,r.escapeSelector=x.escape;var y=function(a,b,c){var d=[],e=void 0!==c;while((a=a[b])&&9!==a.nodeType)if(1===a.nodeType){if(e&&r(a).is(c))break;d.push(a)}return d},z=function(a,b){for(var c=[];a;a=a.nextSibling)1===a.nodeType&&a!==b&&c.push(a);return c},A=r.expr.match.needsContext,B=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i,C=/^.[^:#\[\.,]*$/;function D(a,b,c){return r.isFunction(b)?r.grep(a,function(a,d){return!!b.call(a,d,a)!==c}):b.nodeType?r.grep(a,function(a){return a===b!==c}):"string"!=typeof b?r.grep(a,function(a){return i.call(b,a)>-1!==c}):C.test(b)?r.filter(b,a,c):(b=r.filter(b,a),r.grep(a,function(a){return i.call(b,a)>-1!==c&&1===a.nodeType}))}r.filter=function(a,b,c){var d=b[0];return c&&(a=":not("+a+")"),1===b.length&&1===d.nodeType?r.find.matchesSelector(d,a)?[d]:[]:r.find.matches(a,r.grep(b,function(a){return 1===a.nodeType}))},r.fn.extend({find:function(a){var b,c,d=this.length,e=this;if("string"!=typeof a)return this.pushStack(r(a).filter(function(){for(b=0;b<d;b++)if(r.contains(e[b],this))return!0}));for(c=this.pushStack([]),b=0;b<d;b++)r.find(a,e[b],c);return d>1?r.uniqueSort(c):c},filter:function(a){return this.pushStack(D(this,a||[],!1))},not:function(a){return this.pushStack(D(this,a||[],!0))},is:function(a){return!!D(this,"string"==typeof a&&A.test(a)?r(a):a||[],!1).length}});var E,F=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/,G=r.fn.init=function(a,b,c){var e,f;if(!a)return this;if(c=c||E,"string"==typeof a){if(e="<"===a[0]&&">"===a[a.length-1]&&a.length>=3?[null,a,null]:F.exec(a),!e||!e[1]&&b)return!b||b.jquery?(b||c).find(a):this.constructor(b).find(a);if(e[1]){if(b=b instanceof r?b[0]:b,r.merge(this,r.parseHTML(e[1],b&&b.nodeType?b.ownerDocument||b:d,!0)),B.test(e[1])&&r.isPlainObject(b))for(e in b)r.isFunction(this[e])?this[e](b[e]):this.attr(e,b[e]);return this}return f=d.getElementById(e[2]),f&&(this[0]=f,this.length=1),this}return a.nodeType?(this[0]=a,this.length=1,this):r.isFunction(a)?void 0!==c.ready?c.ready(a):a(r):r.makeArray(a,this)};G.prototype=r.fn,E=r(d);var H=/^(?:parents|prev(?:Until|All))/,I={children:!0,contents:!0,next:!0,prev:!0};r.fn.extend({has:function(a){var b=r(a,this),c=b.length;return this.filter(function(){for(var a=0;a<c;a++)if(r.contains(this,b[a]))return!0})},closest:function(a,b){var c,d=0,e=this.length,f=[],g="string"!=typeof a&&r(a);if(!A.test(a))for(;d<e;d++)for(c=this[d];c&&c!==b;c=c.parentNode)if(c.nodeType<11&&(g?g.index(c)>-1:1===c.nodeType&&r.find.matchesSelector(c,a))){f.push(c);break}return this.pushStack(f.length>1?r.uniqueSort(f):f)},index:function(a){return a?"string"==typeof a?i.call(r(a),this[0]):i.call(this,a.jquery?a[0]:a):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(a,b){return this.pushStack(r.uniqueSort(r.merge(this.get(),r(a,b))))},addBack:function(a){return this.add(null==a?this.prevObject:this.prevObject.filter(a))}});function J(a,b){while((a=a[b])&&1!==a.nodeType);return a}r.each({parent:function(a){var b=a.parentNode;return b&&11!==b.nodeType?b:null},parents:function(a){return y(a,"parentNode")},parentsUntil:function(a,b,c){return y(a,"parentNode",c)},next:function(a){return J(a,"nextSibling")},prev:function(a){return J(a,"previousSibling")},nextAll:function(a){return y(a,"nextSibling")},prevAll:function(a){return y(a,"previousSibling")},nextUntil:function(a,b,c){return y(a,"nextSibling",c)},prevUntil:function(a,b,c){return y(a,"previousSibling",c)},siblings:function(a){return z((a.parentNode||{}).firstChild,a)},children:function(a){return z(a.firstChild)},contents:function(a){return a.contentDocument||r.merge([],a.childNodes)}},function(a,b){r.fn[a]=function(c,d){var e=r.map(this,b,c);return"Until"!==a.slice(-5)&&(d=c),d&&"string"==typeof d&&(e=r.filter(d,e)),this.length>1&&(I[a]||r.uniqueSort(e),H.test(a)&&e.reverse()),this.pushStack(e)}});var K=/[^\x20\t\r\n\f]+/g;function L(a){var b={};return r.each(a.match(K)||[],function(a,c){b[c]=!0}),b}r.Callbacks=function(a){a="string"==typeof a?L(a):r.extend({},a);var b,c,d,e,f=[],g=[],h=-1,i=function(){for(e=a.once,d=b=!0;g.length;h=-1){c=g.shift();while(++h<f.length)f[h].apply(c[0],c[1])===!1&&a.stopOnFalse&&(h=f.length,c=!1)}a.memory||(c=!1),b=!1,e&&(f=c?[]:"")},j={add:function(){return f&&(c&&!b&&(h=f.length-1,g.push(c)),function d(b){r.each(b,function(b,c){r.isFunction(c)?a.unique&&j.has(c)||f.push(c):c&&c.length&&"string"!==r.type(c)&&d(c)})}(arguments),c&&!b&&i()),this},remove:function(){return r.each(arguments,function(a,b){var c;while((c=r.inArray(b,f,c))>-1)f.splice(c,1),c<=h&&h--}),this},has:function(a){return a?r.inArray(a,f)>-1:f.length>0},empty:function(){return f&&(f=[]),this},disable:function(){return e=g=[],f=c="",this},disabled:function(){return!f},lock:function(){return e=g=[],c||b||(f=c=""),this},locked:function(){return!!e},fireWith:function(a,c){return e||(c=c||[],c=[a,c.slice?c.slice():c],g.push(c),b||i()),this},fire:function(){return j.fireWith(this,arguments),this},fired:function(){return!!d}};return j};function M(a){return a}function N(a){throw a}function O(a,b,c){var d;try{a&&r.isFunction(d=a.promise)?d.call(a).done(b).fail(c):a&&r.isFunction(d=a.then)?d.call(a,b,c):b.call(void 0,a)}catch(a){c.call(void 0,a)}}r.extend({Deferred:function(b){var c=[["notify","progress",r.Callbacks("memory"),r.Callbacks("memory"),2],["resolve","done",r.Callbacks("once memory"),r.Callbacks("once memory"),0,"resolved"],["reject","fail",r.Callbacks("once memory"),r.Callbacks("once memory"),1,"rejected"]],d="pending",e={state:function(){return d},always:function(){return f.done(arguments).fail(arguments),this},"catch":function(a){return e.then(null,a)},pipe:function(){var a=arguments;return r.Deferred(function(b){r.each(c,function(c,d){var e=r.isFunction(a[d[4]])&&a[d[4]];f[d[1]](function(){var a=e&&e.apply(this,arguments);a&&r.isFunction(a.promise)?a.promise().progress(b.notify).done(b.resolve).fail(b.reject):b[d[0]+"With"](this,e?[a]:arguments)})}),a=null}).promise()},then:function(b,d,e){var f=0;function g(b,c,d,e){return function(){var h=this,i=arguments,j=function(){var a,j;if(!(b<f)){if(a=d.apply(h,i),a===c.promise())throw new TypeError("Thenable self-resolution");j=a&&("object"==typeof a||"function"==typeof a)&&a.then,r.isFunction(j)?e?j.call(a,g(f,c,M,e),g(f,c,N,e)):(f++,j.call(a,g(f,c,M,e),g(f,c,N,e),g(f,c,M,c.notifyWith))):(d!==M&&(h=void 0,i=[a]),(e||c.resolveWith)(h,i))}},k=e?j:function(){try{j()}catch(a){r.Deferred.exceptionHook&&r.Deferred.exceptionHook(a,k.stackTrace),b+1>=f&&(d!==N&&(h=void 0,i=[a]),c.rejectWith(h,i))}};b?k():(r.Deferred.getStackHook&&(k.stackTrace=r.Deferred.getStackHook()),a.setTimeout(k))}}return r.Deferred(function(a){c[0][3].add(g(0,a,r.isFunction(e)?e:M,a.notifyWith)),c[1][3].add(g(0,a,r.isFunction(b)?b:M)),c[2][3].add(g(0,a,r.isFunction(d)?d:N))}).promise()},promise:function(a){return null!=a?r.extend(a,e):e}},f={};return r.each(c,function(a,b){var g=b[2],h=b[5];e[b[1]]=g.add,h&&g.add(function(){d=h},c[3-a][2].disable,c[0][2].lock),g.add(b[3].fire),f[b[0]]=function(){return f[b[0]+"With"](this===f?void 0:this,arguments),this},f[b[0]+"With"]=g.fireWith}),e.promise(f),b&&b.call(f,f),f},when:function(a){var b=arguments.length,c=b,d=Array(c),e=f.call(arguments),g=r.Deferred(),h=function(a){return function(c){d[a]=this,e[a]=arguments.length>1?f.call(arguments):c,--b||g.resolveWith(d,e)}};if(b<=1&&(O(a,g.done(h(c)).resolve,g.reject),"pending"===g.state()||r.isFunction(e[c]&&e[c].then)))return g.then();while(c--)O(e[c],h(c),g.reject);return g.promise()}});var P=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;r.Deferred.exceptionHook=function(b,c){a.console&&a.console.warn&&b&&P.test(b.name)&&a.console.warn("jQuery.Deferred exception: "+b.message,b.stack,c)},r.readyException=function(b){a.setTimeout(function(){throw b})};var Q=r.Deferred();r.fn.ready=function(a){return Q.then(a)["catch"](function(a){r.readyException(a)}),this},r.extend({isReady:!1,readyWait:1,holdReady:function(a){a?r.readyWait++:r.ready(!0)},ready:function(a){(a===!0?--r.readyWait:r.isReady)||(r.isReady=!0,a!==!0&&--r.readyWait>0||Q.resolveWith(d,[r]))}}),r.ready.then=Q.then;function R(){d.removeEventListener("DOMContentLoaded",R),
a.removeEventListener("load",R),r.ready()}"complete"===d.readyState||"loading"!==d.readyState&&!d.documentElement.doScroll?a.setTimeout(r.ready):(d.addEventListener("DOMContentLoaded",R),a.addEventListener("load",R));var S=function(a,b,c,d,e,f,g){var h=0,i=a.length,j=null==c;if("object"===r.type(c)){e=!0;for(h in c)S(a,b,h,c[h],!0,f,g)}else if(void 0!==d&&(e=!0,r.isFunction(d)||(g=!0),j&&(g?(b.call(a,d),b=null):(j=b,b=function(a,b,c){return j.call(r(a),c)})),b))for(;h<i;h++)b(a[h],c,g?d:d.call(a[h],h,b(a[h],c)));return e?a:j?b.call(a):i?b(a[0],c):f},T=function(a){return 1===a.nodeType||9===a.nodeType||!+a.nodeType};function U(){this.expando=r.expando+U.uid++}U.uid=1,U.prototype={cache:function(a){var b=a[this.expando];return b||(b={},T(a)&&(a.nodeType?a[this.expando]=b:Object.defineProperty(a,this.expando,{value:b,configurable:!0}))),b},set:function(a,b,c){var d,e=this.cache(a);if("string"==typeof b)e[r.camelCase(b)]=c;else for(d in b)e[r.camelCase(d)]=b[d];return e},get:function(a,b){return void 0===b?this.cache(a):a[this.expando]&&a[this.expando][r.camelCase(b)]},access:function(a,b,c){return void 0===b||b&&"string"==typeof b&&void 0===c?this.get(a,b):(this.set(a,b,c),void 0!==c?c:b)},remove:function(a,b){var c,d=a[this.expando];if(void 0!==d){if(void 0!==b){r.isArray(b)?b=b.map(r.camelCase):(b=r.camelCase(b),b=b in d?[b]:b.match(K)||[]),c=b.length;while(c--)delete d[b[c]]}(void 0===b||r.isEmptyObject(d))&&(a.nodeType?a[this.expando]=void 0:delete a[this.expando])}},hasData:function(a){var b=a[this.expando];return void 0!==b&&!r.isEmptyObject(b)}};var V=new U,W=new U,X=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,Y=/[A-Z]/g;function Z(a){return"true"===a||"false"!==a&&("null"===a?null:a===+a+""?+a:X.test(a)?JSON.parse(a):a)}function $(a,b,c){var d;if(void 0===c&&1===a.nodeType)if(d="data-"+b.replace(Y,"-$&").toLowerCase(),c=a.getAttribute(d),"string"==typeof c){try{c=Z(c)}catch(e){}W.set(a,b,c)}else c=void 0;return c}r.extend({hasData:function(a){return W.hasData(a)||V.hasData(a)},data:function(a,b,c){return W.access(a,b,c)},removeData:function(a,b){W.remove(a,b)},_data:function(a,b,c){return V.access(a,b,c)},_removeData:function(a,b){V.remove(a,b)}}),r.fn.extend({data:function(a,b){var c,d,e,f=this[0],g=f&&f.attributes;if(void 0===a){if(this.length&&(e=W.get(f),1===f.nodeType&&!V.get(f,"hasDataAttrs"))){c=g.length;while(c--)g[c]&&(d=g[c].name,0===d.indexOf("data-")&&(d=r.camelCase(d.slice(5)),$(f,d,e[d])));V.set(f,"hasDataAttrs",!0)}return e}return"object"==typeof a?this.each(function(){W.set(this,a)}):S(this,function(b){var c;if(f&&void 0===b){if(c=W.get(f,a),void 0!==c)return c;if(c=$(f,a),void 0!==c)return c}else this.each(function(){W.set(this,a,b)})},null,b,arguments.length>1,null,!0)},removeData:function(a){return this.each(function(){W.remove(this,a)})}}),r.extend({queue:function(a,b,c){var d;if(a)return b=(b||"fx")+"queue",d=V.get(a,b),c&&(!d||r.isArray(c)?d=V.access(a,b,r.makeArray(c)):d.push(c)),d||[]},dequeue:function(a,b){b=b||"fx";var c=r.queue(a,b),d=c.length,e=c.shift(),f=r._queueHooks(a,b),g=function(){r.dequeue(a,b)};"inprogress"===e&&(e=c.shift(),d--),e&&("fx"===b&&c.unshift("inprogress"),delete f.stop,e.call(a,g,f)),!d&&f&&f.empty.fire()},_queueHooks:function(a,b){var c=b+"queueHooks";return V.get(a,c)||V.access(a,c,{empty:r.Callbacks("once memory").add(function(){V.remove(a,[b+"queue",c])})})}}),r.fn.extend({queue:function(a,b){var c=2;return"string"!=typeof a&&(b=a,a="fx",c--),arguments.length<c?r.queue(this[0],a):void 0===b?this:this.each(function(){var c=r.queue(this,a,b);r._queueHooks(this,a),"fx"===a&&"inprogress"!==c[0]&&r.dequeue(this,a)})},dequeue:function(a){return this.each(function(){r.dequeue(this,a)})},clearQueue:function(a){return this.queue(a||"fx",[])},promise:function(a,b){var c,d=1,e=r.Deferred(),f=this,g=this.length,h=function(){--d||e.resolveWith(f,[f])};"string"!=typeof a&&(b=a,a=void 0),a=a||"fx";while(g--)c=V.get(f[g],a+"queueHooks"),c&&c.empty&&(d++,c.empty.add(h));return h(),e.promise(b)}});var _=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,aa=new RegExp("^(?:([+-])=|)("+_+")([a-z%]*)$","i"),ba=["Top","Right","Bottom","Left"],ca=function(a,b){return a=b||a,"none"===a.style.display||""===a.style.display&&r.contains(a.ownerDocument,a)&&"none"===r.css(a,"display")},da=function(a,b,c,d){var e,f,g={};for(f in b)g[f]=a.style[f],a.style[f]=b[f];e=c.apply(a,d||[]);for(f in b)a.style[f]=g[f];return e};function ea(a,b,c,d){var e,f=1,g=20,h=d?function(){return d.cur()}:function(){return r.css(a,b,"")},i=h(),j=c&&c[3]||(r.cssNumber[b]?"":"px"),k=(r.cssNumber[b]||"px"!==j&&+i)&&aa.exec(r.css(a,b));if(k&&k[3]!==j){j=j||k[3],c=c||[],k=+i||1;do f=f||".5",k/=f,r.style(a,b,k+j);while(f!==(f=h()/i)&&1!==f&&--g)}return c&&(k=+k||+i||0,e=c[1]?k+(c[1]+1)*c[2]:+c[2],d&&(d.unit=j,d.start=k,d.end=e)),e}var fa={};function ga(a){var b,c=a.ownerDocument,d=a.nodeName,e=fa[d];return e?e:(b=c.body.appendChild(c.createElement(d)),e=r.css(b,"display"),b.parentNode.removeChild(b),"none"===e&&(e="block"),fa[d]=e,e)}function ha(a,b){for(var c,d,e=[],f=0,g=a.length;f<g;f++)d=a[f],d.style&&(c=d.style.display,b?("none"===c&&(e[f]=V.get(d,"display")||null,e[f]||(d.style.display="")),""===d.style.display&&ca(d)&&(e[f]=ga(d))):"none"!==c&&(e[f]="none",V.set(d,"display",c)));for(f=0;f<g;f++)null!=e[f]&&(a[f].style.display=e[f]);return a}r.fn.extend({show:function(){return ha(this,!0)},hide:function(){return ha(this)},toggle:function(a){return"boolean"==typeof a?a?this.show():this.hide():this.each(function(){ca(this)?r(this).show():r(this).hide()})}});var ia=/^(?:checkbox|radio)$/i,ja=/<([a-z][^\/\0>\x20\t\r\n\f]+)/i,ka=/^$|\/(?:java|ecma)script/i,la={option:[1,"<select multiple='multiple'>","</select>"],thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};la.optgroup=la.option,la.tbody=la.tfoot=la.colgroup=la.caption=la.thead,la.th=la.td;function ma(a,b){var c;return c="undefined"!=typeof a.getElementsByTagName?a.getElementsByTagName(b||"*"):"undefined"!=typeof a.querySelectorAll?a.querySelectorAll(b||"*"):[],void 0===b||b&&r.nodeName(a,b)?r.merge([a],c):c}function na(a,b){for(var c=0,d=a.length;c<d;c++)V.set(a[c],"globalEval",!b||V.get(b[c],"globalEval"))}var oa=/<|&#?\w+;/;function pa(a,b,c,d,e){for(var f,g,h,i,j,k,l=b.createDocumentFragment(),m=[],n=0,o=a.length;n<o;n++)if(f=a[n],f||0===f)if("object"===r.type(f))r.merge(m,f.nodeType?[f]:f);else if(oa.test(f)){g=g||l.appendChild(b.createElement("div")),h=(ja.exec(f)||["",""])[1].toLowerCase(),i=la[h]||la._default,g.innerHTML=i[1]+r.htmlPrefilter(f)+i[2],k=i[0];while(k--)g=g.lastChild;r.merge(m,g.childNodes),g=l.firstChild,g.textContent=""}else m.push(b.createTextNode(f));l.textContent="",n=0;while(f=m[n++])if(d&&r.inArray(f,d)>-1)e&&e.push(f);else if(j=r.contains(f.ownerDocument,f),g=ma(l.appendChild(f),"script"),j&&na(g),c){k=0;while(f=g[k++])ka.test(f.type||"")&&c.push(f)}return l}!function(){var a=d.createDocumentFragment(),b=a.appendChild(d.createElement("div")),c=d.createElement("input");c.setAttribute("type","radio"),c.setAttribute("checked","checked"),c.setAttribute("name","t"),b.appendChild(c),o.checkClone=b.cloneNode(!0).cloneNode(!0).lastChild.checked,b.innerHTML="<textarea>x</textarea>",o.noCloneChecked=!!b.cloneNode(!0).lastChild.defaultValue}();var qa=d.documentElement,ra=/^key/,sa=/^(?:mouse|pointer|contextmenu|drag|drop)|click/,ta=/^([^.]*)(?:\.(.+)|)/;function ua(){return!0}function va(){return!1}function wa(){try{return d.activeElement}catch(a){}}function xa(a,b,c,d,e,f){var g,h;if("object"==typeof b){"string"!=typeof c&&(d=d||c,c=void 0);for(h in b)xa(a,h,c,d,b[h],f);return a}if(null==d&&null==e?(e=c,d=c=void 0):null==e&&("string"==typeof c?(e=d,d=void 0):(e=d,d=c,c=void 0)),e===!1)e=va;else if(!e)return a;return 1===f&&(g=e,e=function(a){return r().off(a),g.apply(this,arguments)},e.guid=g.guid||(g.guid=r.guid++)),a.each(function(){r.event.add(this,b,e,d,c)})}r.event={global:{},add:function(a,b,c,d,e){var f,g,h,i,j,k,l,m,n,o,p,q=V.get(a);if(q){c.handler&&(f=c,c=f.handler,e=f.selector),e&&r.find.matchesSelector(qa,e),c.guid||(c.guid=r.guid++),(i=q.events)||(i=q.events={}),(g=q.handle)||(g=q.handle=function(b){return"undefined"!=typeof r&&r.event.triggered!==b.type?r.event.dispatch.apply(a,arguments):void 0}),b=(b||"").match(K)||[""],j=b.length;while(j--)h=ta.exec(b[j])||[],n=p=h[1],o=(h[2]||"").split(".").sort(),n&&(l=r.event.special[n]||{},n=(e?l.delegateType:l.bindType)||n,l=r.event.special[n]||{},k=r.extend({type:n,origType:p,data:d,handler:c,guid:c.guid,selector:e,needsContext:e&&r.expr.match.needsContext.test(e),namespace:o.join(".")},f),(m=i[n])||(m=i[n]=[],m.delegateCount=0,l.setup&&l.setup.call(a,d,o,g)!==!1||a.addEventListener&&a.addEventListener(n,g)),l.add&&(l.add.call(a,k),k.handler.guid||(k.handler.guid=c.guid)),e?m.splice(m.delegateCount++,0,k):m.push(k),r.event.global[n]=!0)}},remove:function(a,b,c,d,e){var f,g,h,i,j,k,l,m,n,o,p,q=V.hasData(a)&&V.get(a);if(q&&(i=q.events)){b=(b||"").match(K)||[""],j=b.length;while(j--)if(h=ta.exec(b[j])||[],n=p=h[1],o=(h[2]||"").split(".").sort(),n){l=r.event.special[n]||{},n=(d?l.delegateType:l.bindType)||n,m=i[n]||[],h=h[2]&&new RegExp("(^|\\.)"+o.join("\\.(?:.*\\.|)")+"(\\.|$)"),g=f=m.length;while(f--)k=m[f],!e&&p!==k.origType||c&&c.guid!==k.guid||h&&!h.test(k.namespace)||d&&d!==k.selector&&("**"!==d||!k.selector)||(m.splice(f,1),k.selector&&m.delegateCount--,l.remove&&l.remove.call(a,k));g&&!m.length&&(l.teardown&&l.teardown.call(a,o,q.handle)!==!1||r.removeEvent(a,n,q.handle),delete i[n])}else for(n in i)r.event.remove(a,n+b[j],c,d,!0);r.isEmptyObject(i)&&V.remove(a,"handle events")}},dispatch:function(a){var b=r.event.fix(a),c,d,e,f,g,h,i=new Array(arguments.length),j=(V.get(this,"events")||{})[b.type]||[],k=r.event.special[b.type]||{};for(i[0]=b,c=1;c<arguments.length;c++)i[c]=arguments[c];if(b.delegateTarget=this,!k.preDispatch||k.preDispatch.call(this,b)!==!1){h=r.event.handlers.call(this,b,j),c=0;while((f=h[c++])&&!b.isPropagationStopped()){b.currentTarget=f.elem,d=0;while((g=f.handlers[d++])&&!b.isImmediatePropagationStopped())b.rnamespace&&!b.rnamespace.test(g.namespace)||(b.handleObj=g,b.data=g.data,e=((r.event.special[g.origType]||{}).handle||g.handler).apply(f.elem,i),void 0!==e&&(b.result=e)===!1&&(b.preventDefault(),b.stopPropagation()))}return k.postDispatch&&k.postDispatch.call(this,b),b.result}},handlers:function(a,b){var c,d,e,f,g,h=[],i=b.delegateCount,j=a.target;if(i&&j.nodeType&&!("click"===a.type&&a.button>=1))for(;j!==this;j=j.parentNode||this)if(1===j.nodeType&&("click"!==a.type||j.disabled!==!0)){for(f=[],g={},c=0;c<i;c++)d=b[c],e=d.selector+" ",void 0===g[e]&&(g[e]=d.needsContext?r(e,this).index(j)>-1:r.find(e,this,null,[j]).length),g[e]&&f.push(d);f.length&&h.push({elem:j,handlers:f})}return j=this,i<b.length&&h.push({elem:j,handlers:b.slice(i)}),h},addProp:function(a,b){Object.defineProperty(r.Event.prototype,a,{enumerable:!0,configurable:!0,get:r.isFunction(b)?function(){if(this.originalEvent)return b(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[a]},set:function(b){Object.defineProperty(this,a,{enumerable:!0,configurable:!0,writable:!0,value:b})}})},fix:function(a){return a[r.expando]?a:new r.Event(a)},special:{load:{noBubble:!0},focus:{trigger:function(){if(this!==wa()&&this.focus)return this.focus(),!1},delegateType:"focusin"},blur:{trigger:function(){if(this===wa()&&this.blur)return this.blur(),!1},delegateType:"focusout"},click:{trigger:function(){if("checkbox"===this.type&&this.click&&r.nodeName(this,"input"))return this.click(),!1},_default:function(a){return r.nodeName(a.target,"a")}},beforeunload:{postDispatch:function(a){void 0!==a.result&&a.originalEvent&&(a.originalEvent.returnValue=a.result)}}}},r.removeEvent=function(a,b,c){a.removeEventListener&&a.removeEventListener(b,c)},r.Event=function(a,b){return this instanceof r.Event?(a&&a.type?(this.originalEvent=a,this.type=a.type,this.isDefaultPrevented=a.defaultPrevented||void 0===a.defaultPrevented&&a.returnValue===!1?ua:va,this.target=a.target&&3===a.target.nodeType?a.target.parentNode:a.target,this.currentTarget=a.currentTarget,this.relatedTarget=a.relatedTarget):this.type=a,b&&r.extend(this,b),this.timeStamp=a&&a.timeStamp||r.now(),void(this[r.expando]=!0)):new r.Event(a,b)},r.Event.prototype={constructor:r.Event,isDefaultPrevented:va,isPropagationStopped:va,isImmediatePropagationStopped:va,isSimulated:!1,preventDefault:function(){var a=this.originalEvent;this.isDefaultPrevented=ua,a&&!this.isSimulated&&a.preventDefault()},stopPropagation:function(){var a=this.originalEvent;this.isPropagationStopped=ua,a&&!this.isSimulated&&a.stopPropagation()},stopImmediatePropagation:function(){var a=this.originalEvent;this.isImmediatePropagationStopped=ua,a&&!this.isSimulated&&a.stopImmediatePropagation(),this.stopPropagation()}},r.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,"char":!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:function(a){var b=a.button;return null==a.which&&ra.test(a.type)?null!=a.charCode?a.charCode:a.keyCode:!a.which&&void 0!==b&&sa.test(a.type)?1&b?1:2&b?3:4&b?2:0:a.which}},r.event.addProp),r.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(a,b){r.event.special[a]={delegateType:b,bindType:b,handle:function(a){var c,d=this,e=a.relatedTarget,f=a.handleObj;return e&&(e===d||r.contains(d,e))||(a.type=f.origType,c=f.handler.apply(this,arguments),a.type=b),c}}}),r.fn.extend({on:function(a,b,c,d){return xa(this,a,b,c,d)},one:function(a,b,c,d){return xa(this,a,b,c,d,1)},off:function(a,b,c){var d,e;if(a&&a.preventDefault&&a.handleObj)return d=a.handleObj,r(a.delegateTarget).off(d.namespace?d.origType+"."+d.namespace:d.origType,d.selector,d.handler),this;if("object"==typeof a){for(e in a)this.off(e,b,a[e]);return this}return b!==!1&&"function"!=typeof b||(c=b,b=void 0),c===!1&&(c=va),this.each(function(){r.event.remove(this,a,c,b)})}});var ya=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi,za=/<script|<style|<link/i,Aa=/checked\s*(?:[^=]|=\s*.checked.)/i,Ba=/^true\/(.*)/,Ca=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;function Da(a,b){return r.nodeName(a,"table")&&r.nodeName(11!==b.nodeType?b:b.firstChild,"tr")?a.getElementsByTagName("tbody")[0]||a:a}function Ea(a){return a.type=(null!==a.getAttribute("type"))+"/"+a.type,a}function Fa(a){var b=Ba.exec(a.type);return b?a.type=b[1]:a.removeAttribute("type"),a}function Ga(a,b){var c,d,e,f,g,h,i,j;if(1===b.nodeType){if(V.hasData(a)&&(f=V.access(a),g=V.set(b,f),j=f.events)){delete g.handle,g.events={};for(e in j)for(c=0,d=j[e].length;c<d;c++)r.event.add(b,e,j[e][c])}W.hasData(a)&&(h=W.access(a),i=r.extend({},h),W.set(b,i))}}function Ha(a,b){var c=b.nodeName.toLowerCase();"input"===c&&ia.test(a.type)?b.checked=a.checked:"input"!==c&&"textarea"!==c||(b.defaultValue=a.defaultValue)}function Ia(a,b,c,d){b=g.apply([],b);var e,f,h,i,j,k,l=0,m=a.length,n=m-1,q=b[0],s=r.isFunction(q);if(s||m>1&&"string"==typeof q&&!o.checkClone&&Aa.test(q))return a.each(function(e){var f=a.eq(e);s&&(b[0]=q.call(this,e,f.html())),Ia(f,b,c,d)});if(m&&(e=pa(b,a[0].ownerDocument,!1,a,d),f=e.firstChild,1===e.childNodes.length&&(e=f),f||d)){for(h=r.map(ma(e,"script"),Ea),i=h.length;l<m;l++)j=e,l!==n&&(j=r.clone(j,!0,!0),i&&r.merge(h,ma(j,"script"))),c.call(a[l],j,l);if(i)for(k=h[h.length-1].ownerDocument,r.map(h,Fa),l=0;l<i;l++)j=h[l],ka.test(j.type||"")&&!V.access(j,"globalEval")&&r.contains(k,j)&&(j.src?r._evalUrl&&r._evalUrl(j.src):p(j.textContent.replace(Ca,""),k))}return a}function Ja(a,b,c){for(var d,e=b?r.filter(b,a):a,f=0;null!=(d=e[f]);f++)c||1!==d.nodeType||r.cleanData(ma(d)),d.parentNode&&(c&&r.contains(d.ownerDocument,d)&&na(ma(d,"script")),d.parentNode.removeChild(d));return a}r.extend({htmlPrefilter:function(a){return a.replace(ya,"<$1></$2>")},clone:function(a,b,c){var d,e,f,g,h=a.cloneNode(!0),i=r.contains(a.ownerDocument,a);if(!(o.noCloneChecked||1!==a.nodeType&&11!==a.nodeType||r.isXMLDoc(a)))for(g=ma(h),f=ma(a),d=0,e=f.length;d<e;d++)Ha(f[d],g[d]);if(b)if(c)for(f=f||ma(a),g=g||ma(h),d=0,e=f.length;d<e;d++)Ga(f[d],g[d]);else Ga(a,h);return g=ma(h,"script"),g.length>0&&na(g,!i&&ma(a,"script")),h},cleanData:function(a){for(var b,c,d,e=r.event.special,f=0;void 0!==(c=a[f]);f++)if(T(c)){if(b=c[V.expando]){if(b.events)for(d in b.events)e[d]?r.event.remove(c,d):r.removeEvent(c,d,b.handle);c[V.expando]=void 0}c[W.expando]&&(c[W.expando]=void 0)}}}),r.fn.extend({detach:function(a){return Ja(this,a,!0)},remove:function(a){return Ja(this,a)},text:function(a){return S(this,function(a){return void 0===a?r.text(this):this.empty().each(function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=a)})},null,a,arguments.length)},append:function(){return Ia(this,arguments,function(a){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var b=Da(this,a);b.appendChild(a)}})},prepend:function(){return Ia(this,arguments,function(a){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var b=Da(this,a);b.insertBefore(a,b.firstChild)}})},before:function(){return Ia(this,arguments,function(a){this.parentNode&&this.parentNode.insertBefore(a,this)})},after:function(){return Ia(this,arguments,function(a){this.parentNode&&this.parentNode.insertBefore(a,this.nextSibling)})},empty:function(){for(var a,b=0;null!=(a=this[b]);b++)1===a.nodeType&&(r.cleanData(ma(a,!1)),a.textContent="");return this},clone:function(a,b){return a=null!=a&&a,b=null==b?a:b,this.map(function(){return r.clone(this,a,b)})},html:function(a){return S(this,function(a){var b=this[0]||{},c=0,d=this.length;if(void 0===a&&1===b.nodeType)return b.innerHTML;if("string"==typeof a&&!za.test(a)&&!la[(ja.exec(a)||["",""])[1].toLowerCase()]){a=r.htmlPrefilter(a);try{for(;c<d;c++)b=this[c]||{},1===b.nodeType&&(r.cleanData(ma(b,!1)),b.innerHTML=a);b=0}catch(e){}}b&&this.empty().append(a)},null,a,arguments.length)},replaceWith:function(){var a=[];return Ia(this,arguments,function(b){var c=this.parentNode;r.inArray(this,a)<0&&(r.cleanData(ma(this)),c&&c.replaceChild(b,this))},a)}}),r.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(a,b){r.fn[a]=function(a){for(var c,d=[],e=r(a),f=e.length-1,g=0;g<=f;g++)c=g===f?this:this.clone(!0),r(e[g])[b](c),h.apply(d,c.get());return this.pushStack(d)}});var Ka=/^margin/,La=new RegExp("^("+_+")(?!px)[a-z%]+$","i"),Ma=function(b){var c=b.ownerDocument.defaultView;return c&&c.opener||(c=a),c.getComputedStyle(b)};!function(){function b(){if(i){i.style.cssText="box-sizing:border-box;position:relative;display:block;margin:auto;border:1px;padding:1px;top:1%;width:50%",i.innerHTML="",qa.appendChild(h);var b=a.getComputedStyle(i);c="1%"!==b.top,g="2px"===b.marginLeft,e="4px"===b.width,i.style.marginRight="50%",f="4px"===b.marginRight,qa.removeChild(h),i=null}}var c,e,f,g,h=d.createElement("div"),i=d.createElement("div");i.style&&(i.style.backgroundClip="content-box",i.cloneNode(!0).style.backgroundClip="",o.clearCloneStyle="content-box"===i.style.backgroundClip,h.style.cssText="border:0;width:8px;height:0;top:0;left:-9999px;padding:0;margin-top:1px;position:absolute",h.appendChild(i),r.extend(o,{pixelPosition:function(){return b(),c},boxSizingReliable:function(){return b(),e},pixelMarginRight:function(){return b(),f},reliableMarginLeft:function(){return b(),g}}))}();function Na(a,b,c){var d,e,f,g,h=a.style;return c=c||Ma(a),c&&(g=c.getPropertyValue(b)||c[b],""!==g||r.contains(a.ownerDocument,a)||(g=r.style(a,b)),!o.pixelMarginRight()&&La.test(g)&&Ka.test(b)&&(d=h.width,e=h.minWidth,f=h.maxWidth,h.minWidth=h.maxWidth=h.width=g,g=c.width,h.width=d,h.minWidth=e,h.maxWidth=f)),void 0!==g?g+"":g}function Oa(a,b){return{get:function(){return a()?void delete this.get:(this.get=b).apply(this,arguments)}}}var Pa=/^(none|table(?!-c[ea]).+)/,Qa={position:"absolute",visibility:"hidden",display:"block"},Ra={letterSpacing:"0",fontWeight:"400"},Sa=["Webkit","Moz","ms"],Ta=d.createElement("div").style;function Ua(a){if(a in Ta)return a;var b=a[0].toUpperCase()+a.slice(1),c=Sa.length;while(c--)if(a=Sa[c]+b,a in Ta)return a}function Va(a,b,c){var d=aa.exec(b);return d?Math.max(0,d[2]-(c||0))+(d[3]||"px"):b}function Wa(a,b,c,d,e){var f,g=0;for(f=c===(d?"border":"content")?4:"width"===b?1:0;f<4;f+=2)"margin"===c&&(g+=r.css(a,c+ba[f],!0,e)),d?("content"===c&&(g-=r.css(a,"padding"+ba[f],!0,e)),"margin"!==c&&(g-=r.css(a,"border"+ba[f]+"Width",!0,e))):(g+=r.css(a,"padding"+ba[f],!0,e),"padding"!==c&&(g+=r.css(a,"border"+ba[f]+"Width",!0,e)));return g}function Xa(a,b,c){var d,e=!0,f=Ma(a),g="border-box"===r.css(a,"boxSizing",!1,f);if(a.getClientRects().length&&(d=a.getBoundingClientRect()[b]),d<=0||null==d){if(d=Na(a,b,f),(d<0||null==d)&&(d=a.style[b]),La.test(d))return d;e=g&&(o.boxSizingReliable()||d===a.style[b]),d=parseFloat(d)||0}return d+Wa(a,b,c||(g?"border":"content"),e,f)+"px"}r.extend({cssHooks:{opacity:{get:function(a,b){if(b){var c=Na(a,"opacity");return""===c?"1":c}}}},cssNumber:{animationIterationCount:!0,columnCount:!0,fillOpacity:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{"float":"cssFloat"},style:function(a,b,c,d){if(a&&3!==a.nodeType&&8!==a.nodeType&&a.style){var e,f,g,h=r.camelCase(b),i=a.style;return b=r.cssProps[h]||(r.cssProps[h]=Ua(h)||h),g=r.cssHooks[b]||r.cssHooks[h],void 0===c?g&&"get"in g&&void 0!==(e=g.get(a,!1,d))?e:i[b]:(f=typeof c,"string"===f&&(e=aa.exec(c))&&e[1]&&(c=ea(a,b,e),f="number"),null!=c&&c===c&&("number"===f&&(c+=e&&e[3]||(r.cssNumber[h]?"":"px")),o.clearCloneStyle||""!==c||0!==b.indexOf("background")||(i[b]="inherit"),g&&"set"in g&&void 0===(c=g.set(a,c,d))||(i[b]=c)),void 0)}},css:function(a,b,c,d){var e,f,g,h=r.camelCase(b);return b=r.cssProps[h]||(r.cssProps[h]=Ua(h)||h),g=r.cssHooks[b]||r.cssHooks[h],g&&"get"in g&&(e=g.get(a,!0,c)),void 0===e&&(e=Na(a,b,d)),"normal"===e&&b in Ra&&(e=Ra[b]),""===c||c?(f=parseFloat(e),c===!0||isFinite(f)?f||0:e):e}}),r.each(["height","width"],function(a,b){r.cssHooks[b]={get:function(a,c,d){if(c)return!Pa.test(r.css(a,"display"))||a.getClientRects().length&&a.getBoundingClientRect().width?Xa(a,b,d):da(a,Qa,function(){return Xa(a,b,d)})},set:function(a,c,d){var e,f=d&&Ma(a),g=d&&Wa(a,b,d,"border-box"===r.css(a,"boxSizing",!1,f),f);return g&&(e=aa.exec(c))&&"px"!==(e[3]||"px")&&(a.style[b]=c,c=r.css(a,b)),Va(a,c,g)}}}),r.cssHooks.marginLeft=Oa(o.reliableMarginLeft,function(a,b){if(b)return(parseFloat(Na(a,"marginLeft"))||a.getBoundingClientRect().left-da(a,{marginLeft:0},function(){return a.getBoundingClientRect().left}))+"px"}),r.each({margin:"",padding:"",border:"Width"},function(a,b){r.cssHooks[a+b]={expand:function(c){for(var d=0,e={},f="string"==typeof c?c.split(" "):[c];d<4;d++)e[a+ba[d]+b]=f[d]||f[d-2]||f[0];return e}},Ka.test(a)||(r.cssHooks[a+b].set=Va)}),r.fn.extend({css:function(a,b){return S(this,function(a,b,c){var d,e,f={},g=0;if(r.isArray(b)){for(d=Ma(a),e=b.length;g<e;g++)f[b[g]]=r.css(a,b[g],!1,d);return f}return void 0!==c?r.style(a,b,c):r.css(a,b)},a,b,arguments.length>1)}});function Ya(a,b,c,d,e){return new Ya.prototype.init(a,b,c,d,e)}r.Tween=Ya,Ya.prototype={constructor:Ya,init:function(a,b,c,d,e,f){this.elem=a,this.prop=c,this.easing=e||r.easing._default,this.options=b,this.start=this.now=this.cur(),this.end=d,this.unit=f||(r.cssNumber[c]?"":"px")},cur:function(){var a=Ya.propHooks[this.prop];return a&&a.get?a.get(this):Ya.propHooks._default.get(this)},run:function(a){var b,c=Ya.propHooks[this.prop];return this.options.duration?this.pos=b=r.easing[this.easing](a,this.options.duration*a,0,1,this.options.duration):this.pos=b=a,this.now=(this.end-this.start)*b+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),c&&c.set?c.set(this):Ya.propHooks._default.set(this),this}},Ya.prototype.init.prototype=Ya.prototype,Ya.propHooks={_default:{get:function(a){var b;return 1!==a.elem.nodeType||null!=a.elem[a.prop]&&null==a.elem.style[a.prop]?a.elem[a.prop]:(b=r.css(a.elem,a.prop,""),b&&"auto"!==b?b:0)},set:function(a){r.fx.step[a.prop]?r.fx.step[a.prop](a):1!==a.elem.nodeType||null==a.elem.style[r.cssProps[a.prop]]&&!r.cssHooks[a.prop]?a.elem[a.prop]=a.now:r.style(a.elem,a.prop,a.now+a.unit)}}},Ya.propHooks.scrollTop=Ya.propHooks.scrollLeft={set:function(a){a.elem.nodeType&&a.elem.parentNode&&(a.elem[a.prop]=a.now)}},r.easing={linear:function(a){return a},swing:function(a){return.5-Math.cos(a*Math.PI)/2},_default:"swing"},r.fx=Ya.prototype.init,r.fx.step={};var Za,$a,_a=/^(?:toggle|show|hide)$/,ab=/queueHooks$/;function bb(){$a&&(a.requestAnimationFrame(bb),r.fx.tick())}function cb(){return a.setTimeout(function(){Za=void 0}),Za=r.now()}function db(a,b){var c,d=0,e={height:a};for(b=b?1:0;d<4;d+=2-b)c=ba[d],e["margin"+c]=e["padding"+c]=a;return b&&(e.opacity=e.width=a),e}function eb(a,b,c){for(var d,e=(hb.tweeners[b]||[]).concat(hb.tweeners["*"]),f=0,g=e.length;f<g;f++)if(d=e[f].call(c,b,a))return d}function fb(a,b,c){var d,e,f,g,h,i,j,k,l="width"in b||"height"in b,m=this,n={},o=a.style,p=a.nodeType&&ca(a),q=V.get(a,"fxshow");c.queue||(g=r._queueHooks(a,"fx"),null==g.unqueued&&(g.unqueued=0,h=g.empty.fire,g.empty.fire=function(){g.unqueued||h()}),g.unqueued++,m.always(function(){m.always(function(){g.unqueued--,r.queue(a,"fx").length||g.empty.fire()})}));for(d in b)if(e=b[d],_a.test(e)){if(delete b[d],f=f||"toggle"===e,e===(p?"hide":"show")){if("show"!==e||!q||void 0===q[d])continue;p=!0}n[d]=q&&q[d]||r.style(a,d)}if(i=!r.isEmptyObject(b),i||!r.isEmptyObject(n)){l&&1===a.nodeType&&(c.overflow=[o.overflow,o.overflowX,o.overflowY],j=q&&q.display,null==j&&(j=V.get(a,"display")),k=r.css(a,"display"),"none"===k&&(j?k=j:(ha([a],!0),j=a.style.display||j,k=r.css(a,"display"),ha([a]))),("inline"===k||"inline-block"===k&&null!=j)&&"none"===r.css(a,"float")&&(i||(m.done(function(){o.display=j}),null==j&&(k=o.display,j="none"===k?"":k)),o.display="inline-block")),c.overflow&&(o.overflow="hidden",m.always(function(){o.overflow=c.overflow[0],o.overflowX=c.overflow[1],o.overflowY=c.overflow[2]})),i=!1;for(d in n)i||(q?"hidden"in q&&(p=q.hidden):q=V.access(a,"fxshow",{display:j}),f&&(q.hidden=!p),p&&ha([a],!0),m.done(function(){p||ha([a]),V.remove(a,"fxshow");for(d in n)r.style(a,d,n[d])})),i=eb(p?q[d]:0,d,m),d in q||(q[d]=i.start,p&&(i.end=i.start,i.start=0))}}function gb(a,b){var c,d,e,f,g;for(c in a)if(d=r.camelCase(c),e=b[d],f=a[c],r.isArray(f)&&(e=f[1],f=a[c]=f[0]),c!==d&&(a[d]=f,delete a[c]),g=r.cssHooks[d],g&&"expand"in g){f=g.expand(f),delete a[d];for(c in f)c in a||(a[c]=f[c],b[c]=e)}else b[d]=e}function hb(a,b,c){var d,e,f=0,g=hb.prefilters.length,h=r.Deferred().always(function(){delete i.elem}),i=function(){if(e)return!1;for(var b=Za||cb(),c=Math.max(0,j.startTime+j.duration-b),d=c/j.duration||0,f=1-d,g=0,i=j.tweens.length;g<i;g++)j.tweens[g].run(f);return h.notifyWith(a,[j,f,c]),f<1&&i?c:(h.resolveWith(a,[j]),!1)},j=h.promise({elem:a,props:r.extend({},b),opts:r.extend(!0,{specialEasing:{},easing:r.easing._default},c),originalProperties:b,originalOptions:c,startTime:Za||cb(),duration:c.duration,tweens:[],createTween:function(b,c){var d=r.Tween(a,j.opts,b,c,j.opts.specialEasing[b]||j.opts.easing);return j.tweens.push(d),d},stop:function(b){var c=0,d=b?j.tweens.length:0;if(e)return this;for(e=!0;c<d;c++)j.tweens[c].run(1);return b?(h.notifyWith(a,[j,1,0]),h.resolveWith(a,[j,b])):h.rejectWith(a,[j,b]),this}}),k=j.props;for(gb(k,j.opts.specialEasing);f<g;f++)if(d=hb.prefilters[f].call(j,a,k,j.opts))return r.isFunction(d.stop)&&(r._queueHooks(j.elem,j.opts.queue).stop=r.proxy(d.stop,d)),d;return r.map(k,eb,j),r.isFunction(j.opts.start)&&j.opts.start.call(a,j),r.fx.timer(r.extend(i,{elem:a,anim:j,queue:j.opts.queue})),j.progress(j.opts.progress).done(j.opts.done,j.opts.complete).fail(j.opts.fail).always(j.opts.always)}r.Animation=r.extend(hb,{tweeners:{"*":[function(a,b){var c=this.createTween(a,b);return ea(c.elem,a,aa.exec(b),c),c}]},tweener:function(a,b){r.isFunction(a)?(b=a,a=["*"]):a=a.match(K);for(var c,d=0,e=a.length;d<e;d++)c=a[d],hb.tweeners[c]=hb.tweeners[c]||[],hb.tweeners[c].unshift(b)},prefilters:[fb],prefilter:function(a,b){b?hb.prefilters.unshift(a):hb.prefilters.push(a)}}),r.speed=function(a,b,c){var e=a&&"object"==typeof a?r.extend({},a):{complete:c||!c&&b||r.isFunction(a)&&a,duration:a,easing:c&&b||b&&!r.isFunction(b)&&b};return r.fx.off||d.hidden?e.duration=0:"number"!=typeof e.duration&&(e.duration in r.fx.speeds?e.duration=r.fx.speeds[e.duration]:e.duration=r.fx.speeds._default),null!=e.queue&&e.queue!==!0||(e.queue="fx"),e.old=e.complete,e.complete=function(){r.isFunction(e.old)&&e.old.call(this),e.queue&&r.dequeue(this,e.queue)},e},r.fn.extend({fadeTo:function(a,b,c,d){return this.filter(ca).css("opacity",0).show().end().animate({opacity:b},a,c,d)},animate:function(a,b,c,d){var e=r.isEmptyObject(a),f=r.speed(b,c,d),g=function(){var b=hb(this,r.extend({},a),f);(e||V.get(this,"finish"))&&b.stop(!0)};return g.finish=g,e||f.queue===!1?this.each(g):this.queue(f.queue,g)},stop:function(a,b,c){var d=function(a){var b=a.stop;delete a.stop,b(c)};return"string"!=typeof a&&(c=b,b=a,a=void 0),b&&a!==!1&&this.queue(a||"fx",[]),this.each(function(){var b=!0,e=null!=a&&a+"queueHooks",f=r.timers,g=V.get(this);if(e)g[e]&&g[e].stop&&d(g[e]);else for(e in g)g[e]&&g[e].stop&&ab.test(e)&&d(g[e]);for(e=f.length;e--;)f[e].elem!==this||null!=a&&f[e].queue!==a||(f[e].anim.stop(c),b=!1,f.splice(e,1));!b&&c||r.dequeue(this,a)})},finish:function(a){return a!==!1&&(a=a||"fx"),this.each(function(){var b,c=V.get(this),d=c[a+"queue"],e=c[a+"queueHooks"],f=r.timers,g=d?d.length:0;for(c.finish=!0,r.queue(this,a,[]),e&&e.stop&&e.stop.call(this,!0),b=f.length;b--;)f[b].elem===this&&f[b].queue===a&&(f[b].anim.stop(!0),f.splice(b,1));for(b=0;b<g;b++)d[b]&&d[b].finish&&d[b].finish.call(this);delete c.finish})}}),r.each(["toggle","show","hide"],function(a,b){var c=r.fn[b];r.fn[b]=function(a,d,e){return null==a||"boolean"==typeof a?c.apply(this,arguments):this.animate(db(b,!0),a,d,e)}}),r.each({slideDown:db("show"),slideUp:db("hide"),slideToggle:db("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(a,b){r.fn[a]=function(a,c,d){return this.animate(b,a,c,d)}}),r.timers=[],r.fx.tick=function(){var a,b=0,c=r.timers;for(Za=r.now();b<c.length;b++)a=c[b],a()||c[b]!==a||c.splice(b--,1);c.length||r.fx.stop(),Za=void 0},r.fx.timer=function(a){r.timers.push(a),a()?r.fx.start():r.timers.pop()},r.fx.interval=13,r.fx.start=function(){$a||($a=a.requestAnimationFrame?a.requestAnimationFrame(bb):a.setInterval(r.fx.tick,r.fx.interval))},r.fx.stop=function(){a.cancelAnimationFrame?a.cancelAnimationFrame($a):a.clearInterval($a),$a=null},r.fx.speeds={slow:600,fast:200,_default:400},r.fn.delay=function(b,c){return b=r.fx?r.fx.speeds[b]||b:b,c=c||"fx",this.queue(c,function(c,d){var e=a.setTimeout(c,b);d.stop=function(){a.clearTimeout(e)}})},function(){var a=d.createElement("input"),b=d.createElement("select"),c=b.appendChild(d.createElement("option"));a.type="checkbox",o.checkOn=""!==a.value,o.optSelected=c.selected,a=d.createElement("input"),a.value="t",a.type="radio",o.radioValue="t"===a.value}();var ib,jb=r.expr.attrHandle;r.fn.extend({attr:function(a,b){return S(this,r.attr,a,b,arguments.length>1)},removeAttr:function(a){return this.each(function(){r.removeAttr(this,a)})}}),r.extend({attr:function(a,b,c){var d,e,f=a.nodeType;if(3!==f&&8!==f&&2!==f)return"undefined"==typeof a.getAttribute?r.prop(a,b,c):(1===f&&r.isXMLDoc(a)||(e=r.attrHooks[b.toLowerCase()]||(r.expr.match.bool.test(b)?ib:void 0)),
void 0!==c?null===c?void r.removeAttr(a,b):e&&"set"in e&&void 0!==(d=e.set(a,c,b))?d:(a.setAttribute(b,c+""),c):e&&"get"in e&&null!==(d=e.get(a,b))?d:(d=r.find.attr(a,b),null==d?void 0:d))},attrHooks:{type:{set:function(a,b){if(!o.radioValue&&"radio"===b&&r.nodeName(a,"input")){var c=a.value;return a.setAttribute("type",b),c&&(a.value=c),b}}}},removeAttr:function(a,b){var c,d=0,e=b&&b.match(K);if(e&&1===a.nodeType)while(c=e[d++])a.removeAttribute(c)}}),ib={set:function(a,b,c){return b===!1?r.removeAttr(a,c):a.setAttribute(c,c),c}},r.each(r.expr.match.bool.source.match(/\w+/g),function(a,b){var c=jb[b]||r.find.attr;jb[b]=function(a,b,d){var e,f,g=b.toLowerCase();return d||(f=jb[g],jb[g]=e,e=null!=c(a,b,d)?g:null,jb[g]=f),e}});var kb=/^(?:input|select|textarea|button)$/i,lb=/^(?:a|area)$/i;r.fn.extend({prop:function(a,b){return S(this,r.prop,a,b,arguments.length>1)},removeProp:function(a){return this.each(function(){delete this[r.propFix[a]||a]})}}),r.extend({prop:function(a,b,c){var d,e,f=a.nodeType;if(3!==f&&8!==f&&2!==f)return 1===f&&r.isXMLDoc(a)||(b=r.propFix[b]||b,e=r.propHooks[b]),void 0!==c?e&&"set"in e&&void 0!==(d=e.set(a,c,b))?d:a[b]=c:e&&"get"in e&&null!==(d=e.get(a,b))?d:a[b]},propHooks:{tabIndex:{get:function(a){var b=r.find.attr(a,"tabindex");return b?parseInt(b,10):kb.test(a.nodeName)||lb.test(a.nodeName)&&a.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),o.optSelected||(r.propHooks.selected={get:function(a){var b=a.parentNode;return b&&b.parentNode&&b.parentNode.selectedIndex,null},set:function(a){var b=a.parentNode;b&&(b.selectedIndex,b.parentNode&&b.parentNode.selectedIndex)}}),r.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){r.propFix[this.toLowerCase()]=this});function mb(a){var b=a.match(K)||[];return b.join(" ")}function nb(a){return a.getAttribute&&a.getAttribute("class")||""}r.fn.extend({addClass:function(a){var b,c,d,e,f,g,h,i=0;if(r.isFunction(a))return this.each(function(b){r(this).addClass(a.call(this,b,nb(this)))});if("string"==typeof a&&a){b=a.match(K)||[];while(c=this[i++])if(e=nb(c),d=1===c.nodeType&&" "+mb(e)+" "){g=0;while(f=b[g++])d.indexOf(" "+f+" ")<0&&(d+=f+" ");h=mb(d),e!==h&&c.setAttribute("class",h)}}return this},removeClass:function(a){var b,c,d,e,f,g,h,i=0;if(r.isFunction(a))return this.each(function(b){r(this).removeClass(a.call(this,b,nb(this)))});if(!arguments.length)return this.attr("class","");if("string"==typeof a&&a){b=a.match(K)||[];while(c=this[i++])if(e=nb(c),d=1===c.nodeType&&" "+mb(e)+" "){g=0;while(f=b[g++])while(d.indexOf(" "+f+" ")>-1)d=d.replace(" "+f+" "," ");h=mb(d),e!==h&&c.setAttribute("class",h)}}return this},toggleClass:function(a,b){var c=typeof a;return"boolean"==typeof b&&"string"===c?b?this.addClass(a):this.removeClass(a):r.isFunction(a)?this.each(function(c){r(this).toggleClass(a.call(this,c,nb(this),b),b)}):this.each(function(){var b,d,e,f;if("string"===c){d=0,e=r(this),f=a.match(K)||[];while(b=f[d++])e.hasClass(b)?e.removeClass(b):e.addClass(b)}else void 0!==a&&"boolean"!==c||(b=nb(this),b&&V.set(this,"__className__",b),this.setAttribute&&this.setAttribute("class",b||a===!1?"":V.get(this,"__className__")||""))})},hasClass:function(a){var b,c,d=0;b=" "+a+" ";while(c=this[d++])if(1===c.nodeType&&(" "+mb(nb(c))+" ").indexOf(b)>-1)return!0;return!1}});var ob=/\r/g;r.fn.extend({val:function(a){var b,c,d,e=this[0];{if(arguments.length)return d=r.isFunction(a),this.each(function(c){var e;1===this.nodeType&&(e=d?a.call(this,c,r(this).val()):a,null==e?e="":"number"==typeof e?e+="":r.isArray(e)&&(e=r.map(e,function(a){return null==a?"":a+""})),b=r.valHooks[this.type]||r.valHooks[this.nodeName.toLowerCase()],b&&"set"in b&&void 0!==b.set(this,e,"value")||(this.value=e))});if(e)return b=r.valHooks[e.type]||r.valHooks[e.nodeName.toLowerCase()],b&&"get"in b&&void 0!==(c=b.get(e,"value"))?c:(c=e.value,"string"==typeof c?c.replace(ob,""):null==c?"":c)}}}),r.extend({valHooks:{option:{get:function(a){var b=r.find.attr(a,"value");return null!=b?b:mb(r.text(a))}},select:{get:function(a){var b,c,d,e=a.options,f=a.selectedIndex,g="select-one"===a.type,h=g?null:[],i=g?f+1:e.length;for(d=f<0?i:g?f:0;d<i;d++)if(c=e[d],(c.selected||d===f)&&!c.disabled&&(!c.parentNode.disabled||!r.nodeName(c.parentNode,"optgroup"))){if(b=r(c).val(),g)return b;h.push(b)}return h},set:function(a,b){var c,d,e=a.options,f=r.makeArray(b),g=e.length;while(g--)d=e[g],(d.selected=r.inArray(r.valHooks.option.get(d),f)>-1)&&(c=!0);return c||(a.selectedIndex=-1),f}}}}),r.each(["radio","checkbox"],function(){r.valHooks[this]={set:function(a,b){if(r.isArray(b))return a.checked=r.inArray(r(a).val(),b)>-1}},o.checkOn||(r.valHooks[this].get=function(a){return null===a.getAttribute("value")?"on":a.value})});var pb=/^(?:focusinfocus|focusoutblur)$/;r.extend(r.event,{trigger:function(b,c,e,f){var g,h,i,j,k,m,n,o=[e||d],p=l.call(b,"type")?b.type:b,q=l.call(b,"namespace")?b.namespace.split("."):[];if(h=i=e=e||d,3!==e.nodeType&&8!==e.nodeType&&!pb.test(p+r.event.triggered)&&(p.indexOf(".")>-1&&(q=p.split("."),p=q.shift(),q.sort()),k=p.indexOf(":")<0&&"on"+p,b=b[r.expando]?b:new r.Event(p,"object"==typeof b&&b),b.isTrigger=f?2:3,b.namespace=q.join("."),b.rnamespace=b.namespace?new RegExp("(^|\\.)"+q.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,b.result=void 0,b.target||(b.target=e),c=null==c?[b]:r.makeArray(c,[b]),n=r.event.special[p]||{},f||!n.trigger||n.trigger.apply(e,c)!==!1)){if(!f&&!n.noBubble&&!r.isWindow(e)){for(j=n.delegateType||p,pb.test(j+p)||(h=h.parentNode);h;h=h.parentNode)o.push(h),i=h;i===(e.ownerDocument||d)&&o.push(i.defaultView||i.parentWindow||a)}g=0;while((h=o[g++])&&!b.isPropagationStopped())b.type=g>1?j:n.bindType||p,m=(V.get(h,"events")||{})[b.type]&&V.get(h,"handle"),m&&m.apply(h,c),m=k&&h[k],m&&m.apply&&T(h)&&(b.result=m.apply(h,c),b.result===!1&&b.preventDefault());return b.type=p,f||b.isDefaultPrevented()||n._default&&n._default.apply(o.pop(),c)!==!1||!T(e)||k&&r.isFunction(e[p])&&!r.isWindow(e)&&(i=e[k],i&&(e[k]=null),r.event.triggered=p,e[p](),r.event.triggered=void 0,i&&(e[k]=i)),b.result}},simulate:function(a,b,c){var d=r.extend(new r.Event,c,{type:a,isSimulated:!0});r.event.trigger(d,null,b)}}),r.fn.extend({trigger:function(a,b){return this.each(function(){r.event.trigger(a,b,this)})},triggerHandler:function(a,b){var c=this[0];if(c)return r.event.trigger(a,b,c,!0)}}),r.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(a,b){r.fn[b]=function(a,c){return arguments.length>0?this.on(b,null,a,c):this.trigger(b)}}),r.fn.extend({hover:function(a,b){return this.mouseenter(a).mouseleave(b||a)}}),o.focusin="onfocusin"in a,o.focusin||r.each({focus:"focusin",blur:"focusout"},function(a,b){var c=function(a){r.event.simulate(b,a.target,r.event.fix(a))};r.event.special[b]={setup:function(){var d=this.ownerDocument||this,e=V.access(d,b);e||d.addEventListener(a,c,!0),V.access(d,b,(e||0)+1)},teardown:function(){var d=this.ownerDocument||this,e=V.access(d,b)-1;e?V.access(d,b,e):(d.removeEventListener(a,c,!0),V.remove(d,b))}}});var qb=a.location,rb=r.now(),sb=/\?/;r.parseXML=function(b){var c;if(!b||"string"!=typeof b)return null;try{c=(new a.DOMParser).parseFromString(b,"text/xml")}catch(d){c=void 0}return c&&!c.getElementsByTagName("parsererror").length||r.error("Invalid XML: "+b),c};var tb=/\[\]$/,ub=/\r?\n/g,vb=/^(?:submit|button|image|reset|file)$/i,wb=/^(?:input|select|textarea|keygen)/i;function xb(a,b,c,d){var e;if(r.isArray(b))r.each(b,function(b,e){c||tb.test(a)?d(a,e):xb(a+"["+("object"==typeof e&&null!=e?b:"")+"]",e,c,d)});else if(c||"object"!==r.type(b))d(a,b);else for(e in b)xb(a+"["+e+"]",b[e],c,d)}r.param=function(a,b){var c,d=[],e=function(a,b){var c=r.isFunction(b)?b():b;d[d.length]=encodeURIComponent(a)+"="+encodeURIComponent(null==c?"":c)};if(r.isArray(a)||a.jquery&&!r.isPlainObject(a))r.each(a,function(){e(this.name,this.value)});else for(c in a)xb(c,a[c],b,e);return d.join("&")},r.fn.extend({serialize:function(){return r.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var a=r.prop(this,"elements");return a?r.makeArray(a):this}).filter(function(){var a=this.type;return this.name&&!r(this).is(":disabled")&&wb.test(this.nodeName)&&!vb.test(a)&&(this.checked||!ia.test(a))}).map(function(a,b){var c=r(this).val();return null==c?null:r.isArray(c)?r.map(c,function(a){return{name:b.name,value:a.replace(ub,"\r\n")}}):{name:b.name,value:c.replace(ub,"\r\n")}}).get()}});var yb=/%20/g,zb=/#.*$/,Ab=/([?&])_=[^&]*/,Bb=/^(.*?):[ \t]*([^\r\n]*)$/gm,Cb=/^(?:about|app|app-storage|.+-extension|file|res|widget):$/,Db=/^(?:GET|HEAD)$/,Eb=/^\/\//,Fb={},Gb={},Hb="*/".concat("*"),Ib=d.createElement("a");Ib.href=qb.href;function Jb(a){return function(b,c){"string"!=typeof b&&(c=b,b="*");var d,e=0,f=b.toLowerCase().match(K)||[];if(r.isFunction(c))while(d=f[e++])"+"===d[0]?(d=d.slice(1)||"*",(a[d]=a[d]||[]).unshift(c)):(a[d]=a[d]||[]).push(c)}}function Kb(a,b,c,d){var e={},f=a===Gb;function g(h){var i;return e[h]=!0,r.each(a[h]||[],function(a,h){var j=h(b,c,d);return"string"!=typeof j||f||e[j]?f?!(i=j):void 0:(b.dataTypes.unshift(j),g(j),!1)}),i}return g(b.dataTypes[0])||!e["*"]&&g("*")}function Lb(a,b){var c,d,e=r.ajaxSettings.flatOptions||{};for(c in b)void 0!==b[c]&&((e[c]?a:d||(d={}))[c]=b[c]);return d&&r.extend(!0,a,d),a}function Mb(a,b,c){var d,e,f,g,h=a.contents,i=a.dataTypes;while("*"===i[0])i.shift(),void 0===d&&(d=a.mimeType||b.getResponseHeader("Content-Type"));if(d)for(e in h)if(h[e]&&h[e].test(d)){i.unshift(e);break}if(i[0]in c)f=i[0];else{for(e in c){if(!i[0]||a.converters[e+" "+i[0]]){f=e;break}g||(g=e)}f=f||g}if(f)return f!==i[0]&&i.unshift(f),c[f]}function Nb(a,b,c,d){var e,f,g,h,i,j={},k=a.dataTypes.slice();if(k[1])for(g in a.converters)j[g.toLowerCase()]=a.converters[g];f=k.shift();while(f)if(a.responseFields[f]&&(c[a.responseFields[f]]=b),!i&&d&&a.dataFilter&&(b=a.dataFilter(b,a.dataType)),i=f,f=k.shift())if("*"===f)f=i;else if("*"!==i&&i!==f){if(g=j[i+" "+f]||j["* "+f],!g)for(e in j)if(h=e.split(" "),h[1]===f&&(g=j[i+" "+h[0]]||j["* "+h[0]])){g===!0?g=j[e]:j[e]!==!0&&(f=h[0],k.unshift(h[1]));break}if(g!==!0)if(g&&a["throws"])b=g(b);else try{b=g(b)}catch(l){return{state:"parsererror",error:g?l:"No conversion from "+i+" to "+f}}}return{state:"success",data:b}}r.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:qb.href,type:"GET",isLocal:Cb.test(qb.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":Hb,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":r.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(a,b){return b?Lb(Lb(a,r.ajaxSettings),b):Lb(r.ajaxSettings,a)},ajaxPrefilter:Jb(Fb),ajaxTransport:Jb(Gb),ajax:function(b,c){"object"==typeof b&&(c=b,b=void 0),c=c||{};var e,f,g,h,i,j,k,l,m,n,o=r.ajaxSetup({},c),p=o.context||o,q=o.context&&(p.nodeType||p.jquery)?r(p):r.event,s=r.Deferred(),t=r.Callbacks("once memory"),u=o.statusCode||{},v={},w={},x="canceled",y={readyState:0,getResponseHeader:function(a){var b;if(k){if(!h){h={};while(b=Bb.exec(g))h[b[1].toLowerCase()]=b[2]}b=h[a.toLowerCase()]}return null==b?null:b},getAllResponseHeaders:function(){return k?g:null},setRequestHeader:function(a,b){return null==k&&(a=w[a.toLowerCase()]=w[a.toLowerCase()]||a,v[a]=b),this},overrideMimeType:function(a){return null==k&&(o.mimeType=a),this},statusCode:function(a){var b;if(a)if(k)y.always(a[y.status]);else for(b in a)u[b]=[u[b],a[b]];return this},abort:function(a){var b=a||x;return e&&e.abort(b),A(0,b),this}};if(s.promise(y),o.url=((b||o.url||qb.href)+"").replace(Eb,qb.protocol+"//"),o.type=c.method||c.type||o.method||o.type,o.dataTypes=(o.dataType||"*").toLowerCase().match(K)||[""],null==o.crossDomain){j=d.createElement("a");try{j.href=o.url,j.href=j.href,o.crossDomain=Ib.protocol+"//"+Ib.host!=j.protocol+"//"+j.host}catch(z){o.crossDomain=!0}}if(o.data&&o.processData&&"string"!=typeof o.data&&(o.data=r.param(o.data,o.traditional)),Kb(Fb,o,c,y),k)return y;l=r.event&&o.global,l&&0===r.active++&&r.event.trigger("ajaxStart"),o.type=o.type.toUpperCase(),o.hasContent=!Db.test(o.type),f=o.url.replace(zb,""),o.hasContent?o.data&&o.processData&&0===(o.contentType||"").indexOf("application/x-www-form-urlencoded")&&(o.data=o.data.replace(yb,"+")):(n=o.url.slice(f.length),o.data&&(f+=(sb.test(f)?"&":"?")+o.data,delete o.data),o.cache===!1&&(f=f.replace(Ab,"$1"),n=(sb.test(f)?"&":"?")+"_="+rb++ +n),o.url=f+n),o.ifModified&&(r.lastModified[f]&&y.setRequestHeader("If-Modified-Since",r.lastModified[f]),r.etag[f]&&y.setRequestHeader("If-None-Match",r.etag[f])),(o.data&&o.hasContent&&o.contentType!==!1||c.contentType)&&y.setRequestHeader("Content-Type",o.contentType),y.setRequestHeader("Accept",o.dataTypes[0]&&o.accepts[o.dataTypes[0]]?o.accepts[o.dataTypes[0]]+("*"!==o.dataTypes[0]?", "+Hb+"; q=0.01":""):o.accepts["*"]);for(m in o.headers)y.setRequestHeader(m,o.headers[m]);if(o.beforeSend&&(o.beforeSend.call(p,y,o)===!1||k))return y.abort();if(x="abort",t.add(o.complete),y.done(o.success),y.fail(o.error),e=Kb(Gb,o,c,y)){if(y.readyState=1,l&&q.trigger("ajaxSend",[y,o]),k)return y;o.async&&o.timeout>0&&(i=a.setTimeout(function(){y.abort("timeout")},o.timeout));try{k=!1,e.send(v,A)}catch(z){if(k)throw z;A(-1,z)}}else A(-1,"No Transport");function A(b,c,d,h){var j,m,n,v,w,x=c;k||(k=!0,i&&a.clearTimeout(i),e=void 0,g=h||"",y.readyState=b>0?4:0,j=b>=200&&b<300||304===b,d&&(v=Mb(o,y,d)),v=Nb(o,v,y,j),j?(o.ifModified&&(w=y.getResponseHeader("Last-Modified"),w&&(r.lastModified[f]=w),w=y.getResponseHeader("etag"),w&&(r.etag[f]=w)),204===b||"HEAD"===o.type?x="nocontent":304===b?x="notmodified":(x=v.state,m=v.data,n=v.error,j=!n)):(n=x,!b&&x||(x="error",b<0&&(b=0))),y.status=b,y.statusText=(c||x)+"",j?s.resolveWith(p,[m,x,y]):s.rejectWith(p,[y,x,n]),y.statusCode(u),u=void 0,l&&q.trigger(j?"ajaxSuccess":"ajaxError",[y,o,j?m:n]),t.fireWith(p,[y,x]),l&&(q.trigger("ajaxComplete",[y,o]),--r.active||r.event.trigger("ajaxStop")))}return y},getJSON:function(a,b,c){return r.get(a,b,c,"json")},getScript:function(a,b){return r.get(a,void 0,b,"script")}}),r.each(["get","post"],function(a,b){r[b]=function(a,c,d,e){return r.isFunction(c)&&(e=e||d,d=c,c=void 0),r.ajax(r.extend({url:a,type:b,dataType:e,data:c,success:d},r.isPlainObject(a)&&a))}}),r._evalUrl=function(a){return r.ajax({url:a,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,"throws":!0})},r.fn.extend({wrapAll:function(a){var b;return this[0]&&(r.isFunction(a)&&(a=a.call(this[0])),b=r(a,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&b.insertBefore(this[0]),b.map(function(){var a=this;while(a.firstElementChild)a=a.firstElementChild;return a}).append(this)),this},wrapInner:function(a){return r.isFunction(a)?this.each(function(b){r(this).wrapInner(a.call(this,b))}):this.each(function(){var b=r(this),c=b.contents();c.length?c.wrapAll(a):b.append(a)})},wrap:function(a){var b=r.isFunction(a);return this.each(function(c){r(this).wrapAll(b?a.call(this,c):a)})},unwrap:function(a){return this.parent(a).not("body").each(function(){r(this).replaceWith(this.childNodes)}),this}}),r.expr.pseudos.hidden=function(a){return!r.expr.pseudos.visible(a)},r.expr.pseudos.visible=function(a){return!!(a.offsetWidth||a.offsetHeight||a.getClientRects().length)},r.ajaxSettings.xhr=function(){try{return new a.XMLHttpRequest}catch(b){}};var Ob={0:200,1223:204},Pb=r.ajaxSettings.xhr();o.cors=!!Pb&&"withCredentials"in Pb,o.ajax=Pb=!!Pb,r.ajaxTransport(function(b){var c,d;if(o.cors||Pb&&!b.crossDomain)return{send:function(e,f){var g,h=b.xhr();if(h.open(b.type,b.url,b.async,b.username,b.password),b.xhrFields)for(g in b.xhrFields)h[g]=b.xhrFields[g];b.mimeType&&h.overrideMimeType&&h.overrideMimeType(b.mimeType),b.crossDomain||e["X-Requested-With"]||(e["X-Requested-With"]="XMLHttpRequest");for(g in e)h.setRequestHeader(g,e[g]);c=function(a){return function(){c&&(c=d=h.onload=h.onerror=h.onabort=h.onreadystatechange=null,"abort"===a?h.abort():"error"===a?"number"!=typeof h.status?f(0,"error"):f(h.status,h.statusText):f(Ob[h.status]||h.status,h.statusText,"text"!==(h.responseType||"text")||"string"!=typeof h.responseText?{binary:h.response}:{text:h.responseText},h.getAllResponseHeaders()))}},h.onload=c(),d=h.onerror=c("error"),void 0!==h.onabort?h.onabort=d:h.onreadystatechange=function(){4===h.readyState&&a.setTimeout(function(){c&&d()})},c=c("abort");try{h.send(b.hasContent&&b.data||null)}catch(i){if(c)throw i}},abort:function(){c&&c()}}}),r.ajaxPrefilter(function(a){a.crossDomain&&(a.contents.script=!1)}),r.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(a){return r.globalEval(a),a}}}),r.ajaxPrefilter("script",function(a){void 0===a.cache&&(a.cache=!1),a.crossDomain&&(a.type="GET")}),r.ajaxTransport("script",function(a){if(a.crossDomain){var b,c;return{send:function(e,f){b=r("<script>").prop({charset:a.scriptCharset,src:a.url}).on("load error",c=function(a){b.remove(),c=null,a&&f("error"===a.type?404:200,a.type)}),d.head.appendChild(b[0])},abort:function(){c&&c()}}}});var Qb=[],Rb=/(=)\?(?=&|$)|\?\?/;r.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var a=Qb.pop()||r.expando+"_"+rb++;return this[a]=!0,a}}),r.ajaxPrefilter("json jsonp",function(b,c,d){var e,f,g,h=b.jsonp!==!1&&(Rb.test(b.url)?"url":"string"==typeof b.data&&0===(b.contentType||"").indexOf("application/x-www-form-urlencoded")&&Rb.test(b.data)&&"data");if(h||"jsonp"===b.dataTypes[0])return e=b.jsonpCallback=r.isFunction(b.jsonpCallback)?b.jsonpCallback():b.jsonpCallback,h?b[h]=b[h].replace(Rb,"$1"+e):b.jsonp!==!1&&(b.url+=(sb.test(b.url)?"&":"?")+b.jsonp+"="+e),b.converters["script json"]=function(){return g||r.error(e+" was not called"),g[0]},b.dataTypes[0]="json",f=a[e],a[e]=function(){g=arguments},d.always(function(){void 0===f?r(a).removeProp(e):a[e]=f,b[e]&&(b.jsonpCallback=c.jsonpCallback,Qb.push(e)),g&&r.isFunction(f)&&f(g[0]),g=f=void 0}),"script"}),o.createHTMLDocument=function(){var a=d.implementation.createHTMLDocument("").body;return a.innerHTML="<form></form><form></form>",2===a.childNodes.length}(),r.parseHTML=function(a,b,c){if("string"!=typeof a)return[];"boolean"==typeof b&&(c=b,b=!1);var e,f,g;return b||(o.createHTMLDocument?(b=d.implementation.createHTMLDocument(""),e=b.createElement("base"),e.href=d.location.href,b.head.appendChild(e)):b=d),f=B.exec(a),g=!c&&[],f?[b.createElement(f[1])]:(f=pa([a],b,g),g&&g.length&&r(g).remove(),r.merge([],f.childNodes))},r.fn.load=function(a,b,c){var d,e,f,g=this,h=a.indexOf(" ");return h>-1&&(d=mb(a.slice(h)),a=a.slice(0,h)),r.isFunction(b)?(c=b,b=void 0):b&&"object"==typeof b&&(e="POST"),g.length>0&&r.ajax({url:a,type:e||"GET",dataType:"html",data:b}).done(function(a){f=arguments,g.html(d?r("<div>").append(r.parseHTML(a)).find(d):a)}).always(c&&function(a,b){g.each(function(){c.apply(this,f||[a.responseText,b,a])})}),this},r.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(a,b){r.fn[b]=function(a){return this.on(b,a)}}),r.expr.pseudos.animated=function(a){return r.grep(r.timers,function(b){return a===b.elem}).length};function Sb(a){return r.isWindow(a)?a:9===a.nodeType&&a.defaultView}r.offset={setOffset:function(a,b,c){var d,e,f,g,h,i,j,k=r.css(a,"position"),l=r(a),m={};"static"===k&&(a.style.position="relative"),h=l.offset(),f=r.css(a,"top"),i=r.css(a,"left"),j=("absolute"===k||"fixed"===k)&&(f+i).indexOf("auto")>-1,j?(d=l.position(),g=d.top,e=d.left):(g=parseFloat(f)||0,e=parseFloat(i)||0),r.isFunction(b)&&(b=b.call(a,c,r.extend({},h))),null!=b.top&&(m.top=b.top-h.top+g),null!=b.left&&(m.left=b.left-h.left+e),"using"in b?b.using.call(a,m):l.css(m)}},r.fn.extend({offset:function(a){if(arguments.length)return void 0===a?this:this.each(function(b){r.offset.setOffset(this,a,b)});var b,c,d,e,f=this[0];if(f)return f.getClientRects().length?(d=f.getBoundingClientRect(),d.width||d.height?(e=f.ownerDocument,c=Sb(e),b=e.documentElement,{top:d.top+c.pageYOffset-b.clientTop,left:d.left+c.pageXOffset-b.clientLeft}):d):{top:0,left:0}},position:function(){if(this[0]){var a,b,c=this[0],d={top:0,left:0};return"fixed"===r.css(c,"position")?b=c.getBoundingClientRect():(a=this.offsetParent(),b=this.offset(),r.nodeName(a[0],"html")||(d=a.offset()),d={top:d.top+r.css(a[0],"borderTopWidth",!0),left:d.left+r.css(a[0],"borderLeftWidth",!0)}),{top:b.top-d.top-r.css(c,"marginTop",!0),left:b.left-d.left-r.css(c,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var a=this.offsetParent;while(a&&"static"===r.css(a,"position"))a=a.offsetParent;return a||qa})}}),r.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(a,b){var c="pageYOffset"===b;r.fn[a]=function(d){return S(this,function(a,d,e){var f=Sb(a);return void 0===e?f?f[b]:a[d]:void(f?f.scrollTo(c?f.pageXOffset:e,c?e:f.pageYOffset):a[d]=e)},a,d,arguments.length)}}),r.each(["top","left"],function(a,b){r.cssHooks[b]=Oa(o.pixelPosition,function(a,c){if(c)return c=Na(a,b),La.test(c)?r(a).position()[b]+"px":c})}),r.each({Height:"height",Width:"width"},function(a,b){r.each({padding:"inner"+a,content:b,"":"outer"+a},function(c,d){r.fn[d]=function(e,f){var g=arguments.length&&(c||"boolean"!=typeof e),h=c||(e===!0||f===!0?"margin":"border");return S(this,function(b,c,e){var f;return r.isWindow(b)?0===d.indexOf("outer")?b["inner"+a]:b.document.documentElement["client"+a]:9===b.nodeType?(f=b.documentElement,Math.max(b.body["scroll"+a],f["scroll"+a],b.body["offset"+a],f["offset"+a],f["client"+a])):void 0===e?r.css(b,c,h):r.style(b,c,e,h)},b,g?e:void 0,g)}})}),r.fn.extend({bind:function(a,b,c){return this.on(a,null,b,c)},unbind:function(a,b){return this.off(a,null,b)},delegate:function(a,b,c,d){return this.on(b,a,c,d)},undelegate:function(a,b,c){return 1===arguments.length?this.off(a,"**"):this.off(b,a||"**",c)}}),r.parseJSON=JSON.parse,"function"==typeof define&&define.amd&&define("jquery",[],function(){return r});var Tb=a.jQuery,Ub=a.$;return r.noConflict=function(b){return a.$===r&&(a.$=Ub),b&&a.jQuery===r&&(a.jQuery=Tb),r},b||(a.jQuery=a.$=r),r});
    </script>
    <script type="text/javascript">
        var noResultsTxt = "<?php echo $langTxt[$lang]['section']['searchNoResults']; ?>";
    </script>
    <script type="text/javascript">
/* ---- elcano Explorer v3.2.6 ---- */

// global variables
var version = '3.2.6';
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
var defaultSettings = {'version':version,'tree':true,'view':'Mosaic','darkMode':false,'showHidden':false,'showExtensions':true,'defaultView':'last','debug':true,'ignoreFiles':ignoreFiles,'systemIndex':true,'directoryIndex':defaultDirectoryIndex,'dbpath':'http://localhost/phpmyadmin/','videopath':'','firstLoad':false}; // configuracion por defecto de la aplicacion
var lang = getCookie('elcano-lang');
var directoryTree = {};
var searchPanelShown = false;

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
                    $('#signInError').html('<p>'+langs[lang].login.error[response.message]+'</p>');
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
        if (searchPanelShown) { closeSearchArea(); }
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

    let fExt = url.split('.').pop();
    let imageTypes = ['jpg','jpeg','gif','png','webp','bmp','tiff','svg'];
    let videoTypes = ['mp4','mov','wmv','avi','mkv','webm','mpeg-2'];

    if (imageTypes.includes(fExt)) {
        showImageViewer(true,url);
    } else if (videoTypes.includes(fExt)) {
        openVideoFile(url);
    } else if (fExt == 'txt') {
        showTextfileViewer(true,url);
    } else {
        window.open(url,'_blank');
    }
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
                directoryTree = data;
                const container = document.getElementById('asideTreeBody'); // El contenedor en tu HTML
                createDirectoryTree(JSON.parse(data), container);

                $('#searchBox').attr('disabled',false);

                $('#searchBarForm').on('submit',function(e) {
                    e.preventDefault();
                    searchFile($('#searchBox').val());
                });
        });
    }
}

function createDirectoryTree(data, container) {
    // Función recursiva para construir el árbol de directorios
    function buildTree(items, parentElement) {
        items.forEach(item => {
            if (item.type == 'dir') {
                const details = document.createElement('details');
                const summary = document.createElement('summary');
                const summaryText = document.createElement('p');

                summaryText.classList.add('linkTree');
                summaryText.setAttribute('onclick',"changePath('"+item.path+"/')");
                summaryText.textContent = item.name;
                summary.appendChild(summaryText);
                details.appendChild(summary);

                parentElement.appendChild(details);

                if (item.content.length > 0) {
                    buildTree(item.content, details);
                }
            } else if (item.type == 'file') {
            }

            $('.spinner').hide();
        });
    }

    // Inicializar el árbol
    buildTree(data, container);
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
        $('.dialogBodyCenter').scrollTop(0);
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
        $('.dialogBodyCenter').scrollTop(0);
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

    // video player
    $('#videoplayerPathInput').val(settings.videopath);

    $('#videoplayerPathInput').on('change',function() {
        settings.videopath = $('#videoplayerPathInput').val();
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

    $('#settingsVersion').text(version);
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

        if (!settings.videopath) {
            newSettings.videopath = '';
        } else {
            newSettings.videopath = defaultSettings.videopath;
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

function showImageViewer(op, url) {
    if (settings.debug){console.log('showing image viewer : '+url)};

    if (op) {
        $('#dialogBack').css('display','flex');
        $('.dialog').hide();
        $('#imageViewer').fadeIn(200);
        $('#imageViewerUrl').text(url);

        $('#imageViewerImageCanvas').html('<img src="'+url+'" />');
        $('#imageViewerOpen').attr('onclick','openImageNewTab("'+url+'")');
    } else {
        $('#imageViewer').fadeOut(200);
        $('#dialogBack').css('display','none');
    }
}

function openImageNewTab(url) {
    window.open(url, '_blank');
}

function showTextfileViewer(op, url) {
    if (settings.debug){console.log('showing text viewer : '+url)};

    if (op) {
        $('#dialogBack').css('display','flex');
        $('.dialog').hide();
        $('#textfileViewer').fadeIn(200);
        $('#textfileViewerUrl').text(url);

        $('#textfileViewerOpen').attr('onclick','openTextfileNewTab("'+url+'")');

        $.post( "", { ruta: url } )
            .done(function( data ) {
                var fileLines = '';

                for (i in data) {
                    if (data[i] == '\r\n') {
                        fileLines += '<br />';
                    } else {
                        fileLines += '<p>'+data[i]+'</p>';
                    }
                }
                $('#textfileViewerCanvas').html(fileLines);
        });
    } else {
        $('#textfileViewer').fadeOut(200);
        $('#dialogBack').css('display','none');
    }
}

function openTextfileNewTab(url) {
    console.log(url);
    window.open(url, '_blank');
}

function openVideoFile(url) {
    console.log('openVideoFile('+url+')');

    let base = window.location.href;
    let baseSplit = base.split('/');
    baseSplit.pop();
    let baseJoin = baseSplit.join('/');

    var fileUrl = url.split('/').slice(1).join('/');

    /*console.log(base);
    console.log(baseSplit);
    console.log(baseJoin);
    console.log(fileUrl);
    console.log(baseJoin+'/'+fileUrl);*/

    let fullUrl = baseJoin+'/'+fileUrl;

    if (settings.videopath !== undefined) {
        if (settings.videopath == '') {
            window.open(url, '_blank');
        } else {
            window.open(settings.videopath+'?load='+fullUrl, '_blank');
        }
    } else {
        window.open(url, '_blank');
    }
}

function searchFile(q) {
    console.log('search file: '+q);

    if (q == '') {
        closeSearchArea();
    } else {
        searchPanelShown = true;
        var searchResults = filterByPath(JSON.parse(directoryTree),q);
        $('#folderBackground').hide();
        $('#itemArea').hide();
        console.log(searchResults);

        var htmlSearchRes = '';
        var countResults = 0;
        for (i in searchResults) {
            countResults++;
            var nameSplitted = searchResults[i].name.split('.');
            var fileExtension = nameSplitted[nameSplitted.length-1];
            if (searchResults[i].type == 'dir') {
                htmlSearchRes += '<div class="item itemDir itemList" onclick="changePathSearch(\''+searchResults[i].path+'/\')">';
                    htmlSearchRes += '<div class="itemLogo">'+setItemIcon('folder', searchResults[i].path)+'</div>';
                    htmlSearchRes += '<div class="itemText">';
                        htmlSearchRes += '<p split-lines>'+searchResults[i].name+'</p>';
                    htmlSearchRes += '</div>';
                    htmlSearchRes += '<div class="itemPath">'+searchResults[i].path+'</div>';
                htmlSearchRes += '</div>';
            } else if (searchResults[i].type == 'file') {
                htmlSearchRes += '<div class="item itemFich itemList" onclick="readFich(\''+searchResults[i].path+'\')">';
                    htmlSearchRes += '<div class="itemLogo">'+setItemIcon(fileExtension, searchResults[i].path)+'</div>';
                    htmlSearchRes += '<div class="itemText">';
                        htmlSearchRes += '<p split-lines>'+searchResults[i].name+'</p>';
                    htmlSearchRes += '</div>';
                    htmlSearchRes += '<div class="itemPath">'+searchResults[i].path+'</div>';
                htmlSearchRes += '</div>';
            }
        }

        if (countResults==0) {
            htmlSearchRes += '<p id="searchNoResults">'+noResultsTxt+'</p>';
        }

        $('#searchAreaContent').html(htmlSearchRes);

        $('#searchBox').focus();
        $('#searchAreaHeaderLeftTerm').text($('#searchBox').val());
        $('#searchArea').css('display','flex');
    }
}

function changePathSearch(path) {
    changePath(path);
    closeSearchArea();
}

function closeSearchArea() {
    searchPanelShown = false;
    $('#folderBackground').show();
    $('#itemArea').show();
    $('#searchArea').fadeOut(100);
    $('#searchBox').val('');
}

function filterByPath(data, searchText) {
    // Check if searchText is a string
    if (typeof searchText !== 'string') {
        throw new Error('searchText must be a string');
    }

    // Convert searchText to lowercase for case-insensitive search
    searchText = searchText.toLowerCase();

    const filteredData = [];

    function traverse(item) {
        // Check for files and directories
        if (item.type === 'file' || item.type === 'dir') {
            // Check if path (lowercase) includes searchText (lowercase)
            if (item.path.toLowerCase().includes(searchText)) {
                filteredData.push(item);
            }
        }

        // Recursively search content of directories
        if (item.content) {
            item.content.forEach(traverse);
        }
    }

    data.forEach(traverse);

    return filteredData;
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

    if (window.event.keyCode == 70) {
        console.log('tecla f');
        var searchBoxItem = document.getElementById('searchBox');
        if (document.activeElement !== searchBoxItem) {
            window.event.preventDefault();
            $('#searchBox').focus();
        }
    }

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
                $('#context>ul').append('<li id="contextExplore" onclick="'+target.closest('.item').attr('onclick')+'">'+langs[lang].context.contextExplore+'</li>'); // CLOSEST: obtener el primer elemento padre con una clase determinada
                $('#context>ul').append('<li id="contextAddFav" onclick="addFavorite('+target.closest('.item').attr('onclick').substring(target.closest('.item').attr('onclick').indexOf('(')+1, target.closest('.item').attr('onclick').indexOf(')'))+')">'+langs[lang].context.contextFavorites+'</li>');
            } else {
                $('#context>ul').append('<li id="contextOpen" onclick="'+target.closest('.item').attr('onclick')+'">'+langs[lang].context.contextOpen+'</li>');
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
            <div id="signInBrand">
                <svg id="signInLogo" xmlns="http://www.w3.org/2000/svg" width="293.08" height="293.08" viewBox="0 0 293.08 293.08"><title>logoAlternativo</title><path id="brandBack" d="M65,282.5a40.46,40.46,0,0,1-39.38-50,283,283,0,0,1,206.9-206.9A40.27,40.27,0,0,1,282.5,65V242A40.55,40.55,0,0,1,242,282.5Z" transform="translate(-6.92 -6.92)"/><path id="brandBorder" d="M242.2,41.92A23,23,0,0,1,265,65V242a23,23,0,0,1-23,23H65a22.71,22.71,0,0,1-18.16-8.84,22.42,22.42,0,0,1-4.23-19.48A265.46,265.46,0,0,1,236.68,42.6a23.21,23.21,0,0,1,5.51-.68h0m0-35a58.23,58.23,0,0,0-13.85,1.69A300.49,300.49,0,0,0,8.61,228.35C-.33,264.82,27.43,300,65,300H242a58,58,0,0,0,58-58V65A58.06,58.06,0,0,0,242.2,6.92Z" transform="translate(-6.92 -6.92)"/></svg>
                <h1>elcano</h1>
            </div>
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
                <svg id="headerLogo" xmlns="http://www.w3.org/2000/svg" width="293.08" height="293.08" viewBox="0 0 293.08 293.08"><title>logoAlternativo</title><path id="logoBack" d="M65,282.5a40.46,40.46,0,0,1-39.38-50,283,283,0,0,1,206.9-206.9A40.27,40.27,0,0,1,282.5,65V242A40.55,40.55,0,0,1,242,282.5Z" transform="translate(-6.92 -6.92)"/><path id="logoBorder" d="M242.2,41.92A23,23,0,0,1,265,65V242a23,23,0,0,1-23,23H65a22.71,22.71,0,0,1-18.16-8.84,22.42,22.42,0,0,1-4.23-19.48A265.46,265.46,0,0,1,236.68,42.6a23.21,23.21,0,0,1,5.51-.68h0m0-35a58.23,58.23,0,0,0-13.85,1.69A300.49,300.49,0,0,0,8.61,228.35C-.33,264.82,27.43,300,65,300H242a58,58,0,0,0,58-58V65A58.06,58.06,0,0,0,242.2,6.92Z" transform="translate(-6.92 -6.92)"/></svg>
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
                <div id="searchBar">
                    <form id="searchBarForm">
                        <input id="searchBox" type="text" name="" placeholder="<?php echo $langTxt[$lang]['header']['headSearch']; ?>" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" disabled />
                    </form>
                    <label for="searchBox"><svg id="searchBarIcon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg></label>
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
                    <div id="folderBackground">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h240l80 80h320q33 0 56.5 23.5T880-640v400q0 33-23.5 56.5T800-160H160Zm0-80h640v-400H447l-80-80H160v480Zm0 0v-480 480Z"/></svg>
                    </div>
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
                    <div id="searchArea">
                        <div id="searchAreaHeader">
                            <div id="searchAreaHeaderLeft">
                                <p><?php echo $langTxt[$lang]['section']['searchResultsTitle'] ?>"<span id="searchAreaHeaderLeftTerm"></span>"</p>
                            </div>
                            <div id="searchAreaHeaderRight">
                                <svg class="settingsClose" onclick="closeSearchArea()" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                            </div>
                        </div>
                        <div id="searchAreaContent">
                            <p>Archivo 1</p>
                            <p>Archivo 2</p>
                        </div>
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
                    <div>
                        <svg class="settingsTitleIcon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
                        <h2><?php echo $langTxt[$lang]['header']['headMoreSettings']; ?></h2>
                    </div>
                    <svg class="settingsClose" onclick="showSettings(false)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                </div>
                <div class="dialogBody">
                    <div class="dialogBodyCenter">
                        <div class="settingsItem" id="settingsItemGeneral">
                            <h3><?php echo $langTxt[$lang]['settings']['general']; ?></h3>

                            <input class="hiddenCheckbox" id="darkModeCheckbox" type="checkbox" />
                            <input class="hiddenCheckbox" id="showHiddenCheckbox" type="checkbox" />
                            <input class="hiddenCheckbox" id="showExtensionCheckbox" type="checkbox" />
                            <div>
                                <label id="settCheckLabelDarkMode" class="settCheckLabel" for="darkModeCheckbox">
                                    <svg class="settCheckboxIconOff" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Z"/></svg>
                                    <svg class="settCheckboxIconOn" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="m424-312 282-282-56-56-226 226-114-114-56 56 170 170ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z"/></svg>
                                    <p><?php echo $langTxt[$lang]['settings']['darkMode']; ?></p>
                                </label>
                            </div>
                            <div>
                                <label id="settCheckLabelShowHidden" class="settCheckLabel" for="showHiddenCheckbox">
                                    <svg class="settCheckboxIconOff" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Z"/></svg>
                                    <svg class="settCheckboxIconOn" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="m424-312 282-282-56-56-226 226-114-114-56 56 170 170ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z"/></svg>
                                    <p><?php echo $langTxt[$lang]['settings']['showHiddenFiles']; ?></p>
                                </label>
                            </div>
                            <div>
                                <label id="settCheckLabelShowExt" class="settCheckLabel" for="showExtensionCheckbox">
                                    <svg class="settCheckboxIconOff" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Z"/></svg>
                                    <svg class="settCheckboxIconOn" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="m424-312 282-282-56-56-226 226-114-114-56 56 170 170ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z"/></svg>
                                    <p><?php echo $langTxt[$lang]['settings']['showfileExtensions']; ?></p>
                                </label>
                            </div>
                        </div>
                        <div class="settingsItem" id="settingsItemStartUp">
                            <h3><?php echo $langTxt[$lang]['settings']['startUp']; ?></h3>
                            <div>
                                <p><?php echo $langTxt[$lang]['settings']['startUpDescrip']; ?></p>
                                <form id="settFormStartUp">
                                    <input class="hiddenCheckbox defaultView" id="defaultViewMosaic" type="radio" name="viewOption" value="Mosaic" />
                                    <input class="hiddenCheckbox defaultView" id="defaultViewList" type="radio" name="viewOption" value="List" />
                                    <input class="hiddenCheckbox defaultView" id="defaultViewWall" type="radio" name="viewOption" value="Icons" />
                                    <input class="hiddenCheckbox defaultView" id="defaultViewLast" type="radio" name="viewOption" value="last" />

                                    <label id="settRadioLabelMosaic" class="settRadioLabel" for="defaultViewMosaic">
                                        <svg class="settRadioIconOff" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <svg class="settRadioIconOn" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-280q83 0 141.5-58.5T680-480q0-83-58.5-141.5T480-680q-83 0-141.5 58.5T280-480q0 83 58.5 141.5T480-280Zm0 200q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p><?php echo $langTxt[$lang]['header']['headViewMosaic']; ?></p>
                                    </label>
                                    <label id="settRadioLabelList" class="settRadioLabel" for="defaultViewList">
                                        <svg class="settRadioIconOff" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <svg class="settRadioIconOn" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-280q83 0 141.5-58.5T680-480q0-83-58.5-141.5T480-680q-83 0-141.5 58.5T280-480q0 83 58.5 141.5T480-280Zm0 200q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p><?php echo $langTxt[$lang]['header']['headViewList']; ?></p>
                                    </label>
                                    <label id="settRadioLabelWall" class="settRadioLabel" for="defaultViewWall">
                                        <svg class="settRadioIconOff" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <svg class="settRadioIconOn" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-280q83 0 141.5-58.5T680-480q0-83-58.5-141.5T480-680q-83 0-141.5 58.5T280-480q0 83 58.5 141.5T480-280Zm0 200q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p><?php echo $langTxt[$lang]['header']['headViewWall']; ?></p>
                                    </label>
                                    <label id="settRadioLabelLast" class="settRadioLabel" for="defaultViewLast">
                                        <svg class="settRadioIconOff" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <svg class="settRadioIconOn" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-280q83 0 141.5-58.5T680-480q0-83-58.5-141.5T480-680q-83 0-141.5 58.5T280-480q0 83 58.5 141.5T480-280Zm0 200q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                                        <p><?php echo $langTxt[$lang]['settings']['startUpLast']; ?></p>
                                    </label>
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
                                <input id="systemIndexPriority" class="hiddenCheckbox" type="checkbox" />
                                <div>
                                    <label id="settCheckLabelIndexPriority" class="settCheckLabel" for="systemIndexPriority">
                                        <svg class="settCheckboxIconOff" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Z"/></svg>
                                        <svg class="settCheckboxIconOn" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="m424-312 282-282-56-56-226 226-114-114-56 56 170 170ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z"/></svg>
                                        <?php echo $langTxt[$lang]['settings']['defaultPriority']; ?>
                                    </label>
                                </div>
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
                        <div class="settingsItem" id="settingsItemVideoplayer">
                            <h3><?php echo $langTxt[$lang]['settings']['videoplayer']; ?></h3>
                            <div>
                                <p><?php echo $langTxt[$lang]['settings']['videoplayerDescrip']; ?></p>
                                <div class="settingsInputText">
                                    <input id="videoplayerPathInput" type="text" name="" value="" spellcheck="false" autocomplete="off" placeholder="<?php echo $langTxt[$lang]['settings']['videoplayerPlaceholder'] ?>" />
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
                        <p id="settingsVersion">(version inserted with js)</p>
                    </div>
                </div>
            </div>
            <div id="history" class="dialog">
                <div class="dialogTitleBar">
                    <div>
                        <svg class="settingsTitleIcon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                        <h2><?php echo $langTxt[$lang]['header']['headMoreHistory']; ?></h2>
                    </div>
                    <svg class="settingsClose" onclick="showHistory(false)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                </div>
                <div class="dialogBody" id="historyBody">
                    <div id="historyPaths"></div>
                </div>
            </div>
            <div id="imageViewer" class="dialog">
                <div class="dialogTitleBar">
                    <div>
                        <svg class="settingsTitleIcon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm40-80h480L570-480 450-320l-90-120-120 160Zm-40 80v-560 560Zm140-360q25 0 42.5-17.5T400-620q0-25-17.5-42.5T340-680q-25 0-42.5 17.5T280-620q0 25 17.5 42.5T340-560Z"/></svg>
                        <h2><?php echo $langTxt[$lang]['viewer']['imageViewerTitle']; ?><span id="imageViewerUrl"></span></h2>
                    </div>
                    <svg class="settingsClose" onclick="showImageViewer(false)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                </div>
                <div class="dialogBody" id="imageViewerBody">
                    <div id="imageViewerImageCanvas">
                    </div>
                    <div id="imageViewerOptions">
                        <svg id="imageViewerOpen" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18 19H6c-.55 0-1-.45-1-1V6c0-.55.45-1 1-1h5c.55 0 1-.45 1-1s-.45-1-1-1H5c-1.11 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6c0-.55-.45-1-1-1s-1 .45-1 1v5c0 .55-.45 1-1 1zM14 4c0 .55.45 1 1 1h2.59l-9.13 9.13c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L19 6.41V9c0 .55.45 1 1 1s1-.45 1-1V3h-6c-.55 0-1 .45-1 1z"/></svg>
                    </div>
                </div>
            </div>

            <div id="textfileViewer" class="dialog">
                <div class="dialogTitleBar">
                    <div>
                        <svg class="settingsTitleIcon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm40-80h480L570-480 450-320l-90-120-120 160Zm-40 80v-560 560Zm140-360q25 0 42.5-17.5T400-620q0-25-17.5-42.5T340-680q-25 0-42.5 17.5T280-620q0 25 17.5 42.5T340-560Z"/></svg>
                        <h2><?php echo $langTxt[$lang]['viewer']['textfileViewerTitle']; ?><span id="textfileViewerUrl"></span></h2>
                    </div>
                    <svg class="settingsClose" onclick="showTextfileViewer(false)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                </div>
                <div class="dialogBody" id="textfileViewerBody">
                    <div id="textfileViewerCanvas">
                    </div>
                    <div id="textfileViewerOptions">
                        <svg id="textfileViewerOpen" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18 19H6c-.55 0-1-.45-1-1V6c0-.55.45-1 1-1h5c.55 0 1-.45 1-1s-.45-1-1-1H5c-1.11 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6c0-.55-.45-1-1-1s-1 .45-1 1v5c0 .55-.45 1-1 1zM14 4c0 .55.45 1 1 1h2.59l-9.13 9.13c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L19 6.41V9c0 .55.45 1 1 1s1-.45 1-1V3h-6c-.55 0-1 .45-1 1z"/></svg>
                    </div>
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
