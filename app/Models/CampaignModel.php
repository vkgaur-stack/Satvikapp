<?php

namespace App\Models;

class CampaignModel extends BaseModel
{
    protected $table         = 'campaigns';
    protected $allowedFields = ['name', 'category', 'target_goal', 'start_date', 'end_date', 'description'];
}
