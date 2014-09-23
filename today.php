<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'dbsupport.php';

$dblink = tarot_connect();

$allTimeScores = load_query("SELECT player, SUM(player_score) as player_score FROM game_insight GROUP BY player_id ORDER BY player_score DESC", $dblink);

$players = load_query("
	SELECT DISTINCT players.name 
	FROM players 
		JOIN game_players ON (players.id = game_players.player_id) 
		JOIN games ON (game_players.game_id = games.id)
	WHERE DATE(games.date) = DATE(NOW())", $dblink);
$players = call_user_func_array('array_merge', $players);

$todayScoreTable = score_array(load_query("SELECT game_id, TIME(date) AS date, contract, score, players.name AS player_name, Player_Game_Score(game_id, player_id) AS player_score
	FROM game_players
		JOIN games ON (game_players.game_id = games.id)
		JOIN players ON (game_players.player_id = players.id)
	WHERE DATE(date) = DATE(NOW())
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

<h1>Today's score</h1>
<?php
echo "<h2>Game by game score</h2>";
echo table_to_html($todayScoreTable);
echo "<h2>Game by game tally</h2>";
echo table_to_html($todayScoreTally);
?>

<h1>All time scores</h1>
<?php
echo table_to_html($allTimeScores);
?>

</body>
</html>

<?php 
mysql_close($dblink);
?>
