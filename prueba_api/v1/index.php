<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: text/html; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"'); 


require_once '../include/DbHandler.php'; 

require '../libs/Slim/Slim.php'; 
\Slim\Slim::registerAutoloader(); 
$app = new \Slim\Slim();


/* Usando GET para consultar los usuarios */

$app->get('/usuario', function() use ($app){
    verifyRequiredParams(array('dni'));
    $dni = $app->request->get('dni');
    validar_dni($dni);
   

    $response = array();
    $db = new DbHandler();
    $usuarios = $db->getUsuarios($dni);
    if(count($usuarios) == 0){
        $response["error"] = true;
        $response["message"] = "No existe un usuario con este dni. Por favor intenta nuevamente.";
        
    }else{
        $response["error"] = false;
        $response["message"] = "Usuarios con dni: " . $dni; 
        $response["usuarios"] = $usuarios;
    }
    
   

    echoResponse(200, $response);
});
/* Usando GET para consultar las simulaciones del usuario */

$app->get('/simulacion_usuario', function() use ($app){
    verifyRequiredParams(array('dni'));
    $dni = $app->request->get('dni');
    validar_dni($dni);
   

    $response = array();
    $db = new DbHandler();
    $usuarios = $db->getUsuarios($dni);
    if(count($usuarios) == 0){
        $response["error"] = true;
        $response["message"] = "No existe un usuario con este dni. Por favor intenta nuevamente.";
        
    }else{
        $simulaciones = $db->getsimulaciones($dni);
        $response["error"] = false;
        $response["message"] = "simulaciones del usuario con dni: " . $dni;
        $response["simulaciones"] = $simulaciones;
    }
   

    echoResponse(200, $response);
});
/* Usando POST para crear un usuario */

$app->post('/crear_usuario',  function() use ($app) {
    
    verifyRequiredParams(array('nombre', 'apellido1', 'apellido2', 'dni', 'email', 'capital'));
    $response = array();
    //capturamos los parametros recibidos y los almacxenamos como un nuevo array
    $param['nombre']  = $app->request->get('nombre');
    $param['apellido1'] = $app->request->get('apellido1');
    $param['apellido2']  = $app->request->get('apellido2');
    $param['dni']  = $app->request->get('dni');
    $param['email']  = $app->request->get('email');
    $param['capital']  = $app->request->get('capital');
    
    validar_dni($param['dni']);
    
    $db = new DbHandler();
    $usuarios = $db->getUsuarios($param['dni']);
    // echo json_encode(count($usuarios));die;
    if(count($usuarios) > 0){
        $response["error"] = true;
        $response["message"] = "Ya existe un usuario con este dni. Por favor intenta nuevamente.";
        
    }else{
        $db->crear_usuario($param);

        if ( is_array($param) ) {
            $response["error"] = false;
            $response["message"] = "Usurio creado satisfactoriamente!";
            $response["parametros"] = $param;
        } else {
            $response["error"] = true;
            $response["message"] = "Error al crear usuario. Por favor intenta nuevamente.";
        }
    }
    
    echoResponse(201, $response);
});

/* Usando POST para modificar un usuario */

$app->post('/modificar_usuario',  function() use ($app) {
   
    verifyRequiredParams(array('nombre', 'apellido1', 'apellido2', 'dni', 'email', 'capital'));
    $response = array();
    //capturamos los parametros recibidos y los almacxenamos como un nuevo array
    $param['nombre']  = $app->request->get('nombre');
    $param['apellido1'] = $app->request->get('apellido1');
    $param['apellido2']  = $app->request->get('apellido2');
    $param['dni']  = $app->request->get('dni');
    $param['email']  = $app->request->get('email');
    $param['capital']  = $app->request->get('capital');
    
    validar_dni($param['dni']);
    $db = new DbHandler();
    $usuarios = $db->getUsuarios($param['dni']);
    // echo json_encode(count($usuarios));die;
    if(count($usuarios) == 0){
        $response["error"] = true;
        $response["message"] = "No existe un usuario con este dni. Por favor intenta nuevamente.";
        
    }else{
        $usuarios = $db->updateUsuario($param);
        $response["error"] = false;
        $response["message"] = "Usuario modificado con dni: " . $param['dni'];
    }
    

    echoResponse(200, $response);
});

