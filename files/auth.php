<?php

$userList = array(
// LIST OF USERS
// --------------------------------------------------------
// type your users here in the format 'user' => 'password',
    'user1' => 'pass1',
// --------------------------------------------------------
);

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
            $return['message'] = 'WrongUser';
        } else {
            if ($credentialsCorrect>0) { // login successfull
                $return['status'] = 200;
                $return['token'] = $token;
                $return['message'] = 'AccessGranted';
            } else { // wrong password
                $return['status'] = 400;
                $return['token'] = $token;
                $return['message'] = 'WrongCredentials';
            }
        }

        /*echo '<pre>';
        print_r($return);
        echo '</pre>';*/

    } else { // missing login data
        $return['status'] = 400;
        $return['token'] = $token;
        $return['message'] = 'missingData';
    }

} else { // missing token
    $return['status'] = 400;
    $return['token'] = 'null';
    $return['message'] = 'missingData';
}

echo json_encode($return);

?>
