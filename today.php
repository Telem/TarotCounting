<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'dbsupport.php';

$dblink = tarot_connect();

$r = mysql_query("SELECT player, SUM(player_score) as player_score FROM game_insight WHERE DATE(date) = DATE(NOW()) GROUP BY player_id ORDER BY player_score DESC", $dblink);
$scores = array();
while ($tuple = mysql_fetch_array($r, MYSQL_ASSOC)) {
	$scores[] = $tuple;
}

$r = mysql_query("SELECT player, SUM(player_score) as player_score FROM game_insight GROUP BY player_id ORDER BY player_score DESC", $dblink);
$allTimeScores = array();
while ($tuple = mysql_fetch_array($r, MYSQL_ASSOC)) {
	$allTimeScores[] = $tuple;
}

?>
<!doctype html>

<html lang="en">
<head>
<meta charset="utf-8">

<title>Tarot today's scores</title>
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

<h1>Today's score</h1>

<table>
<thead><tr><th>Player</th><th>Score</th></tr></thead>
<tbody>
<?php 
foreach ($scores as $score) {
	echo "<tr><td>{$score['player']}</td><td>{$score['player_score']}</td></tr>";
}
?>
</tbody>
</table>

<h1>All time scores</h1>

<table>
<thead><tr><th>Player</th><th>Score</th></tr></thead>
<tbody>
<?php 
foreach ($allTimeScores as $score) {
	echo "<tr><td>{$score['player']}</td><td>{$score['player_score']}</td></tr>";
}
?>
</tbody>
</table>

</body>
</html>

<?php 
mysql_close($dblink);
?>