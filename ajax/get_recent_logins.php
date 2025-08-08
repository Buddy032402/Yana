<?php
session_start();
if (!isset($_SESSION["admin"])) {
    exit("Unauthorized");
}

include "../../db.php";

// Updated query to get recent logins with proper join and status check
$recent_logins = $conn->query("
    SELECT l.*, u.name as username 
    FROM login_history l
    LEFT JOIN users u ON l.user_id = u.id
    WHERE l.status = 'success'
    ORDER BY l.login_time DESC 
    LIMIT 5
");

// Check if query executed successfully
if (!$recent_logins) {
    echo "<tr><td colspan='3'>Error fetching login data: " . $conn->error . "</td></tr>";
    exit;
}

// Check if there are any results
if ($recent_logins->num_rows == 0) {
    echo "<tr><td colspan='3'>No recent login activity found</td></tr>";
} else {
    // Display the login records
    while($login = $recent_logins->fetch_assoc()): 
    ?>
        <tr>
            <td><?php echo htmlspecialchars($login['username'] ?? $login['email'] ?? 'Unknown User'); ?></td>
            <td><?php echo date('M d, Y H:i', strtotime($login['login_time'])); ?></td>
            <td>
                <span class="badge bg-<?php echo $login['status'] == 'success' ? 'success' : 'danger'; ?>">
                    <?php echo ucfirst($login['status']); ?>
                </span>
            </td>
        </tr>
    <?php endwhile;
}
?>