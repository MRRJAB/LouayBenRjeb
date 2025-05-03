document.addEventListener('DOMContentLoaded', function() {
    // Check if any medication needs to be taken today
    checkMedicationNotification();
    
    // Add floating effect to cards on hover
    const cards = document.querySelectorAll('.medication-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.animation = 'pulse 1s ease infinite';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.animation = '';
        });
    });
    
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(button => {
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
            }, 1000);
        });
    });
    
    // Add date picker enhancements
    const dateInput = document.querySelector('input[type="date"]');
    if (dateInput) {
        dateInput.addEventListener('focus', function() {
            this.type = 'date';
        });
        
        dateInput.addEventListener('blur', function() {
            if (!this.value) this.type = 'text';
        });
    }
});

function filterMedications() {
    const input = document.getElementById('searchBar');
    const filter = input.value.toUpperCase();
    const cards = document.querySelectorAll('.medication-item');
    
    cards.forEach(card => {
        const text = card.textContent || card.innerText;
        if (text.toUpperCase().indexOf(filter) > -1) {
            card.style.display = "";
            card.style.animation = "fadeIn 0.5s ease";
        } else {
            card.style.display = "none";
        }
    });
}

function checkMedicationNotification() {
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    const medications = document.querySelectorAll('.medication-item');
    
    medications.forEach(med => {
        const startDate = med.dataset.startdate;
        if (startDate === todayStr) {
            showNotification();
            med.style.animation = "pulse 2s ease infinite";
            med.style.boxShadow = "0 0 0 3px rgba(108, 92, 231, 0.3)";
        }
    });
}

function showNotification() {
    const notification = document.getElementById('notification');
    notification.classList.add('show');
    
    // Hide notification aprés 5 secondes
    setTimeout(() => {
        notification.classList.remove('show');
    }, 5000);
    
    // Allow user to click to hide
    notification.addEventListener('click', () => {
        notification.classList.remove('show');
    });
}

// Add some confetti when medication is added avec succes
if (window.location.search.includes('success')) {
    setTimeout(() => {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }, 500);
}
