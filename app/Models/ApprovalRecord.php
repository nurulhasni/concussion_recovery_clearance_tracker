<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRecord extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approval_link_id',
        'patient_id',
        'stage',
        'approver_role',
        'decision',
        'comments',
        'ai_recommendation',
        'decided_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stage' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * Get the patient associated with the approval record.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the approval link associated with the approval record.
     */
    public function approvalLink(): BelongsTo
    {
        return $this->belongsTo(ApprovalLink::class);
    }
}
