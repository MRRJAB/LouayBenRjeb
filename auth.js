
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser particles.js
    if (document.getElementById('particles-js')) {
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 80,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: "#4361ee"
                },
                shape: {
                    type: "circle",
                    stroke: {
                        width: 0,
                        color: "#000000"
                    },
                    polygon: {
                        nb_sides: 5
                    }
                },
                opacity: {
                    value: 0.3,
                    random: true,
                    anim: {
                        enable: true,
                        speed: 1,
                        opacity_min: 0.1,
                        sync: false
                    }
                },
                size: {
                    value: 3,
                    random: true,
                    anim: {
                        enable: true,
                        speed: 2,
                        size_min: 0.1,
                        sync: false
                    }
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: "#4361ee",
                    opacity: 0.2,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 1,
                    direction: "none",
                    random: true,
                    straight: false,
                    out_mode: "out",
                    bounce: false,
                    attract: {
                        enable: true,
                        rotateX: 600,
                        rotateY: 1200
                    }
                }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: {
                        enable: true,
                        mode: "grab"
                    },
                    onclick: {
                        enable: true,
                        mode: "push"
                    },
                    resize: true
                },
                modes: {
                    grab: {
                        distance: 140,
                        line_linked: {
                            opacity: 0.5
                        }
                    },
                    push: {
                        particles_nb: 4
                    }
                }
            },
            retina_detect: true
        });
    }

    // la visibilité du mot de passe
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Validation du mot de passe en temps réel
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.id === 'password') {
                updatePasswordStrength(this.value);
            }
            
            if (this.id === 'confirm-password' && document.getElementById('password')) {
                const password = document.getElementById('password').value;
                if (this.value !== password && this.value.length > 0) {
                    this.style.borderColor = 'var(--danger)';
                } else {
                    this.style.borderColor = '';
                }
            }
        });
    });

    // Animation des boutons
    const buttons = document.querySelectorAll('.btn-login, .btn-register');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.querySelector('i').style.transform = 'translateX(5px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.querySelector('i').style.transform = 'translateX(0)';
        });
    });

    // Effet de focus sur les inputs
    const inputs = document.querySelectorAll('.form-group input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentNode.querySelector('label').style.color = 'var(--primary)';
            this.style.backgroundColor = 'var(--white)';
        });
        
        input.addEventListener('blur', function() {
            this.parentNode.querySelector('label').style.color = 'var(--dark)';
            if (!this.value) {
                this.style.backgroundColor = '#f8f9fa';
            }
        });
    });

    // Fonction pour calculer la force du mot de passe
    function updatePasswordStrength(password) {
        const strengthBars = document.querySelectorAll('.strength-bar');
        const strengthText = document.querySelector('.strength-text');
        
        if (!strengthBars.length) return;
        
        // Réinitialiser
        strengthBars.forEach(bar => {
            bar.style.backgroundColor = '#e9ecef';
        });
        
        let strength = 0;
        
        // Longueur minimale
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        
        // Contient des chiffres
        if (/\d/.test(password)) strength++;
        
        // Contient des caractères spéciaux
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
        
        // Mettre à jour l'affichage
        for (let i = 0; i < strengthBars.length; i++) {
            if (i < strength) {
                let color;
                if (strength <= 2) color = 'var(--danger)';
                else if (strength === 3) color = 'var(--warning)';
                else color = 'var(--success)';
                
                strengthBars[i].style.backgroundColor = color;
            }
        }
        
        // Mettre à jour le texte
        if (strengthText) {
            let text;
            if (password.length === 0) {
                text = 'Force du mot de passe';
            } else if (strength <= 2) {
                text = 'Faible';
            } else if (strength === 3) {
                text = 'Moyen';
            } else {
                text = 'Fort';
            }
            strengthText.textContent = text;
        }
    }

    // Transition entre les pages
    const links = document.querySelectorAll('a[href^=""]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== '#' && !this.classList.contains('toggle-password')) {
                e.preventDefault();
                document.querySelector('.auth-container').style.opacity = '0';
                document.querySelector('.auth-container').style.transform = 'translateY(20px)';
                setTimeout(() => {
                    window.location.href = this.getAttribute('href');
                }, 300);
            }
        });
    });
});
// Animation au survol du bouton portfolio
document.querySelector('.portfolio-btn').addEventListener('mouseover', function() {
    this.style.transform = 'scale(1.05)';
});

document.querySelector('.portfolio-btn').addEventListener('mouseout', function() {
    this.style.transform = 'scale(1)';
});
// Gestion du mot de passe oublié
document.getElementById('forgot-password-link').addEventListener('click', function() {
    document.getElementById('forgot-password-modal').style.display = 'block';
});

document.querySelector('.close-modal').addEventListener('click', function() {
    document.getElementById('forgot-password-modal').style.display = 'none';
});

// Envoi du formulaire
document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('reset-email').value;
    
    fetch('send_reset_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Un email de réinitialisation a été envoyé !');
            document.getElementById('forgot-password-modal').style.display = 'none';
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
});