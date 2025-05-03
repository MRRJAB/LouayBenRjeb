<?php
session_start();
require_once 'config.php';



if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$contacts = [];
$error = '';
$success = '';

// Récupération des contacts
$stmt = $conn->prepare("SELECT * FROM emergencies WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$contacts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ajout de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if (!empty($name) && !empty($phone)) {
        $stmt = $conn->prepare("INSERT INTO emergencies (user_id, name, phone_number) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $name, $phone);
        
        if ($stmt->execute()) {
            $success = "Contact ajouté avec succès";
            header("Refresh:1; url=contacts.php");
        } else {
            $error = "Erreur lors de l'ajout";
        }
        $stmt->close();
    } else {
        $error = "Tous les champs sont requis";
    }
}

// Suppression de contact
if (isset($_GET['delete'])) {
    $contact_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM emergencies WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $contact_id, $user_id);
    
    if ($stmt->execute()) {
        $success = "Contact supprimé avec succès";
        header("Refresh:1; url=contacts.php");
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts Urgence | MediAssist</title>
    <link rel="stylesheet" href="contacts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
                <span>MediAssist</span>
            </div>
            <nav>
                <a href="dashboard.php"><i class="fas fa-home"></i> Tableau de bord</a>
                <a href="contacts.php" class="active"><i class="fas fa-address-book"></i> Contacts Urgence</a>
                
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </nav>
        </aside>

        <main class="main-content">
            <header>
                <h1><i class="fas fa-address-book"></i> Contacts d'Urgence</h1>
                <p>Gérez vos contacts en cas d'urgence médicale</p>
            </header>

            <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <div class="content-grid">
                <section class="form-section">
                    <h2><i class="fas fa-user-plus"></i> Ajouter un contact</h2>
                    <form id="contactForm" method="POST">
                        <div class="form-group">
                            <label for="name">Nom complet</label>
                            <input type="text" id="name" name="name" placeholder="Jean Dupont" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" placeholder="+216 12 34 56 78" required>
                        </div>
                        <button type="submit" name="add_contact" class="btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </form>
                </section>

                <section class="contacts-section">
                    <h2><i class="fas fa-list"></i> Mes contacts (<?= count($contacts) ?>)</h2>
                    <div class="contacts-list">
                        <?php if (empty($contacts)): ?>
                            <div class="empty-state">
                                <i class="fas fa-address-book"></i>
                                <p>Aucun contact enregistré</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($contacts as $contact): ?>
                                <div class="contact-card" data-id="<?= $contact['id'] ?>">
                                    <div class="contact-info">
                                        <h3><?= htmlspecialchars($contact['name']) ?></h3>
                                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($contact['phone_number']) ?></p>
                                    </div>
                                    <div class="contact-actions">
                                        <a href="tel:<?= $contact['phone_number'] ?>" class="btn-call">
                                            <i class="fas fa-phone-alt"></i> Appeler
                                        </a>
                                        <a href="contacts.php?delete=<?= $contact['id'] ?>" class="btn-delete">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="contacts.js"></script>
</body>
</html>