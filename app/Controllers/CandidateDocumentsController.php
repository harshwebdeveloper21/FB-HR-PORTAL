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

        $uploadPath = ROOTPATH . 'public/uploads/candidate_docs/' . $candidateId . '/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $files = $this->request->getFiles();
        $posts = $this->request->getPost();

        foreach ($files as $key => $file) {
            $docTitle = $posts['doc_title_' . $key] ?? null;
            $docSubtitle = $posts['doc_subtitle_' . $key] ?? null;

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);

                // Upsert record
                $existing = $db->table('candidate_documents')
                    ->where('candidate_id', $candidateId)
                    ->where('doc_key', $key)
                    ->get()->getRowArray();

                $updateData = [
                    'file_name'   => $file->getClientName(),
                    'file_path'   => 'uploads/candidate_docs/' . $candidateId . '/' . $newName,
                    'status'      => 'approved',
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
                if ($docTitle !== null) {
                    $updateData['doc_title'] = $docTitle;
                }
                if ($docSubtitle !== null) {
                    $updateData['doc_subtitle'] = $docSubtitle;
                }

                if ($existing) {
                    $db->table('candidate_documents')->where('id', $existing['id'])->update($updateData);
                } else {
                    $updateData['candidate_id'] = $candidateId;
                    $updateData['doc_key']      = $key;
                    $updateData['created_at']   = date('Y-m-d H:i:s');
                    $db->table('candidate_documents')->insert($updateData);
                }
            } else if ($docTitle !== null || $docSubtitle !== null) {
                // If doc title/subtitle was updated without uploading a new file
                $existing = $db->table('candidate_documents')
                    ->where('candidate_id', $candidateId)
                    ->where('doc_key', $key)
                    ->get()->getRowArray();

                $up = ['updated_at' => date('Y-m-d H:i:s')];
                if ($docTitle !== null) $up['doc_title'] = $docTitle;
                if ($docSubtitle !== null) $up['doc_subtitle'] = $docSubtitle;

                if ($existing) {
                    $db->table('candidate_documents')->where('id', $existing['id'])->update($up);
                }
            }
        }

        // Also check any posted doc_title_* or doc_subtitle_* that might not have a file attachment yet
        foreach ($posts as $postKey => $postVal) {
            if (strpos($postKey, 'doc_title_') === 0) {
                $key = str_replace('doc_title_', '', $postKey);
                $existing = $db->table('candidate_documents')
                    ->where('candidate_id', $candidateId)
                    ->where('doc_key', $key)
                    ->get()->getRowArray();

                if ($existing) {
                    $db->table('candidate_documents')->where('id', $existing['id'])->update([
                        'doc_title'  => $postVal,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } elseif (strpos($postKey, 'doc_subtitle_') === 0) {
                $key = str_replace('doc_subtitle_', '', $postKey);
                $formattedVal = (preg_match('/^\d{4}-\d{2}$/', trim($postVal)))
                    ? date('F Y', strtotime(trim($postVal) . '-01'))
                    : $postVal;

                $existing = $db->table('candidate_documents')
                    ->where('candidate_id', $candidateId)
                    ->where('doc_key', $key)
                    ->get()->getRowArray();

                if ($existing) {
                    $db->table('candidate_documents')->where('id', $existing['id'])->update([
                        'doc_subtitle' => $formattedVal,
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Document uploaded successfully!',
                'csrf_hash' => csrf_hash()
            ]);
        }
        return redirect()->to('/candidate-documents/' . $candidateId)->with('success', 'Documents uploaded successfully!');
    }

    public function remove()
    {
        $db = \Config\Database::connect();
        $candidateId = $this->request->getPost('candidate_id');
        $key = $this->request->getPost('doc_key');

        if ($candidateId && $key) {
            $existing = $db->table('candidate_documents')
                ->where('candidate_id', $candidateId)
                ->where('doc_key', $key)
                ->get()->getRowArray();

            if ($existing) {
                // Remove file
                $filepath = ROOTPATH . 'public/' . $existing['file_path'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                $db->table('candidate_documents')->where('id', $existing['id'])->delete();
                return $this->response->setJSON(['status' => 'success', 'message' => 'Document removed successfully!', 'csrf_hash' => csrf_hash()]);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to remove document.', 'csrf_hash' => csrf_hash()]);
    }
}
