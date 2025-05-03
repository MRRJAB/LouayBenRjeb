<?php


$host = "localhost";
$user = "root";
$password = "";
$database = "login_system";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour sécuriser les données
function secureInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}
?>