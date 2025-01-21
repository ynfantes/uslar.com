<?php

/**
 * Clase que mantiene la tabla propietario
 *
 * @autor   Edgar Messia
 * @static  
 * @package     Valoriza2.Framework
 * @subpackage	FileSystem
 * @since	1.0
 */

class propietario extends db implements crud  {

    const tabla = "propietarios";

    public function actualizar($id, $data) {
        
        return db::update(self::tabla, $data, Array("id"=>$id));
    }

    public function borrar($id) {
        return db::delete(self::tabla, Array("id"=>$id));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }

    public function insertar($data) {
        return db::insert(self::tabla,$data);
    }

    public function listar() {
        return db::select("*", self::tabla);
    }

    public function ver($id) {
        return db::select("*",self::tabla,Array("id"=>$id));
    }
    
    public function cambioDeClave($id,$clave) {
        return db::update(self::tabla, Array("clave"=>$clave,"id"=>$id,"cambio_clave"=>1));
    }
    
    public function login($apto, $password) {
        
        if ($apto!="" && $password!="") {
            
            
            $result = db::select("*",self::tabla,Array("clave"=>"'".$password."'"));
            
            if ($result['suceed'] == 'true' && count($result['data']) > 0) {
                
                $consulta = "select * from propiedades where cedula in (SELECT cedula FROM `propietarios` where clave='$password' ) and apto='$apto'";
                $propiedades = db::query($consulta);
                
                if ($propiedades['suceed'] == 'true' && count($propiedades['data'])>0) {
                    $res = db::select("*","junta_condominio",Array("cedula"=>$result['data'][0]['cedula']));
                    
                    $junta_condominio = '';
                    if ($res['suceed'] && count($res['data'])> 0) {
                        $junta_condominio = $res['data'][0]['id_inmueble'];
                    }
                    
                    $consulta = "select * from propietarios where clave='$password' and cedula in (select cedula from propiedades where apto='$apto')";
                    $propietario = db::query($consulta);
                   
                    if ($propietario['suceed'] == 'true' && count($propietario['data']) > 0) {
                        // registramos la sesion del usuario
                        $sesion = $this->generarIdInicioSesion($result['data'][0]['cedula']);
                        session_start();
                        if ($sesion['suceed']) {
                            $_SESSION['id_sesion'] = $sesion['insert_id'];
                        }
                        $_SESSION['usuario']    = $propietario['data'][0];
                        $_SESSION['junta']      = $junta_condominio;
                        $_SESSION['status']     = 'logueado';

                        header("location:" . URL_SISTEMA );
                        return $result;

                    } else {
                        $result['suceed'] = false;
                        $result['error'] = "Contraseña inválida.";
                        return $result;
                    }   
                } else {
                        $result['suceed'] = false;
                        $result['error'] = "Código de apartamento incorrecto.";
                        return $result;
                }
            } else {
                $result['suceed'] = false;
                $result['error'] = "La contraseña que introdujo es incorrecta.";
                return $result;
            }
        } else {
            $result['suceed'] = false;
            $result['error'] = "Código apartamento y/o password requerído.";
            return $result;
        }
    }
    
    public function generarIdInicioSesion($cedula) {
        $sql = "insert into sesion(cedula,inicio,fin) values(".$cedula.",now(),now())";
        return db::exec_query($sql);
    }
    
    public function recuperarContraSena($email) {
        if ($email!="") {
            $result = db::select("*",self::tabla,Array("email"=>"'".$email."'"));
            
            if ($result['suceed'] && count($result['data']) > 0) {
                
                if (!$result['data'][0]['clave']=='') {
                
                    if ($result['data'][0]['email']!='') {
                        $r = db::select('*','propiedades',array('cedula'=>$result['data'][0]['cedula']));
                        $apto='';
                        if ($r['suceed'] && count($r['data']) > 0) {
                            $apto = $r['data'][0]['apto'];
                        }
                        $template = 'enlinea/plantillas/clave-servicio.html';
                        if (file_exists($template)) {
                            $contenido = file_get_contents($template);
                            // se envia el email de confirmación
                            $mail = new mailto();
                            foreach ($result['data'][0] as $key => $value) {
                                $contenido = str_replace("[".$key."]", $value, $contenido);
                            }
                            $contenido = str_replace('[apto]', $apto, $contenido);
                            $r = $mail->enviar_email("Credenciales de acceso", $contenido, "", 
                                    $result['data'][0]['email'],
                                    $result['data'][0]['nombre']);
                            
                            if ($r=="") {
                                $result['suceed'] = true;
                                $result['error'] = "Clave enviada al email: ".$result['data'][0]['email'];
                            } else {
                                $result['suceed']=false;
                                $result['error']="No se puedo enviar el correo electrónico.
                                    Póngase en contacto con Administradora Halcón";
                            }
                        } else {
                            $result['suceed']=false;
                            $result['error'] = "No se puedo enviar la información, póngase en contacto"
                                    . "con la administradora.";
                        }  

                    } else {
                        $result['suceed']=false;
                        $result['error']="No tenemos registrado un email a donde enviarle su contraseña.
                            Por favor póngase en contacto con nosotros para actualizar su información de
                            contacto.";
                    }
                } else {
                    $result=false;
                    $result['error']="Por favor póngase en contacto con la administradora para darle acceso al servicio.";
                }
            } else {
                $result=false;
                $result['error']="<strong>Email no registrado.</strong> Si considera
                    que es un error, póngase en contacto con Administradora Dinastía";
            }
        } else {
            $result['suceed']=false;
            $result['error'] = "Debe introducir su email.";
            
        }
        return $result;
    }
   
