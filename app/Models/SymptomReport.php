<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymptomReport extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'report_text',
        'ai_severity',
        'ai_red_flag',
        'ai_safety_override',
        'extracted_symptoms',
        'reported_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ai_red_flag' => 'boolean',
            'ai_safety_override' => 'boolean',
            'extracted_symptoms' => 'array',
            'reported_at' => 'datetime',
        ];
    }

    /**
     * Get the patient that owns the symptom report.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
