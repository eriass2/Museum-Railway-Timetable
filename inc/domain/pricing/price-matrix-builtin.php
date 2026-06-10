<?php
/**
 * 2026 Lennakatten fare table (Taxa 2026, lennakatten.se/biljetter).
 *
 * @package Museum_Railway_Timetable
 * @return array<string, array<string, array<int, int|null>>>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'single' => array(
		'adult'          => array(
			1 => 80,
			2 => 110,
			3 => 130,
		),
		'child_4_15'     => array(
			1 => 30,
			2 => 30,
			3 => 30,
		),
		'child_0_3'      => array(
			1 => 0,
			2 => 0,
			3 => 0,
		),
		'student_senior' => array(
			1 => 70,
			2 => 100,
			3 => 120,
		),
	),
	'return' => array(
		'adult'          => array(
			1 => 160,
			2 => 220,
			3 => 260,
		),
		'child_4_15'     => array(
			1 => 60,
			2 => 60,
			3 => 60,
		),
		'child_0_3'      => array(
			1 => 0,
			2 => 0,
			3 => 0,
		),
		'student_senior' => array(
			1 => 140,
			2 => 200,
			3 => 220,
		),
	),
	'day'    => array(
		'adult'          => array(
			1 => 280,
			2 => 280,
			3 => 280,
		),
		'child_4_15'     => array(
			1 => 80,
			2 => 80,
			3 => 80,
		),
		'child_0_3'      => array(
			1 => 0,
			2 => 0,
			3 => 0,
		),
		'student_senior' => array(
			1 => 260,
			2 => 260,
			3 => 260,
		),
	),
);
