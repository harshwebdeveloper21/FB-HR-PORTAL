<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateEducationModel extends Model
{
    protected $table = 'candidate_educations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'candidate_id', 'degree', 'course', 'university', 'passing_year', 'percentage'
    ];
    protected $useTimestamps = true;
}
