<?php
$db = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
$db->query("START TRANSACTION");

$staffId = 1;
$toBranchId = 2;
$fromBranchId = 1;
$actorId = 1;
$reason = 'test';
$effectiveDate = '2026-10-02';
$createdAt = date('Y-m-d H:i:s');

$res1 = $db->query("UPDATE users SET branch_id = $toBranchId WHERE id = $staffId");
if (!$res1) echo "Error update: " . $db->error . "\n";

$res2 = $db->query("INSERT INTO staff_transfers (user_id, from_branch_id, to_branch_id, transferred_by, reason, effective_date, status, created_at) VALUES ($staffId, $fromBranchId, $toBranchId, $actorId, '$reason', '$effectiveDate', 'completed', '$createdAt')");
if (!$res2) echo "Error insert: " . $db->error . "\n";

if ($res1 && $res2) {
    echo "Success!";
    $db->query("ROLLBACK");
} else {
    $db->query("ROLLBACK");
}
