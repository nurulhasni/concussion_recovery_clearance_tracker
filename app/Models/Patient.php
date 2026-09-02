<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'injury_type',
        'injury_date',
        'current_stage',
        'activity_step',
        'parent_email',
        'login_token',
        'login_token_expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'injury_date' => 'date',
            'current_stage' => 'integer',
            'activity_step' => 'integer',
            'login_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the symptom reports for the patient.
     */
    public function symptomReports(): HasMany
    {
        return $this->hasMany(SymptomReport::class)->orderBy('reported_at', 'desc');
    }

    /**
     * Get the approval links for the patient.
     */
    public function approvalLinks(): HasMany
    {
        return $this->hasMany(ApprovalLink::class);
    }

    /**
     * Get the approval records for the patient.
     */
    public function approvalRecords(): HasMany
    {
        return $this->hasMany(ApprovalRecord::class)->orderBy('decided_at', 'desc');
    }
}
