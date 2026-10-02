<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

$res = $db->query("DESCRIBE branch_rules");
$fields = [];
while ($row = $res->fetch_assoc()) {
    $fields[] = $row['Field'];
}
print_r($fields);

$db->query("
CREATE TABLE IF NOT EXISTS geofence_events (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    branch_id INT(11) NOT NULL,
    event_type ENUM('EXIT', 'RE-ENTRY') NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(10,8) NOT NULL,
    event_time DATETIME NOT NULL,
    duration_outside INT(11) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
echo "Table created.";
