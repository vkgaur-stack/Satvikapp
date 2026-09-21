<?php

namespace App\Controllers;

use App\Models\User as UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // Show login form
    public function login()
    {
        return view('auth/login');
    }

    // Process login
    public function authenticate()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Email not found');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Invalid password');
        }

        if ($user['status'] !== 'active') {
            return redirect()->back()->with('error', 'Your account is inactive');
        }

        // Set session
        $sessionData = [
            'id'      => $user['id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'role_id' => $user['role_id'],
            'logged_in' => true
        ];
        
        session()->set($sessionData);
        $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        return redirect()->to('/dashboard');
    }

    // Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login');
    }

    // Show registration form
    public function register()
    {
        return view('auth/register');
    }

    // Process registration
    public function storeUser()
    {
        $data = [
            'name'    => $this->request->getPost('name'),
            'email'   => $this->request->getPost('email'),
            'phone'   => $this->request->getPost('phone'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role_id' => $this->request->getPost('role_id') ?? 3, // Default to field_worker
            'status'  => 'active'
        ];

        if ($this->userModel->insert($data)) {
            return redirect()->to('/auth/login')->with('success', 'Registration successful! Please login.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }
    }
}