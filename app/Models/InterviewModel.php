<?php

namespace App\Models;

use CodeIgniter\Model;

class InterviewModel extends Model
{
    protected $table      = 'interviews';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'candidate_id', 'job_id', 'interviewer_id', 'description', 'status', 'schedule_date', 'created_by', 'interview_type', 'location',
        'full_name', 'mobile_number', 'email', 'date_of_birth', 'gender', 'current_address', 'city', 'state', 'pincode',
        'position_applied_for', 'department_id', 'job_type', 'interview_date', 'interview_time', 'interview_round',
        'highest_qualification', 'degree_course', 'college_university', 'passing_year', 'percentage_cgpa',
        'experience_type', 'total_experience', 'previous_company', 'previous_job_title', 'previous_salary', 'expected_salary',
        'notice_period', 'reason_for_leaving', 'technical_skills', 'communication_skills', 'computer_skills', 'relevant_experience',
        'key_strengths', 'interview_score', 'interview_feedback', 'interview_status', 'selection_status', 'offered_salary',
        'joining_date', 'hr_remarks', 'candidate_remarks'
    ];

    protected $useTimestamps = true;
}
