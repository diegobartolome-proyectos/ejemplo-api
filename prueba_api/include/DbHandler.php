<?php

class DbHandler {
 
    private $conn;
 
    function __construct() {
        require_once dirname(__FILE__) . './DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
 
    public function getUsuarios($dni)
    {
        $sql = "SELECT * FROM usuarios WHERE dni like '".$dni."'";      
        $result = $this->conn->query($sql);
        
        $data = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)){
            array_push($data, $row);

        }
        return $data;
    }

    public function getSimulaciones($dni)
    {
        $sql = "SELECT * FROM simulaciones WHERE dni like '".$dni."'";      
        $result = $this->conn->query($sql);
        
        $data = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)){
            array_push($data, $row);

        }
        return $data;
    }

    public function crear_usuario($params)
    {
        $sql = "insert into usuarios(nombre,apellido1,apellido2,dni,email,capital) values('".$params['nombre']."','".$params['apellido1']."','".$params['apellido2']."','".$params['dni']."','".$params['email']."','".$params['capital']."');";      
        $result = $this->conn->query($sql);

    }

    public function updateUsuario($params)
    {
        $sql = "Update usuarios set nombre='".$params['nombre']."', apellido1='".$params['apellido1']."', apellido2='".$params['apellido2']."', email='".$params['email']."',capital='".$params['capital']."' where dni='".$params['dni']."';";      
        $result = $this->conn->query($sql);

    }

    public function deleteUsuario($dni)
    {
        $sql = "DELETE FROM usuarios WHERE dni like '".$dni."'";      
        $result = $this->conn->query($sql);
        
    }

    public function crear_simulacion($params)
    {
        $sql = "insert into simulaciones(dni,capital,tae,plazo_amortizacion,cuota,importe_total) values('".$params['dni']."','".$params['capital']."','".$params['tae']."','".$params['plazo_amortizacion']."','".$params['cuota']."','".$params['importe_total']."');";      
        $result = $this->conn->query($sql);

    }
}
 
