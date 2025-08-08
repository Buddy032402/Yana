// Theme Management
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    
    // Save theme preference
    fetch('save_theme.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ theme: newTheme })
    });
}

// Responsive Sidebar
const toggleButton = document.createElement('button');
toggleButton.className = 'sidebar-toggle btn btn-primary d-md-none';
toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
document.querySelector('.header-content').prepend(toggleButton);

toggleButton.addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(e.target) && 
        !toggleBtn.contains(e.target)) {
        sidebar.classList.remove('active');
    }
});

// Table Search Functionality
document.querySelectorAll('.table-search').forEach(input => {
    input.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const table = this.closest('.card').querySelector('table');
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    });
});

// Confirmation Dialogs
function confirmDelete(id, type) {
    if (confirm('Are you sure you want to delete this ' + type + '?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete_handler.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        
        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'type';
        typeInput.value = type;
        
        form.appendChild(idInput);
        form.appendChild(typeInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Form Validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
});

// Image Preview
document.querySelectorAll('.image-upload').forEach(input => {
    input.addEventListener('change', function() {
        const preview = this.closest('.form-group').querySelector('.image-preview');
        if (preview && this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => preview.src = e.target.result;
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// Notification System
class NotificationSystem {
    static show(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }, 100);
    }
}

// Handle AJAX errors
function handleAjaxError(error) {
    console.error('Error:', error);
    NotificationSystem.show('An error occurred. Please try again.', 'danger');
}

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', () => {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => new bootstrap.Popover(popover));
});

// Add these functions to your existing admin.js

// Advanced Filtering
function initFilters() {
    const filters = {
        global: document.getElementById('globalSearch'),
        date: document.getElementById('dateFilter'),
        status: document.getElementById('statusFilter')
    };

    Object.values(filters).forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyFilters);
        }
    });
}

function applyFilters() {
    const searchText = document.getElementById('globalSearch').value.toLowerCase();
    const dateFilter = document.getElementById('dateFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    document.querySelectorAll('table tbody tr').forEach(row => {
        let show = true;
        
        // Global search
        if (searchText && !row.textContent.toLowerCase().includes(searchText)) {
            show = false;
        }

        // Status filter
        if (statusFilter && !row.querySelector('.status-cell').textContent.includes(statusFilter)) {
            show = false;
        }

        // Date filter
        if (dateFilter) {
            const dateCell = row.querySelector('.date-cell').textContent;
            const rowDate = new Date(dateCell);
            const today = new Date();
            
            switch(dateFilter) {
                case 'today':
                    show = rowDate.toDateString() === today.toDateString();
                    break;
                case 'week':
                    const weekAgo = new Date(today - 7 * 24 * 60 * 60 * 1000);
                    show = rowDate >= weekAgo;
                    break;
                case 'month':
                    const monthAgo = new Date(today.setMonth(today.getMonth() - 1));
                    show = rowDate >= monthAgo;
                    break;
            }
        }

        row.style.display = show ? '' : 'none';
    });
}

// Bulk Actions
function initBulkActions() {
    document.querySelectorAll('.select-all').forEach(checkbox => {
        checkbox.addEventListener('change', e => {
            const table = e.target.closest('table');
            table.querySelectorAll('.select-item').forEach(item => {
                item.checked = e.target.checked;
            });
        });
    });

    ['exportSelected', 'deleteSelected'].forEach(id => {
        const button = document.getElementById(id);
        if (button) {
            button.addEventListener('click', () => handleBulkAction(id));
        }
    });
}

function handleBulkAction(action) {
    const selectedIds = Array.from(document.querySelectorAll('.select-item:checked'))
        .map(checkbox => checkbox.value);

    if (selectedIds.length === 0) {
        alert('Please select items first');
        return;
    }

    if (action === 'deleteSelected' && !confirm('Are you sure you want to delete selected items?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', action === 'exportSelected' ? 'export' : 'delete');
    formData.append('ids', JSON.stringify(selectedIds));
    formData.append('type', getCurrentPage());

    fetch('bulk_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Initialize all features
document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    initFilters();
    initBulkActions();
});

// Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-collapsed');
        mainContent.classList.toggle('main-content-expanded');
        
        // Store the state
        const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });

    // Check stored state on page load
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('sidebar-collapsed');
        mainContent.classList.add('main-content-expanded');
    }
});