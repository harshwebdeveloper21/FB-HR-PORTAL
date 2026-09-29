<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
if ($db->connect_error) die('Connect failed: ' . $db->connect_error);

$queries = [
    "ALTER TABLE interview_assessments ADD COLUMN IF NOT EXISTS current_salary VARCHAR(255) NULL AFTER notice_period",
    "ALTER TABLE interview_assessments ADD COLUMN IF NOT EXISTS joining_date DATE NULL AFTER current_salary",
];

foreach ($queries as $q) {
    if ($db->query($q)) echo "OK: $q\n";
    else echo "ERR: " . $db->error . "\n";
}
echo "Done.\n";