    public static function esPropietarioLogueado() {
        session_start();
        if (!isset($_SESSION['status']) || $_SESSION['status'] != 'logueado' || !isset($_SESSION['usuario'])) {
            header("location:" . ROOT );
            die();
        }
    }
    
    public function logout() {
        session_start();
        if (isset($_SESSION['id_sesion'])) {
            $this->exec_query("update sesion set fin=now() where id=".$_SESSION['id_sesion']);
        }
        if (isset($_SESSION['status'])) {
            unset($_SESSION['status']);
            unset($_SESSION['usuario']);
            session_unset();
            session_destroy();

            if (isset($_COOKIE[session_name()]))
                setcookie(session_name(), '', time() - 1000);
            header("location:" . ROOT);
        }
    }
    
    public static function listarPropietariosClavesActualizadas() {
        $query = "select propiedades.id_inmueble, propiedades.apto , propietarios.id, propietarios.clave 
            from propietarios join propiedades
            on propietarios.cedula = propiedades.cedula
            where propietarios.cambio_clave=1";
        return db::query($query);
    }
    
    public function obtenerPropietariosActualizados() {
        $query = "SELECT p . * , pr.id_inmueble, pr.apto
            FROM propietarios p
            JOIN propiedades pr ON p.cedula = pr.cedula
            WHERE p.modificado = 1 Order By pr.id_inmueble ASC";
        
        return $this->dame_query($query);
    }
    
    public function listarPropietariosConEmail($id = null) {
        $query = "select * from ".self::tabla." where email !=''";
        if($id != null) {
            $query.= " and cedula=".$id;
        }
        return $this->dame_query($query);
    }
    
    public static function obtenerInfoUltimasSesiones($cedula, $sesion_actual) {
        $consulta = "SELECT id, inicio, fin, timediff(fin , inicio) as duracion 
            FROM sesion where id <".$sesion_actual ." and cedula=" .$cedula. " order by id desc limit 0,5";
        return db::query($consulta);
    }

    public function envioMasivoEmail($asunto,$template, $id = null) {
        $propieatarios = $this->listarPropietariosConEmail($id);
        
        if ($propieatarios['suceed'] && count($propieatarios['data'])>0) {
            // cargamos el template
            if (file_exists($template)) {
                $contenido_original = file_get_contents($template);
                
                if ($contenido_original=='') {
                    echo "No se puedo cargar el contenido de ".$template;
                    die();
                }
                // enviamos el email a los destinatarios
                $resultado='';
                $n=1;
                
                foreach ($propieatarios['data'] as $propietario) {
                    $mail = new mailto();
                    $contenido = $contenido_original;
                    // hacemos la personalizacion del contenido
                    foreach ($propietario as $key => $value) {
                        $contenido = str_replace("[".$key."]", $value, $contenido);
                    }
                    
                    // aquí enviamos el email
                    $destinatario = $propietario['email'];
                    
                    $r = $mail->enviar_email($asunto, $contenido, '', $destinatario, $propietario['nombre']);
                    
                    if ($r=='') {
                        $resultado.= $n.".- Mensaje enviado a ".$destinatario." Ok!\n";
                    } else {
                        $resultado.= $n.".- Mensaje enviado a ".$destinatario." Falló\n";
                    }
                    $n++;
                }
                echo nl2br($resultado);
            } else {
                echo $template." no existe";
            }
        }
    }

    public function insertarActualizar($data){
        return db::insertUpdate(self::tabla, $data,$data);
    }
}
