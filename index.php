<?php
include_once 'includes/constants.php';

$propietario = new propietario();
$result = array();
if (isset($_POST['submit'])) {
    $result = $propietario->login($_POST['cedula'], $_POST['password'], 0);
} else {
    if (isset($_POST['cedula'])) {
        $result = $propietario->recuperarContraSena($_POST['cedula']);
    }
}
echo $twig->render('login.html.twig', array("mensaje" => $result));