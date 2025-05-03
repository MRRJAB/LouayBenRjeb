<?php
require 'config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $conn->real_escape_string($data['email']);

// Vérifier si l'email existe
$sql = "SELECT id FROM users WHERE email='$email' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email non trouvé']);
    exit;
}

// Générer un token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', time() + 3600); // 1 heure d'expiration

// Stocker le token en base
$user = $result->fetch_assoc();
$sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $user['id'], $token, $expires);
$stmt->execute();

// Envoyer l'email
$reset_link = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/reset_password.php?token=$token";
$subject = "Réinitialisation de votre mot de passe";
$message = "Cliquez sur ce lien pour réinitialiser votre mot de passe: $reset_link";
$headers = "From: no-reply@votresite.com";

if (mail($email, $subject, $message, $headers)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'email']);
}