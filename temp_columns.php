<?php $mysqli = new mysqli("localhost", "root", "", "hr_protal_new_db"); $result = $mysqli->query("SHOW COLUMNS FROM candidate"); while($row = $result->fetch_assoc()){ echo $row["Field"] . "\n"; } ?>
