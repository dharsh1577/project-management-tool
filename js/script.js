// Global functions for the application
class ProjectManagerApp {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initModals();
        this.initFilters();
    }

    bindEvents() {
        // Modal handling
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-trigger')) {
                this.openModal(e.target.dataset.modal);
            }
            if (e.target.classList.contains('modal-close') || e.target.classList.contains('modal')) {
                this.closeModal(e.target);
            }
        });

        // Form submissions with loading states
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.classList.contains('ajax-form')) {
                e.preventDefault();
                this.submitForm(form);
            }
        });

        // Task status updates
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('status-select')) {
                this.updateTaskStatus(e.target);
            }
        });
    }

    initModals() {
        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    initFilters() {
        const filterForm = document.getElementById('task-filters');
        if (filterForm) {
            filterForm.addEventListener('change', () => {
                this.applyFilters();
            });
        }

        const searchInput = document.getElementById('search-tasks');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce(() => {
                this.searchTasks();
            }, 300));
        }
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modalElement) {
        if (modalElement.classList.contains('modal')) {
            modalElement.style.display = 'none';
        } else {
            modalElement.closest('.modal').style.display = 'none';
        }
        document.body.style.overflow = 'auto';
    }

    closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = 'auto';
    }

    async submitForm(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Processing...';
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                } else {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            this.showAlert('An error occurred. Please try again.', 'error');
            console.error('Form submission error:', error);
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async updateTaskStatus(select) {
        const taskId = select.dataset.taskId;
        const newStatus = select.value;

        try {
            const response = await fetch('tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_status&task_id=${taskId}&status=${newStatus}`
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Task status updated successfully!', 'success');
                // Update UI
                const taskItem = select.closest('.task-item');
                taskItem.className = `task-item ${newStatus}`;
                
                // Update status badge if exists
                const statusBadge = taskItem.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = `status-badge status-${newStatus}`;
                    statusBadge.textContent = newStatus.replace('-', ' ');
                }
            } else {
                this.showAlert(result.message, 'error');
                // Revert selection
                select.value = select.dataset.originalValue;
            }
        } catch (error) {
            this.showAlert('Error updating task status', 'error');
            select.value = select.dataset.originalValue;
            console.error('Status update error:', error);
        }
    }

    applyFilters() {
        const form = document.getElementById('task-filters');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        // Update URL without page reload
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState({}, '', newUrl);
        
        // Reload the page to apply filters
        window.location.reload();
    }

    searchTasks() {
        const searchInput = document.getElementById('search-tasks');
        const searchTerm = searchInput.value.trim();
        
        if (searchTerm.length >= 2 || searchTerm.length === 0) {
            // Implement search functionality here
            // This would typically make an AJAX call to search endpoint
            console.log('Searching for:', searchTerm);
        }
    }

    showAlert(message, type) {
        // Remove existing alerts
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '10000';
        alert.style.minWidth = '300px';

        document.body.appendChild(alert);

        // Auto remove after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Utility function to format dates
    formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    // Utility function to check if date is overdue
    isOverdue(dueDate) {
        return new Date(dueDate) < new Date();
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new ProjectManagerApp();
});

// Additional utility functions
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

function toggleElement(id) {
    const element = document.getElementById(id);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}
// Dashboard specific functions
class Dashboard {
    constructor() {
        this.initCharts();
        this.initNotifications();
    }

    initCharts() {
        // Initialize any charts if needed
        console.log('Dashboard charts initialized');
    }

    initNotifications() {
        // Notification system
        this.checkDueTasks();
    }

    checkDueTasks() {
        // Check for due tasks and show notifications
        const dueTasks = document.querySelectorAll('.task-item.overdue');
        if (dueTasks.length > 0) {
            this.showNotification(`You have ${dueTasks.length} overdue tasks!`, 'warning');
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class='bx bx-bell'></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-left: 4px solid ${type === 'warning' ? '#e74c3c' : '#3498db'};
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);

        // Close on click
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }
}

// Initialize dashboard when on dashboard page
if (window.location.pathname.includes('dashboard.php')) {
    window.dashboard = new Dashboard();
}