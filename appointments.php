<?php
session_start();

require_once 'config.php';



if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_appointment'])) {
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, doctor_name, appointment_date, reason, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $_POST['doctor_name'], $_POST['appointment_date'], $_POST['reason'], $_POST['notes']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Rendez-vous ajouté avec succès!";
        } else {
            $_SESSION['error'] = "Erreur: " . $conn->error;
        }
        header("Location: appointments.php");
        exit();
    }
    elseif (isset($_POST['delete_appointment'])) {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $_POST['id'], $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Rendez-vous supprimé!";
        } else {
            $_SESSION['error'] = "Erreur: " . $conn->error;
        }
        header("Location: appointments.php");
        exit();
    }
    elseif (isset($_POST['update_appointment'])) {
        $stmt = $conn->prepare("UPDATE appointments SET doctor_name=?, appointment_date=?, reason=?, notes=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssssii", $_POST['doctor_name'], $_POST['appointment_date'], $_POST['reason'], $_POST['notes'], $_POST['id'], $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Rendez-vous modifié!";
        } else {
            $_SESSION['error'] = "Erreur: " . $conn->error;
        }
        header("Location: appointments.php");
        exit();
    }
}

// Récupération des rendez-vous
$appointments = [];
$stmt = $conn->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
$conn->close();

