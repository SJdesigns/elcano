<?php

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

?>
