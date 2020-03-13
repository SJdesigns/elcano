<?php

// Usuarios Habilitados
// ----------------------------------------------------
// $userList = {'user':'pass',...}
$userList = array(
    'root' => 'admin',
    'user1' => 'pass1',
    'user2' => 'pass2',
    'user3' => 'pass3'
);
// ----------------------------------------------------

$return = 'false';

if (isset($_GET['user']) && isset($_GET['pass'])) {

    $user = $_GET['user'];
    $pass = $_GET['pass'];

    foreach ($userList as $key => $valor) {
        if ($user == $key) {
            $encryptedPass = sha1($valor);

            if ($pass == sha1($valor)) {
                $return = 'true';
            } else {
                $return = 'false';
            }
        } else {
        }
    }

    echo $return;

} else {
}

?>
