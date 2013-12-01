
	$(function() {
		console.log("Here comes searchfield");
		console.log($("#searchField"));
		$("#searchField").autocomplete({
			minlength: 3,
			source: 'http://icd.myskiprofile.com/search_icd9.php',
			target: $("#suggestions"),
			link: "index2.php?icd9_code="
		});

		$("#clearPatientBtn").unbind('click').click(function() {
			window.location.href = "index2.php?f=c";
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
			var url = "index2.php?action=add&icd9=<?= $icd9_code ?>&icd10=" + icd10_ids.join('+');
			window.location.href = url;
		}
	}
