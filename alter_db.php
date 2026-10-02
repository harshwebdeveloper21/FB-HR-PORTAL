<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

$db->query("ALTER TABLE branch_rules ADD COLUMN office_latitude DECIMAL(10,8) NULL AFTER enable_geofencing");
$db->query("ALTER TABLE branch_rules ADD COLUMN office_longitude DECIMAL(11,8) NULL AFTER office_latitude");
$db->query("ALTER TABLE branch_rules ADD COLUMN office_radius INT(11) DEFAULT 15 AFTER office_longitude");
echo "Columns added to branch_rules.";
