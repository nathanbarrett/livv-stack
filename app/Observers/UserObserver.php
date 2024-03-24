<?php

namespace App\Observers;

use App\Models\User;
use App\Repositories\UserRepository;

class UserObserver
{
    public function __construct(private readonly UserRepository $users)
    {
        //
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (config('auth.email_verification.enabled')) {
            $this->users->createEmailVerificationToken($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
