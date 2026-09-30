<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DigitalSignatureController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $signatures = $db->table('digital_signatures')
            ->orderBy('is_default', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        $defaultSignature = null;
        foreach ($signatures as $sig) {
            if ($sig['is_default'] == 1) {
                $defaultSignature = $sig;
                break;
            }
        }

        return view('settings/digital_signature', [
            'signatures'       => $signatures,
            'defaultSignature' => $defaultSignature,
        ]);
    }

    public function save()
    {
        $db = \Config\Database::connect();

        $signeeName   = $this->request->getPost('signee_name');
        $designation  = $this->request->getPost('designation');
        $companyName  = $this->request->getPost('company_name');
        $isDefault    = $this->request->getPost('is_default') ? 1 : 0;
        $drawnData    = $this->request->getPost('signature_drawn_data');

        if (empty($signeeName)) {
            return redirect()->back()->with('error', 'Signee Name is required.');
        }

        $uploadPath = ROOTPATH . 'public/uploads/signatures/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $signaturePath = null;
        $stampPath     = null;

        // 1. Process Signature (Drawn Canvas vs Uploaded File)
        if (!empty($drawnData) && strpos($drawnData, 'data:image/') === 0) {
            // Save drawn signature base64 as PNG image
            $dataParts = explode(',', $drawnData);
            if (count($dataParts) === 2) {
                $decodedImg = base64_decode($dataParts[1]);
                $fileName   = 'sig_drawn_' . time() . '_' . rand(1000, 9999) . '.png';
                file_put_contents($uploadPath . $fileName, $decodedImg);
                $signaturePath = 'uploads/signatures/' . $fileName;
            }
        }

        // If file uploaded instead of drawn
        if (!$signaturePath) {
            $sigFile = $this->request->getFile('signature_file');
            if ($sigFile && $sigFile->isValid() && !$sigFile->hasMoved()) {
                $newName = $sigFile->getRandomName();
                $sigFile->move($uploadPath, $newName);
                $signaturePath = 'uploads/signatures/' . $newName;
            }
        }

        // 2. Process Stamp Uploaded File
        $stampFile = $this->request->getFile('stamp_file');
        if ($stampFile && $stampFile->isValid() && !$stampFile->hasMoved()) {
            $newName = $stampFile->getRandomName();
            $stampFile->move($uploadPath, $newName);
            $stampPath = 'uploads/signatures/' . $newName;
        }

        // Check total existing records count
        $count = $db->table('digital_signatures')->countAllResults();
        if ($count === 0) {
            $isDefault = 1;
        }

        if ($isDefault) {
            $db->table('digital_signatures')->update(['is_default' => 0]);
        }

        $db->table('digital_signatures')->insert([
            'signee_name'    => $signeeName,
            'designation'    => $designation,
            'company_name'   => $companyName,
            'signature_path' => $signaturePath,
            'stamp_path'     => $stampPath,
            'is_default'     => $isDefault,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/digital-signature')->with('success', 'Digital signature created successfully!');
    }

    public function setDefault($id)
    {
        $db = \Config\Database::connect();
        $db->table('digital_signatures')->update(['is_default' => 0]);
        $db->table('digital_signatures')->where('id', $id)->update(['is_default' => 1]);

        return redirect()->to('/digital-signature')->with('success', 'Default signature updated successfully!');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $db->table('digital_signatures')->where('id', $id)->delete();

        return redirect()->to('/digital-signature')->with('success', 'Signature deleted successfully!');
    }
}
