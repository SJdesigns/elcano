<?php

function directoryTree($path) {
// función que rellena un array con los datos de cada directorio y devuelve los datos
	if (substr($path,-1) == '/') {
		$path = substr($path,0,-1);
	}

	$tree = [];

	$explodePath = explode($path,'/');

	$directory = retrieveDirectory($path);
	$tree[$path] = $directory;

	foreach ($tree[$path] as $value) {
		//echo 'path: ' . $value['path']; // ./
		//echo 'name: ' . $value['name']; // blank
		//echo 'type: ' . $value['type']; // dir

		if ($value['type'] == 'dir') {
			$directory = retrieveDirectory($value['path'].'/'.$value['name']);
			$explodePath = explode('/',$value['path'].'/'.$value['name']);

			/*echo '<pre>';
			print_r($explodePath);
			echo '</pre>';*/

			echo '<p>';
			$explodeCursor = 0;
			foreach($explodePath as $value2) {
				$explodeCursor++;
				if (count($explodePath) > 1) {

				}
				echo $value2 . ' ';

			}
			echo '</p>';
		}
	}

	/*echo '<pre>';
	print_r($tree);
	echo '</pre>';*/

}

function retrieveDirectory($path) {
// recuperar directorio individual (llamada desde la función directoryTree)
// $path no debe terminar en /

	$directory = [];

	$explDir = opendir($path);
	while ($explFich = readdir($explDir)) {
		if (is_dir($path . '/' . $explFich)) {
			if ($explFich != "." && $explFich != "..") {
				array_push($directory,['path' => $path, 'name' => $explFich, 'type' => 'dir']);
			}
		} else {
			array_push($directory,['path' => $path,'name' => $explFich, 'type' => 'fich']);
		}
	}

	/*echo '<pre>';
	print_r($directory);
	echo '</pre>';*/

	return $directory;
}

retrieveDirectory($_GET['ruta']);

if (isset($_GET['ruta'])) {
	$ruta = $_GET['ruta'];

	if (strpos($ruta,'../') !== false) {
		$ruta = './';
	}
	directoryTree($ruta);
} else {
	echo '<p>Árbol de directorios</p><p>no disponible</p>';
}

?>