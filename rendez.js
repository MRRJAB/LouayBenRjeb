
document.addEventListener('DOMContentLoaded', function() {
    // Animation des cartes de rendez-vous avec délai
    const appointmentCards = document.querySelectorAll('.appointment-card');
    appointmentCards.forEach((card, index) => {
        card.style.setProperty('--order', index);
        card.style.opacity = '0';
    });

    // Effet de survol premium pour les boutons
    const buttons = document.querySelectorAll('.btn, .btn-edit, .btn-delete');
    buttons.forEach(button => {
        // Effet de profondeur
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.2)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
        });
        
        // Effet ripple
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

    
    function initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

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
            events: window.appointments.map(app => ({
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
            },
            dayMaxEventRows: true,
            views: {
                timeGrid: {
                    dayMaxEventRows: 4
                }
            }
        });
        
        calendar.render();
    }

    // Initialisation après le chargement de FullCalendar
    if (window.FullCalendar) {
        initCalendar();
    } else {
        document.addEventListener('fullcalendar:loaded', initCalendar);
    }

    // Effet de chargement initial
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 200);
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
`;
document.head.appendChild(style);
