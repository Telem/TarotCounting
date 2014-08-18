<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'dbsupport.php';

$dblink = tarot_connect();

$r = mysql_query("SELECT 
	player_id,
	players.name AS player, 
	COUNT(game_id) AS games_count,
	SUM(Player_Game_Score(game_id, player_id)) AS player_score, 
	SUM(role = 1) AS attacks, 
	SUM(role = 2) AS defenses
	FROM game_players 
		JOIN players ON (game_players.player_id = players.id)
	GROUP BY player_id 
	ORDER BY player_score DESC", $dblink);
$stats = array();
while ($tuple = mysql_fetch_array($r, MYSQL_ASSOC)) {
	$stats[$tuple['player_id']] = $tuple;
}

$r = mysql_query("SELECT 
	player_id,
	AVG(Hand_Score(game_id)) AS avg_attack_hand_score
	FROM game_players
	WHERE role = 1
	GROUP BY player_id", $dblink);
while ($tuple = mysql_fetch_array($r, MYSQL_ASSOC)) {
	$stats[$tuple['player_id']]['avg_attack_hand_score'] = $tuple['avg_attack_hand_score'];
}

$player_average = load_query("SELECT players.name AS Player, role AS Role, COUNT(game_id) AS ' #games', AVG(player_score) AS 'Average score', MAX(player_score) AS 'Best win', MIN(player_score) AS 'Worst loss', SUM(IF(player_score>0,player_score,0)) AS 'Cumulated High', SUM(IF(player_score<0,player_score,0)) AS 'Cumulated Low' FROM player_insight JOIN players ON (player_id = players.id) GROUP BY player_id, role", $dblink);
$roles_averages = load_query("SELECT role AS Role, AVG(player_score) AS 'Average score' FROM player_insight GROUP BY role", $dblink);


?>
<!doctype html>

<html lang="en">
<head>
<meta charset="utf-8">

<title>Tarot player stats</title>
<link rel="stylesheet" href="css/index.css?v=1.0">

<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="jquery/jquery-2.1.1.min.js"></script>
<script src="jquery-ui-1.11.0/jquery-ui.min.js"></script>
<link rel="stylesheet" href="jquery-ui-1.11.0/jquery-ui.min.css">
<script src="tarot.js"></script>
</head>

<body>

<table>
<thead><tr><th>Player</th><th>All time score</th><th>Games played</th><th>Attacks</th><th>Defenses</th><th>Average hand score when attacking</th></tr></thead>
<tbody>
<?php 
foreach ($stats as $player_id => $stat) {
	echo "<tr><td>{$stat['player']}</td><td>{$stat['player_score']}</td><td>{$stat['games_count']}</td><td>{$stat['attacks']}</td><td>{$stat['defenses']}</td><td>{$stat['avg_attack_hand_score']}</td></tr>";
}
?>
</tbody>
</table>

<?php
echo table_to_html($player_average);
echo table_to_html($roles_averages);
?>

</body>
</html>

<?php 
mysql_close($dblink);
?>
