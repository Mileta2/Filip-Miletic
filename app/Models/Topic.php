<?php

namespace App\Models;

use App\Enums\TopicStatus;
use App\Enums\TopicType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    use HasFactory;

    public const ACTIVE_MENTOR_SCOPE = 'active_mentor';

    protected $fillable = [
        'title',
        'course',
        'description',
        'type',
        'status',
        'mentor_id',
        'student_id',
        'pdf_path',
        'reserved_at',
        'defended_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TopicType::class,
            'status' => TopicStatus::class,
            'reserved_at' => 'datetime',
            'defended_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(self::ACTIVE_MENTOR_SCOPE, fn (Builder $query) => $query
            ->whereHas('mentor', fn (Builder $query) => $query->where('is_active', true)));
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id')->withTrashed();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id')->withTrashed();
    }

    public function committeeMembers(): HasMany
    {
        return $this->hasMany(DefenseCommitteeMember::class);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn (Builder $query) => $query->where('type', $type));
    }
}
