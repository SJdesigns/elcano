<?php

function directoryTree($explRuta, $rutaGlobal) {
	//echo 'ruta global: ' . $rutaGlobal . '<br />';
	//echo 'ruta nivel: ' . $explRuta . '<br />';
	$sepRutaGlobal = explode("/", $rutaGlobal);
	$sepRutaNivel = explode("/", $explRuta);
	$contGlobal = count($sepRutaGlobal) - 1;
	$contNivel = count($sepRutaNivel) - 2;

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

				echo '<summary><p class="linkTree" onclick="getData(\'' . $explRuta2 . $explFich . '/\')">' . $explFich . '</p></summary>';
				directoryTree($explRuta . $explFich . "/", $rutaGlobal);
				echo '</details>';
			}
	    }
	}
}

if (isset($_GET['ruta'])) {
	$ruta = $_GET['ruta'];

	if ($ruta == './') {
		$ruta = '../';
	}
	directoryTree("../", $ruta);
} else {
	echo '<p>Árbol de directorios</p><p>no disponible</p>';
}

?>