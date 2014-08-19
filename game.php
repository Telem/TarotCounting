<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'dbsupport.php';

$dblink = tarot_connect();

$players = load_table('players', $dblink);
$roles = load_table('roles', $dblink);
$bids = load_table('bids', $dblink);
$achievements = load_table('achievements', $dblink);
$achievement_kinds = load_table('achievement_kind', $dblink);
$contracts = load_table('contracts', $dblink);

?>

<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Tarot scoring interface</title>
  <link rel="stylesheet" href="css/common.css?v=1.0">
  <link rel="stylesheet" href="css/game.css?v=1.0">
  <meta name="viewport" content="width=device-width">

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
  
  <script src="game.js"></script>
  <script src="tarot.js"></script>
</head>

<body>

<?php
include 'templates/header.php';
?>

<ul id="allplayers">
<?php
	foreach ($players as $player) {
		echo "<li class='player' data-dbid='{$player['id']}'><span class='name'>{$player['name']}</span><ul class='tokens-container'></ul></li>";
	}
?>
</ul>

<div class="right-side">

<ul id="tokens" class="tokens">
<?php
//exclusive mean each player can have one token only of that exclusion-class
//unique means one token can be assigned to one person only. non-unique means it can be duplicated for each player
foreach($roles as $role) {
	if ($role['name'] == 'Defender') {
		continue;
	}
	$active = ($role['name'] == 'Callee')?' inactive':'';
	echo "<li class='token role exclusive unique{$active}' data-exclusion-class='role' data-dbid='{$role['id']}'>{$role['name']}</li>";
}
foreach($bids as $bid) {
	if ($bid['name'] == 'Pass') {
		continue;
	}
	echo "<li class='token bid exclusive unique' data-exclusion-class='bid' data-dbid='{$bid['id']}'>{$bid['name']}</li>";
}
foreach($achievements as $achievement) {
	$kindname = $achievement_kinds[$achievement['kind']]['name'];
	$exclusive = ($kindname == 'slam')?' exclusive':'';
	$unique = ($kindname == 'slam')?' unique':'';
	echo "<li class='token achievement {$kindname}{$exclusive}{$unique}' data-dbid='{$achievement['id']}'>{$achievement['name']}</li>";
}
?>
</ul>
</div>
</div>

<div id='scoring'>
<div class="contract">Contract
<select name="contract"><?php
	foreach ($contracts as $contract) {
		echo "<option value={$contract['value']} data-dbid={$contract['value']}>{$contract['name']}</option>";
	}
?></select></div>
<div class="score">Score<input type="number" name="score" /></div>
<input class="submitter" type="button" value="Send score" name="score_submission" />
<div class="submission_notice"></div>
<ul id='game_score'></ul>
</div>

</body>
</html>

<?php 
mysql_close($dblink);
?>
