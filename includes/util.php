<?

function json_failure($errmsg) {
	$json = array(
		'success' => 0,
		'errmsg' => $errmsg
	);
	_json_out($json);
}

function json_success($data) {
	$json = array(
		'success' => 1,
		'data' => $data
	);
	_json_out($json);
}

function _json_out($json) {
	header('Content-type: application/json');
	print json_encode($json);
	exit;
}

function preit($s) {
	if (is_array($s)) {
		$s = var_export($s, true);
	}

	print "<pre>$s</pre>";
}


function check_param($param, $type, $args = null, $nojson = false) {

	if (is_null($args)) {
		$args = $_REQUEST;
	}

	$error_handler = 'json_failure';
	if ($nojson === true) {
		$error_handler = 'preit';
	}
	else if (is_null($nojson)) {
		$error_handler = 'noop';
	}

	if (!array_key_exists($param, $args)) {
		$error_handler("param $param was expected but was not passed");
		return;
	}

	$value = trim($args[$param]);

	if (empty($value) || ($value == '')) {
		$error_handler("param $param was present but was null or empty");
		return;
	}

	if ($type == 'int') {
		$int_value = (int)$value;
		if (!is_int($int_value)) {
			$error_handler("param $param was expected to be an int, and it isn't");
			return;
		}
	}

	return mysql_real_escape_string($value);
}




function check_result($sql, $nojson = false) {
	$function = 'json_failure';
	if ($nojson === true) {
		$function = 'preit';
	}

	$result = mysql_query($sql);
	if (!$result) {
		mysql_query('ROLLBACK');
		$function(mysql_error());
	}
	return $result;
}


function noop($s) {
	// do nothing
}



