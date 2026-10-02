<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

$res = $db->query("DESCRIBE branch_rules");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
