<?php
session_start();
if (!isset($_SESSION["admin"])) {
    exit;
}

include "../../db.php";

// Get recent messages
$recent_messages_query = "
    SELECT 
        id,
        sender_name,
        sender_email as email,
        subject,
        message,
        created_at,
        is_read
    FROM admin_messages 
    WHERE status = 'active' OR status IS NULL
    ORDER BY created_at DESC 
    LIMIT 5";
$recent_messages = $conn->query($recent_messages_query);

// Output JavaScript to center table headers
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    const recentMessagesTable = document.querySelector("#recent-messages-table");
    if (recentMessagesTable) {
        const headers = recentMessagesTable.querySelectorAll("th");
        headers.forEach(header => {
            header.classList.add("text-center");
        });
    }
});
</script>';

// Check if we have messages
if($recent_messages && $recent_messages->num_rows > 0) {
    // Output the HTML for each message
    while($message = $recent_messages->fetch_assoc()) {
        echo '<tr class="' . (!$message['is_read'] ? 'table-warning' : '') . '" data-message-id="' . $message['id'] . '">';
        echo '<td class="text-center">' . htmlspecialchars($message['sender_name']) . '</td>';
        echo '<td class="email-cell text-center"><div class="email-preview">' . htmlspecialchars($message['email']) . '</div></td>';
        echo '<td class="text-center"><div class="message-preview"><a href="inbox.php?view=' . $message['id'] . '" class="message-link">' 
            . htmlspecialchars($message['subject'] ?: 'No Subject') . '</a></div></td>';
        echo '<td class="text-center">' . date('M d, h:i A', strtotime($message['created_at'])) . '</td>';
        echo '<td class="text-center"><span class="badge bg-' . ($message['is_read'] ? 'success' : 'warning') . '">' 
            . ($message['is_read'] ? 'Read' : 'New') . '</span></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5" class="text-center">No messages found</td></tr>';
}
?>