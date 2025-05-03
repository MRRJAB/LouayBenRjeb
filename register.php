<?php
session_start();
require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Vérifier si l'utilisateur existe déjà
    $check = $conn->query("SELECT id FROM users WHERE username='$username' OR email='$email'");
    
    if ($check->num_rows > 0) {
        $error = "Nom d'utilisateur ou email déjà utilisé";
    } else {
        // Insérer le nouvel utilisateur
        if ($conn->query("INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')")) {
            $_SESSION['register_success'] = true;
            header('Location: login.php');
            exit();
        } else {
            $error = "Erreur d'inscription: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Système Médical</title>
    <link rel="stylesheet" href="auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="particles" id="particles-js"></div>
        
        <div class="card">
            <div class="card-left">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MediCare</span>
                </div>
                <h1>Commencez</h1>
                <p>Créez votre compte pour bénéficier de toutes les fonctionnalités de suivi médical.</p>
                
                <div class="animation-container">
                    <div class="medical-animation">
                        <i class="fas fa-pills"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-stethoscope"></i>
                    </div>
                </div>
            </div>
            
            <div class="card-right">
                <h2>Inscription</h2>
                <p class="subtitle">Remplissez le formulaire pour créer un compte</p>
                
                <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $error ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            <span>Nom d'utilisateur</span>
                        </label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            <span>Adresse email</span>
                        </label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            <span>Mot de passe</span>
                        </label>
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="password-strength">
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                            <span class="strength-text">Force du mot de passe</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password">
                            <i class="fas fa-lock"></i>
                            <span>Confirmer le mot de passe</span>
                        </label>
                        <input type="password" id="confirm-password" name="confirm-password" required>
                    </div>
                    
                    <div class="form-options">
                        <label class="terms">
                            <input type="checkbox" name="terms" required>
                            <span class="checkmark"></span>
                            J'accepte les <a href="#">conditions d'utilisation</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        <span>S'inscrire</span>
                        <i class="fas fa-user-plus"></i>
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="auth.js"></script>
</body>
</html>