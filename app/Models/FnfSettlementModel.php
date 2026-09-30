<?php
namespace App\Models;
use CodeIgniter\Model;
class FnfSettlementModel extends Model {
    protected $table = "fnf_settlements";
    protected $primaryKey = "id";
    protected $useTimestamps = true;
    protected $allowedFields = ["resignation_id","total_earnings","total_deductions","net_payable","status","finance_approver_id","finance_remarks","paid_date","payment_ref","hr_prepared_by"];
    public function getByResignation(int $resignationId): ?array {
        return $this->where("resignation_id",$resignationId)->orderBy("id","DESC")->first();
    }
}
