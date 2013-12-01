<?

session_start();
$error_message = null;
if ($_POST) {
	include_once('includes/connect.php');
	$userid = check_param('userid', 'string', null, null);
	$pass = check_param('pass', 'string', null, null);

	$sql = "
		SELECT doctor_id, CONCAT(fname, ' ', lname) AS doctor_name
		FROM doctors
		WHERE userid = '$userid' AND md5_password = md5('$pass')
	";

	$result = mysql_query($sql);
	if (!$result) {
		preit(mysql_error());
	}
	else {
		if (mysql_num_rows($result) == 0) {
			$error_message = "Login failed.  Please check your userid and password.";
		}
		else {
			$row = mysql_fetch_assoc($result);
			$doctor_id = $row['doctor_id'];
			$doctor_name = $row['doctor_name'];
			$_SESSION['doctor_id'] = $doctor_id;
			$_SESSION['doctor_name'] = $doctor_name;
		}
	}
}


if (!array_key_exists('doctor_id', $_SESSION)) {
?>
<!doctype html>
<html>
  
  <head>
    <title>ICD Conversion Kit - Please Log In</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link rel="stylesheet" href="https://app.divshot.com/css/bootstrap.css">
    <link rel="stylesheet" href="https://app.divshot.com/css/bootstrap-responsive.css">
    <script src="https://app.divshot.com/js/jquery.min.js"></script>
    <script src="https://app.divshot.com/js/bootstrap.min.js"></script>
  </head>
  
  <body>
    <div id="main-page"class="container-fluid" style="background-color:#39B7CD">
      <div class="row-fluid">
        <div class="span12">
          <div class="page-header">
            <h1>ICD Conversion Kit</h1>
          </div>
        </div>
      </div>
			<div class="row-fluid">
				<div class="span8 offset2">
					<div class="well sell-small">
            <form class="form-horizontal" method="post">
              <fieldset>
                <legend>Please Log In</legend>
							</fieldset>
							<? if(!is_null($error_message)): ?>
							<p><span class="text-error"><?= $error_message ?></span></p>
							<? endif; ?>
							<div class="control-group">
								<label class="control-label" for="userid">User ID</label>
								<div class="controls">
									<input type="text" id="userid" name="userid" />
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="pass">Password</label>
								<div class="controls">
									<input type="password" id="pass" name="pass" />
								</div>
							</div>
							<div class="control-group">
								<div class="controls">
									<button type="submit" class="btn">Log In</button>
								</div>
							</div>
            </form>
					</div>
				</div>
			</div>
		</div>
	</body>

</html>


<?
	exit();
}
?>
