<?php
header ('Content-type: text/html; charset=utf-8');

include_once 'includes/db.php';
include_once 'includes/file.php';
include_once 'includes/inmueble.php';
include_once 'includes/junta_condominio.php';
include_once 'includes/propietario.php';
include_once 'includes/propiedades.php';
include_once 'includes/factura.php';

$db = new db();

$tablas = array("DetFact", "facturas", "propiedades", "propietarios", "junta_condominio", "inmueble", "inmueble_deuda_confidencial","fondos_movimiento");

if (isset($_GET['codinm'])) {
    $codinm = $_GET['codinm'];
    $db->exec_query("delete from factura_detalle where id_factura in (select numero_factura from facturas wher id_inmueble='".$codinm."')");
    $db->exec_query("delete from facturas where id_inmueble='".$codinm."'");
    $db->exec_query("delete from propietarios where cedula in (select cedula from propiedades where id_inmueble='".$codinm."')");
    $db->exec_query("delete from junta_condominio where id_inmueble='".$codinm."'");
    $db->exec_query("delete from propiedades where id_inmueble='".$codinm."'");
    $db->exec_query("delete from inmueble where id='".$codinm."'");
    $db->exec_query("delete from inmueble_deuda_confidencial where id_inmueble='".$codinm."'");
    $mensaje = "Actualización inmueble ".$codinm."<br>";
} else {
    $mensaje = "Proceso de Actualización Ejecutado<br />";
    foreach ($tablas as $tabla) {
        $r = $db->exec_query("truncate table " . $tabla);
        echo "limpiar tabla: " .$tabla."<br />";
    }
}

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo inmueble">
$archivo = ACTUALIZ . ARCHIVO_INMUEBLE;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$inmueble = new inmueble();
$mensaje.= "procesar archivo inmueble (".count($lineas).")<br />";
echo "procesar archivo inmueble (".count($lineas).")<br />";

