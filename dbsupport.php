<?php

function tarot_connect() {
	include_once "dbcredentials.php";
	$dblink = mysql_connect($host, $user, $password);
	mysql_set_charset('utf8', $dblink);
	mysql_select_db('tarot', $dblink);
	return $dblink;
}

function load_table($tableName, $dbLink = null) {
	$r = mysql_query("SELECT * from {$tableName}", $dbLink);
	$result = array();
	while ($tuple = mysql_fetch_array($r, MYSQL_ASSOC)) {
		if (isset($tuple['id'])) {
			$result[$tuple['id']] = $tuple;
		}
		else {
			$result[] = $tuple;
		}
	}
	return $result;
}

function load_query($query, $dbLink = null) {
	$r = mysql_query($query, $dbLink);
	$result = array();
	while ($tuple = mysql_fetch_array($r, MYSQL_ASSOC)) {
		if (isset($tuple['id'])) {
			$result[$tuple['id']] = $tuple;
		}
		else {
			$result[] = $tuple;
		}
	}
	return $result;
}

function table_to_html(array $rows) {
	$result = '';
	$headers = current($rows);
	$result .= "<table><thead><tr>";
	foreach ($headers as $header => $bla) {
		$result .= "<th>{$header}</th>";
	}
	$result .= "</tr></thead><tbody>";
	foreach ($rows as $row) {
		$result .= "<tr>";
		foreach ($row as $val) {
			$result .= "<td>{$val}</td>";
		}
		$result .= "</tr>";
	}
	$result .= "</tbody></table>";
	return $result;
}

/**
 * Converts rows for games with game_id, date, contract, score, player_name, player_score to an array with a column for each player
 */
function score_array($game_rows) {
	$result = array();
	foreach ($game_rows as $r) {
		$result[$r['game_id']]['game'] = "{$r['date']} - {$r['score']} for {$r['contract']}";
		$result[$r['game_id']][$r['player_name']] = $r['player_score'];
	}
	foreach ($result as $k => $r) {
		$summary = $r['game'];
		unset($r['game']);
		ksort($r);
		$result[$k] = array_merge(array('game' => $summary), $r);
	}
	return $result;
}

function accumulate_rows($rows, array $keysToAccumulate) {
	$result = unserialize(serialize($rows));
	$lastRow = array();
	foreach ($result as &$r) {
		foreach ($r as $rk => &$rv) {
			if (in_array($rk, $keysToAccumulate)) {
				$rv += @$lastRow[$rk];
			}
		}
		$lastRow = $r;
	}
	return $result;
}


