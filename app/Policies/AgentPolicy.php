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
        $agent = Agent::where('user_id',auth('api')->id())->first();

        if ($agent && $agent->status == Agent::STATUS_NORMAL) {
            return true;
        } else {
            return false;
        }
    }
}
