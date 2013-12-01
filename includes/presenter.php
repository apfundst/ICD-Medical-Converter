<?

include_once('connect.php');

function do_login() {
	$userid = check_param('userid', 'string');
	$password = check_param('password', 'string');

	$sql = "
		SELECT doctor_id, fname, lname
		FROM doctors
		WHERE user_id = '$userid'
		AND md5_password = '$md5_password'
	";

	$result = check_result($sql);
	if (mysql_num_rows($result) == 0) {
		json_failure("Login failed");
	}
	$row = mysql_fetch_assoc($result);

	json_success($row);
}


function do_remove_condition($nojson = false) {
	$condition_id = check_param('condition_id', 'int', $_REQUEST, $nojson);
	$sql = "
		DELETE FROM patient_conditions
		WHERE condition_id = $condition_id	
	";

	$result = check_result($sql, $nojson);

	if ($nojson) {
		return;
	}
	else {
		json_success();
	}
}


function do_get_patients_for_doctor($nojson = false) {

	if ($nojson) {
		$args = $_SESSION;
	}
	else {
		$args = $_REQUEST;
	}

	$doctor_id = check_param('doctor_id', 'int', $args, $nojson);

	$sql = "
		SELECT *
		FROM patients
		WHERE doctor_id = $doctor_id
	";

	$result = check_result($sql);

	$patients = array();
	while ($row = mysql_fetch_assoc($result)) {
		$patients[$row['patient_id']] = "{$row['fname']} {$row['lname']}";
	}

	if ($nojson) {
		return $patients;
	}
	else {
		json_success($patients);
	}
}
//for building patient info
function do_get_patient_with_dob($nojson = false) {

	if ($nojson) {
		$args = $_SESSION;
	}
	else {
		$args = $_REQUEST;
	}

	//$doctor_id = check_param('doctor_id', 'int', $args, $nojson);
	$patient_id = check_param('patient_id', 'int', $args, $nojson);

	//most likely a bug here since I assume patient_id's could be common between multiple doctors
	//but if we only have 1 doctor it works
	// Indeed, we assume that a patient has only one doctor.  Again, this is a small town.  -jgatt
	$sql = "
		SELECT *
		FROM patients
		WHERE patient_id = $patient_id
	";

	$result = check_result($sql);

	// $patients = array();
	// $patients = array($row['patient_id'], $row['fname'],$row['lname'], $row['dob']);

	if (mysql_num_rows($result) == 0) {
		json_failure("No such patient with that ID");
	}

	// The mysql_fetch_assoc will automatically give us an associative array
	// with the keys being the column names and the values being the column values.
	// Hence, it will look like this:
	// $patient = array(
	//   'patient_id' => 6,
	//   'fname' => 'Jane',
	//   ...
	// );
	$patient = mysql_fetch_assoc($result); 

	if ($nojson) {
		return $patient;
	}
	else {
		json_success($patient);
	}
}


function do_convert_icd9($nojson = false) {
	$icd9_code = check_param('icd9_code', 'int', $_REQUEST, $nojson);

	$sql = "
		SELECT DISTINCT
			a.icd10_code
			,b.description
		FROM
			2013_gem a
			JOIN icd10_codes b ON a.icd10_code = b.icd10_id
		WHERE icd9_code = '$icd9_code'
		ORDER BY scenario, choice_list
	";

	$result = check_result($sql);

	$icd10_codes = array();
	while ($row = mysql_fetch_assoc($result)) {
		$icd10_code = $row['icd10_code'];
		$description = $row['description'];

		$icd10_codes[$icd10_code] = $description;
	}

	if ($nojson) {
		return $icd10_codes;
	}
	else {
		json_success($icd10_codes);
	}
}


function do_delete_condition() {
	$condition_id = check_param('condition_id', 'int');

	$sql = "
		DELETE FROM patient_conditions
		WHERE condition_id = $condition_id
	";

	check_result($sql);
	json_success();
}


function do_get_conditions_for_patient($nojson = false) {
	$args = $_REQUEST;
	if ($nojson) {
		$args = $_SESSION;
	}

	$patient_id = check_param('patient_id', 'int', $args, $nojson);

	$sql = "
		SELECT
			a.condition_id
			,a.icd9_id AS icd9_code
			,b.description
		FROM
			patient_conditions a
			JOIN icd9_codes b ON a.icd9_id = b.icd9_id
			WHERE patient_id = $patient_id
	";

	$conditions = array();
	$result = check_result($sql);

	while ($row = mysql_fetch_assoc($result)) {
		$condition_id = $row['condition_id'];
		$icd9_code = $row['icd9_code'];
		$description = $row['description'];

		$condition = array();

		$condition['icd9_code'] = $icd9_code;
		$condition['icd9_description'] = $description;
		$condition['icd10_codes'] = array();

		$sql = "
			SELECT
				c.icd10_code
				,d.description AS icd10_description
			FROM
				patient_conditions_icd10_intersect c
				JOIN icd10_codes d ON c.icd10_code = d.icd10_id
			WHERE
				c.condition_id = $condition_id
		";

		$result2 = check_result($sql);	
		while ($row2 = mysql_fetch_assoc($result2)) {
			$icd10_code = $row2['icd10_code'];
			$icd10_description = $row2['icd10_description'];

			$condition['icd10_codes'][$icd10_code] = $icd10_description;
		}

		$conditions[$condition_id] = $condition;
	}

	if ($nojson) {
		return $conditions;
	}
	else {
		json_success($conditions);
	}
}


function do_add_condition($nojson = false) {

	$patient_id = check_param('patient_id', 'int', $_SESSION, $nojson);
	$icd9_code = check_param('icd9', 'string', $_REQUEST, $nojson);
	$icd10_codes = check_param('icd10', 'string', $REQUEST, $nojson);

	mysql_query('BEGIN');

	$sql = "
		INSERT INTO patient_conditions (patient_id, icd9_id)
		VALUES ($patient_id, '$icd9_code');
	";

	$result = check_result($sql, $nojson);

	$condition_id = mysql_insert_id();

	$foo = explode(' ', $icd_codes);

	foreach (explode(' ', $icd10_codes) as $icd10_code) {
		$sql = "
			INSERT INTO patient_conditions_icd10_intersect (condition_id, icd10_code)
			VALUES ($condition_id, '$icd10_code')
		";

		$result2 = check_result($sql, $nojson);
	}

	mysql_query('COMMIT');
	if ($nojson) {
		// Do nothing
	}
	else {
		json_success($condition_id);
	}
}
	

