<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');

// Add status column if not exists
$db->query("ALTER TABLE staff_transfers ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'completed'");
echo "Column added. " . PHP_EOL;

// Update any NULL or empty status rows to completed
$db->query("UPDATE staff_transfers SET status = 'completed' WHERE status IS NULL OR status = ''");
echo "Updated: " . $db->affected_rows . " rows to completed." . PHP_EOL;
echo "Done.";
