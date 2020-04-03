<?php
/* ---- elcano Explorer v3.0 - alpha 1.5. ---- */

if (isset($_POST['token'])) {
    // auth.php

    // Usuarios Habilitados
    // ----------------------------------------------------
    // $userList = array('user'=>'pass')
    $userList = array(
        'root' => 'admin',
    );
    // ----------------------------------------------------

    if (isset($_GET['token'])) {

        if (isset($_GET['user']) && isset($_GET['pass'])) {

            $user = htmlspecialchars($_GET['user']);
            $pass = htmlspecialchars($_GET['pass']);
            $token = htmlspecialchars($_GET['token']);

            $existentUser = 0;
            $credentialsCorrect = 0;

            foreach ($userList as $key => $valor) {
                if ($user == $key) {
                    $existentUser++;
                    if ($pass == sha1($valor)) {
                        $credentialsCorrect++;
                        $return['valor'] = $valor;
                    }
                }
            }

            if ($existentUser==0) {
                $return['status'] = 400;
                $return['token'] = $token;
                $return['message'] = 'Wrong User';
            } else {
                if ($credentialsCorrect>0) { // login successfull
                    $return['status'] = 200;
                    $return['token'] = $token;
                    $return['message'] = 'Access Granted';
                } else { // wrong password
                    $return['status'] = 400;
                    $return['token'] = $token;
                    $return['message'] = 'Wrong Credentials';
                }
            }

            /*echo '<pre>';
            print_r($return);
            echo '</pre>';*/

        } else { // missing login data
            $return['status'] = 400;
            $return['token'] = $token;
            $return['message'] = 'missing data';
        }

    } else { // missing token
        $return['status'] = 400;
        $return['token'] = 'null';
        $return['message'] = 'missing data';
    }

    echo json_encode($return);
} else if (isset($_POST['listDir'])) {
    // listDirectory.php



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

    if (isset($_POST['ruta'])) {
    	if (file_exists($_POST['ruta'])) {
    		listDirectory($_POST['ruta']);
    	} else {
    		directoryError(404,$_POST['ruta']);
    	}
    } else {
    	directoryError(403);
    }

} else if (isset($_POST['dirTree'])) {
    // directoryTree.php

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

} else {
    // html

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="img/logo.png">
    <title>elcano v3.0</title>
    <style>
    @font-face{font-family:Roboto-Regular;font-style:normal;font-weight:400;src:local("Roboto-Regular");src:url(fonts/Roboto-Regular.ttf) format("truetype")}@font-face{font-family:Roboto-Light;font-style:normal;font-weight:400;src:local("Roboto-Light");src:url(fonts/Roboto-Light.ttf) format("truetype")}*{padding:0;margin:0;font-family:Roboto-Regular,sans-serif}body{overflow:overlay}.screen{position:fixed;width:100%;height:100%;background-color:#fff;display:none}#startUp{display:block}#blocked{display:none;z-index:15}#blocked #blockedBack{position:absolute;width:100%;height:100%;display:flex;justify-content:center;align-items:center}#blocked #blockedBack #signIn{position:relative;width:340px;height:auto;box-shadow:2px 2px 8px rgba(0,0,0,.1)}#blocked #blockedBack #signIn #signInTitle{position:relative;width:100%;height:auto;padding:25px 0;text-align:center;color:#fff;background-color:#34515f}#blocked #blockedBack #signIn #signInTitle h1{font-family:Roboto-Light}#blocked #blockedBack #signIn #signInBody{position:relative;width:100%;height:auto}#blocked #blockedBack #signIn #signInBody #signInError{position:relative;width:100%;min-height:30px;padding:15px 0}#blocked #blockedBack #signIn #signInBody .signInBodyItem{position:relative;margin:0 40px 28px}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser{width:100%;height:40px;font-size:13pt;padding-left:40px;border:0;outline:0;box-sizing:border-box}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:hover~.signInInputEffect,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:hover~.signInInputEffect{width:100%;left:0;border-color:#ccc}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:focus~.signInInputEffect,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:focus~.signInInputEffect{width:100%;left:0;border-color:#34515f}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInPass:focus~.signInIcons,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInUser:focus~.signInIcons{fill:#444}#blocked #blockedBack #signIn #signInBody .signInBodyItem .signInIcons{position:absolute;width:25px;height:25px;left:6px;top:8px;fill:#777}#blocked #blockedBack #signIn #signInBody .signInBodyItem .signInInputEffect{position:absolute;width:0%;left:50%;bottom:0;border-bottom:2px solid;transition:all .2s}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit{width:100%;height:50px;color:#34515f;font-size:13pt;margin-top:15px;text-align:center;border:0;outline:0;background-color:#fff}#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit:focus,#blocked #blockedBack #signIn #signInBody .signInBodyItem #signInSubmit:hover{background-color:#f4f4f4}header{position:relative;width:100%;height:50px;top:0;background-color:#34515f;display:flex;justify-content:space-between;z-index:10}header div#headerTitle{position:relative;width:auto;height:100%;display:flex;padding-right:50px}header div#headerTitle #headerLogo{position:relative;width:40px;height:40px;top:5px;left:20px}header div#headerTitle h1{color:#fff;padding:4px 0;margin-left:30px;cursor:default;font-family:Roboto-Light,sans-serif}header nav{position:relative;width:100%;height:100%;display:flex}header nav .navItem{position:relative;height:34px;color:#fff;margin:8px 0;padding:7px 10px;box-sizing:border-box;cursor:pointer}header nav .navItem:hover{background-color:rgba(255,255,255,.1)}header nav .navSeparator{position:relative;margin:8px 0;padding:8px 6px;font-size:11pt;color:#999;cursor:default}header div#options{position:relative;width:200px;height:100%;padding-left:50px;padding-right:20px;display:flex;flex-direction:row-reverse}header div#options .option{position:relative;width:50px;height:50px}header div#options .option .optionArea{position:absolute;width:0%;height:0%;left:50%;top:50%;border-radius:50%;background-color:rgba(255,255,255,.1);transition:all .2s}header div#options .option svg{position:relative;width:34px;height:34px;padding:8px 8px;fill:#fff;z-index:1}header div#options .option svg:hover~.optionArea{width:100%;height:100%;left:0;top:0;border-radius:0}header div#options .optDropDown{position:absolute;width:260px;height:auto;right:0;top:50px;padding:8px 0;background-color:#fff;box-shadow:-2px 2px 8px rgba(0,0,0,.15);cursor:pointer;display:none}header div#options .optDropDown div.optDropDownItem{position:relative;width:100%;padding:12px 20px;text-align:center;box-sizing:border-box;display:flex}header div#options .optDropDown div.optDropDownItem:hover{background-color:rgba(0,0,0,.1)}header div#options .optDropDown div.optDropDownItem:hover .optChainDropdown{display:flex}header div#options .optDropDown div.optDropDownItem svg{width:25px;height:25px;margin-right:12px}header div#options .optDropDown div.optDropDownItem p{margin-top:2px}header div#options .optDropDown div.optDropDownItem p small{color:#aaa;margin-left:12px}header div#options .optDropDown .optChainDropdown{position:absolute;width:200px;margin-left:-220px;top:-8px;padding:8px 0;background-color:#fff;box-shadow:2px 2px 8px rgba(0,0,0,.1);flex-direction:column;display:none}main{position:absolute;width:100%;height:100%;top:0;padding-top:50px;box-sizing:border-box}main #errorReporting{position:fixed;right:0;z-index:1}main #errorReporting .errorItem{position:relative;max-width:400px;padding:10px 35px;margin:10px 18px;background-color:rgba(255,68,68,.8);box-shadow:-2px 2px 8px rgba(0,0,0,.1);display:flex;justify-content:center}main #errorReporting .errorItem svg{width:30px;height:30px;fill:#fff}main #errorReporting .errorItem p{color:#fff;font-size:13pt;padding:5px 12px}main #shadow{position:fixed;width:100%;height:100%;background-color:rgba(0,0,0,.1);display:none;z-index:1}main #mainCenter{position:relative;width:100%;height:100%;display:flex}main #mainCenter aside{position:relative;width:320px;margin-left:0;height:100%;background-color:#8eacbc;display:flex;flex-direction:column;transition:margin-left .4s}main #mainCenter aside hr{border:0;border-bottom:1px solid #34515f}main #mainCenter aside #asideFavorites{position:relative;width:100%;height:auto;max-height:190px}main #mainCenter aside #asideFavorites #asideFavBody{padding:5px 0 8px;overflow:auto;max-height:143px}main #mainCenter aside #asideFavorites #asideFavBody .favFolder{padding:3px 10px;box-sizing:border-box;cursor:pointer}main #mainCenter aside #asideFavorites #asideFavBody .favFolder:hover{background-color:#fff}main #mainCenter aside #asideFavorites #asideFavBody .favFolder small{font-size:10pt;font-weight:300;font-style:italic;margin-left:12px;color:#444}main #mainCenter aside #asideTree{position:relative;width:100%;height:auto}main #mainCenter aside .asideTitle{position:relative;width:100%;height:30px;display:flex;flex-direction:row}main #mainCenter aside .asideTitle svg{width:25px;height:25px;fill:#222;margin:2px 8px}main #mainCenter aside .asideTitle p{margin:4px 0}main #mainCenter section{position:relative;width:100%;height:100%;overflow:auto}main #mainCenter section #itemArea{position:relative;width:100%;height:auto;display:flex;flex-wrap:wrap}main #mainCenter section #itemArea #emptyFolder{padding:25px 50px}main #mainCenter section #itemArea .item{position:relative;padding:0 12px;overflow:hidden;box-sizing:border-box;box-shadow:2px 2px 8px rgba(0,0,0,.1);display:flex}main #mainCenter section #itemArea .item:hover{background-color:rgba(0,0,0,.1)}main #mainCenter section #itemArea .itemHidden{opacity:.4}main #mainCenter section #itemArea .itemActive{background-color:rgba(0,0,0,.1)}main #mainCenter section #itemArea .itemMosaic{width:16.6666666667%;height:60px}main #mainCenter section #itemArea .itemMosaic .itemLogo{position:relative;width:60px;height:100%;padding:0;display:flex;justify-content:center;align-items:center}main #mainCenter section #itemArea .itemMosaic .itemLogo img,main #mainCenter section #itemArea .itemMosaic .itemLogo svg{height:42px}main #mainCenter section #itemArea .itemMosaic .itemText{position:relative;width:100%;height:100%;display:flex;align-items:center;margin-left:12px}main #mainCenter section #itemArea .itemMosaic .itemText p{font-size:13pt;margin:8px 0;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemMosaic .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemMosaic .itemFilesize,main #mainCenter section #itemArea .itemMosaic .itemFiletype{display:none}main #mainCenter section #itemArea .itemList{width:100%;height:60px}main #mainCenter section #itemArea .itemList .itemLogo{position:relative;width:60px;height:60px;margin-left:4px;display:flex;justify-content:center;align-items:center}main #mainCenter section #itemArea .itemList .itemLogo img,main #mainCenter section #itemArea .itemList .itemLogo svg{height:42px}main #mainCenter section #itemArea .itemList .itemText{position:relative;width:100%;height:100%;margin-left:18px;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemText p{font-size:13pt;margin:8px 0;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemList .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemList .itemFiletype{position:relative;width:200px;height:100%;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemFiletype p{font-size:13pt;margin:8px 0;cursor:default}main #mainCenter section #itemArea .itemList .itemFilesize{position:relative;width:200px;height:100%;margin-right:20%;display:flex;align-items:center}main #mainCenter section #itemArea .itemList .itemFilesize p{font-size:13pt;margin:8px 0;cursor:default}main #mainCenter section #itemArea .itemWall{width:auto;height:auto;flex-direction:column}main #mainCenter section #itemArea .itemWall .itemLogo{position:relative;width:50px;height:50px;padding:9px;display:flex;justify-content:center;margin:0 auto}main #mainCenter section #itemArea .itemWall .itemLogo img,main #mainCenter section #itemArea .itemWall .itemLogo svg{width:50px;height:50px}main #mainCenter section #itemArea .itemWall .itemText{position:relative;width:100%;height:100%;display:flex;align-items:center}main #mainCenter section #itemArea .itemWall .itemText p{font-size:13pt;margin:8px 0;margin:8px auto;word-wrap:break-word;cursor:default}main #mainCenter section #itemArea .itemWall .itemText p[split-lines]{white-space:pre-wrap}main #mainCenter section #itemArea .itemWall .itemFilesize,main #mainCenter section #itemArea .itemWall .itemFiletype{display:none}main #mainCenter section #folderInfo{position:fixed;right:0;bottom:0;font-size:11pt;padding:3px 7px 4px;background-color:rgba(0,0,0,.1)}details{margin:0;color:#444;padding:5px;cursor:default;-webkit-transition:all .1s}details[open]{animation-name:slideDown;animation-duration:.2s;animation-timing-function:ease-in}details:hover{background-color:#fff}details details{border:0;margin-left:12px}summary p.linkTree{color:#333;text-decoration:none}summary p.linkTree:hover{color:#2196f3}summary p.linkTree:focus{text-decoration:none}details#activo>summary>p.linkTree{color:#2196f3}summary{outline:0}details summary::-webkit-details-marker{display:none}summary::before{position:relative;float:left;content:"+";margin-right:0;padding-right:12px;margin-top:-5px;font-size:18pt}summary:hover::before{color:#2196f3}details[open]>summary::before{position:relative;float:left;content:"-";margin-left:0;padding-left:2px;margin-right:0;padding-right:14px;margin-top:-10px;font-size:22pt}@keyframes slideDown{0%{opacity:0;height:0}100%{opacity:1;height:20px}}aside ::selection{background-color:transparent}.spinner{margin:100px auto;width:50px;height:50px;position:relative;text-align:center;-webkit-animation:sk-rotate 2s infinite linear;animation:sk-rotate 2s infinite linear}.dot1,.dot2{width:60%;height:60%;display:inline-block;position:absolute;top:0;background-color:#2196f3;border-radius:100%;-webkit-animation:sk-bounce 2s infinite ease-in-out;animation:sk-bounce 2s infinite ease-in-out}.dot2{top:auto;bottom:0;-webkit-animation-delay:-1s;animation-delay:-1s}@-webkit-keyframes sk-rotate{100%{-webkit-transform:rotate(360deg)}}@keyframes sk-rotate{100%{transform:rotate(360deg);-webkit-transform:rotate(360deg)}}@-webkit-keyframes sk-bounce{0%,100%{-webkit-transform:scale(0)}50%{-webkit-transform:scale(1)}}@keyframes sk-bounce{0%,100%{transform:scale(0);-webkit-transform:scale(0)}50%{transform:scale(1);-webkit-transform:scale(1)}}@media screen and (max-width:2100px){main #mainCenter section #itemArea .itemMosaic{width:20%}}@media screen and (max-width:1750px){main #mainCenter section #itemArea .itemMosaic{width:25%}}@media screen and (max-width:1300px){main #mainCenter section #itemArea .itemMosaic{width:33.33333333%}}@media screen and (max-width:1050px){main #mainCenter section #itemArea .itemMosaic{width:50%}}@media screen and (max-width:750px){main #mainCenter section #itemArea .itemMosaic{width:100%}}@media screen and (max-width:420px){main #mainCenter aside{margin-left:-320px}}#dialogBack{position:fixed;width:100%;height:100%;top:0;padding-top:25px;background-color:rgba(0,0,0,.1);align-items:center;display:none}#dialogBack .dialog{position:relative;width:950px;height:520px;margin:0 auto;background-color:#fff;box-shadow:2px 2px 8px rgba(0,0,0,.1);display:none}#dialogBack .dialog .dialogTitleBar{position:relative;width:100%;height:50px;box-shadow:2px 2px 8px rgba(0,0,0,.1);display:flex;justify-content:space-between;z-index:1}#dialogBack .dialog .dialogTitleBar h2{padding:10px 20px;font-family:Roboto-Light,Roboto-Regular,sans-serif}#dialogBack .dialog .dialogTitleBar svg{width:30px;height:30px;fill:#777;padding:10px 20px}#dialogBack .dialog .dialogTitleBar svg:hover{fill:#000}#dialogBack .dialog .dialogBody{position:relative;width:100%;height:auto}#dialogBack .dialog .dialogBody .settingsItem{position:relative;width:100%;height:auto;padding:16px 24px;box-sizing:border-box}#dialogBack .dialog .dialogBody .settingsItem h3{margin-bottom:5px}#dialogBack .dialog .dialogBody .settingsItem div{padding:0 8px}#dialogBack .dialog .dialogBody .settingsItem div input[type=checkbox]{vertical-align:middle;margin-right:8px}#dialogBack .dialog #historyBody{position:absolute;width:100%;height:100%;top:0;padding-top:50px;box-sizing:border-box}#dialogBack .dialog #historyBody #historyPaths{position:relative;width:100%;height:100%;overflow:auto}#dialogBack .dialog #historyBody #historyPaths .historyItem{position:relative;width:100%;height:50px;padding:4px 20px;box-sizing:border-box;border-bottom:1px solid #f2f2f2;display:flex}#dialogBack .dialog #historyBody #historyPaths .historyItem p{min-width:130px;padding:12px 40px}#dialogBack .dialog #historyBody #historyPaths .historyItem p:last-child{color:#999}#dialogBack .dialog #historyBody #historyPaths .historyItem svg{position:absolute;right:30px;width:30px;height:30px;fill:#6f6f6f;padding:7px 25px}#dialogBack .dialog #historyBody #historyPaths .historyItem svg:hover{fill:#222}@media screen and (max-width:1080px){#dialogBack .dialog{width:92%}}@media screen and (max-width:800px){#dialogBack .dialog{width:100%}}@media screen and (max-height:620px){#dialogBack{padding-top:50px}#dialogBack .dialog{height:100%}}::-webkit-scrollbar{width:10px;height:10px}::-webkit-scrollbar-thumb{background:#aaa}::-webkit-scrollbar-track{background:rgba(0,0,0,.05)}
    </style>
    <script type="text/javascript" src="js/jquery-3.1.1.min.js">
    </script>
    <script type="text/javascript">
    // global variables
    var allowedAccess = false; // indica si el usuario esta autenticado
    var path = './'; // ruta actual del eplorador
    var favorites = []; // almacena las rutas favoritas
    var optMoreDespl = false; // estado del desplegable de más opciones
    var optViewDespl = false; // estado del desplegable de las vistas
    var timeline = []; // almacena todas las rutas accedidas anteriormente
    var posSelect = null; // posición del fichero o directorio seleccionado con las flechas de dirección
    var defaultDirectoryIndex = ['index.php','index.asp','index.html'];
    var currentPathLaunch = false; // almacena el fichero ejecutable prioritario en el directorio actual (modificado por la funcion setLaunchOptions)

    if (localStorage.getItem('elcano-settings') == null) {
        var settings = {'tree':true,'view':'Mosaic','debug':true,'systemIndex':true,'directoryIndex':defaultDirectoryIndex};
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    } else {
        var settings = JSON.parse(localStorage.getItem('elcano-settings'));
    }

    if (localStorage.getItem('elcano-favorites') == null) {
        localStorage.setItem('elcano-favorites','[]');
        favorites = JSON.parse(localStorage.getItem('elcano-favorites'));
    } else {
        favorites = JSON.parse(localStorage.getItem('elcano-favorites'));
    }

    $(function() { // init
        if (sessionStorage.getItem('elcano-access') != null) {
            storedAuth = JSON.parse(sessionStorage.getItem('elcano-access'));
            let storedUser = storedAuth.user;
            let storedPass = storedAuth.pass;
            authorize(storedUser,storedPass);
        } else {
            disableExplorer();
        }

        $('#signInForm').on('submit',function(e) { // evento al rellenar el formulario de login
            e.preventDefault();
            let user = $('#signInUser').val();
            let pass = $('#signInPass').val();
            authorize(user,sha1(pass));
        });

        // aside tree loading circle
        $('#asideTreeBody').html('<div class="spinner"><div class="dot1"></div><div class="dot2"></div></div>');

        // options functionalities
        $('#optLaunch').on('click', function() {
            if (settings.systemIndex) {
                window.open(path,'_blank');
            } else {
                window.open(currentPathLaunch,'_blank');
            }
        });

        $('#optDatabase').on('click', function() {
            window.open('http://localhost/phpmyadmin/','_blank');
        });

        $('#optFavorite').on('click', function() {
            if (favorites.length>0) {
                for (x in favorites) {
                    if (favorites[x].path == path) {
                        favorites.splice(x,1);
                        localStorage.setItem('elcano-favorites',JSON.stringify(favorites));
                    }
                }
                $('#optFavorite').hide();$('#optNotFavorite').show();
                showFavorites();
            }
        });

        $('#optNotFavorite').on('click', function() {
            addFavorite();
        });

        $('#optView').on('click', function() {
            if (optViewDespl) {
                $('#optViewDespl').slideUp(200);
                $('#shadow').fadeOut(200);
                optViewDespl = false;
            } else {
                $('#optViewDespl').slideDown(200);
                $('#shadow').fadeIn(200);
                optViewDespl = true;
                $('#optMoreDespl').slideUp(200);
                optMoreDespl = false;
            }
        });

        $('#optMore').on('click', function() {
            if (optMoreDespl) {
                $('#optMoreDespl').slideUp(200);
                $('#shadow').fadeOut(200);
                optMoreDespl = false;
            } else {
                $('#optMoreDespl').slideDown(200);
                $('#shadow').fadeIn(200);
                optMoreDespl = true;
                $('#optViewDespl').slideUp(200);
                optViewDespl = false;
            }
        });

        if (settings.tree) { // set startup state of the aside tree
            $('#optMoreDesplExplorer p').html('Ocultar Explorador<small>alt+x</small>');
        } else {
            $('aside').css('margin-left','-320px');
            $('#optMoreDesplExplorer p').html('Mostrar Explorador<small>alt+x</small>');
        }

        $('#shadow').on('click', function() {
            $('#optViewDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optViewDespl = false;
            $('#optMoreDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optMoreDespl = false;
        })

        $('#optMoreHistory').on('click',function(e) {
            if (e.target.id == 'optMoreHistory' || e.target.parentNode.id == 'optMoreHistory' || e.target.parentNode.parentNode.id == 'optMoreHistory') {
                showHistory(true);
            } else if (e.target.id == 'optMorePrevPath' || e.target.parentNode.id == 'optMorePrevPath' || e.target.parentNode.parentNode.id == 'optMorePrevPath') {
                prevPath();
            } else if (e.target.id == 'optMoreNextPath' || e.target.parentNode.id == 'optMoreNextPath' || e.target.parentNode.parentNode.id == 'optMoreNextPath') {
                nextPath();
            }
        });
    });

    function authorize(authUser,authPass) {
        // función que actúa tanto al rellenar el formulario de login como al cargar por primera vez la página si la contraseña está guardada en sessionStorage
        // la variable authPass contiene la contraseña a comprobar ya encriptada para enviar a PHP
        if (settings.debug){console.log('authorize')};

        var tokenCheck = Math.random();

        $.get( "auth.php", { user: authUser,pass: authPass,token: tokenCheck } )
            .done(function( data ) {
                //console.log(data);
                var response = JSON.parse(data);

                if (response.token == tokenCheck) {
                    if (response.status == 200) {
                        accessDataJson = JSON.stringify({'user':authUser,'pass':authPass});
                        sessionStorage.setItem('elcano-access',accessDataJson);
                        enableExplorer();
                    } else {
                        disableExplorer();
                    }
                } else {
                    disableExplorer();
                }
        });
    }

    function logout() { // cierra la sesión iniciada volviendo a mostrar el formulario de login
        if (settings.debug){console.log('logout')};
        sessionStorage.removeItem('elcano-access');
        disableExplorer();
        $('#optMoreDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optMoreDespl = false;
    }

    function enableExplorer() {
        if (settings.debug){console.log('explorer enabled')};
        allowedAccess = true;
        changePath('./');
        showFavorites();
        loadTree();
        $('.screen').hide();
        $('#explorer').fadeIn(200);
    }

    function disableExplorer() {
        if (settings.debug){console.log('explorer disabled')};
        allowedAccess = false;
        $('.screen').hide();
        $('#blocked').show();
        $('#signInUser,#signInPass').val('');
        $('#signInUser').focus();
    }

    function getFolder(url) { // recupera los ficheros y directorios existentes en la ruta que se le introduzca como parámetro
        if (allowedAccess) {
            if (settings.debug){console.log('getFolder(\''+url+'\')')};

            var coincidencia = false;
            if (favorites.length>0) {
                for (x in favorites) {
                    if (favorites[x].path == url) {
                        coincidencia = true;
                    }
                }
            }

            if (coincidencia) {
                $('#optFavorite').show();
                $('#optNotFavorite').hide();
            } else {
                $('#optFavorite').hide();
                $('#optNotFavorite').show();
            }

            $.post( "", { ruta: url, listDir: true } )
                .done(function( data ) {
                    if (settings.debug){console.log(data)};
                    var response = JSON.parse(data);

                    if (response.error != null) {
                        if (settings.debug){console.log('Se ha producido un error al acceder al directorio')};
                        errorReporting(response);
                    } else {
                        path = url; // cambiamos la ruta general al confirmar la existencia del directorio
                        explodePath(url);
                        $('#itemArea').html('');

                        var files = 0;
                        var directories = 0;

                        if (response.dir.length == 0 && response.files.length == 0) {
                            $('#itemArea').html('<div id="emptyFolder"><p>Esta carpeta está vacia</p></div>');
                        }
                        for (i in response.dir) {
                            setFolderItems(response.dir[i].fileName,response.dir[i].filePath, response.dir[i].fileType);
                            directories++;
                        }
                        for (i in response.files) {
                            setFolderItems(response.files[i].fileName,response.files[i].filePath,response.files[i].fileType,response.files[i].fileSize);
                            files++;
                        }

                        setLaunchOptions(response);

                        $('#folderInfo p').text(directories+' carpetas y '+files+' archivos');
                    }
            });
        }
    }

    function setFolderItems(name,path,type,size) {
        var html = '';
        if (settings.debug){console.log(type)};
        if (type=='folder') {
            if (name.substring(0,1) == '.') {
                html += '<div class="item item'+settings.view+' itemHidden" onclick="changePath(\'' + path + '/\')">';
            } else {
                html += '<div class="item item'+settings.view+'" onclick="changePath(\'' + path + '/\')">';
            }
        } else {
            html += '<div class="item item'+settings.view+'" onclick="readFich(\'' + path + '\')">';
        }
        html += '<div class="itemLogo">';
        html += setItemIcon(type,path);
        html += '</div>';
        html += '<div class="itemText">';
        html += '<p split-lines>'+name+'</p>';
        html += '</div>';
        html += '<div class="itemFiletype">';
        if (type!='folder') {
            html += '<p>'+type+'</p>';
        }
        html += '</div>';
        html += '<div class="itemFilesize">';
        if (type!='folder') {
            html += '<p>'+size+'</p>';
        }
        html += '</div>';
        html += '</div>';
        $('#itemArea').append(html);
    }

    function changePath(url) { // cambia la ruta actual
        if (settings.debug){console.log('changePath to: '+url)};
        if (url.substring(0,2) == './' && url.indexOf('../') == -1) {
            getFolder(url);
            document.title = 'elcano ' + url;
            if (timeline.length>0) {
                if (timeline[timeline.length-1].path != url) {
                    timeline.push({'path':url});
                }
            } else {
                timeline.push({'path':url});
            }
            reloadHistory();
        } else {
            if (settings.debug){console.log('forbiden access to path '+url)};
        }
    }

    function readFich(url) {
        //console.log('reading ' + url);
        window.open(url,'_blank');
    }

    function explodePath(url) { // actualiza los directorios del nav
        var explode = url.split('/');
        $('nav').html('');

        for (i=0;i<explode.length;i++) {
            if (explode[i]=='') {
                explode.splice(i,1);
            } else {
                if (explode[i]=='.') {
                    explode[i] = 'paginas';
                }

                var implode = ''; // permite asignar la url a cada item del nav
                for (j=0;j<=i;j++) {
                    if (explode[j] == 'paginas') {
                        implode += './';
                    } else {
                        implode += explode[j] + '/';
                    }
                }

                if (i != explode.length-1 && i != explode.length-2) {
                    $('nav').append('<div class="navItem" onclick="changePath(\'' + implode + '\')"><p>'+explode[i]+'</p></div>');
                    if (i != explode.length -1) {
                        $('nav').append('<div class="navSeparator"><p>/</p></div>');
                    }
                } else if (explode[i] != '') {
                    $('nav').append('<div class="navItem"><p>'+explode[i]+'</p></div>');
                }
            }
        }
    }

    function showFavorites() { // recarga la lista de favoritos en el aside
        if (favorites.length>0) {
            var favs = '';
            for (x in favorites) {
                favs += '<div class="favFolder" onclick="changePath(\'' + favorites[x].path + '\')"><p>' + favorites[x].title + '<small>' + favorites[x].path + '</small></p></div>';
            }
            $('#asideFavBody').html(favs);
        } else {
            $('#asideFavBody').html('<p style="text-align:center;color:#555">No hay favoritos</p>');
        }
    }

    function addFavorite() {
        console.log('addFavorite');
        var titFav = prompt('Título del favorito: ','');
        if (titFav!=null && titFav!='') {
            if (settings.debug){console.log(path)};
            favorites.push({'path':path,'title':titFav});
            localStorage.setItem('elcano-favorites',JSON.stringify(favorites));
            if (settings.debug){console.log(favorites)};
            $('#optFavorite').show();$('#optNotFavorite').hide();
            showFavorites();
        }
    }

    function changeView(view) { // permite cambiar el layout de elementos en el directorio
        if (view != settings.view && (view=='Mosaic' || view=='List' || view=='Wall')) {
            $('.item').removeClass('itemMosaic');
            $('.item').removeClass('itemList');
            $('.item').removeClass('itemWall');

            if (view == 'Mosaic') {
                $('.item').addClass('itemMosaic');
                settings.view = 'Mosaic';
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            } else if (view == 'List') {
                $('.item').addClass('itemList');
                settings.view = 'List';
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            } else if (view == 'Wall') {
                $('.item').addClass('itemWall');
                settings.view = 'Wall';
                localStorage.setItem('elcano-settings',JSON.stringify(settings));
            }
            $('#optViewDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optViewDespl = false;
        }
    }

    function setItemIcon(type,path) {
        var standardSvgIcon = false; // indica si utiliza el icono svg estándar para un tipo concreto
        var paper='';var bend='';var text='';var size='36px';
        var image = 'default';
        if (type=='folder') { // folder
            image = '<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
        } else if (type=='zip' || type=='rar' || type=='7z') {
            image ='<svg height="24" version="1.1" width="24" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><g transform="translate(0 -1028.4)"><g transform="translate(-72,66)"><path d="m76 965.36c-1.105 0-2 0.9-2 2v8 2 1 6c0 1.11 0.895 2 2 2h2 12 2c1.105 0 2-0.89 2-2v-6-1-2-4-4c0-1.1-0.895-2-2-2h-2-2-2-8-2z" fill="#e67e22"/><path d="m76-64c-1.105 0-2 0.895-2 2v8 2 1 6c0 1.105 0.895 2 2 2h2 12 2c1.105 0 2-0.895 2-2v-6-1-2-4-4c0-1.105-0.895-2-2-2h-2-2-2-8-2z" fill="#f39c12" transform="translate(0 1028.4)"/><path d="m76-65c-1.105 0-2 0.895-2 2v8 2 1 6c0 1.105 0.895 2 2 2h2 12 2c1.105 0 2-0.895 2-2v-6-1-2-4-4c0-1.105-0.895-2-2-2h-2-2-2-8-2z" fill="#e67e22" transform="translate(0 1028.4)"/><path d="m76-66c-1.105 0-2 0.895-2 2v8 2 1 6c0 1.105 0.895 2 2 2h2 12 2c1.105 0 2-0.895 2-2v-6-1-2-4-4c0-1.105-0.895-2-2-2h-2-2-2-8-2z" fill="#f1c40f" transform="translate(0 1028.4)"/></g><path d="m17 1040.4c-1.105 0-2 0.9-2 2v4c0 1.1 0.895 2 2 2s2-0.9 2-2v-4c0-1.1-0.895-2-2-2zm0 1c0.552 0 1 0.4 1 1 0 0.5-0.448 1-1 1s-1-0.5-1-1c0-0.6 0.448-1 1-1zm0 3c0.552 0 1 0.4 1 1v1c0 0.5-0.448 1-1 1s-1-0.5-1-1v-1c0-0.6 0.448-1 1-1z" fill="#f39c12"/><g transform="translate(5)"><path d="m10 1028.4v10c0 1.1 0.895 2 2 2s2-0.9 2-2v-10h-4z" fill="#34495e"/><path d="m12 1028.4v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1c0.552 0 1-0.5 1-1h-1z" fill="#95a5a6"/><path d="m11 1028.4v1h1v-1h-1zm0 2v1h1v-1h-1zm0 2v1h1v-1h-1zm0 2v1h1v-1h-1zm0 2v1h1v-1h-1zm0 2c0 0.5 0.448 1 1 1v-1h-1z" fill="#ecf0f1"/></g><path d="m17 1039.4c-1.105 0-2 0.9-2 2v4c0 1.1 0.895 2 2 2s2-0.9 2-2v-4c0-1.1-0.895-2-2-2zm0 1c0.552 0 1 0.4 1 1 0 0.5-0.448 1-1 1s-1-0.5-1-1c0-0.6 0.448-1 1-1zm0 3c0.552 0 1 0.4 1 1v1c0 0.5-0.448 1-1 1s-1-0.5-1-1v-1c0-0.6 0.448-1 1-1z" fill="#ecf0f1"/></g></svg>';
        } else if (type=='exe') {
        } else if (type=='bat') {
            standardSvgIcon = true;
            paper='#424242';bend='#6d6d6d';text='BAT';
        } else if (type=='txt') {
            standardSvgIcon = true;
            paper='#607d8b';bend='#8eacbb';text='TXT';
        } else if (type=='pdf') { // PDF
            standardSvgIcon = true;
            paper='#f00';bend='#ff5252';text='PDF';
        } else if (type=='html' || type=='htm') { // HTML
            standardSvgIcon = true;
            paper='#f44336';bend='#ff7961';text='HTML';
        } else if (type=='css') { // CSS
            standardSvgIcon = true;
            paper='#1e88e5';bend='#6ab7ff';text='CSS';
        } else if (type=='sass' || type=='scss') { // SASS
            standardSvgIcon = true;
            paper='#ec407a';bend='#ff77a9';text='SASS';
        } else if (type=='js') { // JS
            standardSvgIcon = true;
            paper='#fdd835';bend='#ffff6b';text='JS';size='42px';
        } else if (type=='php') { // PHP
            standardSvgIcon = true;
            paper='#6a1b9a';bend='#9c4dcc';text='PHP';
        } else if (type=='py') {
            standardSvgIcon = true;
            paper='#1e88e5';bend='#fdd835';text='PY';size='36px';
        } else if (type=='c' || type=='cpp' || type=='cs') {
            standardSvgIcon = true;
            paper='#2196f3';bend='#6ec6ff';text='C';
        } else if (type=='asp') {
            standardSvgIcon = true;
            paper='#6cbeff';bend='#a1d9fe';text='ASP';
        } else if (type=='doc' || type=='docx' || type=='odt') {
            standardSvgIcon = true;
            paper='#1565c0';bend='#5e92f3';text='W';var size='50';
        } else if (type=='xls' || type=='xlsx' || type=='ods') {
            standardSvgIcon = true;
            paper='#2e7d32';bend='#60ad5e';text='X';var size='50';
        } else if (type=='ppt' || type=='pptx' || type=='odp') {
            standardSvgIcon = true;
            paper='#d84315';bend='#ff7543';text='P';var size='50';
        } else if (type=='accdb') {
            standardSvgIcon = true;
            paper='#c62828';bend='#ff5f52';text='A';var size='50';
        } else if (type=='jpg' || type=='jpeg' || type=='png' || type=='gif' || type=='svg' || type=='bmp' || type=='ico') {
            image = '<img src="'+path+'" />';
        } else if (type=='mp3' || type=='wav' || type=='ogg' || type=='wma' || type=='cda' || type=='mid' || type=='midi' || type=='ac3' || type=='ogm' || type=='aac' || type=='flac') { // audio types
            image = '<svg style="fill:#039be5" xmlns="http://www.w3.org/2000/svg" height="34" viewBox="0 0 24 24" width="34"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 5v8.55c-.94-.54-2.1-.75-3.33-.32-1.34.48-2.37 1.67-2.61 3.07-.46 2.74 1.86 5.08 4.59 4.65 1.96-.31 3.35-2.11 3.35-4.1V7h2c1.1 0 2-.9 2-2s-.9-2-2-2h-2c-1.1 0-2 .9-2 2z"/></svg>';
        } else if (type=='mp4' || type=='avi' || type=='wmv' || type=='rpm' || type=='mov' || type=='dvd' || type=='div' || type=='divx' || type=='asf' || type=='wm') { // video types
            image ='<svg style="fill:#039be5" xmlns="http://www.w3.org/2000/svg" height="34" viewBox="0 0 24 24" width="34"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l2.29 2.29c.63.63 1.71.18 1.71-.71V8.91c0-.89-1.08-1.34-1.71-.71L17 10.5z"/></svg>';
        } else if (type=='psd') {
            standardSvgIcon = true;
            paper='#0d47a1';bend='#5472d3';text='PS';var size='45';
        } else if (type=='ai') {
            standardSvgIcon = true;
            paper='#ff6f00';bend='#ffa040';text='AI';var size='45';
        } else {
            standardSvgIcon = true;
            paper='#eceff1';bend='#e0e0e0';text='';
        }

        if (standardSvgIcon) {
            image = '<svg id="fileicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 86 120"><title>'+text+'</title><g id="paper"><path d="M106,42v74a8,8,0,0,1-8,8H28a8,8,0,0,1-8-8V12a8,8,0,0,1,8-8H68L78,14V32H96Z" transform="translate(-20 -4)" style="fill:'+paper+'"/></g><text id="text" transform="translate(42 80.3)" style="text-anchor:middle;font-size:'+size+';fill:#fff;font-family:Calibri;cursor:default">'+text+'</text><polygon id="bend" points="86 38 48 38 48 0 86 38" style="fill:'+bend+'"/></svg>';
        }

        return image;
        //return '<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
    }

    function showTree() { // muestra u oculta el árbol de directorios lateral
        if (settings.debug){console.log('showTree')};
        if (settings.tree) {
            $('aside').css('margin-left','-320px');
            settings.tree = false;
            $('#optMoreDesplExplorer p').html('Mostrar Explorador<small>alt+x</small>');
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        } else {
            $('aside').css('margin-left','0px');
            settings.tree = true;
            $('#optMoreDesplExplorer p').html('Ocultar Explorador<small>alt+x</small>');
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        }
        $('#optMoreDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optMoreDespl = false;
    }

    function loadTree() { // carga los datos del árbol de directorios
        if (allowedAccess) {
            $.post( "", { ruta: path, dirTree: true } )
                .done(function( data ) {
                    $('#asideTreeBody').html(data);
                    if (settings.debug){console.log("tree loaded")};
            });
        }
    }

    function showSettings(op) { // muestra u oculta la ventana de configuración
        if (settings.debug){console.log('showSettings')};
        if (op) {
            $('#dialogBack').css('display','flex');
            $('.dialog').hide();
            $('#settings').fadeIn(200);
        } else {
            $('#settings').fadeOut(200);
            $('#dialogBack').css('display','none');
        }

        $('#optMoreDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optMoreDespl = false;
    }

    function prevPath() {
        if (settings.debug){console.log('prevPath')};
        if (timeline.length>1) {
            var url = timeline[timeline.length-2].path;
            timeline.splice(timeline.length-1,1);
            changePath(url);
        }
        $('#optMoreDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optMoreDespl = false;
    }

    function nextPath() {
        if (settings.debug){console.log('nextPath')};
    }

    function showHistory(op) {
        if (settings.debug){console.log('showHistory')};
        if (op) {
            $('#dialogBack').css('display','flex');
            $('.dialog').hide();
            $('#history').fadeIn(200);
        } else {
            $('#history').fadeOut(200);
            $('#dialogBack').css('display','none');
        }

        $('#optMoreDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optMoreDespl = false;
    }

    function reloadHistory() {
        var html = '';
        for (i in timeline) {
            var folderName = timeline[i].path.split('/');
            if (folderName.length==2) {
                folderName2 = 'Pagina de Inicio';
            } else {
                folderName2 = folderName[folderName.length-2];
            }
            html += '<div class="historyItem">';
            html += '<p>'+folderName2+'</p>';
            html += '<p>'+timeline[i].path+'</p>';
            html += '<svg onclick="navigateHistory('+i+')" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L3.71 8.71C3.08 8.08 2 8.52 2 9.41V15c0 .55.45 1 1 1h5.59c.89 0 1.34-1.08.71-1.71l-1.91-1.91c1.39-1.16 3.16-1.88 5.12-1.88 3.16 0 5.89 1.84 7.19 4.5.27.56.91.84 1.5.64.71-.23 1.07-1.04.75-1.72C20.23 10.42 16.65 8 12.5 8z"/></svg>';
            html += '</div>';
        }
        $('#historyPaths').html(html);
    }

    function navigateHistory(pos) {
        if (settings.debug){console.log('navigateHistory to: '+pos)};
        changePath(timeline[pos].path);
    }

    function errorReporting(info) { // Muestra los mensajes de error por pantalla
        if (settings.debug){console.log('errorReporting')};

        var errorItemId = Math.floor(Math.random()*10000);

        var errorMsg = '<div class="errorItem" id="errorItem'+errorItemId+'">';
        errorMsg += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>';
        errorMsg += '<p>'+info.error.message+'</p>';
        errorMsg += '</div>';

        $('#errorReporting').append(errorMsg);

        setTimeout(function() {
            $('#errorItem'+errorItemId).remove();
        },5000);
    }

    function setLaunchOptions(data) { // indica el fichero ejecutable prioritario del directorio
        console.log(settings.directoryIndex);
        var priority = settings.directoryIndex;
        var priorityFound = false;
        var launchPath = '';

        for (i in priority) {
            console.log(priority[i]); // index.php
            for (j in data.files) {
                if (!priorityFound) {
                    if (data.files[j].fileName==priority[i]) {
                        launchPath = data.files[j].filePath;
                        priorityFound = true;
                    }
                }
            }
        }

        if (priorityFound) {
            $('#optLaunch').show();
            currentPathLaunch = launchPath;
        } else {
            $('#optLaunch').hide();
            currentPathLaunch = false;
        }
    }

    /* ---- app utils ---- */

    function passEncoder(pass) { // encode the sign in password
        let hash = '';
        for (i=0;i<pass.length;i++) {
            var passCode = ''+pass.charCodeAt(i);
            if (passCode.length == 1) {
                passCode = '00' + passCode;
            } else if (passCode.length == 2) {
                passCode = '0' + passCode;
            }
            hash += passCode;
        }

        return hash;
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');

        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return false;
    }

    function sha1 (str) {
        var hash
        try {
            var crypto = require('crypto')
            var sha1sum = crypto.createHash('sha1')
            sha1sum.update(str)
            hash = sha1sum.digest('hex')
        } catch (e) {
            hash = undefined
        }

        if (hash !== undefined) {
            return hash
        }

        var _rotLeft = function (n, s) {
            var t4 = (n << s) | (n >>> (32 - s))
            return t4
        }

        var _cvtHex = function (val) {
            var str = ''
            var i
            var v

            for (i = 7; i >= 0; i--) {
                v = (val >>> (i * 4)) & 0x0f
                str += v.toString(16)
            }
            return str
        }

        var blockstart
        var i, j
        var W = new Array(80)
        var H0 = 0x67452301
        var H1 = 0xEFCDAB89
        var H2 = 0x98BADCFE
        var H3 = 0x10325476
        var H4 = 0xC3D2E1F0
        var A, B, C, D, E
        var temp

        // utf8_encode
        str = unescape(encodeURIComponent(str))
        var strLen = str.length

        var wordArray = []
        for (i = 0; i < strLen - 3; i += 4) {
            j = str.charCodeAt(i) << 24 |
            str.charCodeAt(i + 1) << 16 |
            str.charCodeAt(i + 2) << 8 |
            str.charCodeAt(i + 3)
            wordArray.push(j)
        }

        switch (strLen % 4) {
            case 0:
                i = 0x080000000
                break
            case 1:
                i = str.charCodeAt(strLen - 1) << 24 | 0x0800000
                break
            case 2:
                i = str.charCodeAt(strLen - 2) << 24 | str.charCodeAt(strLen - 1) << 16 | 0x08000
                break
            case 3:
                i = str.charCodeAt(strLen - 3) << 24 |
                str.charCodeAt(strLen - 2) << 16 |
                str.charCodeAt(strLen - 1) << 8 | 0x80
                break
            }

            wordArray.push(i)

            while ((wordArray.length % 16) !== 14) {
                wordArray.push(0)
            }

            wordArray.push(strLen >>> 29)
            wordArray.push((strLen << 3) & 0x0ffffffff)

            for (blockstart = 0; blockstart < wordArray.length; blockstart += 16) {
                for (i = 0; i < 16; i++) {
                    W[i] = wordArray[blockstart + i]
                }
                for (i = 16; i <= 79; i++) {
                    W[i] = _rotLeft(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1)
                }

                A = H0
                B = H1
                C = H2
                D = H3
                E = H4

                for (i = 0; i <= 19; i++) {
                    temp = (_rotLeft(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff
                    E = D
                    D = C
                    C = _rotLeft(B, 30)
                    B = A
                    A = temp
                }

                for (i = 20; i <= 39; i++) {
                    temp = (_rotLeft(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff
                    E = D
                    D = C
                    C = _rotLeft(B, 30)
                    B = A
                    A = temp
                }

                for (i = 40; i <= 59; i++) {
                    temp = (_rotLeft(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff
                    E = D
                    D = C
                    C = _rotLeft(B, 30)
                    B = A
                    A = temp
                }

                for (i = 60; i <= 79; i++) {
                    temp = (_rotLeft(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff
                    E = D
                    D = C
                    C = _rotLeft(B, 30)
                    B = A
                    A = temp
                }

                H0 = (H0 + A) & 0x0ffffffff
                H1 = (H1 + B) & 0x0ffffffff
                H2 = (H2 + C) & 0x0ffffffff
                H3 = (H3 + D) & 0x0ffffffff
                H4 = (H4 + E) & 0x0ffffffff
            }

            temp = _cvtHex(H0) + _cvtHex(H1) + _cvtHex(H2) + _cvtHex(H3) + _cvtHex(H4)
            return temp.toLowerCase()
    }

    /* ----- comandos de teclado ----- */

    document.onkeydown = function() {
        if (window.event.keyCode == 86) { if (event.altKey) { changeView('List') } }
        if (window.event.keyCode == 88) { if (event.altKey) { showTree() } }
        if (window.event.keyCode == 72) { if (event.altKey) { showHistory(true) } }
        if (window.event.keyCode == 83) { if (event.altKey) { showSettings(true) } }

        if (window.event.keycode == 8) {
            if (settings.debug){console.log('backspace')};
        }

        // navegación por el directorio con las flechas de dirección

        // ATENCIÓN: NO MODIFICAR estos datos sin cambiar las media queries correspondientes
        // Cada variable indica la anchura máxima para cada cantidad de columnas
        cols1=750; cols2=1050; cols3=1300; cols4=1750; cols5=2100;

        if (window.event.keyCode == 37) { // flecha izquierda
            $('.item').removeClass('itemActive');
            if (posSelect<=0) {
                posSelect=0;
            } else if (posSelect>=$('.item').length-1) {
                posSelect=$('.item').length-2;
            } else {
                posSelect--;
            }
            $($('.item')[posSelect]).addClass('itemActive');
            if (settings.debug){console.log(posSelect)};
        }
        if (window.event.keyCode == 39) { // flecha derecha
            $('.item').removeClass('itemActive');
            if (posSelect==null) {
                posSelect=0;
            } else if (posSelect<=0) {
                posSelect=1;
            } else if (posSelect>=$('.item').length-1) {
                posSelect=$('.item').length-1;
            } else {
                posSelect++;
            }
            $($('.item')[posSelect]).addClass('itemActive');
            if (settings.debug){console.log(posSelect)};
        }
        if (window.event.keyCode == 38) { // fecha arriba
            $('.item').removeClass('itemActive');
            if (posSelect==null) {
                posSelect=0;
            } else if (posSelect<=0) {
                posSelect=0;
            } else {
                if (window.innerWidth < cols1) { // 1 columna
                    if (posSelect-1>=0) { posSelect = posSelect-1; }
                } else if (window.innerWidth < cols2) { // 2 columnas
                    if (posSelect-2>=0) { posSelect = posSelect-2; }
                } else if (window.innerWidth < cols3) { // 3 columnas
                    if (posSelect-3>=0) { posSelect = posSelect-3; }
                } else if (window.innerWidth < cols4) { // 4 columnas
                    if (posSelect-4>=0) { posSelect = posSelect-4; }
                } else if (window.innerWidth < cols5) { // 5 columnas
                    if (posSelect-5>=0) { posSelect = posSelect-5; }
                } else if (window.innerWidth >= cols5) { // 6 columnas
                    if (posSelect-6>=0) { posSelect = posSelect-6; }
                }
            }
            $($('.item')[posSelect]).addClass('itemActive');
            if (settings.debug){console.log(posSelect)};
        }
        if (window.event.keyCode == 40) { // flecha abajo
            $('.item').removeClass('itemActive');
            if (posSelect==null) {
                posSelect=0;
            } else if (posSelect>=$('.item').length-1) {
                posSelect=$('.item').length-1;
            } else {
                if (window.innerWidth < cols1) { // 1 columna
                    if (posSelect+1<=$('.item').length-1) { posSelect = posSelect+1; }
                } else if (window.innerWidth < cols2) { // 2 columnas
                    if (posSelect+2<=$('.item').length-1) { posSelect = posSelect+2; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                } else if (window.innerWidth < cols3) { // 3 columnas
                    if (posSelect+3<=$('.item').length-1) { posSelect = posSelect+3; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                } else if (window.innerWidth < cols4) { // 4 columnas
                    if (posSelect+4<=$('.item').length-1) { posSelect = posSelect+4; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                } else if (window.innerWidth < cols5) { // 5 columnas
                    if (posSelect+5<=$('.item').length-1) { posSelect = posSelect+5; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                } else if (window.innerWidth >= cols5) { // 6 columnas
                    if (posSelect+6<=$('.item').length-1) { posSelect = posSelect+6; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                }
            }
            $($('.item')[posSelect]).addClass('itemActive');
            if (settings.debug){console.log(posSelect)};
        }
        if (window.event.keyCode == 13) { // intro
            if (posSelect!=null) {
                $($('.item')[posSelect]).click();
            }
        }
        $('section').on('click',function() { // deseleccionar los items
            posSelect=null;
            $('.item').removeClass('itemActive');
        });
    }

    </script>
</head>
<body>
    <div class="screen" id="startUp"></div>
    <div class="screen" id="blocked">
        <div id="blockedBack">
            <div id="signIn">
                <div id="signInTitle">
                    <h1>Inicio de Sesión</h1>
                </div>
                <div id="signInBody">
                    <div id="signInError"></div>
                    <form id="signInForm">
                        <div class="signInBodyItem">
                            <input id="signInUser" type="text" placeholder="usuario" autocomplete="off" spellcheck="false" autofocus />
                            <svg class="signInIcons" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1c0 .55.45 1 1 1h14c.55 0 1-.45 1-1v-1c0-2.66-5.33-4-8-4z"/></svg>
                            <div class="signInInputEffect"></div>
                        </div>
                        <div class="signInBodyItem">
                            <input id="signInPass" type="password" placeholder="password" />
                            <svg class="signInIcons" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12.65 10C11.7 7.31 8.9 5.5 5.77 6.12c-2.29.46-4.15 2.29-4.63 4.58C.32 14.57 3.26 18 7 18c2.61 0 4.83-1.67 5.65-4H17v2c0 1.1.9 2 2 2s2-.9 2-2v-2c1.1 0 2-.9 2-2s-.9-2-2-2h-8.35zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                            <div class="signInInputEffect"></div>
                        </div>
                        <div class="signInBodyItem">
                            <input id="signInSubmit" type="submit" name="login" value="Continuar" />
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
                <div class="option" id="optMore">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="optDropDown" id="optMoreDespl">
                    <div class="optDropDownItem" id="optMoreHistory">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                        <p>Historial<small>alt+h</small></p>
                        <div class="optChainDropdown">
                            <div class="optDropDownItem" id="optMorePrevPath">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L3.71 8.71C3.08 8.08 2 8.52 2 9.41V15c0 .55.45 1 1 1h5.59c.89 0 1.34-1.08.71-1.71l-1.91-1.91c1.39-1.16 3.16-1.88 5.12-1.88 3.16 0 5.89 1.84 7.19 4.5.27.56.91.84 1.5.64.71-.23 1.07-1.04.75-1.72C20.23 10.42 16.65 8 12.5 8z"/></svg>
                                <p>Atrás</p>
                            </div>
                            <div class="optDropDownItem" id="optMoreNextPath">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z"/></svg>
                                <p>Adelante</p>
                            </div>
                        </div>
                    </div>
                    <div class="optDropDownItem" id="optMoreDesplExplorer" onclick="showTree()">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z"/></svg>
                        <p>Mostrar Explorador<small>alt+x</small></p>
                    </div>
                    <div class="optDropDownItem" id="optMoreSettings" onclick="showSettings(true)">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
                        <p>Configuración<small>alt+s</small></p>
                    </div>
                    <div class="optDropDownItem" id="optMoreLogout" onclick="logout()">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10.79 16.29c.39.39 1.02.39 1.41 0l3.59-3.59c.39-.39.39-1.02 0-1.41L12.2 7.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L12.67 11H4c-.55 0-1 .45-1 1s.45 1 1 1h8.67l-1.88 1.88c-.39.39-.38 1.03 0 1.41zM19 3H5c-1.11 0-2 .9-2 2v3c0 .55.45 1 1 1s1-.45 1-1V6c0-.55.45-1 1-1h12c.55 0 1 .45 1 1v12c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1v3c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                        <p>Cerrar Sesión</p>
                    </div>
                </div>
                <div class="option" id="optView">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 11h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm0 7h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm6 0h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm6 0h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm-6-7h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm5-5v4c0 .55.45 1 1 1h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="optDropDown" id="optViewDespl">
                    <div class="optDropDownItem" id="optViewDesplMosaic" onclick="changeView('Mosaic')">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 11h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm0 7h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm6 0h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm6 0h3c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm-6-7h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm5-5v4c0 .55.45 1 1 1h3c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-3c-.55 0-1 .45-1 1z"/></svg>
                        <p>Mosaico</p>
                    </div>
                    <div class="optDropDownItem" id="optViewDesplList" onclick="changeView('List')">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none" opacity=".87"/><path d="M4 14h2c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zm0 5h2c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zM4 9h2c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zm5 5h10c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1H9c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zm0 5h10c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1H9c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1zM8 6v2c0 .55.45 1 1 1h10c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1H9c-.55 0-1 .45-1 1z"/></svg>
                        <p>Lista</p>
                    </div>
                    <div class="optDropDownItem" id="optViewDesplWall" onclick="changeView('Wall')">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 15h14c.55 0 1-.45 1-1s-.45-1-1-1H5c-.55 0-1 .45-1 1s.45 1 1 1zm0 4h14c.55 0 1-.45 1-1s-.45-1-1-1H5c-.55 0-1 .45-1 1s.45 1 1 1zm0-8h14c.55 0 1-.45 1-1s-.45-1-1-1H5c-.55 0-1 .45-1 1s.45 1 1 1zM4 6c0 .55.45 1 1 1h14c.55 0 1-.45 1-1s-.45-1-1-1H5c-.55 0-1 .45-1 1z"/></svg>
                        <p>Muro</p>
                    </div>
                </div>
                <div class="option" id="optFavorite">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.35 20.13c-.76.69-1.93.69-2.69-.01l-.11-.1C5.3 15.27 1.87 12.16 2 8.28c.06-1.7.93-3.33 2.34-4.29 2.64-1.8 5.9-.96 7.66 1.1 1.76-2.06 5.02-2.91 7.66-1.1 1.41.96 2.28 2.59 2.34 4.29.14 3.88-3.3 6.99-8.55 11.76l-.1.09z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="option" id="optNotFavorite">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19.66 3.99c-2.64-1.8-5.9-.96-7.66 1.1-1.76-2.06-5.02-2.91-7.66-1.1-1.4.96-2.28 2.58-2.34 4.29-.14 3.88 3.3 6.99 8.55 11.76l.1.09c.76.69 1.93.69 2.69-.01l.11-.1c5.25-4.76 8.68-7.87 8.55-11.75-.06-1.7-.94-3.32-2.34-4.28zM12.1 18.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="option" id="optDatabase">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 13H5c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-4c0-1.1-.9-2-2-2zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM19 3H5c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM7 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                    <div class="optionArea"></div>
                </div>
                <div class="option" id="optLaunch">
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
                        <div class="asideTitle">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19.66 3.99c-2.64-1.8-5.9-.96-7.66 1.1-1.76-2.06-5.02-2.91-7.66-1.1-1.4.96-2.28 2.58-2.34 4.29-.14 3.88 3.3 6.99 8.55 11.76l.1.09c.76.69 1.93.69 2.69-.01l.11-.1c5.25-4.76 8.68-7.87 8.55-11.75-.06-1.7-.94-3.32-2.34-4.28zM12.1 18.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/></svg>
                            <p>Favoritos</p>
                        </div>
                        <div class="asideBody" id="asideFavBody"></div>
                    </div>
                    <hr />
                    <div id="asideTree">
                        <div class="asideTitle">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
                            <p>Directorios</p>
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
                    <h2>Settings</h2>
                    <svg onclick="showSettings(false)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                </div>
                <div class="dialogBody">
                    <p style="color:#f00">Ventana en desarrollo</p>
                    <div class="settingsItem">
                        <h3>General</h3>
                        <div><label><input type="checkbox" />Activar el modo oscuro</label></div>
                        <div><label><input type="checkbox" />Mostrar archivos ocultos</label></div>
                        <div><label><input type="checkbox" />Mostrar extensión de los archivos</label></div>
                        <div><label><input type="checkbox" />Activar</label></div>
                    </div>
                    <div class="settingsItem">
                        <h3>Estado Inicial</h3>
                        <div>
                            <p>Vista activa por defecto</p>
                            <form>
                                <label><input type="radio" name="viewOption" value="Mosaic" />Mosaico</label>
                                <label><input type="radio" name="viewOption" value="List" />Lista</label>
                                <label><input type="radio" name="viewOption" value="Icons" />Iconos</label>
                                <label><input type="radio" name="viewOption" value="last" />Último activo</label>
                            </form>
                        </div>
                    </div>
                    <div class="settingsItem">
                        <h3>Omitir archivos</h3>
                        <div>
                            <p>nombres de archivos y extensiones separados por comas</p>
                            <input id="ignoreFilesInput" type="text" name="" value="" />
                        </div>
                    </div>
                    <div class="settingsItem">
                        <h3>Prioridad de Índices</h3>
                        <div>
                            <div><label><input type="checkbox" />Índice predeterminado del sistema</label></div>
                            <p>Lista de prioridad de ejecución para los directorios</p>
                            <input id="indexPriorityInput" type="text" name="" value="" />
                        </div>
                    </div>
                </div>
            </div>
            <div id="history" class="dialog">
                <div class="dialogTitleBar">
                    <h2>Historial</h2>
                    <svg onclick="showHistory(false)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/></svg>
                </div>
                <div class="dialogBody" id="historyBody">
                    <div id="historyPaths"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
}
?>
