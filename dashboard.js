
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le graphique
    const ctx = document.getElementById('medicalChart').getContext('2d');
    const medicalChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Visites médicales',
                data: [12, 19, 3, 5, 2, 3, 8],
                backgroundColor: 'rgba(67, 97, 238, 0.2)',
                borderColor: 'rgba(67, 97, 238, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }, {
                label: 'Médicaments pris',
                data: [5, 10, 6, 12, 8, 15, 10],
                backgroundColor: 'rgba(76, 201, 240, 0.2)',
                borderColor: 'rgba(76, 201, 240, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gestion des boutons "Pris" pour les médicaments
    document.querySelectorAll('.btn-taken').forEach(btn => {
        btn.addEventListener('click', function() {
            const medicationItem = this.closest('.medication-item');
            medicationItem.style.opacity = '0.5';
            medicationItem.style.textDecoration = 'line-through';
            
            // Animation de confirmation
            const checkmark = document.createElement('div');
            checkmark.innerHTML = '<i class="fas fa-check-circle"></i>';
            checkmark.style.position = 'absolute';
            checkmark.style.right = '20px';
            checkmark.style.fontSize = '24px';
            checkmark.style.color = '#4cc9f0';
            checkmark.style.opacity = '0';
            checkmark.style.transition = 'all 0.3s';
            
            medicationItem.style.position = 'relative';
            medicationItem.appendChild(checkmark);
            
            setTimeout(() => {
                checkmark.style.opacity = '1';
                checkmark.style.transform = 'scale(1.5)';
            }, 100);
            
            // Désactiver le bouton
            this.disabled = true;
        });
    });

    // Gestion des rappels de rendez-vous
    document.querySelectorAll('.btn-reminder').forEach(btn => {
        btn.addEventListener('click', function() {
            // Demander une confirmation
            if (confirm('Voulez-vous vraiment configurer un rappel pour ce rendez-vous ?')) {
                // Simuler l'envoi d'une notification
                const notification = document.querySelector('.notification .badge');
                if (notification) {
                    let count = parseInt(notification.textContent) || 0;
                    notification.textContent = count + 1;
                    notification.style.animation = 'pulse 0.5s';
                    
                    setTimeout(() => {
                        notification.style.animation = '';
                    }, 500);
                }
                
                // Afficher un message de succès
                alert('Un rappel a été programmé pour ce rendez-vous !');
            }
        });
    });

    // Gestion de la déconnexion
    document.querySelector('.logout-btn').addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
            window.location.href = this.href;
        }
    });

    // Animation des cartes au chargement
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `all 0.5s ease ${index * 0.1}s`;
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });

    // Ajouter une animation de pulse pour les notifications
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);
});
