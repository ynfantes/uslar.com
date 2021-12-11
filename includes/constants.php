<?php
// <editor-fold defaultstate="collapsed" desc="configuracion regional">
date_default_timezone_set("America/La_Paz");
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="init">
$debug = true;
$sistema = "/dinastia";
$email_error = true;
$mostrar_error = true;

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Cheqeuo servidor">
if ($_SERVER['SERVER_NAME'] == "www.administradoradinastia.com.ve" | $_SERVER['SERVER_NAME'] == "administradoradinastia.com.ve") {
    $user = "cemeduve_valoriza2";
    $password = "valoriza25231";
    $db = "cemeduve_valoriza2";
    $email_error = true;
    $mostrar_error = false;
    $debug = false;
    $sistema = "/app";
} else {
    $user = "root";
    $password = "";
    $db = "lfb978_dwap525";
}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Acceso a la BD">
define("HOST", "localhost");
define("USER", $user);
define("PASSWORD", $password);
define("DB", $db);
// </editor-fold>
//<editor-fold defaultstate="collapsed" desc="configuracion de ficheros del sistema">
define("SISTEMA", $sistema);
define("EMAIL_ERROR", $email_error);
define("EMAIL_CONTACTO", "ynfantes@gmail.com");
define("EMAIL_TITULO", "error");
define("MOSTRAR_ERROR", $mostrar_error);
define("DEBUG", $debug);

define("TITULO", "Administradora Dinastía");
/**
 * para las urls
 */
define("ROOT", 'https://' . $_SERVER['SERVER_NAME'] . SISTEMA);

define("URL_SISTEMA", ROOT . "/enlinea");
define("URL_INTRANET", ROOT . "/intranet");
/**
 * para los includes
 */
define("SERVER_ROOT", $_SERVER['DOCUMENT_ROOT'] . SISTEMA);

/*set_include_path(SERVER_ROOT . "/site/");*/
define("TEMPLATE", SERVER_ROOT . "/template/");
define("mailPHP",0);
define("sendMail",1);
define("SMTP",2);
//</editor-fold>
////<editor-fold defaultstate="collapsed" desc="Twig">
include_once SERVER_ROOT . '/includes/twig/lib/Twig/Autoloader.php';
include_once SERVER_ROOT . '/includes/extensiones.php';

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(SERVER_ROOT . '/template');

$twig = new Twig_Environment($loader, array(
            'debug' => true,
            'cache' => SERVER_ROOT . 'cache',
            "auto_reload" => true)
);
if (isset($_SESSION))
    $twig->addGlobal("session", $_SESSION);

$twig->addExtension(new extensiones());
$twig->addExtension(new Twig_Extension_Debug());

//</editor-fold>
//<editor-fold defaultstate="collapsed" desc="autoload">

function __autoload($clase) {
    include_once SERVER_ROOT . "/includes/" . $clase . ".php";
}

spl_autoload_register("__autoload", false);
//</editor-fold>
//<editor-fold defaultstate="collapsed" desc="cerrar sesión">
if (isset($_GET['logout']) && $_GET['logout'] == true) {
    $user_logout = new propietario();
    $user_logout->logout();
}
//</editor-fold>

define("NOMBRE_APLICACION","Dinastía Administradora");
define("ACTUALIZ","data/");
define("ARCHIVO_INMUEBLE","INMUEBLE.txt");
define("ARCHIVO_FACTURA","FACTURA.txt");
define("ARCHIVO_CUENTAS","CUENTAS.txt");
define("ARCHIVO_FACTURA_DETALLE","FACTURA_DETALLE.txt");
define("ARCHIVO_JUNTA_CONDOMINIO","JUNTA_CONDOMINIO.txt");
define("ARCHIVO_PROPIEDADES","PROPIEDADES.txt");
define("ARCHIVO_PROPIETARIOS","PROPIETARIOS.txt");
define("ARCHIVO_EDO_CTA_INM","EDO_CUENTA_INMUEBLE.txt");
define("ARCHIVO_ACTUALIZACION","ACTUALIZACION.txt");
define("ARCHIVO_CUENTAS_DE_FONDO","CUENTAS_FONDO.txt");
define("ARCHIVO_MOVIMIENTOS_DE_FONDO","MOVIMIENTO_FONDO.txt");
define("SMTP_SERVER","host.caracaspanel.com");
define("PORT","465");
define("USER_MAIL","info@dinastiaadministradora.com.ve");
define("PASS_MAIL","edgar5231");
define("MESES_COBRANZA","6");
define("GRAFICO_FACTURACION",0);
define("GRAFICO_COBRANZA",0);
define("DEMO",0);
define("MOVIMIENTO_FONDO",1);