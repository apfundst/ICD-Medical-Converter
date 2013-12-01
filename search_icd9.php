<?

include_once('includes/connect.php');

$term = $_GET['term'];

if (strlen($term) < 3) {
	exit;
}

$term = mysql_real_escape_string(trim($term));

if ($term == '') {
	json_failure("No term given");
}

$sql = "
    SELECT icd9_id, description
    FROM icd9_codes
    WHERE icd9_id LIKE '$term%'
		ORDER BY 1
";

$result = mysql_query($sql);
if (!$result) {
	json_failure(mysql_error());
}

$json = array();
while ($row = mysql_fetch_assoc($result)) {
    $json[] = array(
        'label' => "{$row['icd9_id']} -- {$row['description']}",
        'value' => $row['icd9_id']
    );
}

header('Content-type: application/json');
print json_encode($json);
exit();

