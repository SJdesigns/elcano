<?php

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

/*function directoryTree2($root) {
    $treeData = [];


	function recursiveTree($root) {
		treePath($root);
	}

	recursiveTree($root);
}

function treePath($path) { // lee directorios de una ruta concreta
    $directories = [];

    $stream = opendir($path);
    while ($fich = readdir($stream)) {
		if (is_dir($path . $fich)) {
			if ($fich != "." && $fich != "..") {
                echo '<p>'.$fich.' <small>('.$path.$fich.')</small></p>';
                $item = [];
                $item['path'] = $path.$fich;
                $item['dir'] = $fich;
                array_push($directories,$item);
            }
        }
    }

    return $directories;

    /*echo '<pre>';
    print_r($directories);
    echo '</pre>';*/
//}

?>
