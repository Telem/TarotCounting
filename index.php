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
  
  <script src="index.js"></script>
  <script src="tarot.js"></script>
</head>

<body>

<?php
include 'templates/header.php';
?>

</body>
</html>

<?php 
mysql_close($dblink);
?>
