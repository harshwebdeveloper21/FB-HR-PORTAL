<?php
namespace App\Models;
use CodeIgniter\Model;
class ResignationAuditLogModel extends Model {
    protected $table = "resignation_audit_logs";
    protected $primaryKey = "id";
    protected $allowedFields = ["resignation_id","actor_id","action","from_status","to_status","remarks","created_at"];
    public function log(int $resignationId, int $actorId, string $action, string $from = "", string $to = "", string $remarks = ""): void {
        $this->insert(["resignation_id"=>$resignationId,"actor_id"=>$actorId,"action"=>$action,"from_status"=>$from,"to_status"=>$to,"remarks"=>$remarks,"created_at"=>date("Y-m-d H:i:s")]);
    }
    public function getByResignation(int $id): array {
        return $this->db->table("resignation_audit_logs l")
            ->select('l.*, CONCAT(ui.firstname," ",ui.lastname) as actor_name')
            ->join("user_info ui","ui.user_id = l.actor_id","left")
            ->where("l.resignation_id",$id)->orderBy("l.created_at","ASC")->get()->getResultArray();
    }
}
