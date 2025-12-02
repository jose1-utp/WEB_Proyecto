<?php
// frontend/test-conexion.php
echo "<h1>🧪 Probando Conexión a MySQL</h1>";

try {
    include_once '../backend/config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    if($db) {
        echo "<p style='color: green;'>✅ CONEXIÓN EXITOSA - MySQL está conectado</p>";
        
        // Probar consulta simple
        $query = "SELECT COUNT(*) as total FROM users";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Usuarios en la base de datos: <strong>" . $result['total'] . "</strong></p>";
        
    } else {
        echo "<p style='color: red;'>❌ ERROR - No se pudo conectar a MySQL</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ EXCEPCIÓN: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Información del Servidor:</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>MySQL Extension: " . (extension_loaded('pdo_mysql') ? '✅ Cargada' : '❌ No cargada') . "</li>";
echo "</ul>";

echo "<a href='index.php'>← Volver al Inicio</a>";
?>