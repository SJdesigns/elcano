<?php

// Usuarios Habilitados
// ----------------------------------------------------
// $userList = array('user'=>'pass')
$userList = array(
    'root' => 'admin',
    'user1' => 'adminAccess1',
    'user2' => 'adminAccess2',
    'user3' => 'adminAccess3'
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

?>
