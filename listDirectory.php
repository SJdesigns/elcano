<?php

if (isset($_POST['ruta'])) {
	if (file_exists($_POST['ruta'])) {
		listDirectory($_POST['ruta']);
	} else {
		directoryError(404,$_POST['ruta']);
	}
} else {
	directoryError(403);
}

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
		$return['error']['message'] = 'No se ha encontrado el directorio';
	} else if ($code == '403') {
		$return['error']['message'] = 'No tiene acceso al directorio';
	}

	echo json_encode($return);
}

?>
