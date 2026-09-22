<?php

namespace App\Commands;

use App\Models\DonorModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Re-evaluates every donor's Active/Lapsed status, tier and lifetime value.
 * Schedule daily (cPanel cron):   php /path/to/satvikdaan/spark donors:refresh
 */
class RefreshDonorStatus extends BaseCommand
{
    protected $group       = 'Satvikdaan';
    protected $name        = 'donors:refresh';
    protected $description = 'Recalculate donor lifetime value, tier and lapsed status.';

    public function run(array $params)
    {
        $model = new DonorModel();
        $ids   = array_column($model->select('id')->findAll(), 'id');
        foreach ($ids as $id) {
            $model->refreshStats((int) $id);
        }
        CLI::write('Refreshed ' . count($ids) . ' donors.', 'green');
    }
}
