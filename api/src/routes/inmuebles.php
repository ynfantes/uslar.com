<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once '../../includes/constants.php';

$app->post('/inmuebles/insert', function(Request $req, Response $res) {
    try {
        
        $inmueble = new inmueble();    
        $data = json_decode($req->getBody(),true);
        
        foreach ($data as $index => $inm) {
            unset($inm['fecha_actualizacion']);
            $result = $inmueble->insertarActualizar($inm);
            $data[$index]['suceed'] = $result['suceed'];
            $data[$index]['stats']  = $result['stats'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

$app->delete('/inmuebles', function(Request $req, Response $res) {
    $result = [];
    $data = json_decode($req->getBody(),true);
    
    $db = new db();
    $tables = [
        [
            'name'  => 'propietarios',
            'field' => 'codinm'
        ],
        [
            'name'  => 'propiedades',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'facturas',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'facturacion_mensual',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'cobranza_mensual',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'cartelera_inmueble',
            'field' => 'inmueble'
        ],
        [
            'name'  => 'fondos',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'grupo',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'inmueble_cuenta',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'inmueble_deuda_confidencial',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'junta_condominio',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'movimiento_caja',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'notificacion',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'pago_detalle',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'prerecibo',
            'field' => 'id_inmueble'
        ],
        [
            'name'  => 'inmueble',
            'field' => 'id'
        ],

        
    ];
    foreach ($data as $index => $inm) {

        $codinm = $inm['id'];
        $count = 0;
        $db->exec_query("START TRANSACTION");
        try {
            foreach ($tables as $index => $obj) {
                
                if ($obj['name']!='' and $obj['field'] !='') {
    
                    $table = $obj['name'];
                    $field = $obj['field'];
                    if ($table == 'propietarios') {
                        // borramos la bitacora del condominio
                        $r = $db->exec_query("delete from bitacora where id_sesion in (".
                            "select id from sesion where cedula in (".
                                "select cedula from propietarios where $field = '$codinm'))");

                        if($r['suceed'] && $r['data']) $count += 1;
                        $r['table'] = 'bitacora';
                        $result[] = $r;
                        // borramos los inicio de sesion
                        $r = $db->exec_query("delete from sesion where cedula in (".
                                "select cedula from propietarios where $field = '$codinm')");
                        if($r['suceed'] && $r['data']) $count += 1;
                        $r['table'] = 'sesion';
                        $result[] = $r;
                    }
                    if ($table == 'fondos') {
                        // borramos el movimiento de las cuentas de fondo
                        $r = $db->exec_query("delete from fondos_movimiento where id_fondo in (".
                                "select id from $table where $field = '$codinm')");
                        if($r['suceed'] && $r['data']) $count += 1;
                        $r['table'] = 'fondos_movimiento';
                        $result[] = $r;
                    }
                    $r = $db->exec_query("delete from $table where $field = '$codinm'");
                    $r['table'] = $table;
                    if($r['suceed'] && $r['data']) $count += 1;
                    $result[] = $r;
                }
            }
            $response[] = [
                'id'              => $codinm,
                'affected_tables' => $count,
                'response'        => $result
            ];
            $db->exec_query("COMMIT");

        } catch (Exception $exc) {
            $db->exec_query("ROLLBACK");
            $response[] = $exc->getTraceAsString();
        }
    }
    $newRes = $res->withJson($response);
    return $newRes;
    


});
$app->put('/cuentas', function(Request $req, Response $res) {
    try {
        
        $inmueble = new inmueble();
        $data = json_decode($req->getBody(),true);
        $act = $data;
        unset($act['IDCuenta'],$act['id']);
        $result = $inmueble->actualizar($data['id'],$act);
        $data['suceed'] = $result['suceed'];
        $data['stats'] = $result['stats'];
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});


$app->post('/cuentas', function(Request $req, Response $res) {
    try {
        
        $inmueble = new inmueble();
        $data = json_decode($req->getBody(),true);
        foreach ($data as $index => $cuenta) {
            unset($cuenta['IDCuenta']);
            $result = $inmueble->agregarCuentaInmueble($cuenta);
            $data[$index]['suceed']  = $result['suceed'];
            $data[$index]['stats']   = $result['stats'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});


$app->delete('/cuentas', function(Request $req, Response $res) {
    try {
        
        $inmueble = new inmueble();
        $data = json_decode($req->getBody(),true);
        foreach ($data as $index => $cuenta) {
            $result = $inmueble->borrarCuentaBancaria($cuenta['id_inmueble'],$cuenta['numero_cuenta']);
            $data[$index]['suceed'] = $result['suceed'];
            $data[$index]['stats']  = $result['stats'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

$app->put('/cobranza/mensual', function(Request $req, Response $res) {
    try {
        
        $inmueble = new inmueble();
        $data = json_decode($req->getBody(),true);
        foreach ($data as $index => $cobranza) {
            $result = $inmueble->insertarCobranzaMensual($cobranza);
            $data[$index]['suceed'] = $result['suceed'];
            $data[$index]['stats']   = $result['stats'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

$app->put('/facturacion/mensual', function(Request $req, Response $res) {
    try {
        
        $inmueble = new inmueble();
        $data = json_decode($req->getBody(),true);
        foreach ($data as $index => $facturacion) {
            $result = $inmueble->insertarFacturacionMensual($facturacion);
            $data[$index]['suceed'] = $result['suceed'];
            $data[$index]['stats']   = $result['stats'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

$app->put('/propietarios/grupo', function(Request $req, Response $res) {
    try {
        
        $inmueble = new inmueble();
        $result = json_decode($req->getBody(),true);
        $codinm = '';
        foreach ($result as $item => $prop) {
    
            if ($codinm !== $prop['id_inmueble']) {
                $codinm = $prop['id_inmueble'];
                $condicion = array_filter($prop, function($key) {
                    return $key === 'id_inmueble';
                },ARRAY_FILTER_USE_KEY);
                $r = $inmueble->borrarGrupo($condicion);
            }
    
            $r = $inmueble->insertarGrupo($prop);
            $result[$item]['suceed'] = $r['suceed'];
            $result[$item]['stats'] = $r['stats'];
            if ($r['stats']['error']) $result[$item]['query'] = $r['query'];
            
        }
        $newRes = $res->withJson($result);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }

});

$app->post('/propietarios/grupo', function(Request $req, Response $res) {
    try {
        $inmueble = new inmueble();
        $result = json_decode($req->getBody(),true);
        $codinm = '';
        $grupo  = '';
        foreach ($result as $item => $prop) {
    
            $r = $inmueble->insertarGrupoPropietario($prop);
            $result[$item]['suceed'] = $r['suceed'];
            $result[$item]['stats'] = $r['stats'];
            if ($r['stats']['error']) $result[$item]['query'] = $r['query'];
            
        }
        $newRes = $res->withJson($result);
        return $newRes;
    } catch (\Throwable $th) {
        return anError($th, $res);        
    }

});