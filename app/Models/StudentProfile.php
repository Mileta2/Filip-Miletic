<?php

namespace App\Models;

use App\Enums\StudyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'index_number',
        'study_level',
        'date_of_birth',
        'phone',
        'city',
        'address',
        'study_year',
    ];

    protected function casts(): array
    {
        return [
            'study_level' => StudyLevel::class,
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
