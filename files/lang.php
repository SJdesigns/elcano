<?php
// language support
// this file must be copied into the index.php in it's designated area to apply any change

if (!isset($_COOKIE['elcano-lang'])) {
    setcookie('elcano-lang', substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2), time()+60*60*24*365, '/', "", false, false);
    $self = $_SERVER['PHP_SELF'];
    header("Location: $self");
};

$langTxt = [
    'es' => [
        'langName' => 'Español',
        'login' => [
            'title' => 'Inicio de Sesión',
            'user' => 'usuario',
            'pass' => 'contraseña',
            'submit' => 'Continuar',
            'error' => [
                'WrongUser' => 'Usuario erroneo',
                'AccessGranted' => 'Acceso permitido',
                'WrongCredentials' => 'Contraseña erronea',
                'MissingData' => 'Falta Informacion',
            ],
        ],
        'header' => [
            'headSearch' => 'Buscar ficheros',
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
            'searchResultsTitle' => 'Resultados de la búsqueda para',
            'searchNoResults' => 'Ningún fichero contiene el término de búsqueda',
        ],
        'context' => [
            'contextExplore' => 'Explorar',
            'contextOpen' => 'Abrir',
            'contextFavorites' => 'Agregar a favoritos',
        ],
        'history' => [
            'historyHome' => 'Página de Inicio',
        ],
        'viewer' => [
            'imageViewerTitle' => 'Visor de imágenes',
            'textfileViewerTitle' => 'Visor de texto plano',
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
            'lang' => 'Idioma',
            'langDescrip' => 'Selecciona el idioma de la aplicación',
            'default' => 'Configuración predeterminada',
            'defaultButton' => 'Reestablecer',
            'defaultDescrip' => 'Volver a la configuración por defecto'
        ],
        'error' => [
        ]
    ],
    'en' => [
        'langName' => 'English',
        'login' => [
            'title' => 'Log In',
            'user' => 'user',
            'pass' => 'password',
            'submit' => 'Continue',
            'error' => [
                'WrongUser' => 'Wrong user',
                'AccessGranted' => 'Access granted',
                'WrongCredentials' => 'Wrong credentials',
                'MissingData' => 'Missing data',
            ],
        ],
        'header' => [
            'headSearch' => 'Search files',
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
            'searchResultsTitle' => 'Search results for',
            'searchNoResults' => 'Your search didn\'t match any file',
        ],
        'context' => [
            'contextExplore' => 'Explore',
            'contextOpen' => 'Open',
            'contextFavorites' => 'Add to favorites',
        ],
        'history' => [
            'historyHome' => 'Homepage',
        ],
        'viewer' => [
            'imageViewerTitle' => 'Image viewer',
            'textfileViewerTitle' => 'Plain text viewer',
        ],
        'settings' => [
            'general' => 'General',
            'darkMode' => 'Enable dark mode',
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
            'lang' => 'Language',
            'langDescrip' => 'Select the app language',
            'default' => 'Default Settings',
            'defaultButton' => 'Reset',
            'defaultDescrip' => 'Return to default settings'
        ],
        'error' => [
        ]
    ],
    'fr' => [
        'langName' => 'Français',
        'login' => [
            'title' => 'Se Connecter',
            'user' => 'Nom d\'utilisateur',
            'pass' => 'mot de passe',
            'submit' => 'Se Connecter',
            'error' => [
                'WrongUser' => 'Utilisateur inexistant',
                'AccessGranted' => 'Accès autorisé',
                'WrongCredentials' => 'mot de passe incorrect',
                'MissingData' => 'Manque des informations',
            ],
        ],
        'header' => [
            'headSearch' => 'Rechercher des fichers',
            'headStartUp' => 'Exécuter l\'index du répertoire',
            'headDataBase' => 'Accéder à la base de données',
            'headFavorite' => 'Ajouter aux favoris',
            'headNotFavorite' => 'Supprimer des favoris',
            'headView' => 'Change de vue',
            'headMore' => 'plus d\' options',
            'headViewMosaic' => 'Mosaïque',
            'headViewList' => 'Liste',
            'headViewWall' => 'Mur',
            'headMoreHistory' => 'Revue de l\' Histoire',
            'headMoreHistoryPrev' => 'Prev',
            'headMoreHistoryNext' => 'Prochain',
            'headMoreShowExpl' => 'Montrer l\' explorateur',
            'headMoreHideExpl' => 'Chacher l\' explorateur',
            'headMoreSettings' => 'réglages',
            'headMoreLogout' => 'Se déconnecter'
        ],
        'aside' => [
            'asideFav' => 'Favoris',
            'asideDir' => 'Arbre des dossiers',
        ],
        'section' => [
            'sectionFolder' => 'dossiers',
            'sectionFiles' => 'fichiers',
            'noResults' => 'ce dossier est vide',
            'searchResultsTitle' => 'Résultats de recherche pour',
            'searchNoResults' => 'Votre recherche n\'a donné aucun résultat',
        ],
        'context' => [
            'contextExplore' => 'Explorer',
            'contextOpen' => 'Ouvrir',
            'contextFavorites' => 'Ajouter aux favoris',
        ],
        'history' => [
            'historyHome' => 'Accueil',
        ],
        'viewer' => [
            'imageViewerTitle' => 'Visionneuse d\'images',
            'textfileViewerTitle' => 'Visionneuse de texte brut',
        ],
        'settings' => [
            'general' => 'Général',
            'darkMode' => 'Activer le mode sombre',
            'showHiddenFiles' => 'montrer les fichiers cachés',
            'showfileExtensions' => 'Afficher les extensions de fichier',
            'startUp' => 'Start Up',
            'startUpDescrip' => 'Vue active par défaut',
            'startUpLast' => 'Dernier actif',
            'hideFiles' => 'Fichiers ignorés',
            'hideFilesDescrip' => 'Noms de fichiers et extensions séparés par des virgules',
            'hideFilesPlaceholder' => 'fichiers ignorés',
            'priority' => 'priorités d\'index',
            'defaultPriority' => 'Index système par défaut',
            'priorityDescrip' => 'Liste des priorités d\'exécution pour les dossiers',
            'priorityPlaceholder' => 'ordre de priorité des fichiers',
            'database' => 'base de données',
            'databaseDescrip' => 'chemin de la base de données',
            'databasePlaceholder' => 'chemin de la base de données',
            'lang' => 'langue',
            'langDescrip' => 'Sélectionnez la langue de l\'application',
            'default' => 'paramètres par défaut',
            'defaultButton' => 'réinitialiser',
            'defaultDescrip' => 'Revenir aux paramètres par défaut'
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
