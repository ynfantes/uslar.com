<?php
class usuario extends db implements crud  {
    
    const tabla = "usuarios";
    
    public function actualizar($id, $data) {
        
    }

    public function borrar($id) {
        
    }

    public function borrarTodo() {
        
    }

    public function insertar($data) {
        
    }

    public function listar() {
        
    }

    public function ver($id) {
        
    }   
    
    public function login($usuario,$password) {
        
        if ($usuario!="" && $password!="") {
        
            $result = db::select("*",self::tabla,Array("nombre"=>"'".$usuario."'"));
            
            if ($result['suceed'] == 'true' && count($result['data']) > 0) {
                //  aquí validamos los privilegios del usuario
                //$res = db::select("*","junta_condominio",Array("cedula"=>$usuario));
                
//                if ($res['suceed'] && count($res['data'])> 0) {
//                    $junta_condominio = $res['data'][0]['id_inmueble'];
//                }
                if ($result['data'][0]['clave']==$password) {
                    session_start();
                    $_SESSION['usuario'] = $result['data'][0];
                    $_SESSION['status'] = 'logueado';
                    header("location:" . URL_INTRANET . "/" . $result['data'][0]['directorio'] );
                    return $result;
                    
                } else {
                    $result['suceed'] = false;
                    $result['error'] = "Contraseña inválida";
                    return $result;
                }
            } else {
                $result['suceed'] = false;
                $result['error'] = "Usuario no registrado.";
                return $result;
            }
        } else {
            $result['suceed'] = false;
            $result['error'] = "Nombre de usuario y/o contraseña requerida.";
            return $result;
        }
    }

    public static function esUsuarioLogueado() {
        session_start();
        if (!isset($_SESSION['status']) || $_SESSION['status'] != 'logueado' || !isset($_SESSION['usuario']))
            header("location:" . ROOT . "login.php");
    }
    
    public static  function logout() {
        //session_start();
        if (isset($_SESSION['status'])) {
            unset($_SESSION['status']);
            unset($_SESSION['usuario']);
            session_unset();
            session_destroy();
            if (isset($_COOKIE[session_name()]))
                setcookie(session_name(), '', time() - 1000);
            header("location:" . ROOT . "intranet.php");
        }
    }
}
?>