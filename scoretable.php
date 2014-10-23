<?php
header('Content-Type: text/html; charset=utf-8');

require "period.php";

require_once 'dbsupport.php';

$dblink = tarot_connect();

$players = load_query("
	SELECT DISTINCT players.name AS name
	FROM players 
		JOIN game_players ON (players.id = game_players.player_id) 
		JOIN games ON (game_players.game_id = games.id)
	WHERE ${periodMatcher}", $dblink);
$newPlayers = array();
foreach($players as $r) {
	$newPlayers[] = $r['name'];
}
$players = $newPlayers;
unset($newPlayers);

$todayScoreTable = score_array(load_query("SELECT game_id, TIME(date) AS date, contract, score, players.name AS player_name, Player_Game_Score(game_id, player_id) AS player_score
	FROM game_players
		JOIN games ON (game_players.game_id = games.id)
		JOIN players ON (game_players.player_id = players.id)
	WHERE ${periodMatcher}
	ORDER BY games.date ASC", $dblink), $players);
//take the columns and remove the 'game' header (first) to find the player names
$todayScoreTally = accumulate_rows($todayScoreTable, $players);

?>
<!doctype html>

<html lang="en">
<head>
<meta charset="utf-8">

<title>Tarot today's scores</title>
  <link rel="stylesheet" href="css/common.css?v=1.0">
  <link rel="stylesheet" href="css/index.css?v=1.0">

  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  <script src="jquery/jquery-2.1.1.min.js"></script>
  <script src="jquery-ui-1.11.0/jquery-ui.min.js"></script>
  <script src="jquery/jquery.ui.touch-punch.min.js"></script>
  <link rel="stylesheet" href="jquery-ui-1.11.0/jquery-ui.min.css">
  
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
  <script src="tarot.js"></script>
</head>

<body>

<?php
include 'templates/header.php';

echo '<!--'.PHP_EOL;
print_r($players);
print_r($todayScoreTable);
print_r($todayScoreTally);
echo '-->'.PHP_EOL;

?>

<?php
echo "<h1>Game by game score</h1>";
echo table_to_html($todayScoreTable);
echo "<h1>Game by game tally</h1>";
echo table_to_html($todayScoreTally);
?>

</body>
</html>

<?php 
mysql_close($dblink);
?>
