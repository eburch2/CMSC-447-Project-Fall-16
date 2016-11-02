
	function getStudentInfo() {
		var studentID = document.querySelector("#umbcid").value;
		studentID = (studentID.toUpperCase()).trim();
		var newValue = "";
		for (var i=0; i < studentID.length; i++) {
			if(studentID[i].match(/[A-Za-z]/) && newValue.length < 2) {
				// must start with 2 characters
				newValue += studentID[i];
			}
			else if(studentID[i].match(/[0-9]/) && newValue.length >= 2 && newValue.length < 7) {
				// after the first 2 characters, it must be followed by 5 digits
				newValue += studentID[i];
			}
		}
		studentID = newValue;
		document.querySelector("#umbcid").value = studentID;
		var firstname = document.querySelector("#firstname");
		var lastname = document.querySelector("#lastname");
		// only run if student id's length is 7
		if(studentID.length == 7) {
			// url address of the php page that provides JSON data
			var studentSearchURL = "./students.php?studentID=" + studentID;
			// make a same orogin XMLHttpRequest to get the list of courses
			var xhr = new XMLHttpRequest();
			// GET Method is being used
			xhr.open("GET", studentSearchURL, true);
			xhr.onload = function() {
				// store the webpage's response. The data result of database query
				var resp = xhr.responseText;
				// convert it to an array that we can manipulate
				var jsonParsed = JSON.parse(resp);
				// make a list of the results
				if(jsonParsed.length > 0) {
					firstname.value = jsonParsed[1];
					lastname.value = jsonParsed[2];
					document.getElementById("firstname").disabled = false;
					document.getElementById("lastname").disabled = false;
				}
				else {
					firstname.value = ">> Disabled << Enter a valid UMBC ID";
					lastname.value = ">> Disabled << Enter a valid UMBC ID";
					document.getElementById("firstname").disabled = true;
					document.getElementById("lastname").disabled = true;
				}
			}
			xhr.send(null);
		}
		else {
			firstname.value = ">> Disabled << Enter a valid UMBC ID";
			lastname.value = ">> Disabled << Enter a valid UMBC ID";
			document.getElementById("firstname").disabled = true;
			document.getElementById("lastname").disabled = true;
		}
	}
	function formatUMBCID() {
		var studentID = this.value;
		studentID = (studentID.toUpperCase()).trim();
		var newValue = "";
		for (var i=0; i < studentID.length; i++) {
			if(studentID[i].match(/[A-Za-z]/) && newValue.length < 2) {
				// must start with 2 characters
				newValue += studentID[i];
			}
			else if(studentID[i].match(/[0-9]/) && newValue.length >= 2 && newValue.length < 7) {
				// after the first 2 characters, it must be followed by 5 digits
				newValue += studentID[i];
			}
		}
		this.value = newValue;
	}
	function formatName() {
		var name = this.value;
		var newName = "";
		// iterate through each character of the firstname's/lastname's value
		for(var i=0; i < name.length; i++) {
			if(name[i].match(/[\w\d\s\.'\-]/)) {
				// if the character is any of the acceptable values, then append it
				newName += name[i];
			}
		}
		this.value = newName;
	}
	function formatUsername() {
		var name = this.value;
		var newName = "";
		// iterate through each character of the username
		for(var i=0; i < name.length; i++) {
			if(name[i].match(/[\w\d]/)) {
				// if the character is any of the acceptable values, then append it
				newName += name[i];
			}
		}
		this.value = newName;
	}
	function submitCheck() {
		var message = "";
		var returnValue = true;
		if(document.querySelector("#password").value != document.querySelector("#password1").value) {
			message += "Passwords does not match.\n";
			returnValue = false;
		}
		if(document.getElementById("firstname").disabled == true || document.getElementById("lastname").disabled == true) {
			message += "Invalid firstname / lastname. \n";
			returnValue = false;
		}
		var pass = document.querySelector("#password").value;
		for(var i=0; i < pass.length; i++) {
			if(!pass[i].match(/[A-Za-z0-9!@#\$%]/)) {
				message += "Invalid password. Must be alphanumeric only and these special characters !@#$%\n";
				returnValue = false;
				break;
			}
		}
		
		if(returnValue == false) {
			alert(message);
		}
		return returnValue;
	}