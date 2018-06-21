<?php

namespace App\Policies;

use App\Models\Status;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatusPolicy
{
    use HandlesAuthorization;

    //删除微博必须是作者
    public function destroy(User $user,Status $status)
    {
        return $user->id === $status->user_id;
    }
}
