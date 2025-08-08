document.addEventListener('DOMContentLoaded', function() {
    // Handle message actions with animations
    const handleMessageAction = async (action, messageId) => {
        const messageElement = document.querySelector(`#message-${messageId}`);
        
        try {
            const response = await fetch('message_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&id=${messageId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                messageElement.style.transform = 'translateX(100%)';
                messageElement.style.opacity = '0';
                
                setTimeout(() => {
                    messageElement.remove();
                }, 300);
                
                showNotification('Success!', 'Message ' + action + 'd successfully');
            }
        } catch (error) {
            showNotification('Error', 'Failed to process request', 'error');
        }
    };

    // Show notification function
    const showNotification = (title, message, type = 'success') => {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <h4>${title}</h4>
            <p>${message}</p>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 3000);
    };

    // Initialize tooltips and other Bootstrap components
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
});