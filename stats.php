<?php
header('Content-Type: text/html; charset=utf-8');

require "period.php";

require_once 'dbsupport.php';
$dblink = tarot_connect();


$r = mysql_query("SELECT 
	player_id,
	players.name AS player, 
	SUM(Player_Game_Score(game_id, player_id)) AS player_score, 
	COUNT(game_id) AS games_count,
	SUM(role = 1) AS attacks, 
	SUM(role = 2) AS defenses,
	AVG(bid != 1) AS `% bids`
	FROM game_players 
		JOIN players ON (game_players.player_id = players.id)
		JOIN games ON (game_players.game_id = games.id)
	WHERE ${periodMatcher}
	GROUP BY player_id 
	ORDER BY player_score DESC", $dblink);
$overview_stats = array();
while ($tuple = mysql_fetch_array($r, MYSQL_ASSOC)) {
	$overview_stats[$tuple['player_id']] = $tuple;
	$overview_stats[$tuple['player_id']]['avg_attack_hand_score'] = NULL;
	$overview_stats[$tuple['player_id']]['avg_attack_contract'] = NULL;
}

$r = mysql_query("SELECT 
	player_id,
	AVG(Hand_Score(game_id)) AS avg_attack_hand_score,
	AVG(contract) AS avg_attack_contract
	FROM game_players
		JOIN games ON (game_players.game_id = games.id)
	WHERE ${periodMatcher}
		AND role = 1
	GROUP BY player_id", $dblink);
while ($tuple = mysql_fetch_array($r, MYSQL_ASSOC)) {
	$overview_stats[$tuple['player_id']]['avg_attack_hand_score'] = $tuple['avg_attack_hand_score'];
	$overview_stats[$tuple['player_id']]['avg_attack_contract'] = $tuple['avg_attack_contract'];
}

$depreciated_scores = load_query("
	SELECT 
	players.name AS player, 
	SUM(Player_Game_Score_Depreciated(game_id, player_id)) AS player_score
	FROM game_players 
		JOIN players ON (game_players.player_id = players.id)
		JOIN games ON (game_players.game_id = games.id)
	WHERE ${periodMatcher}
	GROUP BY player_id 
	ORDER BY player_score DESC", $dblink);

$player_average = load_query("
	SELECT players.name AS Player, 
		role AS Role, 
		COUNT(game_id) AS ' #games', 
		AVG(player_score) AS 'Average score', 
		MAX(player_score) AS 'Best win', 
		MIN(player_score) AS 'Worst loss', 
		SUM(IF(player_score>0,player_score,0)) AS 'Cumulated High', 
		SUM(IF(player_score<0,player_score,0)) AS 'Cumulated Low',
		AVG(hand_score) AS 'Average hand score', 
		MAX(IF(player_score>0,hand_score,0)) AS 'Best hand score', 
		MIN(hand_score) AS 'Worst hand score'
	FROM player_insight 
		JOIN players ON (player_id = players.id) 
		JOIN games ON (player_insight.game_id = games.id)
	WHERE ${periodMatcher}
	GROUP BY player_id, role", $dblink);
$roles_averages = load_query("
	SELECT role AS Role, AVG(player_score) AS 'Average score' 
	FROM player_insight 
		JOIN games ON (player_insight.game_id = games.id)
	WHERE ${periodMatcher}
	GROUP BY role", $dblink);

$player_bids = load_query("
	SELECT players.name AS Player, 
		bids.name AS Bid, 
		SUM(game_players.bid = bids.id) AS 'Count', 
		SUM(score >= contract) AS 'won',
		SUM(score < contract) AS 'lost',
		AVG(score >= contract) AS 'win ratio',
		AVG(IF(score>=contract,score - contract,NULL)) AS 'average won by',
		AVG(IF(score<contract,contract - score,NULL)) AS 'average lost by'
	FROM game_players 
		JOIN bids ON (game_players.bid = bids.id) 
		JOIN players ON (game_players.player_id = players.id)
		JOIN games ON (game_players.game_id = games.id)
	WHERE game_players.role = 1 
		AND ${periodMatcher}
	GROUP BY game_players.player_id, game_players.bid", $dblink);

$players_attack_stats = load_query("
	SELECT players.name AS player, 
		contracts.name AS contract,
		SUM(score >= contract) / COUNT(*) AS 'win ratio',
		SUM(score >= contract) as won, 
		AVG(IF(score>=contract,score - contract,NULL)) AS 'average won by',
		SUM(score < contract) AS lost, 
		AVG(IF(score<contract,contract - score,NULL)) AS 'average lost by'
	FROM game_players 
		JOIN games on (games.id = game_players.game_id) 
		JOIN bids on (game_players.bid = bids.id) 
		JOIN roles on (game_players.role = roles.id) 
		JOIN players on (game_players.player_id = players.id) 
		JOIN contracts on (games.contract = contracts.value) 
	WHERE game_players.role = 1 
		AND ${periodMatcher}
	GROUP BY player, games.contract
	ORDER BY player ASC, games.contract DESC", $dblink);


$self_calling = load_query("
	SELECT date, players.name AS player, bids.name AS bid, score, contract
	FROM game_players
		JOIN games ON (game_players.game_id = games.id)
		JOIN bids ON (game_players.bid = bids.id)
		JOIN players ON (game_players.player_id = players.id)
	WHERE 
		game_id IN (
		SELECT game_id
			FROM game_players 
			GROUP BY game_id
				HAVING COUNT(player_id) >= 5 
				AND SUM(role = 3) = 0
		)
		AND ${periodMatcher}
		AND role = 1", $dblink);

$cutest_couples = load_query("
	SELECT attackers_details.name AS attacker, callees_details.name AS callee, COUNT(*)
	FROM games
		JOIN game_players AS attackers ON (games.id = attackers.game_id)
			JOIN players AS attackers_details ON (attackers.player_id = attackers_details.id)
		JOIN game_players AS callees ON (games.id = callees.game_id)
			JOIN players AS callees_details ON (callees.player_id = callees_details.id)
	WHERE attackers.role = 1
		AND callees.role = 3
		AND ${periodMatcher}
	GROUP BY attacker, callee", $dblink);

?>
<!doctype html>

<html lang="en">
<head>
<meta charset="utf-8">

<?php
if (@$_GET['period'] == 'today') {
	$htmlTitle = "Stats for today";
}
else if ($periodStart && $periodEnd) {
	if (@$_GET['period'] == 'season') {
		$htmlTitle = "Stats for this season ({$periodStart} to {$periodEnd})";
	}
	else {
		$htmlTitle = "Stats for {$periodStart} to {$periodEnd}";
	}
}
else if ($periodStart) {
	$htmlTitle = "Stats since {$periodStart}";
}
else if ($periodEnd) {
	$htmlTitle = "Stats until {$periodEnd}";
}
else {
	$htmlTitle = "Stats for all time";
}

?>
<title><?php echo $htmlTitle; ?></title>
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

  <link rel="stylesheet" href="css/common.css?v=1.0">
  <script src="common.js"></script>
  <link rel="stylesheet" href="css/index.css?v=1.0">

  <script src="tarot.js"></script>
</head>

<body>


<?php
include 'templates/header.php';
?>

<h1><?php echo $htmlTitle; ?></h1>

<div class="tab-summary"></div>

<div class="tab" data-groupname="Scores">
<?php 
echo table_to_html($overview_stats, array(
	'player' => 'Player', 
	'games_count' => 'Games played',
	'player_score' => 'Score', 
	'attacks' => 'Attacks', 
	'defenses' => 'Defenses',
	'% bids' => '% games with a bid',
	'avg_attack_hand_score' => 'Average hand score when attacking',
	'avg_attack_contract' => 'Average contract when attacking',
));
?>
</div>

<div class="tab" data-groupname="Depreciated scores">
<?php 
echo table_to_html($depreciated_scores, array(
	'player' => 'Player', 
	'player_score' => 'Depreciated score',
));
?>
</div>



<div class="tab" data-groupname="Attack statistics">
<?php
echo table_to_html($players_attack_stats);
?>
</div>


<div class="tab" data-groupname="Bids">
<?php
echo table_to_html($player_bids);
?>
</div>

<div class="tab" data-groupname="Averages by roles">
<?php
echo '<div class="roleperuser">'.table_to_html($player_average).'</div>';
echo table_to_html($roles_averages);
?>
</div>

<div class="tab" data-groupname="Self-calling">
<?php
echo table_to_html($self_calling);
?>
</div>

<div class="tab" data-groupname="Cutest couples">
<?php
echo table_to_html($cutest_couples);
?>
</div>

<div class="tab" data-groupname="Daily score scatter">
<?php

require_once 'SVGGraph/SVGGraph.php';

$dates = load_query("SELECT DISTINCT DATE(date) AS 'date' 
	FROM games
	WHERE ${periodMatcher}", $dblink);
$graphdata = load_query("SELECT DATE(date) AS 'date', players.name AS 'Player', SUM(player_score) AS daily_score
	FROM player_insight 
		JOIN games ON (games.id = player_insight.game_id)
		JOIN players ON (players.id = player_insight.player_id)
	WHERE ${periodMatcher}
	GROUP BY DATE(date), Player
	ORDER BY date ASC, Player ASC", $dblink);

$graphValues = array();
foreach ($dates as $daterow) {
	$graphValues['baseline'][$daterow['date']] = 0;
}
foreach ($graphdata as $row) {
	$graphValues[$row['Player']][$row['date']] = $row['daily_score'];
}
$settings = array(
	'legend_entries' => array_keys($graphValues),
	'legend_position' => 'outer right -5 40'
);
$graph = new SVGGraph(640, 480,$settings);
$graph->Values($graphValues);
echo $graph->Fetch('MultiScatterGraph', false);
echo $graph->FetchJavascript();
?>
</div>

<div class="tab" data-groupname="Cumulated score graph">
<?php

require_once 'SVGGraph/SVGGraph.php';

$dates = load_query("SELECT DISTINCT DATE(date) AS 'date' 
	FROM games 
	WHERE ${periodMatcher}", $dblink);
$graphdata = load_query("SELECT DATE(date) AS 'date', players.name AS 'Player', SUM(player_score) AS daily_score
	FROM player_insight 
		JOIN games ON (games.id = player_insight.game_id)
		JOIN players ON (players.id = player_insight.player_id)
	WHERE ${periodMatcher}
	GROUP BY players.name, DATE(date)
	ORDER BY Player ASC, date ASC", $dblink);

$graphValues = array();
foreach ($dates as $daterow) {
	$graphValues['baseline'][$daterow['date']] = 0;
}
$currentPlayer = null;
foreach ($graphdata as $row) {
	if ($row['Player'] != $currentPlayer) {
		if (isset($currentGraphLine)) {
			$graphValues[$currentPlayer] = $currentGraphLine;
		}
		$currentGraphLine = array();
		$currentPlayer = $row['Player'];
		$cumulatedScore = 0;
	}
	$currentGraphLine[$row['date']] = $cumulatedScore = $row['daily_score'] + $cumulatedScore;
}
if (isset($currentGraphLine)) {
	$graphValues[$currentPlayer] = $currentGraphLine;
}

$settings = array(
	'legend_entries' => array_keys($graphValues),
	'legend_position' => 'outer right -5 40'
);
$graph = new SVGGraph(640, 480,$settings);
$graph->Values($graphValues);
echo $graph->Fetch('MultiLineGraph', false);
echo $graph->FetchJavascript();
?>
</div>

</body>
</html>

<?php 
mysql_close($dblink);
?>
