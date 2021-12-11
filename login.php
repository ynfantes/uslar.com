<?php
include_once 'includes/constants.php';
$propietario = new propietario();
$result = array();
$password = '';
$apto = '';
if (isset($_POST['apto']) && isset($_POST['password'])) {

//if (isset($_POST['submit'])) {
    $apto = $_POST['apto'];
    $password = $_POST['password'];
    $result = $propietario->login($apto,$password, 0);
    
    if ($result['suceed']=='true') {
        
        if ($_SESSION['status'] == 'logueado') {
            header("location:" . URL_SISTEMA );
        }
        die();
    }
} else {
    if (isset($_POST['email'])) {
        $result = $propietario->recuperarContraSena($_POST['email']);
    }
}
//var_dump($result);
echo $twig->render('login.html.twig', array("mensaje" => $result));