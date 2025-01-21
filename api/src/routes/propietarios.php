<?php
header('Content-Type: application/json; charset=utf-8');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once '../../includes/constants.php';

$app->get('/propietarios/actualizados', function(Request $req, Response $res) {
    
    $prop = new propietario();
    $res = $prop->obtenerPropietariosActualizados();
    unset($res['query'],$res['row']);
    echo json_encode($res);
    
});

$app->get('/propietarios', function(Request $req, Response $res) {
    
    try {
        $prop = new propietario();
        $data = $prop->listar();
        unset($data['query']);
        $newRes = $res->withJson($data);
        return $newRes;
    } catch (\Throwable $th) {
        return anError($th,$res);
    }
    
});

$app->put('/propietarios/update', function(Request $req, Response $res) {

    try {
        $prop = new propietario();
        $result = json_decode($req->getBody(),true);
        
        foreach ($result as $item => $value) {
            $r = $prop->actualizar($value['id'], ['modificado'=>0,'cambio_clave'=>0]);
            $result[$item]['updated'] = $r['suceed'];
            $result[$item]['stats']   = $r['stats'];
        }
        $newRes =  $res->WithJson($result);
        return $newRes;
        
    } catch (\Throwable $th) {
        return anError($th,$res);
    }

});


$app->post('/propietarios/insert', function(Request $req, Response $res) {

    try {
        //throw(new Exception('Este es un error generado automáticamente'));
        $propietario = new propietario();
        $propiedad   = new propiedades();
        $inmueble    = new inmueble();
    
        $data = json_decode($req->getBody(),true, 512, JSON_UNESCAPED_UNICODE);
    
        foreach ($data as $index => $item) {
            
            // actualizamos el estado de cuenta general del inmueble
            $edocta = [
                'id_inmueble' => $item['codinm'],
                'apto'        => $item['apto'],
                'propietario' => $item['nombre'],
                'recibos'     => $item['recibos'],
                'deuda'       => $item['deuda'],
                'deuda_usd'   => $item['deuda_usd'],
            ];
            
            $result = $inmueble->insertarActualizarEstadoDeCuentaInmueble($edocta);
            $data[$index]['account_statement']['suceed'] = $result['suceed'];
    
            // actualizamos la tabla propiedades
            $prop = [
                'cedula'          => $item['cedula'],
                'id_inmueble'     => $item['codinm'],
                'apto'            => $item['apto'],
                'alicuota'        => $item['alicuota'],
                'meses_pendiente' => $item['recibos'],
                'deuda_total'     => $item['deuda'],
                'deuda_usd'       => $item['deuda_usd'],
            ];
            $result = $propiedad->insertarActualizar($prop);
            $data[$index]['property']['suceed'] = $result['suceed'];
    
            // actualizamos la tabla propietarios
            unset($item['id'],$item['alicuota'],$item['deuda'],$item['deuda_usd']);
            
            $result = $propietario->insertarActualizar($item);
            $data[$index]['data']['suceed']  = $result['suceed'];
            $data[$index]['insert_id']       = $result['insert_id'];
            $data[$index]['stats']           = $result['stats'];
    
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});
