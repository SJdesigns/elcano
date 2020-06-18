<?php
// texto en diferentes idiomas

$lang = [
    'es' => [
        'header' => [
            'headStartUp' => 'Ejecutar el índice del directorio',
            'headDataBase' => 'Acceder a la base de datos',
            'headFavorite' => 'Añadir a favoritos',
            'headView' => 'Cambiar la vista',
            'headMore' => 'Más opciones',
            'headViewMosaic' => 'Mosaico',
            'headViewList' => 'Lista',
            'headViewWall' => 'Muro',
            'headMoreHistory' => 'Historial',
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
        ],
        'context' => [
            'contextExplore' => 'Explorar',
            'contextOpen' => 'Abrir',
            'contextFavorites' => 'Añadir a favoritos',
        ],
        'history' => [
            'historyHome' => 'Página de Inicio',
        ],
        'settings' => [
            'general' => 'General',
            'darkMode' => 'Activar el modo oscuro',
            'showHidenFiles' => 'Mostrar archivos ocultos',
            'showfileExtensions' => 'Mostrar extensiones de los archivos',
            'startUp' => 'Estado inicial',
            'startUpDescrip' => 'Vista activa por defecto',
            'startUpLast' => 'Último activo',
            'hideFiles' => 'Omitir archivos',
            'hideFilesDescrip' => 'Nombres de archivos y extensiones separados por comas',
            'priority' => 'Prioridad de Índices',
            'defaultPriority' => 'Índice predeterminado del sistema',
            'priorityDescrip' => 'Lista de prioridad de ejecución para los directorios',
            'database' => 'Base de datos',
            'databaseDescrip' => 'Ruta de acceso a la base de datos',
            'default' => 'Configuración predeterminada',
            'defaultButton' => 'Reestablecer',
            'defaultDescrip' => 'Volver a la configuración por defecto'
        ]
    ],
];


echo '<pre>';
echo print_r($lang);
echo '</pre>';

//echo '<p>'. json_encode($lang) . '</p>';

?>
