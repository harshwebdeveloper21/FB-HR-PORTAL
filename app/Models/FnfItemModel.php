<?php
namespace App\Models;
use CodeIgniter\Model;
class FnfItemModel extends Model {
    protected $table = "fnf_items";
    protected $primaryKey = "id";
    protected $allowedFields = ["fnf_id","type","title","amount","created_at"];
    public function getByFnf(int $fnfId): array { return $this->where("fnf_id",$fnfId)->findAll(); }
    public function getEarnings(int $fnfId): array { return $this->where("fnf_id",$fnfId)->where("type","earning")->findAll(); }
    public function getDeductions(int $fnfId): array { return $this->where("fnf_id",$fnfId)->where("type","deduction")->findAll(); }
}
