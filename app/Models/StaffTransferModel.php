<?php

namespace App\Models;

use CodeIgniter\Model;

class StaffTransferModel extends Model
{
    protected $table      = 'staff_transfers';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id', 'from_branch_id', 'to_branch_id',
        'transferred_by', 'reason', 'effective_date', 'created_at',
    ];

    protected $useTimestamps = false;
    protected $returnType    = 'array';

    /**
     * Get full transfer history for a user, joining branch and transferrer names.
     */
    public function getHistoryForUser(int $userId): array
    {
        return $this->db->table('staff_transfers st')
            ->select('st.*, 
                fb.name AS from_branch_name, 
                tb.name AS to_branch_name,
                ui.firstname, ui.lastname, ui.employee_id,
                tui.firstname AS transferred_by_firstname,
                tui.lastname  AS transferred_by_lastname')
            ->join('branches fb', 'fb.id = st.from_branch_id', 'left')
            ->join('branches tb', 'tb.id = st.to_branch_id', 'left')
            ->join('user_info ui', 'ui.user_id = st.user_id', 'left')
            ->join('user_info tui', 'tui.user_id = st.transferred_by', 'left')
            ->where('st.user_id', $userId)
            ->orderBy('st.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Get all transfer records (Admin view), optionally filtered by branch, date range, and search.
     */
    public function getAllHistory(?int $branchId = null, array $filters = []): array
    {
        $builder = $this->db->table('staff_transfers st')
            ->select('st.*, 
                fb.name AS from_branch_name, 
                tb.name AS to_branch_name,
                ui.firstname, ui.lastname, ui.employee_id,
                tui.firstname AS transferred_by_firstname,
                tui.lastname  AS transferred_by_lastname')
            ->join('branches fb', 'fb.id = st.from_branch_id', 'left')
            ->join('branches tb', 'tb.id = st.to_branch_id', 'left')
            ->join('user_info ui', 'ui.user_id = st.user_id', 'left')
            ->join('user_info tui', 'tui.user_id = st.transferred_by', 'left');

        if ($branchId) {
            $builder->groupStart()
                    ->where('st.from_branch_id', $branchId)
                    ->orWhere('st.to_branch_id', $branchId)
                    ->groupEnd();
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('ui.firstname', $search)
                ->orLike('ui.lastname', $search)
                ->orLike('ui.employee_id', $search)
                ->orLike('st.reason', $search)
                ->groupEnd();
        }

        if (!empty($filters['filter_branch_id'])) {
            $fBranch = $filters['filter_branch_id'];
            $builder->groupStart()
                ->where('st.from_branch_id', $fBranch)
                ->orWhere('st.to_branch_id', $fBranch)
                ->groupEnd();
        }

        if (!empty($filters['start_date'])) {
            $builder->where('st.effective_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('st.effective_date <=', $filters['end_date']);
        }

        $builder->orderBy('st.created_at', 'DESC');

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $builder->limit($filters['limit'], $filters['offset']);
        }

        return $builder->get()->getResultArray();
    }
    
    public function countAllHistory(?int $branchId = null, array $filters = []): int
    {
        $builder = $this->db->table('staff_transfers st')
            ->join('user_info ui', 'ui.user_id = st.user_id', 'left');

        if ($branchId) {
            $builder->groupStart()
                    ->where('st.from_branch_id', $branchId)
                    ->orWhere('st.to_branch_id', $branchId)
                    ->groupEnd();
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('ui.firstname', $search)
                ->orLike('ui.lastname', $search)
                ->orLike('ui.employee_id', $search)
                ->orLike('st.reason', $search)
                ->groupEnd();
        }

        if (!empty($filters['filter_branch_id'])) {
            $fBranch = $filters['filter_branch_id'];
            $builder->groupStart()
                ->where('st.from_branch_id', $fBranch)
                ->orWhere('st.to_branch_id', $fBranch)
                ->groupEnd();
        }

        if (!empty($filters['start_date'])) {
            $builder->where('st.effective_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('st.effective_date <=', $filters['end_date']);
        }

        return $builder->countAllResults();
    }
}
