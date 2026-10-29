<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusChange extends Model
{
    protected $fillable = [
        'employee_name', 'nik', 'join_date',
        'current_position', 'current_division', 'current_department',
        'current_grade', 'current_work_location', 'current_employment_status',
        'current_marital_dependents', 'current_salary', 'current_benefit',
        'current_description',
        'proposed_position', 'proposed_division', 'proposed_department',
        'proposed_grade', 'proposed_work_location', 'proposed_employment_status',
        'proposed_marital_dependents', 'proposed_salary', 'proposed_benefit',
        'proposed_description', 'effective_date', 'proposed_by',
        'origin_department_head', 'destination_department_head',
        'hcga_verifier', 'director_approver',
    ];

    protected $dates = ['join_date', 'effective_date'];

    public function getMaritalOptions()
    {
        return [
            'TK/0' => 'Tidak kawin (TK/0)',
            'TK/1' => 'Tidak kawin (TK/1)',
            'TK/2' => 'Tidak kawin (TK/2)',
            'TK/3' => 'Tidak kawin (TK/3)',
            'K/0' => 'Kawin (K/0)',
            'K/1' => 'Kawin (K/1)',
            'K/2' => 'Kawin (K/2)',
            'K/3' => 'Kawin (K/3)',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            if (!$model->form_number) {
                $model->form_number = 'PS-' . date('Ym') . '-' . str_pad($model->id, 5, '0', STR_PAD_LEFT);
                $model->saveOrFail();
            }
        });
    }
}
