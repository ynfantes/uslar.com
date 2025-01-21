<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once '../../includes/constants.php';

$app->get('/facturas/notAvailable' , function(Request $req, Response $res){
    try {
        $factura = new factura();
        $recibos = [];
        $result = $factura->listarFacturasConNumFact();
        $n = 0;
        if ($result['suceed'] && count($result['data'])>0) {
            
            foreach ($result['data'] as $aviso) {
                $filename = '../../enlinea/avisos/'.$aviso['numero_factura'].'.pdf';
                if (!file_exists($filename)) {
                    $recibo = [
                        'codinm'    => $aviso['id_inmueble'],
                        'apto'      => $aviso['apto'],
                        'numfact'   => $aviso['numero_factura']
                    ];
                    $recibos['recibos'][] = $recibo;
                    $n++;
                }
            }
            
        }
        $recibos['total'] = $n;
        $newRes = $res->withJson($recibos);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

$app->post('/facturas/insert', function(Request $req, Response $res) {
    try {
        
        $factura = new factura();    
        $data = json_decode($req->getBody(),true);
    
        foreach ($data as $index => $fact) {
            unset($fact['id'],$fact['fidea']);
            $result = $factura->insertarActualizar($fact);
            $data[$index]['suceed'] = $result['suceed'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
    
});

$app->delete('/facturas/delete', function(Request $req, Response $res) {
    try {
        
        $factura = new factura();
        $data = json_decode($req->getBody(),true);
        
        foreach ($data as $index => $fact) {
            unset($fact['id']);
            $result = $factura->borrarFactura($fact);
            $data[$index]['suceed'] = $result['suceed'];
        }
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }

});

$app->post('/avisos/strToPDF', function(Request $req, Response $res) {

    try {
        
        $data = json_decode($req->getBody(),true);
        //Decode pdf content
        $pdf_decoded = base64_decode($data['base64']);
        //Write data back to pdf file
        $pdf = fopen('../../enlinea/avisos/'.$data['filename'],'w');
        $data['suceed'] = fwrite($pdf,$pdf_decoded);
        unset($data['base64']);
        //close output file
        fclose($pdf);
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }

});

$app->delete('/facturas/reversoFacturacion/periodo/{periodo}/inmueble/{inmueble}', function(Request $req, Response $res, array $args) {
    
    try {
        
        $factura = new factura();
        if (DateTime::createFromFormat('Y-m-d',$args['periodo']) !== false && $args['inmueble']<>'') {
            
            $data['periodo'] = $args['periodo'];
            $data['id_inmueble'] = $args['inmueble'];            
            $result = $factura->borrarFactura($data);
            unset($result['query']);
            $data['result'] = $result;

        } else {
            $data['error'] = 'Período inválido';
        }
        
        $newRes = $res->withJson($data);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

$app->get('/facturas/checkAccountStatemet', function(Request $req, Response $res) {
    try {
        $factura = new factura();
        $result = [];
        $result = $factura->listarDiferenciasRecibosPendientesPorPropietario();
        unset($result['query'], $result['row']);
        if(!$result['suceed']) unset($result['data']);
        $newRes = $res->withJson($result);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});