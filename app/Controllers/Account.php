<?php

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\CurrentUser;

class Account extends BaseController
{
    public function password()
    {
        return view('account/password');
    }

    public function updatePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[10]|max_length[72]',
            'confirm_password' => 'required|matches[new_password]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }
        $db   = db_connect();
        $user = $db->table('users')->where('id', CurrentUser::id())->get()->getRowArray();
        if (! $user || ! password_verify((string) $this->request->getPost('current_password'), $user['password_hash'])) {
            return redirect()->back()->with('errors', ['current_password' => 'Current password is incorrect.']);
        }
        $db->table('users')->where('id', $user['id'])->update([
            'password_hash' => password_hash((string) $this->request->getPost('new_password'), PASSWORD_BCRYPT),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        Audit::log('password_change', 'users', (int) $user['id']);

        return redirect()->to(site_url('/'))->with('success', 'Password updated.');
    }
}
