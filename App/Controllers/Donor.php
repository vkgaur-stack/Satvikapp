<?php

namespace App\Controllers;

use App\Models\Donor as DonorModel;

class Donor extends BaseController
{
    protected $donorModel;

    public function __construct()
    {
        $this->donorModel = new DonorModel();
    }

    // List all donors
    public function index()
    {
        $donors = $this->donorModel->withDeleted(false)->findAll();
        
        return view('donors/index', [
            'donors' => $donors,
            'title' => 'Donors'
        ]);
    }

    // Show create form
    public function create()
    {
        return view('donors/create', [
            'title' => 'Add New Donor'
        ]);
    }

    // Store donor in database
    public function store()
    {
        $data = [
            'name'                 => $this->request->getPost('name'),
            'type'                 => $this->request->getPost('type'),
            'email'                => $this->request->getPost('email'),
            'phone'                => $this->request->getPost('phone'),
            'address'              => $this->request->getPost('address'),
            'city'                 => $this->request->getPost('city'),
            'state'                => $this->request->getPost('state'),
            'zip_code'             => $this->request->getPost('zip_code'),
            'total_contribution'   => $this->request->getPost('total_contribution') ?? 0,
            'status'               => $this->request->getPost('status') ?? 'active',
        ];

        if ($this->donorModel->insert($data)) {
            return redirect()->to('/donors')->with('success', 'Donor added successfully!');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->donorModel->errors());
        }
    }

    // Show single donor
    public function show($id)
    {
        $donor = $this->donorModel->find($id);
        
        if (!$donor) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Donor not found');
        }

        return view('donors/show', [
            'donor' => $donor,
            'title' => 'Donor Details'
        ]);
    }

    // Show edit form
    public function edit($id)
    {
        $donor = $this->donorModel->find($id);
        
        if (!$donor) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Donor not found');
        }

        return view('donors/edit', [
            'donor' => $donor,
            'title' => 'Edit Donor'
        ]);
    }

    // Update donor
    public function update($id)
    {
        $donor = $this->donorModel->find($id);
        
        if (!$donor) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Donor not found');
        }

        $data = [
            'name'                 => $this->request->getPost('name'),
            'type'                 => $this->request->getPost('type'),
            'email'                => $this->request->getPost('email'),
            'phone'                => $this->request->getPost('phone'),
            'address'              => $this->request->getPost('address'),
            'city'                 => $this->request->getPost('city'),
            'state'                => $this->request->getPost('state'),
            'zip_code'             => $this->request->getPost('zip_code'),
            'total_contribution'   => $this->request->getPost('total_contribution'),
            'status'               => $this->request->getPost('status'),
        ];

        if ($this->donorModel->update($id, $data)) {
            return redirect()->to('/donors/' . $id)->with('success', 'Donor updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->donorModel->errors());
        }
    }

    // Delete donor (soft delete)
    public function delete($id)
    {
        $donor = $this->donorModel->find($id);
        
        if (!$donor) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Donor not found');
        }

        if ($this->donorModel->delete($id)) {
            return redirect()->to('/donors')->with('success', 'Donor deleted successfully!');
        }
    }
}