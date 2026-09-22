<?php

namespace App\Models;

use CodeIgniter\Model;

/** Common behaviour: array rows, timestamps and soft deletes (records are never physically removed). */
abstract class BaseModel extends Model
{
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $protectFields  = true;
}
