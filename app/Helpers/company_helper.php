<?php

use App\Models\CompanyLogoModel;

if (!function_exists('getCompanyLogo')) {
    function getCompanyLogo()
    {
        $companyModel = new CompanyLogoModel();
        $company = $companyModel->first(); // Fetch the first record

        return !empty($company['logo_img']) 
            ? base_url('upload/' . $company['logo_img']) 
            : base_url(env('ImagePath') . 'assets/images/fab_logo.png');
    }
}

if (!function_exists('getCompanyFavicon')) {
    function getCompanyFavicon()
    {
        $companyModel = new CompanyLogoModel();
        $company = $companyModel->first(); // Fetch the first record

        return !empty($company['favicon_icon']) 
            ? base_url('upload/' . $company['favicon_icon']) 
            : base_url(env('ImagePath') . 'assets/images/fab_fav_icon.png');
    }
}

if (!function_exists('getCompanyPdfLogo')) {
    function getCompanyPdfLogo()
    {
        $companyModel = new CompanyLogoModel();
        $company = $companyModel->first(); // Fetch the first record

        if (!empty($company['pdf_logo'])) {
            return base_url('upload/' . $company['pdf_logo']);
        }
        return getCompanyLogo();
    }
}

if (!function_exists('getCompanyName')) {
    function getCompanyName()
    {
        $companyModel = new CompanyLogoModel();
        $company = $companyModel->first(); // Fetch the first record

        return !empty($company['company_name']) 
            ? $company['company_name'] 
            : 'Fablead Developers Technolab';
    }
}

if (!function_exists('getDefaultProfileImage')) {
    function getDefaultProfileImage()
    {
        return base_url(env('ImagePath') . 'assets/images/default_avatar.png');
    }
}

if (!function_exists('getUserProfileImage')) {
    function getUserProfileImage($profileImage = null)
    {
        if (!empty($profileImage) && $profileImage !== '1789966027_54c5a38ccda20f7c2bac.jpg') {
            $rootUpload = ROOTPATH . 'upload/' . $profileImage;
            $fcUpload = defined('FCPATH') ? FCPATH . 'upload/' . $profileImage : '';
            if (file_exists($rootUpload) || (!empty($fcUpload) && file_exists($fcUpload))) {
                return base_url('upload/' . $profileImage);
            }
        }
        return getDefaultProfileImage();
    }
}

