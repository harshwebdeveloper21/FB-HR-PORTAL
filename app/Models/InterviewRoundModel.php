<?php

namespace App\Models;

use CodeIgniter\Model;

class InterviewRoundModel extends Model
{
    protected $table      = 'interview_rounds';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'interview_id', 'interviewer_id', 'interview_round', 'interview_score', 'interview_status'
    ];

    protected $useTimestamps = true;
}
