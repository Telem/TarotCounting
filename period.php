<?php
require_once 'dbsupport.php';

//variables exported by this script
$periodStart = $periodEnd = $periodMatcher = null;


switch (@$_GET['period']) {
	case 'season':
		$periodDBLink = tarot_connect();
		$r = load_query("SELECT `start`, `end` FROM seasons ORDER BY `end` DESC LIMIT 1", $periodDBLink);
		mysql_close($periodDBLink);
		$periodStart = "'".$r[0]['start']."'";
		$periodEnd = "'".$r[0]['end']."'";
		break;
	default:
		$matches = array();
		preg_match(',([0-9-]+)?/([0-9-]+)?,', $_GET['period'], $matches);
		$startStr = mysql_real_escape_string($matches[1]);
		$endStr = mysql_real_escape_string($matches[2]);
		if ($matches[1]) {
			$periodStart = "'".$startStr."'";
		}
		if ($matches[2]) {
			$periodEnd = "'".$endStr."'";
		}
		break;
	case 'today':
		$periodStart = "NOW()";
		break;
	case null:
	case 'alltime':
		break;
}

$periodStmts = array();
if ($periodStart) {
	$periodStmts[] = "DATE(date) >= DATE(".$periodStart.")";
}
if ($periodEnd) {
	$periodStmts[] = "DATE(date) <= DATE(".$periodEnd.")";
}
if (count($periodStmts) == 0) {
	$periodStmts[] = "TRUE";
}
$periodMatcher = implode(" AND ", $periodStmts);


?>
