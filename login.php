<?php

session_start();
require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier si c'est une demande de réinitialisation de mot de passe
    if (isset($_POST['reset_email'])) {
        $email = $conn->real_escape_string($_POST['reset_email']);
        
        // Vérifier si l'email existe
        $sql = "SELECT id FROM users WHERE email='$email' LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows === 1) {
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
                $error = "Un email de réinitialisation a été envoyé !";
            } else {
                $error = "Erreur lors de l'envoi de l'email";
            }
        } else {
            $error = "Email non trouvé";
        }
    } 
    // Sinon, c'est une tentative de connexion normale
    else {
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                if (isset($_SESSION['user_id'])) {
                    $redirect_url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/dashboard.php";
                    header("Location: ".$redirect_url);
                    exit();
                } else {
                    $error = "Erreur de session";
                }
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Nom d'utilisateur incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Système Médical</title>
    <link rel="stylesheet" href="auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- btn portf -->
    <a href="Portfolio/index.html" class="portfolio-btn">
        <i class="fas fa-user-tie"></i>
        <span>About Us </span>
    </a>

    <div class="auth-container">
        <div class="particles" id="particles-js"></div>
        
        <div class="card">
            <div class="card-left">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MediAssist</span>
                </div>
                <h1>Bienvenue</h1>
                <p>Connectez-vous pour accéder à votre espace personnel de suivi médical.</p>
                
                <div class="animation-container">
                    <div class="doctor-animation">
                        <i class="fas fa-user-md"></i>
                    </div>
                </div>
            </div>
            
            <div class="card-right">
                <h2>Connexion</h2>
                <p class="subtitle">Entrez vos identifiants pour continuer</p>
                
                <?php if ($error): ?>
                <div class="alert <?= strpos($error, 'envoyé') !== false ? 'success' : 'error' ?>">
                    <i class="fas <?= strpos($error, 'envoyé') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="auth-form" id="login-form">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            <span>Nom d'utilisateur</span>
                        </label>
                        <input type="text" id="username" name="username" required>
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
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                            Se souvenir de moi
                        </label>
                        <a href="#" id="forgot-password-link" class="forgot-password">Mot de passe oublié ?</a>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <span>Se connecter</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <!-- Formulaire caché pour la réinitialisation -->
                <form method="POST" class="auth-form" id="reset-form" style="display:none;">
                    <div class="form-group">
                        <label for="reset_email">
                            <i class="fas fa-envelope"></i>
                            <span>Votre email</span>
                        </label>
                        <input type="email" id="reset_email" name="reset_email" required>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <span>Envoyer le lien</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    
                    <div class="auth-footer">
                        <a href="#" id="back-to-login">Retour à la connexion</a>
                    </div>
                </form>
                
                <div class="auth-footer" id="register-footer">
                    <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="auth.js"></script>
    <script>
        // Gestion de l'affichage des formulaires
        document.getElementById('forgot-password-link').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('reset-form').style.display = 'block';
            document.getElementById('register-footer').style.display = 'none';
        });

        document.getElementById('back-to-login').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('reset-form').style.display = 'none';
            document.getElementById('register-footer').style.display = 'block';
        });
    </script>
</body>
</html>