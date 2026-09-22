<?php

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Rbac;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('user')) {
            return redirect()->to(site_url('/'));
        }

        return view('auth/login');
    }

    public function attempt()
    {
        $throttler = service('throttler');
        if ($throttler->check('login_' . md5($this->request->getIPAddress()), 8, MINUTE) === false) {
            return redirect()->to(site_url('login'))->with('error', 'Too many sign-in attempts. Please wait a minute and try again.');
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        $user = db_connect()->table('users u')
            ->select('u.*, r.name AS role_name')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.email', $email)->where('u.deleted_at', null)
            ->get()->getRowArray();

        // Always run a hash comparison so response time does not reveal whether the email exists.
        $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforsaltfakehashfakehashfakehashfakehashfa';
        $ok   = password_verify($password, $hash) && $user && $user['status'] === 'active';

        if (! $ok) {
            Audit::log('login_failed', 'users', $user ? (int) $user['id'] : null, null, ['email' => $email], $user ? (int) $user['id'] : null);

            return redirect()->to(site_url('login'))->withInput()->with('error', 'Email or password is incorrect, or the account is disabled.');
        }

        session()->regenerate();
        session()->set('user', Rbac::sessionUser($user, $user['role_name']));
        db_connect()->table('users')->where('id', $user['id'])->update(['last_login_at' => date('Y-m-d H:i:s')]);
        Audit::log('login', 'users', (int) $user['id'], null, null, (int) $user['id']);

        return redirect()->to(site_url('/'));
    }

    public function logout()
    {
        $user = session()->get('user');
        Audit::log('logout', 'users', $user['id'] ?? null, null, null, $user['id'] ?? null);
        session()->destroy();

        return redirect()->to(site_url('login'))->with('success', 'You have been signed out.');
    }
}
