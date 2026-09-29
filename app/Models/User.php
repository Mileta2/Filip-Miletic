<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'must_change_password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function professorProfile(): HasOne
    {
        return $this->hasOne(ProfessorProfile::class);
    }

    public function mentoredTopics(): HasMany
    {
        return $this->hasMany(Topic::class, 'mentor_id');
    }

    public function selectedTopic(): HasOne
    {
        return $this->hasOne(Topic::class, 'student_id');
    }

    public function committeeMemberships(): HasMany
    {
        return $this->hasMany(DefenseCommitteeMember::class, 'professor_id');
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }
}
