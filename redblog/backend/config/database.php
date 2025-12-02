<?php
// backend/config/database.php - CORREGIDO
class Database {
    private $host = "localhost";
    private $db_name = "redblog"; 
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            
            $this->conn->exec("set names utf8");
            
            // REMOVER: echo "<!-- ✅ Conexión a MySQL exitosa -->";
            
        } catch(PDOException $exception) {
            // REMOVER: echo "<!-- ❌ Error de conexión: " . $exception->getMessage() . " -->";
            error_log("Error de conexión MySQL: " . $exception->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
        
        return $this->conn;
    }
}
?>