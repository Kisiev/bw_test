<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;
use App\Models\Game;
use App\Http\Requests\GameSolveRequest;
use App\Services\SolutionType\Manualy as ManualSolution;
class GameController extends Controller
{
	const GAME_PREFIX = 'mixed_game_sequence_';
	const START_TIME = 'start_time_';

	public function create(Request $request)
	{
		$sequence = $request->get('sequence', '');

		$gameService = new GameService;
		if (empty($sequence)){
			$sequence = $gameService->generate();
		}
		if (!$gameService->isValidSequence($sequence)){
			return response()->json(['message' => 'Некорректные данные'], 422);
		}
		$user = Auth::user();
		$mixedSequence = $gameService->shuffle($sequence);

		$game = $user->games()->create(['sequence' => $sequence]);
		Redis::set(self::GAME_PREFIX . $game->id, $mixedSequence);
		Redis::set(self::START_TIME . $game->id, time());

		return response()->json(['game_id' => $game->id, 'sequence' => $mixedSequence]);
	}
	public function solve(GameSolveRequest $request, Game $game)
	{
		$mixedSequence = Redis::get(self::GAME_PREFIX . $game->id);
		$steps = $request->get('steps');

		if (empty($mixedSequence)){
			return response()->json(['message' => 'Ошибка. Игра не найдена'], 500);
		}

		$startTime     = Redis::get(self::START_TIME . $game->id);
		$executionTime = date('H:i:s', time() - $startTime);

		$manualSolution   = new ManualSolution($mixedSequence, $steps);
		$sequenceSolution = $manualSolution->solve();

		if ($sequenceSolution == $game->sequence){
			$game->update(['execution_time' => $executionTime]);
			return response()->json(['message' => 'Успешно', 'execution_time' => $executionTime]);
		}
		return response()->json(['message' => 'Ошибка. Некорректное решение'], 500);
	}
}
