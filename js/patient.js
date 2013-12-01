//patient.js
//Code Author: Drew Pfundstein
//patient class


function Patient (data) {
    this.ident = data.patient_id;
    this.fname = data.fname;
    this.lname = data.lname;
    this.dob = data.dob;
    this.conditions = [];
    this.getConditions = function() {
    	var url = ajax.php;
    	var params ={
    		action: 'do_get_conditions_for_patient',
    		patient_id: this.id

    	};
        

        $.post(url, params, function(response){
        	if (response.success == 0) 
        	{
				alert(response.errmsg);
			}
			else 
			{
				for(var i=0; i<response.length)
				{
					this.conditions[i] = response
				}
			}
        })
        
    };
    //displays patient data in top right well
    this.displayMe = function(){
    	//handle left colomn of patient data
    	var element = document.getElementById("patient_name");
    	element.innerHTML=this.fname + " " + this.lname;
    	element = document.getElementById("patient_id");
    	element.innerHTML=this.ident;
    	element = document.getElementById("patient_dob");
    	element.innerHTML=this.dob;

    	//handle right colomn of patient data
    	element = document.getElementById("patient_conditions");
    	element.innerHTML="";

    	for(var i = 0; i<this.conditions.length; i++)
    	{
    		element.innerHTML += '<li id="'this.conditions[i].condition_id'"><a >
							
							
							'this.conditions[i].condition'</a>
							<a href="#edit-condition" data-rel="popup" data-position-to="window" data-transition="pop">Edit Condition</a>
						</li>'
    	}
    };
    //adds condition to patient by calling the api and then refreshing the view of the well
    this.addCondition = function(condition_object)
    {
    	var url = ajax.php;
    	var params ={
    		action: 'do_add_condition',
    		
    		patient_id: this.ident,
			icd9_code: condition_object.icd9_code,
			icd10_scenario: condition_object.icd10_scenario,
			icd10_choices: condition_object.icd10_choices

    	};
        

        $.post(url, params, function(response)
        {
        	if (response.success == 0) 
        	{
				alert(response.errmsg);
			}
			else
			{
				this.getConditions();//updates condition array
				this.displayMe();//updates view
			}
		}

    };
    this.deleteCondition = function(condition_ident)
    {
    	var url = ajax.php;
    	var params ={
    		action: 'do_delete_condition',
    		condition_id: condition_ident

    	};
        

        $.post(url, params, function(response)
        {
        	if (response.success == 0) 
        	{
				alert(response.errmsg);
			}
			else
			{
				this.getConditions();//updates condition array
				this.displayMe();//updates view
			}
		}

    }
    this.editCondition = function()
    {
    	//i dont want to deal with this now

    };
}