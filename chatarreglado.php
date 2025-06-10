<html>
<body>
<?php
session_start(); // Inicia la sesión para acceder a variables de sesión como el usuario

// Muestra un saludo con el nombre del usuario, escapando caracteres para evitar XSS
echo "Hola " . htmlspecialchars($_SESSION['user']); 

// Verifica si la variable de sesión 'user' está definida (el usuario está autenticado)
if (!isset($_SESSION['user'])) {
    // Si no hay sesión, redirige al formulario de login
    header('Location: http://172.22.8.213/xss/login.php');
    exit; // Importante: salir del script para evitar que siga ejecutándose
} else {
?>

<!-- Formulario para enviar mensajes al chat -->
<form action="chat.php" method="post">
    <input name="message" type="text" /> <!-- Campo de texto para el mensaje -->
    <input type="submit" value="enviar" /> <!-- Botón de enviar -->
</form>

<?php
// Conexión a la base de datos a través de una clase externa
require_once('/var/www/html/xss/DBManager.php');
$con = new DBManager(); // Instancia la conexión a la base de datos

// Si se ha enviado el formulario (es decir, si hay datos POST)
if ($_POST) {
    $message = $_POST['message']; // Captura el mensaje enviado por el usuario

    // Escapa caracteres especiales para prevenir ataques XSS (por ejemplo, <, >, ", ')
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    try {
        // Inserta el mensaje en la base de datos de forma segura usando una consulta preparada
        $sql = "INSERT INTO msg(msg) VALUES (:msg)";
        $stmt = $con->getConexion()->prepare($sql); // Prepara la consulta
        $stmt->bindParam(':msg', $message); // Asocia el parámetro de forma segura
        $stmt->execute(); // Ejecuta la consulta
    } catch (PDOException $e) {
        // Muestra cualquier error de base de datos
        echo $e->getMessage();
    }

    try {
        // Recupera todos los mensajes almacenados
        $sql = "SELECT * FROM msg";
        $stmt = $con->getConexion()->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los resultados como array asociativo
    } catch (PDOException $e) {
        echo $e->getMessage(); // Muestra error si falla la consulta
    }

    // Recorre todos los mensajes y los muestra
    foreach ($row as $msg) {
        // Escapa cada mensaje antes de mostrarlo, para evitar inyección de scripts en el HTML
        echo "Mensaje: " . htmlspecialchars($msg['msg'], ENT_QUOTES, 'UTF-8') . "<br>";
    }
}
?>

</body>
</html>