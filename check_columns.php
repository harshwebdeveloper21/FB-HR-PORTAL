<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
$res = $db->query("SELECT id, branch_id, enable_geofencing, office_latitude, office_longitude, office_radius FROM branch_rules");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