/* Usando POST para crear una simulacion de hipoteca */

$app->post('/simulacion_hipoteca',  function() use ($app) {
    
    verifyRequiredParams(array('dni', 'tae', 'plazo_amortizacion'));
    $response = array();
    //capturamos los parametros recibidos y los almacenamos como un nuevo array
    $param['dni']  = $app->request->get('dni');
    $param['tae'] = $app->request->get('tae');
    $param['plazo_amortizacion']  = $app->request->get('plazo_amortizacion');
    
    validar_dni($param['dni']);
    
    $db = new DbHandler();
    $usuarios = $db->getUsuarios($param['dni']);

    if(count($usuarios) == 0){
        $response["error"] = true;
        $response["message"] = "No existe un usuario con este dni. Por favor intenta nuevamente.";
        
    }else{
        $capital = $usuarios[0]['capital'];
        $tipo_interes = $param['tae']/1200;
        $meses_amortizacion = $param['plazo_amortizacion']*12;

        $cuota = $capital*$tipo_interes/(1-pow((1+$tipo_interes),-$meses_amortizacion));
        $importe_total = $cuota*$meses_amortizacion;
        $param['capital'] = $capital;
        $param['cuota'] =  $cuota;
        $param['importe_total'] =  $importe_total;
        $db->crear_simulacion($param);

        $response["error"] = false;
        $response["message"] = "La cuota para el capital: ".$capital." , el TAE: ".$param['tae'].", y el plazo de aortizacion: ".$param['plazo_amortizacion']." es de:".$cuota." € al mes y el importe total sera de: ".$importe_total."€";
        $response["parametros"] = $param;
    }
    
    echoResponse(201, $response);
});

/* Usando DELETE para eliminar un usuario */
$app->delete('/usuario', function() use ($app){
    verifyRequiredParams(array('dni'));
    $dni = $app->request->get('dni');
    validar_dni($dni);
   

    $response = array();
    $db = new DbHandler();
    $usuarios = $db->getUsuarios($dni);
    if(count($usuarios) == 1){
        $db->deleteUsuario($dni);
        $response["error"] = false;
        $response["message"] = "Usuario eliminado con dni: " . $dni; 
        
        
    }else{
        $response["error"] = true;
        $response["message"] = "No existe un usuario con este dni. Por favor intenta nuevamente.";
    }

    

    echoResponse(200, $response);
});

/* corremos la aplicación */
$app->run();

/*********************** USEFULL FUNCTIONS **************************************/

/**
 * Verificando los parametros requeridos en el metodo o endpoint
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        
        $app->stop();
    }
}
 
function validar_dni($dni) {
    // Comprobar que el DNI tiene 9 caracteres
    if (strlen($dni) != 9) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'La longitud del dni no es valido';
        echoResponse(400, $response);
        
        $app->stop();
    }
    
    // Extraer el número y la letra del DNI
    $numero = substr($dni, 0, 8);
    $letra = strtoupper(substr($dni, 8, 1));
    
    // Comprobar que el número del DNI es válido
    if (!is_numeric($numero) || $numero < 0 || $numero > 99999999) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'El numero del dni no es valido';
        echoResponse(400, $response);
        
        $app->stop();
    }
    
    // Calcular la letra del DNI correspondiente al número
    $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
    $indice_letra = $numero % 23;
    $letra_correcta = $letras[$indice_letra];
    
    // Comprobar que la letra del DNI introducida coincide con la letra correcta
    if ($letra != $letra_correcta) {

        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'La letra del dni no es valido';
        echoResponse(400, $response);
        
        $app->stop();
    }
    
    // Si hemos llegado aquí, el DNI es válido
    return true;
  }
  
 
/**
 * Mostrando la respuesta en formato json al cliente o navegador
 */
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
}

?>