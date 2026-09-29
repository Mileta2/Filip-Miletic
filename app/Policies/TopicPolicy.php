<?php

namespace App\Policies;

use App\Enums\TopicStatus;
use App\Enums\UserRole;
use App\Models\Topic;
use App\Models\User;

class TopicPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole(UserRole::SuperAdmin) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Topic $topic): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Professor);
    }

    public function update(User $user, Topic $topic): bool
    {
        return $user->hasRole(UserRole::Professor) && $topic->mentor_id === $user->id;
    }

    public function delete(User $user, Topic $topic): bool
    {
        return $user->hasRole(UserRole::Professor)
            && $topic->mentor_id === $user->id
            && $topic->status === TopicStatus::Available;
    }

    public function release(User $user, Topic $topic): bool
    {
        return $user->hasRole(UserRole::Professor) && $topic->mentor_id === $user->id;
    }
}
