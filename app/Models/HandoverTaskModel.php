<?php
namespace App\Models;
use CodeIgniter\Model;
class HandoverTaskModel extends Model {
    protected $table = "handover_tasks";
    protected $primaryKey = "id";
    protected $useTimestamps = true;
    protected $allowedFields = ["resignation_id","task","description","handover_to","due_date","status","acceptor_remarks","accepted_at","completed_at"];
    public function getByResignation(int $id): array {
        return $this->db->table("handover_tasks h")
            ->select('h.*, CONCAT(ui.firstname," ",ui.lastname) as handover_to_name')
            ->join("user_info ui","ui.user_id = h.handover_to","left")
            ->where("h.resignation_id",$id)->orderBy("h.due_date","ASC")->get()->getResultArray();
    }
    public function getPendingForUser(int $userId): array {
        return $this->db->table("handover_tasks h")
            ->select('h.*, r.id as resignation_id, CONCAT(ui.firstname," ",ui.lastname) as from_employee')
            ->join("resignations r","r.id = h.resignation_id","left")
            ->join("user_info ui","ui.user_id = r.employee_id","left")
            ->where("h.handover_to",$userId)->whereIn("h.status",["pending","accepted"])->get()->getResultArray();
    }
    public function allCompleted(int $resignationId): bool {
        $total = $this->where("resignation_id",$resignationId)->countAllResults(false);
        $done  = $this->where("resignation_id",$resignationId)->where("status","completed")->countAllResults();
        return $total > 0 && $total === $done;
    }
}
