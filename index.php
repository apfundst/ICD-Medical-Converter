<?

include_once("login.php");
include_once('includes/presenter.php');
include_once('includes/util.php');

$icd9_code = null;
$icd10_codes = null;

// If the page supplies an icd9_code then get the icd10_codes that correspond with it
if (array_key_exists('icd9_code', $_REQUEST)) {
	$icd9_code = $_REQUEST['icd9_code'];
	$icd10_codes = do_convert_icd9(true);
}


if (array_key_exists('patient_id', $_REQUEST)) {
	// We get here if the doctor clicked on a different patient.
	$_SESSION['patient_id'] = $_REQUEST['patient_id'];
}

if (array_key_exists('f', $_REQUEST)) {
	$f = $_REQUEST['f'];
	if ($f == 'c') {
		unset($_SESSION['patient_id']);
	}
}



if (array_key_exists('action', $_REQUEST)) {
	$action = $_REQUEST['action'];
	if ($action == 'add') {
		do_add_condition(true);
	}
	if ($action == 'rem') {
		do_remove_condition(true);
	}
}

$current_conditions_HTML = '';

if (array_key_exists('patient_id', $_SESSION)) {
  $current_patient_id = $_SESSION['patient_id'];
  $current_doctor_id = $_SESSION['doctor_id'];
  $current_patient = do_get_patient_with_dob($current_patient_id);

  $current_patient__info_HTML = '';
  #$current_patient__info_HTML .= '<dt>Name</dt><dd id="patient_name">'.$current_patient['fname'].' '. $current_patient['lname'].'</dd><dt>ID</dt><dd id="patient_id">'.$current_patient['patient_id'].'</dd><dt>DOB</dt><dd id="patient_dob">'.$current_patient['dob'].'</dd>';
  $current_patient__info_HTML .= '<strong>Patient Name</strong>:'.$current_patient['fname'].' '. $current_patient['lname'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<strong>Date of Birth:</strong>'.$current_patient['dob'];

  $current_conditions = do_get_conditions_for_patient(true);
	if (count($current_conditions) > 0) {
		foreach ($current_conditions as $condition_id => $condition) {
			$current_conditions_HTML .= '<div class="well">';
			$current_conditions_HTML .= '<u>ICD-9 Info</u><br>'.$condition['icd9_code'].' -- '.$condition['icd9_description'] . '<br>';
			$current_conditions_HTML .= "<u>ICD-10 Info</u><br>";
			$current_condition_icd10 = $condition['icd10_codes'];
			foreach ($current_condition_icd10 as $icd10 => $icd10_description) {
				 $current_conditions_HTML .= $icd10.' -- '.$icd10_description.'<br>';
			}
			$current_conditions_HTML .= '<a class="btn removeBtn" condition_id="' . $condition_id . '">Remove this condition</a>';
			$current_conditions_HTML .= '</div>';

		}  
	}
}   

// Load up the patient list
$doctor_id = $_SESSION['doctor_id'];
$patients = do_get_patients_for_doctor($doctor_id);
$patients_list_HTML = '';
foreach ($patients as $patient_id => $patient_name) {
	$patients_list_HTML .= '<a class="btn btn-block btn-large" href="index4.php?patient_id=' . $patient_id . '" id="' . $patient_id . '">' . $patient_name . '</a><br>';
}

?>
<!doctype html>
<html>
  
  <head>
    <title>ICD Conversion Kit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">

    <link rel="stylesheet" href="https://app.divshot.com/css/bootstrap.css">
    <link rel="stylesheet" href="https://app.divshot.com/css/bootstrap-responsive.css">
		<link rel="stylesheet" href="styles.css">
		<link rel="stylesheet" href="css/jquery-ui-1.10.3.custom.min.css">

		<script type="text/javascript" src="js/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.10.3.custom.min.js"></script>
    <script src="https://app.divshot.com/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/lib.js"></script>

  </head>
  
  <body>
    <div id="main-page" class="container-fluid" style="background-color:#07c0e1">
      <div class="row-fluid">
        <div class="span12">
          <div class="page-header">
            <img src="http://icd.myskiprofile.com/images/ICD_LOGO.png">
          </div>
        </div>
      </div>

      <p class="lead">Patient Records</p>
      
  	  <div class="row-fluid">
  			<div class="span3">
          <div class="well well-small" style="height: 495px;">
            <h4>Select Patient</h4>
            <div style="height: 400px; overflow-y: scroll">
							<?= $patients_list_HTML ?>
							<? if (array_key_exists('patient_id', $_SESSION)): ?>
							<a class="btn" id="clearPatientBtn" role="button" data-toggle="modal">Clear patient</a>
							<? endif; ?>
            </div>

          </div>

        </div>
        
          <div id="patient_info_area" class="span9">
            <div id="patient_info" class="well">
							<? if (!array_key_exists('patient_id', $_SESSION)): ?>
							<h4>Please select a patient</h4>
							<? else: ?>
    				  <!-- here is where the patient info goes-->
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <td>
											<?=$current_patient__info_HTML ?>
                    </td>
									</tr>
									<tr>
                    <!--This is where the record of Icd codes goes.-->
                    <td>
											<? if ($current_conditions_HTML == ''): ?>
											<p><strong>This patient has no conditions assigned yet.  Please use the form (below) to add new conditions.</strong></p>
											<? else: ?>
											<div style="height: 400px; overflow-y: scroll">
											<dl>
												<ol id="patient_conditions" >
												<?= $current_conditions_HTML ?>
												</ol>
                      </dl>
											</div>
											<? endif; ?>
                    </td>
                  </tr>
                </tbody>
              </table>
							<? endif; ?>
    			   </div>
            </div>
  		</div>
			<? if (array_key_exists('patient_id', $_SESSION)): ?>
      <div class="row-fluid">
        <div class="span6">
          <div class="well">
						<label for="searchField">Add a new Condition: Start typing an ICD-9 Code</label>
						<input type="text" id="searchField" <? if (!is_null($icd9_code)) { echo "value=\"$icd9_code\""; } else { echo "placeholder=\"Enter ICD-9 Code\""; } ?>>
            <!-- here is the icd9 search results show up-->
						<ul id="suggestions" data-role="listview" data-inset="true"></ul>

          </div>
        </div>
				<? if (!is_null($icd9_code)): ?>
        <div class="span6">
          <div class="well">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th></th>
                  <th>Select the ICD-10 Codes that best represent ICD-9 Code <?= $icd9_code ?></th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
            <!-- here is the icd10 search results show up-->
						<? if (is_array($icd10_codes)) { ?>
						<div data-role="fieldcontain">
						<form id="icd10_select_group">
						<? 	foreach ($icd10_codes as $icd10_code => $description) { ?>
							<label class="checkbox">
								<input type="checkbox" value="<?= $icd10_code ?>" class="icd10_select" id="<?= $icd10_code ?>">
								<?= $icd10_code ?> -- <?= $description ?> 
							</label>
						<?   
								}
						   }
						?>
						</form>
						</div>
            <a class="btn" id="icd_insert" onclick="do_insert();">Insert in record</a> 
          </div>
        </div>
				<? endif; ?>
				<? endif; ?>
      </div>
    </div>
    <!--end of main content section -->
  </body>
<script type="text/javascript">

	$(function() {
		$("#searchField").autocomplete({
			minLength: 3,
			source: 'http://icd.myskiprofile.com/search_icd9.php',
			select: function(event, ui) {
				window.location.href = "index4.php?icd9_code=" + ui.item.value;
			}
		});

		$("#clearPatientBtn").unbind('click').click(function() {
			window.location.href = "index4.php?f=c";
		});

		console.log("Here it is!");
		console.log($("#icd_insert"));

	});

	function do_insert() {
		var icd10_select = $(".icd10_select");
		var checked = 0;
		var icd10_ids = new Array();
		var i = 0;
		$.each(icd10_select, function(i, e) {
			console.log(e);
			if (e.checked) {
				icd10_ids[i] = e.id;
				checked++;
			}
		});

		if (checked == 0) {
			alert("Please select at least one ICD-10 code to add the condition to the patient's record");
		}
		else {
			var url = "index4.php?action=add&icd9=<?= $icd9_code ?>&icd10=" + icd10_ids.join('+');
			window.location.href = url;
		}
	}


	$(".removeBtn").click(function(e) {
		var $target = $(e.target);
		var conditionId = $target.attr('condition_id');
		if (confirm("Are you sure you want to remove that condition?")) {
			var url = "index4.php?action=rem&condition_id=" + conditionId;
			window.location.href = url;
		}
	});

</script>
</html>
