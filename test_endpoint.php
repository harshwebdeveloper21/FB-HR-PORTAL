<?php
// Mock request for initiate
$ch = curl_init('http://localhost:8080/api/staff-transfer/initiate');

$payload = json_encode([
    'user_id' => 1,
    'to_branch_id' => 2,
    'effective_date' => '2026-10-02',
    'reason' => 'Testing API directly'
]);

// Since it requires auth, we can't easily curl without token. Let's just instantiate the controller.
