<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_title',
        'department',
        'research_area',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
