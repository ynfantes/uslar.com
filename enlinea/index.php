<?php
include_once '../includes/constants.php';
include_once '../includes/propietario.php';
include_once '../includes/file.php';

propietario::esPropietarioLogueado();

$archivo = '../'.ACTUALIZ . ARCHIVO_ACTUALIZACION;
$fecha_actualizacion = JFile::read($archivo);

$session = $_SESSION;

switch ($accion) {
    default :
        echo $twig->render('enlinea/index.html.twig', array(
            "session" => $session,
            "fecha_actualizacion" => $fecha_actualizacion,
            "GRAFICO_FACTURACION" => GRAFICO_FACTURACION,
            "GRAFICO_COBRANZA" => GRAFICO_COBRANZA
            ));
        break;
}