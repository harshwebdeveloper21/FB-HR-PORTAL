<?php
namespace App\Models;
use CodeIgniter\Model;

class ResignationModel extends Model
{
    protected $table         = "resignations";
    protected $primaryKey    = "id";
    protected $useTimestamps = true;
    protected $allowedFields = [
        "employee_id","resignation_date","reason","requested_lwd","final_lwd",
        "notice_days","notice_shortfall_days","notice_waived","status",
        "manager_id","manager_remarks","manager_action_at",
        "hr_id","hr_remarks","hr_action_at",
    ];

    public function getWithDetails(int $id): ?array
    {
        return $this->db->table("resignations r")
            ->select('r.*, ui.firstname, ui.lastname, ui.employee_id as emp_code, u.email,
                      CONCAT(mi.firstname," ",mi.lastname) as manager_name,
                      CONCAT(hi.firstname," ",hi.lastname) as hr_name')
            ->join("users u",   "u.id = r.employee_id", "left")
            ->join("user_info ui", "ui.user_id = r.employee_id", "left")
            ->join("users mu", "mu.id = r.manager_id", "left")
            ->join("user_info mi", "mi.user_id = r.manager_id", "left")
            ->join("users hu", "hu.id = r.hr_id", "left")
            ->join("user_info hi", "hi.user_id = r.hr_id", "left")
            ->where("r.id", $id)
            ->get()->getRowArray();
    }

    public function getAllWithEmployee(): array
    {
        return $this->db->table("resignations r")
            ->select('r.*, CONCAT(ui.firstname," ",ui.lastname) as employee_name, ui.employee_id as emp_code, u.email')
            ->join("users u",   "u.id = r.employee_id", "left")
            ->join("user_info ui", "ui.user_id = r.employee_id", "left")
            ->orderBy("r.created_at", "DESC")
            ->get()->getResultArray();
    }

    public function getPendingForManager(int $managerId): array
    {
        return $this->db->table("resignations r")
            ->select('r.*, CONCAT(ui.firstname," ",ui.lastname) as employee_name, ui.employee_id as emp_code')
            ->join("user_info ui", "ui.user_id = r.employee_id", "left")
            ->where("r.manager_id", $managerId)
            ->whereIn("r.status", ["submitted"])
            ->orderBy("r.created_at", "DESC")
            ->get()->getResultArray();
    }

    public function getActiveForEmployee(int $empId): ?array
    {
        return $this->whereIn("status", ["submitted","manager_approved","hr_approved","notice_period","handover","clearance","fnf"])
            ->where("employee_id", $empId)
            ->orderBy("id", "DESC")
            ->first();
    }
}
