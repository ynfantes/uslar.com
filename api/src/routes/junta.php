<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once '../../includes/constants.php';

$app->post('/junta/registrar', function(Request $req, Response $res) {
    try {
        
        $junta = new junta_condominio();
        $data = json_decode($req->getBody(),true);
        $codinm = '';
        foreach ($data as $index => $inmueble) {
            if ($codinm !== $inmueble['id_inmueble']) {
                $codinm = $inmueble['id_inmueble'];
                $condicion = array_filter($inmueble, function($k) {
                    return $k === 'id_inmueble';
                },ARRAY_FILTER_USE_KEY);
                $junta->borrarJuntaPorCondominio($condicion);
            }
            $result = $junta->insertar($inmueble);
            $data[$index]['suceed']  = $result['suceed'];
            $data[$index]['stats']   = $result['stats'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});