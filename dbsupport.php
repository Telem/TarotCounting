<?php

function tarot_connect() {
	$settings = parse_ini_file('tarotdb.ini');
	$dblink = mysql_connect($settings['host'], $settings['user'], $settings['password']);
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

