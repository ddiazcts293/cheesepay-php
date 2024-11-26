<?php

require_once __DIR__ . '/models/school_year.php';

header('Content-Type: text/json');

$year = SchoolYear::get();
$years = SchoolYear::get_all();

$fee1 = $year->get_uniform_fee('prima', 2);
$fee2 = $year->get_uniform_fee('secun');

//echo $fee->to_json_string();
echo array_to_json($years);
//echo convert_array_to_json($current_monthlies);