foreach ($lineas as $linea) {
    
    $registro = explode("\t", $linea);
    if ($registro[0] != "") {
        $registro = Array(
            "id" => $registro[0],            
            "nombre_inmueble"   => utf8_encode($registro[1]),
            "deuda"             => $registro[2],
            "fondo_reserva"     => $registro[3],
            "beneficiario"      => utf8_encode($registro[4]),
            "banco"             => '',
            "numero_cuenta"     => '',
            "supervision"       => '0',
            "RIF"               => $registro[5],
            "meses_mora"        => $registro[6],
            "porc_mora"         => $registro[7],
            "moneda"            => $registro[8],
            "unidad"            => $registro[9],
            "facturacion_usd"   => $registro[10],
            "tasa_cambio"       => $registro[11],
            "redondea_usd"      => $registro[12]);
        
        $r = $inmueble->insertar($registro);
        
        if($r["suceed"]==FALSE){
            echo ARCHIVO_INMUEBLE."<br/>".$r['stats']['errno']." ".'<br/>'.$r['query'].'<br/>';
        }   
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="procesamos el archivo cuentas">
$archivo = ACTUALIZ . ARCHIVO_CUENTAS;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$inmueble = new inmueble();
$mensaje.= "actulizando cuentas inmuebles (" . count($lineas) . ")<br />";
echo "actualizando cuentas inmuebles (" . count($lineas) . ")<br />";

foreach ($lineas as $linea) {
    $id = '';
    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        $id=$registro[0];
        $registro = Array(
            "numero_cuenta" => $registro[1],
            "banco" => $registro[2]);


        $r = $inmueble->actualizar("'".$id."'",$registro);
        if ($r["suceed"] == FALSE) {
            //echo ARCHIVO_INMUEBLE."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
            echo $r['query'];
            die();
        }

    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Junta_Condominio">
$archivo = ACTUALIZ . ARCHIVO_JUNTA_CONDOMINIO;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$junta_condominio = new junta_condominio();
echo "procesar archivo Junta Condominio (".count($lineas).")<br />";
$mensaje.= "procesar archivo Junta Condominio (".count($lineas).")<br />";
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        $registro = Array("id_cargo" => $registro[1],
            "id_inmueble" => $registro[0],
            "cedula" => $registro[2]);
        $r = $junta_condominio->insertar($registro);
        
        if($r["suceed"]==FALSE){
            echo ARCHIVO_JUNTA_CONDOMINIO."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
        }
    }
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Propietarios">
$archivo = ACTUALIZ . ARCHIVO_PROPIETARIOS;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$propietario = new propietario();
echo "procesar archivo Propietarios (".count($lineas).")<br />";
$mensaje.= "procesar archivo Propietarios (".count($lineas).")<br />";
foreach ($lineas as $linea) {


    $registro = explode("\t", $linea);


    if ($registro[0] != "") {
        
       $registro = Array(
                    'nombre' => utf8_encode($registro[0]),
                    'clave' => $registro[1],
                    'email' => utf8_encode($registro[2]),
                    'cedula' => $registro[3],
                    'telefono1' => $registro[4],
                    'telefono2' => $registro[5],
                    'telefono3' => $registro[6],
                    'direccion' => utf8_encode($registro[7]),
                    'recibos' => $registro[8]
           );
       
       $r = $propietario->insertar($registro);
       if($r["suceed"]==FALSE){
            echo "<b>Archivo Propietario: ".$archivo.' - '.$r['stats']['errno']."-".$r['stats']['error']."</b>".'<br/>'.$r['query'].'<br/>';
            die($r['query']);
        }
            /*}
        }*/
    }
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Propiedades">
$archivo = ACTUALIZ . ARCHIVO_PROPIEDADES;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$propiedades = new propiedades();
echo "procesar archivo Propiedades (".count($lineas).")<br />";
$mensaje.= "procesar archivo Propiedades (".count($lineas).")<br />";
foreach ($lineas as $linea) {


    $registro = explode("\t", $linea);

    if ($registro[0] != "") {
        $registro = Array(
            'cedula'        => $registro[0],
            'id_inmueble'   => $registro[1],
            'apto'          => $registro[2],
            'alicuota'      => $registro[3],
            'meses_pendiente' => $registro[4],
            'deuda_total'   => $registro[5],
            'deuda_usd'     => str_replace("\r", "", $registro[6])
               );
        
        $r = $propiedades->insertar($registro);
        if($r["suceed"]==FALSE){
            echo "<b>Archivo Propiedades: ".$r['stats']['errno']."-".$r['stats']['error']."</b><br />".'<br/>'.$r['query'].'<br/>';
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Facturas">
$archivo = ACTUALIZ . ARCHIVO_FACTURA;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$facturas = new factura();
echo "procesar archivo Facturas (".count($lineas).")<br />";
$mensaje.= "procesar archivo Facturas (".count($lineas).")<br />";
foreach ($lineas as $linea) {
    
    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        
        $registro = Array(
            'id_inmueble'   => $registro[0],
            'apto'          => $registro[1],
            'numero_factura'=> $registro[2],
            'periodo'       => $registro[3],
            'facturado'     => $registro[4],
            'abonado'       => $registro[5],
            'fecha'         => $registro[6],
            'facturado_usd' => $registro[7]
                );
        
        $r = $facturas->insertar($registro);
                
        if(!$r["suceed"]){
            echo($r['stats']['errno']."-".$r['stats']['error'].'<br/>'.$r['query'].'<br/>');
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Detalle Factura">
$archivo = ACTUALIZ . ARCHIVO_FACTURA_DETALLE;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
echo "procesar archivo Detalla Factura (".count($lineas).")<br />";
$mensaje.="procesar archivo Detalle Factura (".count($lineas).")<br />";
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);


    if ($registro[0] != "") {

        $registro = Array(
            "id_factura" => $registro[0],
            "detalle" => utf8_encode($registro[1]),
            "codigo_gasto" => $registro[2],
            "comun" => $registro[3],
            "monto" => str_replace("\r","",$registro[4])
                );
        
        $r = $facturas->insertar_detalle_factura($registro);
        
        if($r["suceed"]==FALSE){
            die($r['stats']['errno']."-".$r['stats']['error'].'<br/>'.$r['query'].'<br/>');
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Inmueble Esado Cuenta">
$archivo = ACTUALIZ . ARCHIVO_EDO_CTA_INM;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
echo "procesar archivo estado de cuenta inmueble (".count($lineas).")<br />";
$mensaje.="procesar archivo estado de cuenta inmueble (".count($lineas).")<br />";
foreach ($lineas as $linea) {


    $registro = explode("\t", $linea);


    if ($registro[0] != "") {

        $registro = Array(
            "id_inmueble" => $registro[0],
            "apto" => $registro[1],
            "propietario" => utf8_encode($registro[2]),
            "recibos" => $registro[3],
           "deuda"         => $registro[4],
            "deuda_usd"         => str_replace("\r", "", $registro[5])
        );

        $r = $inmueble->insertarEstadoDeCuentaInmueble($registro);


        if ($r["suceed"] == FALSE) {
            die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="graficos">

// <editor-fold defaultstate="collapsed" desc="facturacion mensual">
if (GRAFICO_FACTURACION == 1) {
    $archivo = ACTUALIZ . "FACTURACION_MENSUAL.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo grafico facturacion mensual (" . count($lineas) . ")<br />";
    $mensaje.="procesar archivo grafico facturación mensual (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_inmueble" => $registro[0],
                "periodo" => $registro[1],
                "facturado" => $registro[2]
            );

            $r = $inmueble->insertarFacturacionMensual($registro);

            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="cobranza mensual">
if (GRAFICO_COBRANZA == 1) {
    $archivo = ACTUALIZ . "COBRANZA_MENSUAL.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo grafico cobranza mensual (" . count($lineas) . ")<br />";
    $mensaje.="procesar archivo grafico facturación mensual (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_inmueble" => $registro[0],
                "periodo" => $registro[1],
                "monto" => $registro[2]
            );

            $r = $inmueble->insertarCobranzaMensual($registro);

            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}
// </editor-fold>

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="movimiento cuentas de fondo">
if (MOVIMIENTO_FONDO == 1) {
    $fondo = new fondo();
    $archivo = ACTUALIZ . ARCHIVO_CUENTAS_DE_FONDO;
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo cuentas de fondo (" .count($lineas).")<br/>";
    $mensaje.="procesar archivo cuentas de fondo (".count($lineas).")<br/>";
    foreach ($lineas as $linea) {
       $registro = explode("\t",$linea);
       if ($registro[0]!="") {
            $registro = Array(
               "id_inmueble"    =>  $registro[0],
               "codigo_gasto"   =>  $registro[1],
               "descripcion"    =>  utf8_encode($registro[2]),
               "saldo"          =>  $registro[3],
               "mostrar"        =>  1
            );
            $r = $fondo->insertarRegistroFondo($registro,
                    Array(
                        "saldo"         => $registro["saldo"],
                        "descripcion"   => $registro["descripcion"])
                    );
       }
    }
    $archivo = ACTUALIZ . ARCHIVO_MOVIMIENTOS_DE_FONDO;
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo movimiento de fondo (" .count($lineas).")<br/>";
    $mensaje.="procesar archivo movimiento de fondo (".count($lineas).")<br/>";
    $id_inmueble = "";
    $codigo_gasto = "";
    foreach ($lineas as $l) {
        
        $movimiento = explode("\t",$l);
        
        if ($movimiento[0]!="") {
            
            if ($id_inmueble != $movimiento[0] || $codigo_gasto != $movimiento[1]) {
                $id_inmueble = $movimiento[0];
                $codigo_gasto = $movimiento[1];
                $r = $fondo->obtenerIdCuentaFondo($id_inmueble, $codigo_gasto);
                if ($r['suceed'] && count($r['data'])>0) {
                    $id = $r['data'][0]['id'];
                } else {
                    $id = 0;
                }
            }
            
            if ($id > 0) {
                $registro = Array(
                    "id_fondo"      => $id,
                    "fecha"         => $movimiento[2],
                    "tipo"          => $movimiento[3],
                    "concepto"      => utf8_encode($movimiento[4]),
                    "debe"          => $movimiento[5],
                    "haber"         => $movimiento[6]
                );

                $r = $fondo->insertarMovimiento($registro);
                if ($r["suceed"] == FALSE) {
                    echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
                }
            }
        }

    }
    
}// </editor-fold>

$fecha = JFILE::read(ACTUALIZ."ACTUALIZACION.txt");
echo "****FIN DEL PROCESO DE ACTUALIZACION****<br />";
echo "Información actualizada al: ".$fecha."<br/>";
$mail = new mailto();
$r = $mail->enviar_email("Actualización ".NOMBRE_APLICACION." ".$fecha,$mensaje, "", 'dinastia.admon@gmail.com',"");
        
if ($r=="") {
    echo "Email de confirmación enviado con éxito<br />";
} else {
    echo "Falló el envio del emailo de ejecución del proceso<br />";
}
echo "Cierre esta ventana para finalizar.";