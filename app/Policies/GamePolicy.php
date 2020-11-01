<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Game;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
	use HandlesAuthorization;

	public function edit(User $user, Game $game)
	{
		return $game->user->id == $user->id;
	}
}
