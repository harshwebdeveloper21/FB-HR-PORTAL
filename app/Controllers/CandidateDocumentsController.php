<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class CandidateDocumentsController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $candidates = $db->table('candidate')
            ->select('id, candidate_name, email, phone_number')
            ->get()->getResultArray();

        return view('candidate/documents', ['candidates' => $candidates]);
    }

    public function show($candidateId)
    {
        $db = \Config\Database::connect();

        $candidate = $db->table('candidate c')
            ->select('c.*, j.job_title')
            ->join('jobs j', 'j.id = c.job_id', 'left')
            ->where('c.id', $candidateId)
            ->get()->getRowArray();

        if (!$candidate) {
            return redirect()->to('/candidate-documents')->with('error', 'Candidate not found.');
        }

        // Fetch existing uploaded documents for this candidate
        $docs = $db->table('candidate_documents')
            ->where('candidate_id', $candidateId)
            ->get()->getResultArray();

        // Index docs by doc_key for easy lookup
        $docsMap = [];
        foreach ($docs as $doc) {
            $docsMap[$doc['doc_key']] = $doc;
        }

        $candidates = $db->table('candidate')
            ->select('id, candidate_name, email')
            ->get()->getResultArray();

        return view('candidate/documents', [
            'candidates'      => $candidates,
            'selectedCandidate' => $candidate,
            'docsMap'         => $docsMap,
        ]);
    }

    public function upload()
    {
        $db = \Config\Database::connect();
        $candidateId = $this->request->getPost('candidate_id');

        if (!$candidateId) {
            return redirect()->back()->with('error', 'Please select a candidate first.');
        }

        // Define all expected document keys
        $docKeys = [
            'salary_1', 'salary_2', 'salary_3',
            'experience_letter', 'relieving_letter',
            'id_proof', 'edu_cert', 'other_doc'
        ];

        $uploadPath = ROOTPATH . 'public/uploads/candidate_docs/' . $candidateId . '/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        foreach ($docKeys as $key) {
            $file = $this->request->getFile($key);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);

                // Upsert record
                $existing = $db->table('candidate_documents')
                    ->where('candidate_id', $candidateId)
                    ->where('doc_key', $key)
                    ->get()->getRowArray();

                if ($existing) {
                    $db->table('candidate_documents')->where('id', $existing['id'])->update([
                        'file_name'   => $file->getClientName(),
                        'file_path'   => 'uploads/candidate_docs/' . $candidateId . '/' . $newName,
                        'status'      => 'approved',
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $db->table('candidate_documents')->insert([
                        'candidate_id' => $candidateId,
                        'doc_key'      => $key,
                        'file_name'    => $file->getClientName(),
                        'file_path'    => 'uploads/candidate_docs/' . $candidateId . '/' . $newName,
                        'status'       => 'approved',
                        'created_at'   => date('Y-m-d H:i:s'),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        return redirect()->to('/candidate-documents/' . $candidateId)->with('success', 'Documents uploaded successfully!');
    }
}
