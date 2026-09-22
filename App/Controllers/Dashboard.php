<?php

namespace App\Controllers;

use App\Libraries\Metrics;

class Dashboard extends BaseController
{
    public function index()
    {
        if (! can('dashboard.view')) {
            return $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
        }

        return view('dashboard/index');
    }

    /** JSON feed for the React dashboard widgets. */
    public function data()
    {
        if (! can('dashboard.view')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        return $this->response->setJSON(Metrics::dashboard());
    }
}
