<?php

namespace App\Services\SolutionType;
class Manualy implements SolutionInterface
{
	protected $squareSequence = [];
	protected $sequence;
	protected $steps;

	function __construct(String $sequence, Array $steps)
	{
		$this->sequence       = $sequence;
		$this->steps          = $steps;
		$this->squareSequence = $this->sequenceToSquare();
	}
	private function sequenceToSquare() : Array
	{
		$squareSequence = [];
		$sequenceLength = mb_strlen($this->sequence);
		$sequencePos = 0;
		for ($posX = 0; $posX < sqrt($sequenceLength); $posX ++){
			$squareSequence[$posX] = [];
			for ($posY = 0; $posY < sqrt($sequenceLength); $posY ++){
				$squareSequence[$posX][] = $this->sequence[$sequencePos ++];
			}
		}
		return $squareSequence;
	}
	private function squareToSequence() : String
	{
		$strSequence = '';
		foreach ($this->squareSequence as $row) {
			$strSequence .= implode('', $row);
		}
		return $strSequence;
	}
	private function isValidStep(Array $step): bool
	{
		$fromX = $step['from']['x'];
		$fromY = $step['from']['y'];
		$toX   = $step['to']['x'];
		$toY   = $step['to']['y'];

		if ((abs($fromX - $toX + $fromY - $toY) != 1)
			|| $this->squareSequence[$toX][$toY] != '_'){
			return false;
		}
		return true;
	}
	private function move(Array $step) : void
	{
		$fromX = $step['from']['x'];
		$fromY = $step['from']['y'];
		$toX   = $step['to']['x'];
		$toY   = $step['to']['y'];

		$movePointValue = $this->squareSequence[$toX][$toY];
		$this->squareSequence[$toX][$toY] = $this->squareSequence[$fromX][$fromY];
		$this->squareSequence[$fromX][$fromY] = $movePointValue;
	}
	public function solve()
	{
		foreach ($this->steps as $step) {
			if (!$this->isValidStep($step)){
				return false;
			}
			$this->move($step);
		}
		return $this->squareToSequence();
	}
}