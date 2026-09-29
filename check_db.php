<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
$sql = "CREATE TABLE IF NOT EXISTS candidate_documents (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT(11) UNSIGNED NOT NULL,
    doc_key VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NULL,
    file_path VARCHAR(500) NULL,
    status VARCHAR(50) DEFAULT 'pending',
    reject_reason TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($db->query($sql)) echo "OK: Table created.\n";
else echo "ERR: " . $db->error . "\n";
