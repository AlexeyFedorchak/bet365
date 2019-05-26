<?php

namespace App;

class MarketsOddConverter
{
	protected static $merketOdds = [
		'1_1' => '1X2, Full Time Result',
		'1_3' => 'O/U, Goal Line',
		'1_5' => '1st Half Asian Handicap',
		'1_7' => '1st Half Asian Corners',
		'18_1' => 'Money Line',
		'18_3' => 'Total Points',
		'18_5' => 'Spread (Half)',
		'18_7' => 'Quarter - Winner (2-Way)',
		'18_9' => 'Quarter - Total (2-Way)',
		'*_1' => 'Match Winner 2-Way',
		'*_2' => 'Asian Handicap',
		'*_3' => 'Over/Under',
		'3_4' => 'Draw No Bet',
		'1_2' => 'Asian Handicap',
		'1_4' => 'Asian Corners',
		'1_6' => '1st Half Goal Line',
		'1_8' => 'Half Time Result',
		'18_2' => 'Spread',
		'18_4' => 'Money Line (Half)',
		'18_6' => 'Total Points (Half)',
		'18_8' => 'Quarter - Handicap',
	];

	public static function convert(string $key)
	{
		return self::$merketOdds[$key] ?? 'Undefined odd';
	}
}
