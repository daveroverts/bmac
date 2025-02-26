<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Faq;
use Illuminate\Auth\Access\HandlesAuthorization;

class FaqPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the faq.
     *
     * @return mixed
     */
    public function view(Faq $faq)
    {
        return $faq->is_online;
    }

    /**
     * Determine whether the user can create faqs.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the faq.
     */
    public function update(User $user, Faq $faq): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the faq.
     */
    public function delete(User $user, Faq $faq): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the faq.
     */
    public function restore(User $user, Faq $faq): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the faq.
     */
    public function forceDelete(User $user, Faq $faq): bool
    {
        return false;
    }
}
