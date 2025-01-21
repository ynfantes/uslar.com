<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
include_once '../../includes/constants.php';

$app->post('/cancelacion/registro', function(Request $req, Response $res) {
    
    try {
        $pago = new pago();
        $data = json_decode($req->getBody(),true);
        foreach ($data as $index => $item) {
            unset($item['id']);
            $result = $pago->insertarActualizarCancelacionDeGastos($item);
            $data[$index]['suceed']    = $result['suceed'];
            $data[$index]['insert_id'] = $result['insert_id'];
            $data[$index]['stats']     = $result['stats'];
        }
        $newRes = $res->withJson($data);
        return $newRes;
    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

$app->get('/cancelacion/test', function(Request $req, Response $res) {
    $pago = new pago();
    $regs   = [];
    $result = $pago->listarCancelacionDeGastos('0014','202');
    if ($result['suceed'] && $result['stats']['affected_rows']>0) {
        foreach ($result['data'] as $data) {
            $regs[] = Array('inmueble'=>$data['id_inmueble'],'apto'=>$data['id_apto'],'numero_factura'=>$data['numero_factura']);
        }
    }
    
    $newRes = $res->withJson($regs);
    return $newRes;
});

$app->get('/cancelacion/exceedQuota/{quota}', function(Request $req, Response $res) {
    try {
        
        $pago   = new pago();
        $regs   = [];
        $quota  = $req->getAttribute("quota");
        $count  = 0;
        $result = $pago->listarPropietariosCuotaExcedida($quota);
        
        if ($result['suceed'] && $result['stats']['affected_rows']>0) {

            foreach ($result['data'] as $data) {
                
                $list = $pago->listarCancelacionDeGastos($data['id_inmueble'],$data['id_apto']);
                
                if ($list['suceed'] && $list['stats']['affected_rows'] > 0) {   
                    
                    $item    = [];
                    //$reg_exe = $list['stats']['affected_rows'] - $quota;
                    for( $i = $quota; $i < $list['stats']['affected_rows']; $i++ ) {
                        $item[] = $list['data'][$i]['id'];
                        $delete = $pago->eliminarCancelacionDeGastos($list['data'][$i]['id']);
                        if ($delete['suceed']) {
                           $count = $count + $delete['stats']['affected_rows'];
                        }
                        $count = $count + 1;
                    }
                    $regs['items'][] = Array('inmueble'=>$data['id_inmueble'],'apto'=>$data['id_apto'],'items'=>$item);
                }
            }

        }
        $regs['suceed']         = true;
        $regs['affected_rows']  = $count;
        $regs['quota']          = $quota;
        $newRes                 = $res->withJson($regs);
        return $newRes;

    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

// chequea las cancelaciones de gastos que faltan por publicar
$app->get('/cancelacion/post', function(Request $req, Response $res) {
    try {
        $pago = new pago();
        $recibos = [];
        $result = $pago->listarCancelacionDeGastosConNumeroFatura();
        
        if ($result['suceed'] && count($result['data'])>0) {
            foreach ($result['data'] as $factura) {
                $filename = '../../cancelacion.gastos/'.$factura['numero_factura'].'.pdf';
                if (!file_exists($filename)) {
                    $recibo = $factura;
                    unset($recibo['fecha_movimiento'], $recibo['descripcion'], $recibo['monto'], $recibo['id']);
                    $recibos[] = $recibo;
                }
            }
        }
        $newRes = $res->withJson($recibos);
        return $newRes;
    } catch (\Throwable $th) {
        return anError($th, $res);
    }
});

$app->post('/cancelacion/strToPDF', function(Request $req, Response $res) {

    try {
        
        $data = json_decode($req->getBody(),true);
        //Decode pdf content
        $pdf_decoded = base64_decode($data['base64']);
        //Write data back to pdf file
        $pdf = fopen('../../cancelacion.gastos/'.$data['filename'],'w');
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