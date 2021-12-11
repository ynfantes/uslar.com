<?php
include_once '../../includes/constants.php';
include_once '../../includes/propietario.php';

propietario::esPropietarioLogueado();

$factura = new factura();

$r = $factura->facturaPerteneceACliente($_GET['id'], $_SESSION['usuario']['cedula']);

if ($r==true) {
    $titulo = "Aviso_Cobro_".$_GET['id'].".pdf";
    $content="Content-type: application/pdf";
    $url = $_GET['id'].".pdf";//URL_SISTEMA."/avisos/".$_GET['id'].".pdf";
    header($content);
    header('Content-Disposition: attachment; filename="'.$titulo.'"');
    header('Content-Length: '.filesize($url));
    readfile($url);
    
} else {
    echo "El recibo de condominio no se puede mostrar en estos momentos.";
}

?>