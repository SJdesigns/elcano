<?php
/* ---- elcano Explorer v3.0 - beta 2.1 ---- */

if (isset($_GET['token'])) {
    // auth.php
} else if (isset($_POST['listDir'])) {
    // listDirectory.php
} else if (isset($_POST['dirTree'])) {
    // directoryTree.php
} else {
    // lang.php
    if (!isset($_COOKIE['elcano-lang'])) { setcookie('elcano-lang', substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2), time()+60*60*24*365, '/', "", false, false); $self = $_SERVER['PHP_SELF']; header("Location: $self"); };
    $langTxt=['es'=>['login'=>['title'=>'Inicio de Sesión','user'=>'usuario','pass'=>'contraseña','submit'=>'Continuar'],'header'=>['headStartUp'=>'Ejecutar el índice del directorio','headDataBase'=>'Acceder a la base de datos','headFavorite'=>'Añadir a favoritos','headNotFavorite'=>'Quitar de favoritos','headView'=>'Cambiar la vista','headMore'=>'Más opciones','headViewMosaic'=>'Mosaico','headViewList'=>'Lista','headViewWall'=>'Muro','headMoreHistory'=>'Historial','headMoreHistoryPrev'=>'Atrás','headMoreHistoryNext'=>'Adelante','headMoreShowExpl'=>'Mostrar Explorador','headMoreHideExpl'=>'Ocultar Explorador','headMoreSettings'=>'Configuración','headMoreLogout'=>'Cerrar Sesión'],'aside'=>['asideFav'=>'Favoritos','asideDir'=>'Directorios',],'section'=>['sectionFolder'=>'carpetas','sectionFiles'=>'archivos','noResults'=>'Esta carpeta está vacia',],'context'=>['contextExplore'=>'Explorar','contextOpen'=>'Abrir','contextFavorites'=>'Agregar a favoritos',],'history'=>['historyHome'=>'Página de Inicio',],'settings'=>['general'=>'General','darkMode'=>'Activar el modo oscuro','showHiddenFiles'=>'Mostrar archivos ocultos','showfileExtensions'=>'Mostrar extensiones de los archivos','startUp'=>'Estado inicial','startUpDescrip'=>'Vista activa por defecto','startUpLast'=>'Último activo','hideFiles'=>'Omitir archivos','hideFilesDescrip'=>'Nombres de archivos y extensiones separados por comas','hideFilesPlaceholder'=>'ficheros ignorados','priority'=>'Prioridad de Índices','defaultPriority'=>'Índice predeterminado del sistema','priorityDescrip'=>'Lista de prioridad de ejecución para los directorios','priorityPlaceholder'=>'orden de prioridad de archivos','database'=>'Base de datos','databaseDescrip'=>'Ruta de acceso a la base de datos','databasePlaceholder'=>'ruta de la base de datos','default'=>'Configuración predeterminada','defaultButton'=>'Reestablecer','defaultDescrip'=>'Volver a la configuración por defecto'],'error'=>[]],'en'=>['login'=>['title'=>'Log In','user'=>'user','pass'=>'password','submit'=>'Continue'],'header'=>['headStartUp'=>'Run directory index','headDataBase'=>'Access database','headFavorite'=>'Add to favorites','headNotFavorite'=>'Remove from favorites','headView'=>'Change view','headMore'=>'More options','headViewMosaic'=>'Mosaic','headViewList'=>'List','headViewWall'=>'Wall','headMoreHistory'=>'History Review','headMoreHistoryPrev'=>'Prev','headMoreHistoryNext'=>'Next','headMoreShowExpl'=>'Show Explorer','headMoreHideExpl'=>'Hide Explorer','headMoreSettings'=>'Settings','headMoreLogout'=>'Log Out'],'aside'=>['asideFav'=>'Favorites','asideDir'=>'Directory Tree',],'section'=>['sectionFolder'=>'folders','sectionFiles'=>'files','noResults'=>'This folder is empty',],'context'=>['contextExplore'=>'Explore','contextOpen'=>'Open','contextFavorites'=>'Add to favorites',],'history'=>['historyHome'=>'Homepage',],'settings'=>['general'=>'General','darkMode'=>'Activate dark mode','showHiddenFiles'=>'Show hidden files','showfileExtensions'=>'Show file extensions','startUp'=>'Start Up','startUpDescrip'=>'Default active view','startUpLast'=>'Last active','hideFiles'=>'Ignore Files','hideFilesDescrip'=>'File names and extensions separated by commas','hideFilesPlaceholder'=>'ignored files','priority'=>'Index Priorities','defaultPriority'=>'Default System Index','priorityDescrip'=>'Execution priority list for directories','priorityPlaceholder'=>'file priority order','database'=>'Database','databaseDescrip'=>'Database path','databasePlaceholder'=>'database path','default'=>'Default Settings','defaultButton'=>'Reset','defaultDescrip'=>'Return to default settings'],'error'=>[]],];
    if(isset($_COOKIE['elcano-lang'])){if(!isset($langTxt[$_COOKIE['elcano-lang']])){$_COOKIE['elcano-lang']='en';}}else{$_COOKIE['elcano-lang']='en';};$lang = $_COOKIE['elcano-lang'];
    $langJson=json_encode($langTxt);

    // html

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="img/favicon.png">
    <title>elcano</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/main.css">
    <script type="text/javascript">var langs = <?php echo $langJson; ?>;</script>
    <script type="text/javascript" src="js/jquery-3.1.1.min.js">
    </script>
    <script type="text/javascript" src="js/main.js"></script>
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
