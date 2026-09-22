<?php

namespace App\Models;

class HouseholdModel extends BaseModel
{
    protected $table         = 'households';
    protected $allowedFields = ['beneficiary_id', 'members_count', 'monthly_income', 'latitude', 'longitude', 'notes', 'created_by'];
}
