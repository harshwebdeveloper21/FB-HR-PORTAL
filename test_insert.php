<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
$query = "INSERT INTO staff_transfers (user_id, from_branch_id, to_branch_id, transferred_by, reason, effective_date, status, created_at) VALUES (1, 1, 2, 1, 'test', '2026-10-02', 'completed', '2026-10-02 12:00:00')";
if ($db->query($query) === TRUE) {
    echo "Insert successful.\n";
    $db->query("DELETE FROM staff_transfers WHERE reason = 'test'");
} else {
    echo "Error: " . $db->error . "\n";
}
