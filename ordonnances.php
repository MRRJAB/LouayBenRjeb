<?php
session_start();

require 'config.php';



if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Traitement de l'enregistrement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_patient'])) {
    $nom_patient = $_POST['nom_patient'] ?? '';
    $date_ordonnance = $_POST['date_ordonnance'] ?? date('Y-m-d');
    $nom_medecin = $_POST['nom_medecin'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $image_path = '';
    
    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validation du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image_path = $targetPath;
            } else {
                $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Erreur lors de l'upload de l'image</div>";
            }
        } else {
            $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Type de fichier non autorisé (seuls JPEG, PNG et GIF sont acceptés)</div>";
        }
    }
    
    if (empty($message)) {
        $sql = "INSERT INTO ordonnances (user_id, nom_patient, date_ordonnance, nom_medecin, image_path, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $user_id, $nom_patient, $date_ordonnance, $nom_medecin, $image_path, $notes);
        
        if ($stmt->execute()) {
            $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Ordonnance enregistrée!</div>";
        } else {
            $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Erreur d'enregistrement</div>";
        }
    }
}

// Traitement suppression
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    
    // Récupérer le chemin de l'image avant suppression
    $sql = "SELECT image_path FROM ordonnances WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row && !empty($row['image_path'])) {
        // Supprimer le fichier image
        if (file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
    }
    
    // Supprimer l'enregistrement
    $sql = "DELETE FROM ordonnances WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Ordonnance supprimée!</div>";
    } else {
        $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Erreur suppression</div>";
    }
}

// Récupération ordonnances
$ordonnances = [];
$sql = "SELECT * FROM ordonnances WHERE user_id = ? ORDER BY date_ordonnance DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $ordonnances[] = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Ordonnances | MediAssist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="ordonnances.css">
</head>
<body>
    <div class="app-container">
        
        <header class="animate__animated animate__fadeInDown">
            <h1><i class="fas fa-prescription-bottle-alt"></i> <span>Medi</span>Assist</h1>
        </header>

        <?php if ($message): ?>
            <div class="message-container animate__animated animate__fadeIn">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="two-column-layout">
            <!-- Colonne de gauche - Affichage des ordonnances -->
            <div class="ordonnances-column">
                <div class="ordonnances-header">
                    <h2><i class="fas fa-file-medical"></i> Vos Ordonnances</h2>
                    <a href="dashboard.php">Accueil</a>
                    
                </div>
                
                <div class="ordonnances-grid">
                    <?php if (empty($ordonnances)): ?>
                        <div class="empty-state animate__animated animate__fadeIn">
                            <i class="fas fa-file-medical"></i>
                            <h3>Aucune ordonnance enregistrée</h3>
                            <p>Commencez par ajouter une nouvelle ordonnance</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ordonnances as $ordonnance): ?>
                            <div class="ordonnance-card animate__animated animate__fadeInUp">
                                <?php if (!empty($ordonnance['image_path'])): ?>
                                    <div class="card-image">
                                        <img src="<?php echo htmlspecialchars($ordonnance['image_path']); ?>" alt="Ordonnance médicale">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-content">
                                    <div class="card-header">
                                        <h3><?php echo htmlspecialchars($ordonnance['nom_patient']); ?></h3>
                                        <span class="date"><?php echo date('d/m/Y', strtotime($ordonnance['date_ordonnance'])); ?></span>
                                    </div>
                                    
                                    <div class="card-body">
                                        <p class="medecin"><i class="fas fa-user-md"></i> <?php echo htmlspecialchars($ordonnance['nom_medecin']); ?></p>
                                        <?php if (!empty($ordonnance['notes'])): ?>
                                            <div class="notes">
                                                <p><?php echo htmlspecialchars($ordonnance['notes']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <form method="POST" class="delete-form">
                                            <input type="hidden" name="id" value="<?php echo $ordonnance['id']; ?>">
                                            <button type="submit" name="delete" class="btn-delete">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Colonne de droite - Formulaire -->
            <div class="form-column" id="formColumn">
                <div class="form-header">
                    <h2><i class="fas fa-edit"></i> Nouvelle Ordonnance</h2>
                    <button id="closeFormBtn" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="ordonnanceForm" method="POST" enctype="multipart/form-data">
                    <div class="form-group floating">
                        <input type="text" id="nom_patient" name="nom_patient" required placeholder=" ">
                        <label for="nom_patient"><i class="fas fa-user"></i> Nom du Patient</label>
                    </div>
                    
                    <div class="form-group floating">
                        <input type="date" id="date_ordonnance" name="date_ordonnance" required placeholder=" " value="<?php echo date('Y-m-d'); ?>">
                        <label for="date_ordonnance"><i class="fas fa-calendar-day"></i> Date</label>
                    </div>
                    
                    <div class="form-group floating">
                        <input type="text" id="nom_medecin" name="nom_medecin" required placeholder=" ">
                        <label for="nom_medecin"><i class="fas fa-user-md"></i> Médecin</label>
                    </div>
                    
                    <div class="form-group floating">
                        <textarea id="notes" name="notes" rows="3" placeholder=" "></textarea>
                        <label for="notes"><i class="fas fa-notes-medical"></i> Notes</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="file-upload-label">
                            <input type="file" id="image" name="image" accept="image/*" class="hidden-input">
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Ajouter une image d'ordonnance</span>
                                <small>Formats acceptés: JPG, PNG, GIF (max 2MB)</small>
                            </div>
                            <div id="imagePreview" class="image-preview"></div>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="ordonnances.js"></script>
</body>
</html>