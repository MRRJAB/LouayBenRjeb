<?php
session_start();

require 'config.php';



error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ajouter un médicament
if (isset($_POST['add_medication'])) {
    try {
        $required = ['name', 'dosage', 'frequency', 'start_date'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Tous les champs obligatoires doivent être remplis");
            }
        }

        $name = htmlspecialchars(trim($_POST['name']));
        $dosage = htmlspecialchars(trim($_POST['dosage']));
        $frequency = htmlspecialchars(trim($_POST['frequency']));
        $start_date = htmlspecialchars(trim($_POST['start_date']));
        $end_date = !empty($_POST['end_date']) ? htmlspecialchars(trim($_POST['end_date'])) : null;
        $notes = !empty($_POST['notes']) ? htmlspecialchars(trim($_POST['notes'])) : null;

        $stmt = $conn->prepare("INSERT INTO medications (user_id, name, dosage, frequency, start_date, end_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt === false) {
            throw new Exception("Erreur de préparation: " . $conn->error);
        }

        $bind = $stmt->bind_param("issssss", $user_id, $name, $dosage, $frequency, $start_date, $end_date, $notes);
        if ($bind === false) {
            throw new Exception("Erreur de liaison: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Erreur d'exécution: " . $stmt->error);
        }

        $_SESSION['success'] = "Médicament ajouté avec succès!";
        header("Location: medicaments.php?success=1");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: medicaments.php");
        exit();
    }
}

// Supprimer un médicament
if (isset($_GET['delete'])) {
    try {
        $med_id = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM medications WHERE id = ? AND user_id = ?");
        
        if ($stmt === false) {
            throw new Exception("Erreur de préparation: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $med_id, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur de suppression: " . $stmt->error);
        }
        
        $_SESSION['success'] = "Médicament supprimé avec succès!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
    header("Location: medicaments.php");
    exit();
}

// Récupérer les médicaments
$medications = [];
try {
    $result = $conn->query("SELECT * FROM medications WHERE user_id = $user_id ORDER BY start_date DESC");
    if ($result !== false) {
        $medications = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur de récupération: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Médicaments | MediCare</title>
    <link rel="stylesheet" href="medicaments.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="logo">MediAssist</div>
        <nav class="nav-links">
            <a href="dashboard.php">Accueil</a>
            
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <div class="dashboard-container">
        <div class="main-content">
            <h1>Gestion des Médicaments</h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class="two-columns">
                <div class="column add-medication">
                    <h2>Ajouter un médicament</h2>
                    <form method="POST">
                        <input type="text" name="name" placeholder="Nom du médicament" required>
                        <input type="text" name="dosage" placeholder="Dosage (ex: 500mg)" required>
                        <input type="text" name="frequency" placeholder="Fréquence (ex: 2 fois/jour)" required>
                        <div class="date-inputs">
                            <div>
                                <label>Date de début:</label>
                                <input type="date" name="start_date" required>
                            </div>
                            <div>
                                <label>Date de fin (optionnel):</label>
                                <input type="date" name="end_date">
                            </div>
                        </div>
                        <textarea name="notes" placeholder="Notes supplémentaires..." rows="4"></textarea>
                        <button type="submit" name="add_medication">Ajouter</button>
                    </form>
                </div>

                <div class="column medication-list">
                    <h2>Mes Médicaments</h2>
                    <input type="text" id="searchBar" onkeyup="filterMedications()" placeholder="Rechercher un médicament..." class="search-bar">

                    <?php if (!empty($medications)): ?>
                        <?php foreach ($medications as $med): ?>
                            <div class="medication-card medication-item" data-startdate="<?= htmlspecialchars($med['start_date']) ?>">
                                <div class="med-header">
                                    <h3><?= htmlspecialchars($med['name']) ?></h3>
                                    <span class="med-status <?= isMedicationActive($med['start_date'], $med['end_date']) ? 'active' : 'inactive' ?>">
                                        <?= isMedicationActive($med['start_date'], $med['end_date']) ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </div>
                                <div class="med-details">
                                    <p><i class="fas fa-prescription-bottle-alt"></i> <strong>Dosage:</strong> <?= htmlspecialchars($med['dosage']) ?></p>
                                    <p><i class="fas fa-clock"></i> <strong>Fréquence:</strong> <?= htmlspecialchars($med['frequency']) ?></p>
                                    <p><i class="fas fa-calendar-day"></i> <strong>Début:</strong> <?= date('d/m/Y', strtotime($med['start_date'])) ?></p>
                                    <?php if (!empty($med['end_date'])): ?>
                                        <p><i class="fas fa-calendar-times"></i> <strong>Fin:</strong> <?= date('d/m/Y', strtotime($med['end_date'])) ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($med['notes'])): ?>
                                    <div class="medication-note">
                                        <div class="note-header">
                                            <i class="fas fa-sticky-note"></i>
                                            <strong>Notes:</strong>
                                        </div>
                                        <p><?= nl2br(htmlspecialchars($med['notes'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="med-actions">
                                    <a href="medicaments.php?delete=<?= $med['id'] ?>" class="delete-link" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce médicament?');">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-medications">Aucun médicament enregistré.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="notification" class="hidden">🎉 Vous devez prendre un médicament aujourd'hui !</div>

    <footer>
        &copy; <?= date('Y') ?> MediCare - Tous droits réservés
    </footer>

    <script src="medicaments.js"></script>
</body>
</html>

<?php
// Fonction pour vérifier si un médicament est actif
function isMedicationActive($start_date, $end_date = null) {
    $today = date('Y-m-d');
    $start = date('Y-m-d', strtotime($start_date));
    
    if ($end_date) {
        $end = date('Y-m-d', strtotime($end_date));
        return ($today >= $start && $today <= $end);
    }
    
    return ($today >= $start);
}
?>