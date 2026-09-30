<?php
namespace App\Models;
use CodeIgniter\Model;
class ClearanceItemModel extends Model {
    protected $table = "clearance_items";
    protected $primaryKey = "id";
    protected $useTimestamps = true;
    protected $allowedFields = ["resignation_id","department","approver_id","status","remarks","approved_at"];
    const DEPARTMENTS = ["Manager","IT","Admin","Finance","HR"];
    public function getByResignation(int $id): array {
        return $this->db->table("clearance_items c")
            ->select('c.*, CONCAT(ui.firstname," ",ui.lastname) as approver_name')
            ->join("user_info ui","ui.user_id = c.approver_id","left")
            ->where("c.resignation_id",$id)->get()->getResultArray();
    }
    public function createDefaults(int $resignationId): void {
        $now = date("Y-m-d H:i:s");
        foreach (self::DEPARTMENTS as $dept) {
            $this->insert(["resignation_id"=>$resignationId,"department"=>$dept,"status"=>"pending","created_at"=>$now,"updated_at"=>$now]);
        }
    }
    public function allApproved(int $resignationId): bool {
        $total    = $this->where("resignation_id",$resignationId)->countAllResults(false);
        $approved = $this->where("resignation_id",$resignationId)->where("status","approved")->countAllResults();
        return $total > 0 && $total === $approved;
    }
}
