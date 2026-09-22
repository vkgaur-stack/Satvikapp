<?php

namespace App\Models;

class ProgramModel extends BaseModel
{
    protected $table         = 'programs';
    protected $allowedFields = ['name', 'category', 'description', 'target_beneficiaries', 'budget', 'start_date', 'end_date', 'status', 'min_age', 'max_age', 'max_monthly_income', 'gender_target'];
}
