<?php

namespace App\Models;

class SurveyModel extends BaseModel
{
    protected $table         = 'surveys';
    protected $allowedFields = ['beneficiary_id', 'program_id', 'survey_type', 'monthly_income', 'outcome_score', 'notes', 'surveyed_on', 'created_by'];
}
