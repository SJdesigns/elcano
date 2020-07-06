<?php
// language support

if (!isset($_COOKIE['elcano-lang'])) {
    setcookie('elcano-lang', substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2), time()+60*60*24*365, '/', "", false, false);
    $self = $_SERVER['PHP_SELF'];
    header("Location: $self");
};

$langTxt = [
    'es' => [
        'login' => [
            'title' => 'Inicio de Sesión',
            'user' => 'usuario',
            'pass' => 'contraseña',
            'submit' => 'Continuar'
        ],
        'header' => [
            'headStartUp' => 'Ejecutar el índice del directorio',
            'headDataBase' => 'Acceder a la base de datos',
            'headFavorite' => 'Añadir a favoritos',
            'headNotFavorite' => 'Quitar de favoritos',
            'headView' => 'Cambiar la vista',
            'headMore' => 'Más opciones',
            'headViewMosaic' => 'Mosaico',
            'headViewList' => 'Lista',
            'headViewWall' => 'Muro',
            'headMoreHistory' => 'Historial',
            'headMoreHistoryPrev' => 'Atrás',
            'headMoreHistoryNext' => 'Adelante',
            'headMoreShowExpl' => 'Mostrar Explorador',
            'headMoreHideExpl' => 'Ocultar Explorador',
            'headMoreSettings' => 'Configuración',
            'headMoreLogout' => 'Cerrar Sesión'
        ],
        'aside' => [
            'asideFav' => 'Favoritos',
            'asideDir' => 'Directorios',
        ],
        'section' => [
            'sectionFolder' => 'carpetas',
            'sectionFiles' => 'archivos',
            'noResults' => 'Esta carpeta está vacia',
        ],
        'context' => [
            'contextExplore' => 'Explorar',
            'contextOpen' => 'Abrir',
            'contextFavorites' => 'Agregar a favoritos',
        ],
        'history' => [
            'historyHome' => 'Página de Inicio',
        ],
        'settings' => [
            'general' => 'General',
            'darkMode' => 'Activar el modo oscuro',
            'showHiddenFiles' => 'Mostrar archivos ocultos',
            'showfileExtensions' => 'Mostrar extensiones de los archivos',
            'startUp' => 'Estado inicial',
            'startUpDescrip' => 'Vista activa por defecto',
            'startUpLast' => 'Último activo',
            'hideFiles' => 'Omitir archivos',
            'hideFilesDescrip' => 'Nombres de archivos y extensiones separados por comas',
            'hideFilesPlaceholder' => 'ficheros ignorados',
            'priority' => 'Prioridad de Índices',
            'defaultPriority' => 'Índice predeterminado del sistema',
            'priorityDescrip' => 'Lista de prioridad de ejecución para los directorios',
            'priorityPlaceholder' => 'orden de prioridad de archivos',
            'database' => 'Base de datos',
            'databaseDescrip' => 'Ruta de acceso a la base de datos',
            'databasePlaceholder' => 'ruta de la base de datos',
            'default' => 'Configuración predeterminada',
            'defaultButton' => 'Reestablecer',
            'defaultDescrip' => 'Volver a la configuración por defecto'
        ],
        'error' => [
        ]
    ],
    'en' => [
        'login' => [
            'title' => 'Log In',
            'user' => 'user',
            'pass' => 'password',
            'submit' => 'Continue'
        ],
        'header' => [
            'headStartUp' => 'Run directory index',
            'headDataBase' => 'Access database',
            'headFavorite' => 'Add to favorites',
            'headNotFavorite' => 'Remove from favorites',
            'headView' => 'Change view',
            'headMore' => 'More options',
            'headViewMosaic' => 'Mosaic',
            'headViewList' => 'List',
            'headViewWall' => 'Wall',
            'headMoreHistory' => 'History Review',
            'headMoreHistoryPrev' => 'Prev',
            'headMoreHistoryNext' => 'Next',
            'headMoreShowExpl' => 'Show Explorer',
            'headMoreHideExpl' => 'Hide Explorer',
            'headMoreSettings' => 'Settings',
            'headMoreLogout' => 'Log Out'
        ],
        'aside' => [
            'asideFav' => 'Favorites',
            'asideDir' => 'Directory Tree',
        ],
        'section' => [
            'sectionFolder' => 'folders',
            'sectionFiles' => 'files',
            'noResults' => 'This folder is empty',
        ],
        'context' => [
            'contextExplore' => 'Explore',
            'contextOpen' => 'Open',
            'contextFavorites' => 'Add to favorites',
        ],
        'history' => [
            'historyHome' => 'Homepage',
        ],
        'settings' => [
            'general' => 'General',
            'darkMode' => 'Activate dark mode',
            'showHiddenFiles' => 'Show hidden files',
            'showfileExtensions' => 'Show file extensions',
            'startUp' => 'Start Up',
            'startUpDescrip' => 'Default active view',
            'startUpLast' => 'Last active',
            'hideFiles' => 'Ignore Files',
            'hideFilesDescrip' => 'File names and extensions separated by commas',
            'hideFilesPlaceholder' => 'ignored files',
            'priority' => 'Index Priorities',
            'defaultPriority' => 'Default System Index',
            'priorityDescrip' => 'Execution priority list for directories',
            'priorityPlaceholder' => 'file priority order',
            'database' => 'Database',
            'databaseDescrip' => 'Database path',
            'databasePlaceholder' => 'database path',
            'default' => 'Default Settings',
            'defaultButton' => 'Reset',
            'defaultDescrip' => 'Return to default settings'
        ],
        'error' => [
        ]
    ],
];

if (isset($_COOKIE['elcano-lang'])) {
    if (!isset($langTxt[$_COOKIE['elcano-lang']])) {
        $_COOKIE['elcano-lang'] = 'en';
    }
} else {
    $_COOKIE['elcano-lang'] = 'en';
}
$lang = $_COOKIE['elcano-lang'];

$langJson = json_encode($langTxt);

?>
