<?php

namespace App\Models;

use App\Enums\CommitteeRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefenseCommitteeMember extends Model
{
    use HasFactory;

    protected $fillable = ['professor_id', 'role'];

    protected function casts(): array
    {
        return ['role' => CommitteeRole::class];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }
}
