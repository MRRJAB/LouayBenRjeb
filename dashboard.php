<?php
session_start();
require 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les données
$medications = $conn->query("SELECT * FROM medications WHERE user_id = $user_id ORDER BY start_date DESC LIMIT 5");
$appointments = $conn->query("SELECT * FROM appointments WHERE user_id = $user_id AND appointment_date >= NOW() ORDER BY appointment_date ASC LIMIT 5");
$prescriptions = $conn->query("SELECT * FROM ordonnances WHERE user_id = $user_id ORDER BY  date_ordonnances DESC LIMIT 5");
$emergencies = $conn->query("SELECT * FROM emergencies WHERE user_id = $user_id ORDER BY id DESC LIMIT 5");

$stats = $conn->query("SELECT 
    (SELECT COUNT(*) FROM medications WHERE user_id = $user_id) as med_count,
    (SELECT COUNT(*) FROM appointments WHERE user_id = $user_id AND appointment_date >= NOW()) as upcoming_appointments,
    (SELECT COUNT(*) FROM ordonnances WHERE user_id = $user_id) as prescription_count
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord | MediCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MediAssist</span>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="#" class="active">
                    <i class="fas fa-home"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="medicaments.php">
                    <i class="fas fa-pills"></i>
                    <span>Médicaments</span>
                </a>
                <a href="appointments.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Rendez-vous</span>
                </a>
                <a href="ordonnances.php">
                    <i class="fas fa-file-prescription"></i>
                    <span>Ordonnances</span>
                </a>
                
                <a href="contacts.php">
                    <i class="fas fa-ambulance"></i>
                    <span>Urgences</span>
                </a>
            </div>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                
                <div class="user-profile">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="avatar">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=4e73df&color=fff" alt="User">
                        <span><?= $_SESSION['username'] ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card bg-primary">
                    <div class="stat-icon">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['med_count'] ?></h3>
                        <p>Médicaments</p>
                    </div>
                </div>
                
                <div class="stat-card bg-success">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['upcoming_appointments'] ?></h3>
                        <p>Rendez-vous à venir</p>
                    </div>
                </div>
                
                <div class="stat-card bg-warning">
                    <div class="stat-icon">
                        <i class="fas fa-file-prescription"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['prescription_count'] ?></h3>
                        <p>Ordonnances</p>
                    </div>
                </div>
                
                <div class="stat-card bg-info">
                    <div class="stat-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="stat-info">
                        <h3>100%</h3>
                        <p>Suivi médical</p>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Médicaments -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-pills"></i> Mes Médicaments</h3>
                        <a href="medicaments.php" class="btn-more">Voir tout <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card-body">
                        <?php if ($medications->num_rows > 0): ?>
                            <div class="medication-list">
                                <?php while ($med = $medications->fetch_assoc()): ?>
                                <div class="medication-item">
                                    <div class="med-icon">
                                        <i class="fas fa-capsules"></i>
                                    </div>
                                    <div class="med-info">
                                        <h4><?= htmlspecialchars($med['name']) ?></h4>
                                        <p><?= htmlspecialchars($med['dosage']) ?> - <?= htmlspecialchars($med['frequency']) ?></p>
                                        <small>Prochaine prise: <?= date('H:i', strtotime('+1 hour')) ?></small>
                                    </div>
                                    <div class="med-action">
                                        <button class="btn-taken"><i class="fas fa-check"></i> Pris</button>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-pills"></i>
                                <p>Aucun médicament enregistré</p>
                                <a href="medicaments.php" class="btn-add">Ajouter un médicament</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Rendez-vous -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-check"></i> Prochains Rendez-vous</h3>
                        <a href="appointments.php" class="btn-more">Voir tout <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card-body">
                        <?php if ($appointments->num_rows > 0): ?>
                            <div class="appointment-list">
                                <?php while ($app = $appointments->fetch_assoc()): ?>
                                <div class="appointment-item">
                                    <div class="appointment-date">
                                        <div class="date-badge">
                                            <span class="day"><?= date('d', strtotime($app['appointment_date'])) ?></span>
                                            <span class="month"><?= strtoupper(date('M', strtotime($app['appointment_date']))) ?></span>
                                        </div>
                                    </div>
                                    <div class="appointment-info">
                                        <h4>Dr. <?= htmlspecialchars($app['doctor_name']) ?></h4>
                                        <p><?= htmlspecialchars($app['reason']) ?></p>
                                        <small><?= date('H:i', strtotime($app['appointment_date'])) ?> - <?= date('H:i', strtotime($app['appointment_date']) + 3600) ?></small>
                                    </div>
                                    <div class="appointment-action">
                                        <button class="btn-reminder"><i class="fas fa-bell"></i> Rappel</button>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>Aucun rendez-vous programmé</p>
                                <a href="appointments.php" class="btn-add">Prendre rendez-vous</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Urgences -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-ambulance"></i> Urgences</h3>
                        <a href="contacts.php" class="btn-more">Voir tout <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card-body">
                        <?php if ($emergencies->num_rows > 0): ?>
                            <div class="emergency-list">
                                <?php while ($emg = $emergencies->fetch_assoc()): ?>
                                <div class="emergency-item">
                                    <div class="emergency-icon">
                                        <i class="fas fa-phone-alt"></i>
                                    </div>
                                    <div class="emergency-info">
                                        <h4><?= htmlspecialchars($emg['name']) ?></h4>
                                        <p><?= htmlspecialchars($emg['phone_number']) ?></p>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-ambulance"></i>
                                <p>Aucun contact d'urgence enregistré</p>
                                <a href="contacts.php" class="btn-add">Ajouter un contact</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Graphique -->
                <div class="card card-chart">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Suivi Médical</h3>
                        <div class="chart-filter">
                            <select>
                                <option>7 jours</option>
                                <option>30 jours</option>
                                <option selected>90 jours</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="medicalChart"></canvas>
                    </div>
                </div>
                
                <!-- Ordonnances -->
               
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="dashboard.js"></script>
</body>
</html>
 