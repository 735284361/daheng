<?php

namespace App\Policies;

use App\Models\Agent;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgentPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function isAgent(User $user)
    {
        return $user->is_agent == 1;
    }
}
