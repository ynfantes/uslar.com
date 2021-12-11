<?php
include_once '../../includes/constants.php';

$propietario = new propietario();

$propietario->esPropietarioLogueado();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";
$session = $_SESSION;

switch ($accion) {
    // <editor-fold defaultstate="collapsed" desc="junta-condominio">
    case "junta-condominio": default :
        $prop = new propiedades();
        $inmuebles = new inmueble();
        $junta_condominio = new junta_condominio();
        $propiedades = $prop->propiedadesPropietario($_SESSION['usuario']['cedula']);


        $miembros = Array();
        $resultado = Array();

        if ($propiedades['suceed'] == true) {
            $id_inmueble = "";
            
            foreach ($propiedades['data'] as $propiedad) {
                
                if ($propiedad['id_inmueble'] != $id_inmueble) {
                    $id_inmueble = $propiedad['id_inmueble'];
                    $inmueble = $inmuebles->ver($id_inmueble);
                    $junta = $junta_condominio->listarJuntaPorInmueble($id_inmueble);
                    
                    if (!count($junta['data']) > 0) {
                        $resultado['suceed'] = true;
                        $resultado['mensaje'] = "No está registrada la información de los miemnbros de la Junta de condominio. 
                            Si considera que esto es un error, pónganse en contacto con la adminsitradora.";
                    }
                    
                    $miembros[] = Array(
                        "inmueble" => $inmueble['data'][0],
                        "miembros" => $junta['data']);
                }
            }
        }
        
        echo $twig->render('enlinea/inmueble/formulario.html.twig', array(
            "session"   => $session,
            "junta"     => $miembros,
            "resultado" => $resultado
        ));
        
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="estado cuenta inmueble">
    case "cuenta":
        $propiedad = new propiedades();
        $inmueble = new inmueble();
        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
        if ($propiedades['suceed'] == true) {
            $cuentas = Array();
            $codigo_inmueble = '';
            
            foreach ($propiedades['data'] as $propiedad) {
                if ($codigo_inmueble != $propiedad['id_inmueble']) {
                    
                    $inm = $inmueble->ver($propiedad['id_inmueble']);
                    $cuenta = $inmueble->estadoDeCuenta($propiedad['id_inmueble']);
                    $cuentas[] = Array("inmueble" => $inm['data'][0], "cuenta" => $cuenta['data']);
                    $codigo_inmueble = $propiedad['id_inmueble'];
                
                }
            }
        }


        echo $twig->render('enlinea/inmueble/estado-de-cuenta.html.twig', array("session" => $session,
            "cuentas" => $cuentas
        ));
        break; // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="cartelera">
    case "cartelera":
        echo $twig->render('enlinea/inmueble/cartelera.html.twig', array("session" => $session));
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="facturacion_flot">
    case "facturacion_flot":
        $inmuebles = new inmueble();
        $facturacion = $inmuebles->movimientoFacturacionMensual();
        $rows = array();
        $table = array();
        $table['label'] = 'Miles de Bs.';
        $i = 0;
        foreach ($facturacion['data'] as $r) {
            $i++;
            //$temp[] = array('v' => (string) Misc::date_periodo_format($r['periodo'])); 
            //$temp[] = array('v' => (float)$r['facturado']/1000); 
            //$rows[] = array('c' => $temp);
            $rows[] = array((string) Misc::date_periodo_format($r['periodo']), $r['facturado'] / 1000);
        }
        $table['data'] = $rows;
        $jsonTable = json_encode($table);
        echo $jsonTable;
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="facturacion google">
    case "facturacion":
        $inmuebles = new inmueble();
        $propiedad = new propiedades();
        $propiedades = $propiedad->inmueblePorPropietario($_SESSION['usuario']['cedula']);
        
        if ($propiedades['suceed']) {
            $facturacion = $inmuebles->movimientoFacturacionMensual($propiedades['data'][0]['id_inmueble']);
            
            if ($facturacion['suceed']) {
                $rows = array();
                $table = array();
                $table['cols'] = array(
                    array('label' => 'Facturación Mensual', 'type' => 'string'),
                    array('label' => 'Bolívares', 'type' => 'number')
                );
                foreach ($facturacion['data'] as $r) {
                    $temp = array();
                    $temp[] = array('v' => (string) Misc::date_periodo_format($r['periodo']));

                    $temp[] = array('v' => (float) $r['facturado']);

                    $rows[] = array('c' => $temp);
                }
                $table['rows'] = $rows;
                $jsonTable = json_encode($table);
            }
        }
        echo $jsonTable;
        
        break; // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="cobranza">
    case "cobranza":
        $inmuebles = new inmueble();
        $propiedad = new propiedades();
        $propiedades = $propiedad->inmueblePorPropietario($_SESSION['usuario']['cedula']);
        $rows = array();
        $table = array();
        $table['cols'] = array(
            array('label' => 'Facturación Mensual', 'type' => 'string'),
            array('label' => 'Bolívares', 'type' => 'number')
        );
        if ($propiedades['suceed']) {
        
            $facturacion = $inmuebles->movimientoCobranzaMensual($propiedades['data'][0]['id_inmueble']);
            
            if ($facturacion['suceed']) {
            
                foreach ($facturacion['data'] as $r) {
                    $temp = array();
                    $temp[] = array('v' => (string) Misc::date_periodo_format($r['periodo']));

                    $temp[] = array('v' => (float) $r['monto']);

                    $rows[] = array('c' => $temp);
                }
            
            }
        }
        $table['rows'] = $rows;
       $jsonTable = json_encode($table);
        echo $jsonTable;
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Listar cuentas de fondos">
    case "listarCuentasDeFondo":
        $fondo = new fondo();
        $inmueble = new inmueble();
        $cuenta = Array();
        $fondos = Array();
        $m = Array();
        $propiedades = $inmueble->listarInmueblesPorPropietario($_SESSION['usuario']['cedula']);
        
        if ($propiedades['suceed'] && count($propiedades['data']) > 0) {
            $id_inmueble = $propiedades['data'][0]['id_inmueble'];
            $r = $fondo->listarCuentasDeFondoInmueble($id_inmueble);
//            $bitacora->insertar(Array(
//                "id_sesion"=>$session['id_sesion'],
//                "id_accion"=> 16,
//                "descripcion"=>$id_inmueble,
//            ));
            
            if ($r['suceed'] && count($r['data']) > 0) {
                $fondos = $r['data'];
                $codigo_gasto = isset($_GET['id']) ? $_GET['id']:$fondos[0]['codigo_gasto'];
                $cuenta_fondo = $fondo->obtenerIdCuentaFondo($id_inmueble, $codigo_gasto);
                
                if ($cuenta_fondo['suceed'] && count($cuenta_fondo['data'])>0) {
                    $cuenta = $cuenta_fondo['data'][0];
                }
                $movimientos = $fondo->consultaEstadoDeCuentaFondo($id_inmueble, $codigo_gasto);
                if ($movimientos['suceed'] && count($movimientos['data'])>0) {
                    $m = $movimientos['data'];
                }
            }
        }
        
        echo $twig->render('enlinea/inmueble/fondos.html.twig', array(
            "session" => $session,
            "propiedades" => $propiedades['data'],
            "id_inmueble" => $id_inmueble,
            "fondos" => $fondos,
            "movimientos"=>$m,
            "cuenta"=>$cuenta
        ));
        break; // </editor-fold>
        
    case "imprimircuenta":
        break;
}