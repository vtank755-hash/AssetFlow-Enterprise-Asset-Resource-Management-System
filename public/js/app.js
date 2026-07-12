/**
 * AssetFlow ERP - Premium Client-Side Interactivity & Animations Module
 * Uses clean Vanilla JavaScript (no external jQuery/AJAX) to provide 
 * entrance animations, interactive sidebars, validated dates, and dropdown menus.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Page Elements Entrance Animations
    animatePageElements();

    // 2. Responsive Sidebar Interactivity
    initSidebarToggle();

    // 3. Interactive Dropdowns (Enhances the <details> CSS fallback)
    initInteractiveDropdowns();

    // 4. Client-side Form Validation and Instant Warnings
    initFormValidators();
});

/**
 * Adds CSS-faded slide-up entrance animations sequentially.
 */
function animatePageElements() {
    const titles = document.querySelectorAll('h1, .breadcrumb');
    const cards = document.querySelectorAll('.card');
    const tables = document.querySelectorAll('.table-responsive');
    const alerts = document.querySelectorAll('.alert');

    // Add animation classes dynamically
    titles.forEach((el, index) => {
        el.classList.add('animate-fade-in');
        el.style.animationDelay = `${index * 0.05}s`;
    });

    cards.forEach((el, index) => {
        el.classList.add('animate-fade-in');
        el.style.animationDelay = `${(index + 2) * 0.05}s`;
    });

    tables.forEach((el, index) => {
        el.classList.add('animate-fade-in');
        el.style.animationDelay = '0.2s';
    });

    alerts.forEach(el => {
        el.classList.add('animate-fade-in');
    });
}

/**
 * Configures the mobile and desktop sidebar layout collapse triggers.
 */
function initSidebarToggle() {
    const checkbox = document.getElementById('sidebar-toggle');
    const menuLabel = document.querySelector('label[for="sidebar-toggle"]');
    const sidebar = document.getElementById('sidebar');

    if (checkbox && sidebar) {
        // Close sidebar on ESC key press
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && checkbox.checked) {
                checkbox.checked = false;
            }
        });
    }
}

/**
 * Handles toggling details-based dropdown menus gracefully.
 */
function initInteractiveDropdowns() {
    const detailsDropdowns = document.querySelectorAll('details.css-dropdown');

    // Close open dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        detailsDropdowns.forEach(dropdown => {
            if (dropdown.hasAttribute('open') && !dropdown.contains(e.target)) {
                dropdown.removeAttribute('open');
            }
        });
    });
}

/**
 * Validates date ranges and allocation schedules on the client side.
 */
function initFormValidators() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        // Date Check: End Time must be greater than Start Time
        const startInput = form.querySelector('input[name="start_time"]');
        const endInput = form.querySelector('input[name="end_time"]');

        if (startInput && endInput) {
            form.addEventListener('submit', (e) => {
                if (startInput.value && endInput.value) {
                    const start = new Date(startInput.value);
                    const end = new Date(endInput.value);

                    if (start >= end) {
                        e.preventDefault();
                        showInstantWarning(endInput, 'The reservation end date/time must be after the start date/time.');
                    }
                }
            });
        }
    });
}

/**
 * Dynamically displays validation error hints using Bootstrap 5 classes.
 */
function showInstantWarning(inputElement, message) {
    // Remove existing warning
    const parent = inputElement.parentElement;
    const existingFeedback = parent.querySelector('.invalid-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }

    inputElement.classList.add('is-invalid');
    
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback d-block mt-1';
    feedback.innerText = message;
    parent.appendChild(feedback);

    // Scroll to the input element
    inputElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
