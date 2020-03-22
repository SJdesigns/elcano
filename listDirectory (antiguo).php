<?php

function listDirectory($ruta) {
	if ($ruta=='./') {
		$path = './';
	} else {
		$path = './' . $ruta;
	}

	$directories = '';
	$files = '';

	$cont = 0;
	$directorio = opendir($path); //ruta actual
	while ($archivo = readdir($directorio))
	{
		$archivoISO = $archivo;
		//$archivo = iconv( "iso-8859-1", "utf-8", $archivo );
		if (is_dir($path . $archivo))//verificamos si es o no un directorio
		{
			// directories
			if ($archivo != "." && $archivo != ".." && $archivo != "explorerConf") {
				$content = '<div class="dir" onclick="changePath(\'' . $ruta . $archivo . '/\')"><input type="hidden" value="d" /><img class="fileIcon" src="fileExplorer/img/folder.png" /><p class="fileName">' . $archivo . '</p></div>';
				$directories.=$content;
				$cont++;
			}
		} else {
			// files

			// obtenemos la extensión del fichero
			$size = filesize($path . $archivoISO);
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
			$fileIcon = '';
			$fileType = '';
			if (substr($archivo, -4) == ".txt") { $fileIcon = 'typeTXT.png'; $fileType = 'archivo de texto'; }
			else if (substr($archivo, -4) == ".pdf") { $fileIcon = 'typePDF.png'; $fileType = 'documento de Adobe Acrobat'; }
			else if (substr($archivo, -4) == ".exe") { $fileIcon = 'typeEXE.png'; $fileType = 'archivo ejecutable'; }
			else if (substr($archivo, -4) == ".bat") { $fileIcon = 'typeBAT.png'; $fileType = 'secuencia de comandos por lotes'; }
			else if (substr($archivo, -4) == ".doc" || substr($archivo, -5) == ".docx") { $fileIcon = 'file.png'; $fileType = 'documento de texto de Word'; }
			else if (substr($archivo, -4) == ".xls" || substr($archivo, -5) == ".xlsx") { $fileIcon = 'file.png'; $fileType = 'hoja de cálculo de Excel'; }
			else if (substr($archivo, -4) == ".ppt" || substr($archivo, -5) == ".pptx") { $fileIcon = 'file.png'; $fileType = 'presentación de PowerPoint'; }
			else if (substr($archivo, -4) == ".jpg") { $fileIcon = 'imagen'; $fileType = 'imagen de mapa de bits jpg'; }
			else if (substr($archivo, -4) == ".png") { $fileIcon = 'imagen'; $fileType = 'imagen vectorial png'; }
			else if (substr($archivo, -4) == ".svg") { $fileIcon = 'imagen'; $fileType = 'gráficos escalables svg'; }
			else if (substr($archivo, -4) == ".gif") { $fileIcon = 'imagen'; $fileType = 'imagen animada gif'; }
			else if (substr($archivo, -4) == ".psd") { $fileIcon = 'typePSD.png'; $fileType = 'proyecto de Adobe Photoshop'; }
			else if (substr($archivo, -4) == ".ai") { $fileIcon = 'typeAI.png'; $fileType = 'proyecto de Adobe Illustrator'; }

			else if (substr($archivo, -4) == ".mp3" || substr($archivo, -4) == ".wav") { $fileIcon = 'typeMP3.png'; $fileType = 'archivo de audio'; }
			else if (substr($archivo, -4) == ".avi" || substr($archivo, -4) == ".mp4" || substr($archivo, -4) == ".ogg") { $fileIcon = 'file.png'; $fileType = 'archivo de vídeo'; }
			else if (substr($archivo, -4) == ".zip") { $fileIcon = 'typeZIP.png'; $fileType = 'archivo comprimido ZIP'; }
			else if (substr($archivo, -4) == ".rar") { $fileIcon = 'typeRAR.png'; $fileType = 'archivo comprimido RAR'; }
			else if (substr($archivo, -5) == ".html") { $fileIcon = 'typeHTML.png'; $fileType = 'archivo de página web'; }
			else if (substr($archivo, -4) == ".css") { $fileIcon = 'typeCSS.svg'; $fileType = 'hoja de estilos en cascada'; }
			else if (substr($archivo, -3) == ".js") { $fileIcon = 'typeJS.png'; $fileType = 'archivo javascript'; }
			else if (substr($archivo, -4) == ".php") { $fileIcon = 'typePHP.png'; $fileType = 'archivo PHP'; }
			else if (substr($archivo, -4) == ".ico") { $fileIcon = 'file.png'; $fileType = 'icono'; }
			else if (substr($archivo, -4) == ".log") { $fileIcon = 'typeLOG.png'; $fileType = 'archivo de logs'; }
			else { $fileIcon = 'file.png'; $fileType = 'archivo desconocido'; }							// tipo de archivo desconocido

			$content = '<div class="dir" onclick="readFich(\'' . $ruta . $archivo . '\')">
				<input type="hidden" value="f" />';
				if ($fileIcon=='imagen') {
					$content .= '<img class="fileIcon" src="' . $ruta . $archivo . '" />';
				} else {
					$content .= '<img class="fileIcon" src="fileExplorer/img/' . $fileIcon . '" />';
				}
				$content .= '<p class="fileName">' . $archivo . '</p>
				<p class="fileType">' . $fileType . '</p>
				<p class="fileSize">' . $size . '</p>
			</div>';
			$files.=$content;
			$cont++;
		}
	}

	$response = array('dir'=>$directories,'fich'=>$files);
	echo json_encode($response);
}

if (isset($_GET['ruta'])) {
	listDirectory($_GET['ruta']);
} else {
	listDirectory('./');
}

?>
