document.addEventListener('DOMContentLoaded', function() {
    // Add Poppins font
    const link = document.createElement('link');
    link.href = 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap';
    link.rel = 'stylesheet';
    document.head.appendChild(link);
  
    // Mobile menu toggle
    const menuToggle = document.createElement('div');
    menuToggle.className = 'mobile-menu-toggle pulse-animation';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.body.appendChild(menuToggle);
    
    menuToggle.addEventListener('click', function() {
      document.querySelector('.sidebar').classList.toggle('active');
      this.classList.toggle('active');
    });
  
    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
      const sidebar = document.querySelector('.sidebar');
      if (!sidebar.contains(e.target) && e.target !== menuToggle) {
        sidebar.classList.remove('active');
        menuToggle.classList.remove('active');
      }
    });
  
    // Contact form validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
      contactForm.addEventListener('submit', function(e) {
        const nameInput = document.getElementById('name');
        const phoneInput = document.getElementById('phone');
        let isValid = true;
  
        // Clear previous errors
        document.querySelectorAll('.form-group').forEach(group => {
          group.classList.remove('error');
        });
  
        // Name validation
        if (nameInput.value.trim() === '') {
          nameInput.parentElement.classList.add('error');
          isValid = false;
          shakeElement(nameInput);
        }
  
        // Phone validation
        const phoneDigits = phoneInput.value.replace(/\D/g, '');
        if (phoneDigits.length < 8) {
          phoneInput.parentElement.classList.add('error');
          isValid = false;
          shakeElement(phoneInput);
        }
  
        if (!isValid) {
          e.preventDefault();
          showErrorToast('Veuillez corriger les erreurs dans le formulaire');
        }
      });
    }
  
    // Delete confirmation avec sweet alert
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.getAttribute('href');
        
        // Create custom confirmation modal
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        modal.innerHTML = `
          <div class="modal-content">
            <div class="modal-header">
              <i class="fas fa-exclamation-triangle"></i>
              <h3>Confirmer la suppression</h3>
            </div>
            <div class="modal-body">
              <p>Êtes-vous sûr de vouloir supprimer ce contact ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
              <button class="modal-btn cancel-btn">Annuler</button>
              <button class="modal-btn confirm-btn">Supprimer</button>
            </div>
          </div>
        `;
        document.body.appendChild(modal);
        
        // Add styles lel modal
        const style = document.createElement('style');
        style.textContent = `
          .custom-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            animation: fadeIn 0.3s forwards;
          }
          
          .modal-content {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(20px);
            animation: slideUp 0.3s forwards;
          }
          
          .modal-header {
            padding: 1.5rem;
            background: #fff5f5;
            color: #d63031;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ffecec;
          }
          
          .modal-header i {
            font-size: 1.8rem;
            margin-right: 1rem;
          }
          
          .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
          }
          
          .modal-body {
            padding: 1.5rem;
            color: #636e72;
          }
          
          .modal-footer {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            border-top: 1px solid #f1f1f1;
          }
          
          .modal-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
          }
          
          .cancel-btn {
            background: #f5f6fa;
            color: #636e72;
          }
          
          .cancel-btn:hover {
            background: #e0e0e0;
          }
          
          .confirm-btn {
            background: linear-gradient(135deg, #ff7675, #d63031);
            color: white;
          }
          
          .confirm-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(214, 48, 49, 0.4);
          }
          
          @keyframes fadeIn {
            to { opacity: 1; }
          }
          
          @keyframes slideUp {
            to { transform: translateY(0); }
          }
        `;
        document.head.appendChild(style);
        
        // Handle button clicks
        document.querySelector('.cancel-btn').addEventListener('click', function() {
          document.body.removeChild(modal);
          document.head.removeChild(style);
        });
        
        document.querySelector('.confirm-btn').addEventListener('click', function() {
          window.location.href = url;
        });
      });
    });
  
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
      phoneInput.addEventListener('input', function(e) {
        let phoneNumber = e.target.value.replace(/\D/g, '');
        
        // Format based on length
        if (phoneNumber.length > 2) {
          phoneNumber = phoneNumber.substring(0, 2) + ' ' + phoneNumber.substring(2);
        }
        if (phoneNumber.length > 5) {
          phoneNumber = phoneNumber.substring(0, 5) + ' ' + phoneNumber.substring(5);
        }
        if (phoneNumber.length > 8) {
          phoneNumber = phoneNumber.substring(0, 8) + ' ' + phoneNumber.substring(8);
        }
        if (phoneNumber.length > 11) {
          phoneNumber = phoneNumber.substring(0, 11) + ' ' + phoneNumber.substring(11);
        }
        
        e.target.value = phoneNumber;
      });
    }
  
    // Animate contact cards on load
    const contactCards = document.querySelectorAll('.contact-card');
    contactCards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      
      setTimeout(() => {
        card.style.transition = 'all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1)';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, 100 * index);
    });
  
    // Show success toast
    if (document.querySelector('.alert.success')) {
      setTimeout(() => {
        document.querySelector('.alert.success').style.transition = 'all 0.5s ease';
        document.querySelector('.alert.success').style.opacity = '0';
        document.querySelector('.alert.success').style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
          if (document.querySelector('.alert.success')) {
            document.querySelector('.alert.success').remove();
          }
        }, 500);
      }, 3000);
    }
  
    // Helper functions
    function shakeElement(element) {
      element.style.animation = 'shake 0.5s';
      setTimeout(() => {
        element.style.animation = '';
      }, 500);
      
      // Add shake animation
      const style = document.createElement('style');
      style.textContent = `
        @keyframes shake {
          0%, 100% { transform: translateX(0); }
          10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
          20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
      `;
      document.head.appendChild(style);
      setTimeout(() => document.head.removeChild(style), 500);
    }
  
    function showErrorToast(message) {
      const toast = document.createElement('div');
      toast.className = 'error-toast';
      toast.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
      `;
      document.body.appendChild(toast);
      
      // Add styles
      const style = document.createElement('style');
      style.textContent = `
        .error-toast {
          position: fixed;
          bottom: 2rem;
          left: 50%;
          transform: translateX(-50%) translateY(100px);
          background: #d63031;
          color: white;
          padding: 1rem 2rem;
          border-radius: 8px;
          display: flex;
          align-items: center;
          box-shadow: 0 10px 25px rgba(214, 48, 49, 0.3);
          z-index: 1000;
          animation: slideUp 0.5s forwards;
        }
        
        .error-toast i {
          margin-right: 1rem;
          font-size: 1.2rem;
        }
        
        @keyframes slideUp {
          to { transform: translateX(-50%) translateY(0); }
        }
      `;
      document.head.appendChild(style);
      
      // Remove after 3 seconds
      setTimeout(() => {
        toast.style.animation = 'fadeOut 0.5s forwards';
        
        const fadeStyle = document.createElement('style');
        fadeStyle.textContent = `
          @keyframes fadeOut {
            to { opacity: 0; transform: translateX(-50%) translateY(-50px); }
          }
        `;
        document.head.appendChild(fadeStyle);
        
        setTimeout(() => {
          document.body.removeChild(toast);
          document.head.removeChild(style);
          document.head.removeChild(fadeStyle);
        }, 500);
      }, 3000);
    }
  
    // Add floating animation to logo icon
    const logoIcon = document.querySelector('.logo i');
    if (logoIcon) {
      logoIcon.style.animation = 'float 3s ease-in-out infinite';
    }
  });
  