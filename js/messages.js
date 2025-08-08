function loadInbox() {
    $.ajax({
        url: 'message_actions.php',
        type: 'POST',
        data: {
            action: 'get_inbox'
        },
        success: function(response) {
            if (response.success) {
                displayMessages(response.messages);
            }
        }
    });
}

function displayMessages(messages) {
    const messageContainer = $('#messageContainer');
    messageContainer.empty();

    messages.forEach(message => {
        const messageHtml = `
            <div class="card mb-3 ${message.is_read ? '' : 'bg-light'}">
                <div class="card-body">
                    <h5 class="card-title">${message.subject || 'No Subject'}</h5>
                    <p class="card-text">${message.message}</p>
                    <div class="text-muted small">
                        From: ${message.name} (${message.email})
                        <br>
                        Received: ${new Date(message.created_at).toLocaleString()}
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-primary" onclick="markAsRead(${message.id})">
                            <i class="fas fa-check"></i> Mark as Read
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="archiveMessage(${message.id})">
                            <i class="fas fa-archive"></i> Archive
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteMessage(${message.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
        messageContainer.append(messageHtml);
    });
}

function markAsRead(id) {
    $.ajax({
        url: 'message_actions.php',
        type: 'POST',
        data: {
            action: 'mark_read',
            id: id
        },
        success: function(response) {
            if (response.success) {
                loadInbox();
            }
        }
    });
}

function archiveMessage(id) {
    $.ajax({
        url: 'message_actions.php',
        type: 'POST',
        data: {
            action: 'archive',
            id: id
        },
        success: function(response) {
            if (response.success) {
                loadInbox();
            }
        }
    });
}

function deleteMessage(id) {
    if (confirm('Are you sure you want to delete this message?')) {
        $.ajax({
            url: 'message_actions.php',
            type: 'POST',
            data: {
                action: 'delete',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    loadInbox();
                }
            }
        });
    }
}

function loadSent() {
    $.ajax({
        url: 'message_actions.php',
        type: 'POST',
        data: {
            action: 'get_sent'
        },
        success: function(response) {
            if (response.success) {
                displaySentMessages(response.messages);
            }
        }
    });
}

function displaySentMessages(messages) {
    const messageContainer = $('#messageContainer');
    messageContainer.empty();

    messages.forEach(message => {
        const messageHtml = `
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">${message.subject || 'No Subject'}</h5>
                    <p class="card-text">${message.message}</p>
                    <div class="text-muted small">
                        To: ${message.recipient_email}
                        <br>
                        Sent: ${new Date(message.created_at).toLocaleString()}
                    </div>
                </div>
            </div>
        `;
        messageContainer.append(messageHtml);
    });
}

// Load inbox when page loads
$(document).ready(function() {
    loadInbox();
});