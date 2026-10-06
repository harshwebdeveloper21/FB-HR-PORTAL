<?php
$conn = new mysqli('localhost', 'root', '', 'hr_protal_new_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("SELECT id FROM users WHERE role != 'admin' LIMIT 5");
$users = [];
while ($row = $res->fetch_assoc()) $users[] = $row;

$managerRes = $conn->query("SELECT id FROM users WHERE role IN ('admin', 'hr') LIMIT 1");
$manager = $managerRes->fetch_assoc();

$hrRes = $conn->query("SELECT id FROM users WHERE role IN ('hr', 'admin') LIMIT 1");
$hr = $hrRes->fetch_assoc();

$statuses = ['submitted', 'manager_approved', 'notice_period', 'handover', 'clearance'];

$count = 0;
foreach ($users as $i => $u) {
    // Check if resignation already exists
    $ch = $conn->query("SELECT id FROM resignations WHERE employee_id = " . $u['id']);
    if ($ch->num_rows > 0) continue;
    
    $status = $statuses[$count % count($statuses)];
    $emp_id = $u['id'];
    $m_id = $manager ? $manager['id'] : $u['id'];
    $hr_id = $hr ? $hr['id'] : $u['id'];
    
    $res_date = date('Y-m-d', strtotime('-' . rand(1, 15) . ' days'));
    $req_lwd = date('Y-m-d', strtotime('+' . rand(15, 30) . ' days'));
    $fin_lwd = date('Y-m-d', strtotime('+' . rand(15, 30) . ' days'));
    
    $mgr_rem = $status != 'submitted' ? 'Approved' : '';
    $mgr_at = $status != 'submitted' ? "'" . date('Y-m-d H:i:s') . "'" : "NULL";
    
    $hr_app = in_array($status, ['notice_period', 'handover', 'clearance', 'fnf', 'relieved']);
    $hr_rem = $hr_app ? 'Approved HR' : '';
    $hr_at = $hr_app ? "'" . date('Y-m-d H:i:s') . "'" : "NULL";
    
    $sql = "INSERT INTO resignations (
        employee_id, manager_id, hr_id, resignation_date, requested_lwd, final_lwd, reason, notice_days, status, manager_remarks, manager_action_at, hr_remarks, hr_action_at, created_at, updated_at
    ) VALUES (
        $emp_id, $m_id, $hr_id, '$res_date', '$req_lwd', '$fin_lwd', 'Career growth $count', 30, '$status', 
        '$mgr_rem', $mgr_at, '$hr_rem', $hr_at, 
        '" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $resId = $conn->insert_id;
        echo "Added resignation for user {$emp_id} with status {$status}\n";
        
        if (in_array($status, ['handover', 'clearance', 'fnf', 'relieved'])) {
            $conn->query("INSERT INTO handover_tasks (resignation_id, task, handover_to, status, created_at) VALUES ($resId, 'Task', $m_id, 'pending', NOW())");
        }
        if (in_array($status, ['clearance', 'fnf', 'relieved'])) {
            $conn->query("INSERT INTO clearance_items (resignation_id, department, status, created_at) VALUES ($resId, 'IT', 'pending', NOW())");
            $conn->query("INSERT INTO clearance_items (resignation_id, department, status, created_at) VALUES ($resId, 'HR', 'pending', NOW())");
        }
        
        $count++;
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

echo "Added {$count} resignations.\n";
