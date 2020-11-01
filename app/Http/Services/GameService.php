<?php
namespace App\Services;
use Illuminate\Support\Str;

class GameService
{
	public function isValidSequence($sequence)
	{
		$sequenceLength = mb_strlen($sequence);
		if (filter_var(sqrt($sequenceLength), FILTER_VALIDATE_INT)){
			return true;
		}
		return false;
	}
	public function generate()
	{
		$sequence = Str::random(16);
		$sequence[mb_strlen($sequence) - 1] = '_';
		return $sequence;
	}
	public function shuffle($sequence)
	{
		return str_shuffle($sequence);
	}
}