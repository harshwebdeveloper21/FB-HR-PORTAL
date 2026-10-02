<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
echo "=== attendance columns ===" . PHP_EOL;
$r1 = $db->query('DESCRIBE attendance');
while($row = $r1->fetch_assoc()){ echo $row['Field'] . PHP_EOL; }

echo PHP_EOL . "=== branch_rules columns ===" . PHP_EOL;
$r2 = $db->query('DESCRIBE branch_rules');
while($row = $r2->fetch_assoc()){ echo $row['Field'] . PHP_EOL; }
