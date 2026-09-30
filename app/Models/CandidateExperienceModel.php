<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateExperienceModel extends Model
{
    protected $table = 'candidate_experiences';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'candidate_id', 'company', 'role', 'total_experience', 'last_salary', 'notice_period', 'reason_for_leaving'
    ];
    protected $useTimestamps = true;
}
