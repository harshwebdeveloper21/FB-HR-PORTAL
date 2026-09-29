<?php
$mysqli = new mysqli("localhost", "root", "", "hr_protal_new_db");
$mysqli->query("ALTER TABLE interview_assessments ADD COLUMN probation_period VARCHAR(255) NULL AFTER current_salary");
echo "Added column probation_period";
?>
