/**
 * Custom JavaScript for Cooperative AR System
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Highlight current page in sidebar
    const currentPage = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar .list-group-item');
    
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPage.includes(href)) {
            link.classList.add('active');
        }
    });
    
    // Add confirm dialog to all delete buttons
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-close alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Member balance check on withdrawal form
    const memberSelect = document.getElementById('member_id');
    const amountInput = document.getElementById('amount');
    const balanceDisplay = document.getElementById('balance-display');
    
    if (memberSelect && amountInput && balanceDisplay) {
        memberSelect.addEventListener('change', async function() {
            const memberId = this.value;
            if (memberId) {
                try {
                    // This would need an appropriate endpoint to fetch the balance
                    const response = await fetch(`../api/get_balance.php?member_id=${memberId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        balanceDisplay.textContent = `Available Balance: ₱${parseFloat(data.balance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        balanceDisplay.classList.remove('d-none');
                        
                        // Store the balance as a data attribute for validation
                        amountInput.dataset.maxBalance = data.balance;
                    }
                } catch (error) {
                    console.error('Error fetching balance:', error);
                }
            } else {
                balanceDisplay.classList.add('d-none');
                delete amountInput.dataset.maxBalance;
            }
        });
        
        // Validate amount against balance
        amountInput.addEventListener('input', function() {
            const maxBalance = parseFloat(this.dataset.maxBalance || 0);
            const amount = parseFloat(this.value || 0);
            
            if (amount > maxBalance) {
                this.classList.add('is-invalid');
                this.setCustomValidity(`Amount exceeds available balance of ₱${maxBalance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
            } else {
                this.classList.remove('is-invalid');
                this.setCustomValidity('');
            }
        });
    }
    
    // Print functionality
    const printButtons = document.querySelectorAll('.btn-print');
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    });
    
    // Date range picker initialization (if needed)
    const dateRangePicker = document.getElementById('date-range');
    if (dateRangePicker && typeof daterangepicker !== 'undefined') {
        new daterangepicker(dateRangePicker, {
            opens: 'left',
            autoApply: true,
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
    }
});

// Format numbers as currency
function formatCurrency(number) {
    return new Intl.NumberFormat('en-PH', { 
        style: 'currency', 
        currency: 'PHP',
        minimumFractionDigits: 2
    }).format(number);
}

// Toggle password visibility
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}