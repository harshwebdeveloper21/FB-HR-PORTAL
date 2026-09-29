<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InterviewAssessments extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('interview_assessments ia');
        $builder->select('ia.*, COALESCE(c.candidate_name, i.full_name) as candidate_name');
        $builder->join('interviews i', 'i.id = ia.interview_id', 'left');
        $builder->join('candidate c', 'c.id = i.candidate_id', 'left');
        $data['assessments'] = $builder->get()->getResultArray();

        return view('assessments/index', $data);
    }

    public function create()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('interviews i');
        $builder->select('i.*, c.candidate_name, j.job_title as position_applied_for, d.department_name, CONCAT(ui.firstname, " ", ui.lastname) as interviewer_name');
        $builder->join('candidate c', 'c.id = i.candidate_id', 'left');
        $builder->join('jobs j', 'j.id = COALESCE(i.job_id, c.job_id)', 'left');
        $builder->join('department d', 'd.id = j.department_id', 'left');
        $builder->join('user_info ui', 'ui.user_id = i.interviewer_id', 'left');
        $data['interviews'] = $builder->get()->getResultArray();
        
        $deptBuilder = $db->table('department');
        $data['departments'] = $deptBuilder->get()->getResultArray();
        
        return view('assessments/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();

        // Collect ratings from dynamic fields
        $ratingsData = [];
        $post = $this->request->getPost();
        foreach ($post as $key => $val) {
            if (strpos($key, 'criteria_title_') === 0) {
                $index = str_replace('criteria_title_', '', $key);
                $ratingsData[] = [
                    'title'   => $val,
                    'desc'    => $post['criteria_desc_' . $index] ?? '',
                    'rating'  => $post['rating_' . $index] ?? 0,
                    'comment' => $post['comment_' . $index] ?? '',
                ];
            }
        }

        $model = new \App\Models\InterviewAssessmentModel();
        $model->save([
            'interview_id'     => $this->request->getPost('interview_id'),
            'job_title'        => $this->request->getPost('job_title'),
            'department'       => $this->request->getPost('department'),
            'interview_round'  => $this->request->getPost('interview_round'),
            'interviewer_name' => $this->request->getPost('interviewer_name'),
            'interview_date'   => $this->request->getPost('interview_date'),
            'interview_mode'   => $this->request->getPost('mode'),
            'ratings_data'     => json_encode($ratingsData),
            'overall_score'    => $this->request->getPost('overall_score') ?? 0,
            'feedback'         => json_encode([
                'strengths'  => $this->request->getPost('strengths'),
                'weaknesses' => $this->request->getPost('weaknesses'),
                'notes'      => $this->request->getPost('notes'),
            ]),
            'expected_salary'  => $this->request->getPost('expected_salary'),
            'notice_period'    => $this->request->getPost('notice_period'),
            'current_salary'   => $this->request->getPost('current_salary'),
            'joining_date'     => $this->request->getPost('joining_date') ?: null,
            'recommendation'   => $this->request->getPost('recommendation'),
            'next_step'        => $this->request->getPost('next_step'),
            'final_remarks'    => $this->request->getPost('final_remarks'),
        ]);

        return redirect()->to('/assessment')->with('success', 'Assessment submitted successfully!');
    }

    public function edit($id)
    {
        $db = \Config\Database::connect();
        $model = new \App\Models\InterviewAssessmentModel();
        $data['assessment'] = $model->find($id);
        if (!$data['assessment']) {
            return redirect()->to('/assessment')->with('error', 'Assessment not found.');
        }

        $builder = $db->table('interviews i');
        $builder->select('i.*, c.candidate_name, j.job_title as position_applied_for, d.department_name, CONCAT(ui.firstname, " ", ui.lastname) as interviewer_name');
        $builder->join('candidate c', 'c.id = i.candidate_id', 'left');
        $builder->join('jobs j', 'j.id = COALESCE(i.job_id, c.job_id)', 'left');
        $builder->join('department d', 'd.id = j.department_id', 'left');
        $builder->join('user_info ui', 'ui.user_id = i.interviewer_id', 'left');
        $data['interviews'] = $builder->get()->getResultArray();

        $deptBuilder = $db->table('department');
        $data['departments'] = $deptBuilder->get()->getResultArray();

        return view('assessments/form', $data);
    }

    public function update($id)
    {
        $model = new \App\Models\InterviewAssessmentModel();
        $post = $this->request->getPost();
        $ratingsData = [];
        foreach ($post as $key => $val) {
            if (strpos($key, 'criteria_title_') === 0) {
                $index = str_replace('criteria_title_', '', $key);
                $ratingsData[] = [
                    'title'   => $val,
                    'desc'    => $post['criteria_desc_' . $index] ?? '',
                    'rating'  => $post['rating_' . $index] ?? 0,
                    'comment' => $post['comment_' . $index] ?? '',
                ];
            }
        }
        $model->update($id, [
            'interview_id'     => $this->request->getPost('interview_id'),
            'job_title'        => $this->request->getPost('job_title'),
            'department'       => $this->request->getPost('department'),
            'interview_round'  => $this->request->getPost('interview_round'),
            'interviewer_name' => $this->request->getPost('interviewer_name'),
            'interview_date'   => $this->request->getPost('interview_date'),
            'interview_mode'   => $this->request->getPost('mode'),
            'ratings_data'     => json_encode($ratingsData),
            'overall_score'    => $this->request->getPost('overall_score') ?? 0,
            'feedback'         => json_encode([
                'strengths'  => $this->request->getPost('strengths'),
                'weaknesses' => $this->request->getPost('weaknesses'),
                'notes'      => $this->request->getPost('notes'),
            ]),
            'expected_salary'  => $this->request->getPost('expected_salary'),
            'notice_period'    => $this->request->getPost('notice_period'),
            'current_salary'   => $this->request->getPost('current_salary'),
            'joining_date'     => $this->request->getPost('joining_date') ?: null,
            'recommendation'   => $this->request->getPost('recommendation'),
            'next_step'        => $this->request->getPost('next_step'),
            'final_remarks'    => $this->request->getPost('final_remarks'),
        ]);
        return redirect()->to('/assessment')->with('success', 'Assessment updated successfully!');
    }

    public function delete($id)
    {
        $model = new \App\Models\InterviewAssessmentModel();
        $model->delete($id);
        return redirect()->to('/assessment')->with('success', 'Assessment deleted successfully!');
    }

    public function show($id)
    {
        $model = new \App\Models\InterviewAssessmentModel();
        $assessment = $model->find($id);
        if (!$assessment) {
            return redirect()->to('/assessment')->with('error', 'Assessment not found.');
        }
        $assessment['ratings_data'] = json_decode($assessment['ratings_data'] ?? '[]', true);
        return view('assessments/view', ['assessment' => $assessment]);
    }

    public function exportExcel()
    {
        // Placeholder data since assessments aren't saved to DB yet
        $records = [
            ['candidate_name' => 'Riya Patel', 'job_title' => 'Sales Executive', 'interview_round' => 'Technical', 'score' => '4.0 / 5']
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Assessments');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Candidate Name',
            'C1' => 'Job Title',
            'D1' => 'Interview Round',
            'E1' => 'Score'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => Color::COLOR_WHITE]],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E75C25'], // The requested orange background
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]
                ]
            ]);
            $sheet->getColumnDimension(substr($cell, 0, 1))->setAutoSize(true);
        }

        $rowNum = 2;
        foreach ($records as $index => $rec) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $rec['candidate_name'] ?? '-');
            $sheet->setCellValue('C' . $rowNum, $rec['job_title'] ?? '-');
            $sheet->setCellValue('D' . $rowNum, $rec['interview_round'] ?? '-');
            $sheet->setCellValue('E' . $rowNum, $rec['score'] ?? '-');

            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]
                ]
            ]);
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Assessments_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit();
    }
}
