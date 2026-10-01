<?php
$file = 'd:/xampp/htdocs/HR/Hr-Korvia/app/Controllers/api/AttendanceController.php';
$lines = file($file);
$lines[3148] = str_replace('""A"', '"A"', $lines[3148]); // line 3149 is index 3148
$lines[3148] = str_replace(' + 1)"', ' + 1)', $lines[3148]);
file_put_contents($file, implode("", $lines));
echo "Fixed!";