// Conversion des données pour JavaScript
$appointments_js = json_encode($appointments);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Rendez-vous | MédiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="rendez.css">
</head>
<body>
    <!-- Header Premium -->
    <header class="header">
        <div class="header-content">
            <a href="#" class="logo">
                <i class="fas fa-heartbeat"></i>
                <span>MediAssist</span>
                
            </a>
            
            <nav class="user-nav">
                
                
                <a href="dashboard.php">Accueil</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </nav>
        </div>
        
        <h1 class="header-title">Gestion des Rendez-vous Médicaux</h1>
        <p class="header-subtitle">Planifiez et gérez vos consultations en toute simplicité</p>
        
        <div class="wave"></div>
    </header>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['success']; unset($_SESSION['success']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $_SESSION['error']; unset($_SESSION['error']) ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h2><i class="fas fa-calendar-plus"></i> Formulaire</h2>
            <form id="appointmentForm" method="POST">
                <input type="hidden" id="appointment_id" name="id">
                
                <div class="form-group">
                    <label for="doctor_name">Médecin</label>
                    <input type="text" id="doctor_name" name="doctor_name" required>
                </div>
                
                <div class="form-group">
                    <label for="appointment_date">Date/Heure</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" required>
                </div>
                
                <div class="form-group">
                    <label for="reason">Motif</label>
                    <select id="reason" name="reason" required>
                        <option value="Consultation">Consultation</option>
                        <option value="Urgence">Urgence</option>
                        <option value="Suivi">Suivi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="add_appointment" id="submitBtn" class="btn">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <button type="button" id="cancelBtn" class="btn cancel">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            </form>
        </div>

        <div class="calendar-container">
            <h2><i class="fas fa-calendar-alt"></i> Calendrier</h2>
            <div id="calendar"></div>
        </div>

        <div class="list-container">
            <h2><i class="fas fa-list"></i> Rendez-vous</h2>
            <div id="appointmentsList">
                <?php foreach ($appointments as $app): ?>
                    <div class="appointment-card" data-id="<?= $app['id'] ?>">
                        <div class="card-header">
                            <h3><?= htmlspecialchars($app['doctor_name']) ?></h3>
                            <span class="date"><?= date('d/m/Y H:i', strtotime($app['appointment_date'])) ?></span>
                        </div>
                        <p><strong>Motif:</strong> <?= htmlspecialchars($app['reason']) ?></p>
                        <?php if (!empty($app['notes'])): ?>
                            <p><strong>Notes:</strong> <?= htmlspecialchars($app['notes']) ?></p>
                        <?php endif; ?>
                        <div class="card-actions">
                            <button class="btn-edit" data-id="<?= $app['id'] ?>">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-delete" data-id="<?= $app['id'] ?>">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Données des rendez-vous
        const appointments = <?= $appointments_js ?>;

        // Initialisation FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration du calendrier
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'fr',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Aujourd\'hui',
                    month: 'Mois',
                    week: 'Semaine',
                    day: 'Jour'
                },
                events: appointments.map(app => ({
                    id: app.id,
                    title: app.doctor_name + ' - ' + app.reason,
                    start: app.appointment_date,
                    extendedProps: {
                        notes: app.notes || ''
                    },
                    backgroundColor: app.reason === 'Urgence' ? '#f72585' : 
                                   app.reason === 'Consultation' ? '#5d8bf4' : '#4cc9f0',
                    borderColor: 'transparent'
                })),
                eventClick: function(info) {
                    const event = info.event;
                    const notes = event.extendedProps.notes || 'Aucune note';
                    const dateOptions = { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    
                    Swal.fire({
                        title: event.title,
                        html: `
                            <div class="swal-custom-content">
                                <p><i class="fas fa-clock"></i> <strong>Date:</strong> ${event.start.toLocaleDateString('fr-FR', dateOptions)}</p>
                                <p><i class="fas fa-sticky-note"></i> <strong>Notes:</strong> ${notes}</p>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonColor: '#5d8bf4',
                        background: 'white',
                        backdrop: `
                            rgba(0,0,0,0.4)
                            url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%235d8bf4' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E")
                            left top
                            repeat
                        `,
                        customClass: {
                            popup: 'swal-custom-popup'
                        }
                    });
                },
                eventMouseEnter: function(info) {
                    info.el.style.transform = 'scale(1.05)';
                    info.el.style.zIndex = '100';
                    info.el.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.2)';
                },
                eventMouseLeave: function(info) {
                    info.el.style.transform = 'scale(1)';
                    info.el.style.zIndex = '';
                    info.el.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.1)';
                }
            });
            calendar.render();

            // Gestion des boutons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const appointment = appointments.find(a => a.id == id);
                    
                    if (appointment) {
                        document.getElementById('appointment_id').value = id;
                        document.getElementById('doctor_name').value = appointment.doctor_name;
                        
                        // Ajustement du fuseau horaire
                        const date = new Date(appointment.appointment_date);
                        const localDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
                        document.getElementById('appointment_date').value = localDate.toISOString().slice(0, 16);
                        
                        document.getElementById('reason').value = appointment.reason;
                        document.getElementById('notes').value = appointment.notes || '';
                        
                        // Changement du formulaire en mode édition
                        document.getElementById('submitBtn').name = 'update_appointment';
                        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Mettre à jour';
                        document.getElementById('cancelBtn').style.display = 'inline-block';
                    }
                });
            });

            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Confirmer la suppression',
                        text: "Voulez-vous vraiment supprimer ce rendez-vous ?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#5d8bf4',
                        cancelButtonColor: '#f72585',
                        confirmButtonText: 'Oui, supprimer',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData();
                            formData.append('delete_appointment', '1');
                            formData.append('id', this.getAttribute('data-id'));
                            
                            fetch('appointments.php', {
                                method: 'POST',
                                body: formData
                            }).then(response => {
                                if (response.ok) {
                                    window.location.reload();
                                }
                            });
                        }
                    });
                });
            });

            // Annuler l'édition
            document.getElementById('cancelBtn').addEventListener('click', function() {
                document.getElementById('appointmentForm').reset();
                document.getElementById('submitBtn').name = 'add_appointment';
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Enregistrer';
                this.style.display = 'none';
            });

            // Effet  pour les boutons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        // Style dynamique pour SweetAlert2
        const style = document.createElement('style');
        style.textContent = `
            .swal-custom-popup {
                border-radius: 12px !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
            }
            .swal-custom-content {
                text-align: left;
            }
            .swal-custom-content i {
                margin-right: 10px;
                color: #5d8bf4;
            }
            .ripple {
                position: absolute;
                background: rgba(255, 255, 255, 0.4);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 600ms linear;
                pointer-events: none;
            }
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>