<?php

require_once 'basics.php';
require_once 'dbsupport.php';

function report_bad_input() {
	header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
}

$dblink = tarot_connect();

$submission = json_decode(file_get_contents('php://input'));

if (!is_int($submission->contract) || !is_int($submission->score)) {
	report_bad_input();
}

mysql_query("INSERT INTO games(contract, score) VALUES({$submission->contract}, {$submission->score})", $dblink);
if (mysql_errno($dblink)) report_bad_input;
$game_id = mysql_insert_id($dblink);

$player_rows = array();
foreach ($submission->players as $player) {
	$player_rows[] = "({$game_id}, {$player->id}, {$player->bid}, {$player->role})";
}
mysql_query("INSERT INTO game_players(game_id, player_id, bid, role) VALUES".implode(',',$player_rows), $dblink);
if (mysql_errno($dblink)) report_bad_input;

$achievements = array();
foreach ($submission->players as $player) {
	foreach ($player->achievements as $achievement) {
		$achievements[] = "({$achievement}, {$game_id}, {$player->id})";
	}
}
if (count($achievements)) {
	mysql_query("INSERT INTO game_achievements(achievement_id, game_id, player_id) VALUES".implode(',',$achievements), $dblink);
	if (mysql_errno($dblink)) report_bad_input;
}

$result = array();
$r = mysql_query("SELECT player_id, Player_Game_Score(game_id, player_id) as score FROM game_players WHERE game_id = {$game_id}");
while ($arr = mysql_fetch_assoc($r)) {
	$result[] = array(
		'player_id' => (int)$arr['player_id'],
		'score' => (int)$arr['score'],
	);
}

header('Content-Type: application/json');
echo json_encode($result);

mysql_close();