document.addEventListener('DOMContentLoaded', function() {
    // Éléments principaux
    const formColumn = document.getElementById('formColumn');
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const closeFormBtn = document.getElementById('closeFormBtn');
    const form = document.getElementById('ordonnanceForm');
    const imageInput = document.getElementById('imageInput');
    const imageDataInput = document.getElementById('image_data');
    
    // Définir la date du jour par défaut
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date_ordonnance').value = today;
    
    // Gestion de l'affichage du formulaire
    function toggleForm() {
        formColumn.classList.toggle('active');
        toggleFormBtn.style.display = formColumn.classList.contains('active') ? 'none' : 'flex';
    }
    
    toggleFormBtn.addEventListener('click', toggleForm);
    closeFormBtn.addEventListener('click', toggleForm);
    
    // Gestion de l'image
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Vérifier la taille du fichier (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux (max 5MB)');
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(event) {
                // Stocker les données de l'image pour l'envoi
                imageDataInput.value = event.target.result;
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // Drag and drop pour l'image
    const uploadLabel = document.querySelector('.file-upload-label');
    
    uploadLabel.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadLabel.querySelector('.file-upload-content').style.borderColor = 'var(--primary)';
        uploadLabel.querySelector('.file-upload-content').style.backgroundColor = 'rgba(67, 97, 238, 0.1)';
    });
    
    uploadLabel.addEventListener('dragleave', () => {
        uploadLabel.querySelector('.file-upload-content').style.borderColor = '#d1d5db';
        uploadLabel.querySelector('.file-upload-content').style.backgroundColor = 'rgba(248, 249, 250, 0.5)';
    });
    
    uploadLabel.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadLabel.querySelector('.file-upload-content').style.borderColor = '#d1d5db';
        uploadLabel.querySelector('.file-upload-content').style.backgroundColor = 'rgba(248, 249, 250, 0.5)';
        
        if (e.dataTransfer.files.length) {
            imageInput.files = e.dataTransfer.files;
            const event = new Event('change');
            imageInput.dispatchEvent(event);
        }
    });
    
    // Animation des labels flottants
    const floatLabels = document.querySelectorAll('.form-floating input, .form-floating textarea');
    
    floatLabels.forEach(input => {
        // Initialiser l'état des labels
        if (input.value) {
            input.nextElementSibling.classList.add('floating');
        }
        
        input.addEventListener('focus', function() {
            this.nextElementSibling.classList.add('floating');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.nextElementSibling.classList.remove('floating');
            }
        });
    });
    
    // Confirmation de suppression
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette ordonnance ?')) {
                e.preventDefault();
            }
        });
    });
    
    // Réinitialiser le formulaire après soumission
    form.addEventListener('submit', function() {
        setTimeout(() => {
            form.reset();
            document.getElementById('date_ordonnance').value = today;
            imageDataInput.value = '';
            imageInput.value = '';
            
            floatLabels.forEach(input => {
                if (!input.value) {
                    input.nextElementSibling.classList.remove('floating');
                }
            });
        }, 500);
    });
    
    // Masquer le formulaire par défaut sur mobile
    if (window.innerWidth < 1024) {
        formColumn.classList.remove('active');
        toggleFormBtn.style.display = 'flex';
    }
    
    // Gestion du redimensionnement
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            formColumn.classList.add('active');
            toggleFormBtn.style.display = 'none';
        } else {
            formColumn.classList.remove('active');
            toggleFormBtn.style.display = 'flex';
        }
    });
});
