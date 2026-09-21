<?php

namespace App\Controllers;

use App\Models\Beneficiary as BeneficiaryModel;

class Beneficiary extends BaseController
{
    protected $beneficiaryModel;

    public function __construct()
    {
        $this->beneficiaryModel = new BeneficiaryModel();
    }

    // List all beneficiaries
    public function index()
    {
        $beneficiaries = $this->beneficiaryModel->withDeleted(false)->findAll();
        
        return view('beneficiaries/index', [
            'beneficiaries' => $beneficiaries,
            'title' => 'Beneficiaries'
        ]);
    }

    // Show create form
    public function create()
    {
        return view('beneficiaries/create', [
            'title' => 'Register New Beneficiary'
        ]);
    }

    // Store beneficiary
    public function store()
    {
        $data = [
            'first_name'      => $this->request->getPost('first_name'),
            'last_name'       => $this->request->getPost('last_name'),
            'phone'           => $this->request->getPost('phone'),
            'email'           => $this->request->getPost('email'),
            'gender'          => $this->request->getPost('gender'),
            'date_of_birth'   => $this->request->getPost('date_of_birth'),
            'address'         => $this->request->getPost('address'),
            'unique_id'       => $this->request->getPost('unique_id'),
            'unique_id_type'  => $this->request->getPost('unique_id_type'),
            'latitude'        => $this->request->getPost('latitude'),
            'longitude'       => $this->request->getPost('longitude'),
            'status'          => $this->request->getPost('status') ?? 'registered',
        ];

        if ($this->beneficiaryModel->insert($data)) {
            return redirect()->to('/beneficiaries')->with('success', 'Beneficiary registered successfully!');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->beneficiaryModel->errors());
        }
    }

    // Show single beneficiary
    public function show($id)
    {
        $beneficiary = $this->beneficiaryModel->find($id);
        
        if (!$beneficiary) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Beneficiary not found');
        }

        return view('beneficiaries/show', [
            'beneficiary' => $beneficiary,
            'title' => 'Beneficiary Details'
        ]);
    }

    // Show edit form
    public function edit($id)
    {
        $beneficiary = $this->beneficiaryModel->find($id);
        
        if (!$beneficiary) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Beneficiary not found');
        }

        return view('beneficiaries/edit', [
            'beneficiary' => $beneficiary,
            'title' => 'Edit Beneficiary'
        ]);
    }

    // Update beneficiary
    public function update($id)
    {
        $beneficiary = $this->beneficiaryModel->find($id);
        
        if (!$beneficiary) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Beneficiary not found');
        }

        $data = [
            'first_name'      => $this->request->getPost('first_name'),
            'last_name'       => $this->request->getPost('last_name'),
            'email'           => $this->request->getPost('email'),
            'gender'          => $this->request->getPost('gender'),
            'date_of_birth'   => $this->request->getPost('date_of_birth'),
            'address'         => $this->request->getPost('address'),
            'unique_id'       => $this->request->getPost('unique_id'),
            'unique_id_type'  => $this->request->getPost('unique_id_type'),
            'latitude'        => $this->request->getPost('latitude'),
            'longitude'       => $this->request->getPost('longitude'),
            'status'          => $this->request->getPost('status'),
        ];

        if ($this->beneficiaryModel->update($id, $data)) {
            return redirect()->to('/beneficiaries/' . $id)->with('success', 'Beneficiary updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->beneficiaryModel->errors());
        }
    }

    // Delete beneficiary (soft delete)
    public function delete($id)
    {
        $beneficiary = $this->beneficiaryModel->find($id);
        
        if (!$beneficiary) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Beneficiary not found');
        }

        if ($this->beneficiaryModel->delete($id)) {
            return redirect()->to('/beneficiaries')->with('success', 'Beneficiary deleted successfully!');
        }
    }
}