<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once '../../includes/constants.php';

$app->post('/fondos/cuentas', function(Request $req, Response $res) {

    try {
        
        $fondo = new fondo();
        $data = json_decode($req->getBody(),true);
        foreach ($data as $index => $cta) {
            $result = $fondo->insertarRegistroFondo($cta, ['saldo' => $cta['saldo']]);
            $data[$index]['suceed'] = $result['suceed'];
            $data[$index]['stats']  = $result['stats'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }

});

$app->post('/fondos/movimiento', function(Request $req, Response $res) {

    try {
        
        $fondo = new fondo();
        $data = json_decode($req->getBody(),true);
        $codinm = '';
        $codgas = '';
        $id = -1;
        foreach ($data as $index => $cta) {
            if ($codinm !== $cta['codigo_inmueble'] || $codgas !== $cta['codigo_gasto']) {
                $codinm = $cta['codigo_inmueble'];
                $codgas = $cta['codigo_gasto'];
                $r = $fondo->obtenerIdCuentaFondo($codinm, $codgas);
                $id = -1;
                if ($r['suceed'] && count($r['data'])>0) {
                    $id = $r['data'][0]['id'];
                    $fondo->borrarMovimientosPorIdFondo($id);
                }
            }
            if ($id > -1) {
                $cta['id_fondo'] = $id;
                unset($cta['codigo_inmueble'],$cta['codigo_gasto']);
                $result = $fondo->insertarMovimiento($cta);
                $data[$index]['suceed'] = $result['suceed'];
                $data[$index]['stats']  = $result['stats'];
            }
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }

});