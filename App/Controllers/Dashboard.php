<?php

namespace App\Controllers;

use App\Models\Beneficiary as BeneficiaryModel;
use App\Models\Donor as DonorModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $beneficiaryModel = new BeneficiaryModel();
        $donorModel = new DonorModel();

        $stats = [
            'total_beneficiaries' => $beneficiaryModel->countAll(),
            'active_beneficiaries' => $beneficiaryModel->where('status', 'active')->countAllResults(),
            'total_donors' => $donorModel->countAll(),
            'total_contributions' => $donorModel->selectSum('total_contribution')->get()->getRow()->total_contribution ?? 0,
        ];

        return view('dashboard', ['stats' => $stats, 'title' => 'Dashboard']);
    }
}