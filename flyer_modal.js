document.addEventListener('DOMContentLoaded', () => {
    // Get all flyer cards
    const flyerCards = document.querySelectorAll('.flyer-card');
    let activeModal = null;

    // Function to open modal
    function openModal(modal) {
        if (!modal) return;
        
        modal.style.display = 'block';
        // Use setTimeout to ensure the display: block is applied before adding the show class
        setTimeout(() => {
            modal.classList.add('show');
            // Focus the close button for accessibility
            const closeBtn = modal.querySelector('.icce-close-btn, .close-btn');
            if (closeBtn) {
                closeBtn.focus();
            }
        }, 10);
        activeModal = modal;
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    // Function to close modal
    function closeModal(modal) {
        if (!modal) return;
        
        modal.classList.remove('show');
        // Wait for the fade out animation to complete
        setTimeout(() => {
            modal.style.display = 'none';
            activeModal = null;
            document.body.style.overflow = ''; // Restore background scrolling
        }, 300);
    }

    // Add click event to flyer cards
    flyerCards.forEach(card => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            const modalId = card.dataset.modal + '-modal';
            const modal = document.getElementById(modalId);
            if (modal) {
                openModal(modal);
            } else {
                console.warn(`Modal with ID '${modalId}' not found`);
            }
        });
    });

    // Get all close buttons (both regular and ICCE modals)
    const closeButtons = document.querySelectorAll('.close-btn, .icce-close-btn');

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal, .icce-modal');
            if (modal) {
                closeModal(modal);
            }
        });
    });

    // Close modal when clicking outside of modal content
    document.addEventListener('click', (event) => {
        if (event.target.classList.contains('modal') || event.target.classList.contains('icce-modal')) {
            closeModal(event.target);
        }
    });

    // Prevent modal content clicks from closing the modal
    document.addEventListener('click', (event) => {
        if (event.target.closest('.icce-modal-content, .modal-content')) {
            event.stopPropagation();
        }
    });

    // Add keyboard accessibility
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && activeModal) {
            closeModal(activeModal);
        }
    });

    // Focus trap for modal
    function trapFocus(modal) {
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstFocusableElement = focusableElements[0];
        const lastFocusableElement = focusableElements[focusableElements.length - 1];

        modal.addEventListener('keydown', (event) => {
            if (event.key === 'Tab') {
                if (event.shiftKey) {
                    if (document.activeElement === firstFocusableElement) {
                        lastFocusableElement.focus();
                        event.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusableElement) {
                        firstFocusableElement.focus();
                        event.preventDefault();
                    }
                }
            }
        });
    }

    // Initialize focus trap for all modals (both regular and ICCE)
    document.querySelectorAll('.modal, .icce-modal').forEach(modal => {
        trapFocus(modal);
    });
}); 