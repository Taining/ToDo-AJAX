$(function(){
	checkAuthentication();

	$("#loginButton").on("click", function(){
		login();
	});

	$("#linkToSignUp").on("click", function(){
		switchLoginAndSignup(1);
	});

	$("#signupButton").on("click", function(){
		signup();
	});
	$("#gobackButton").on("click", function(){
		switchLoginAndSignup(0);
	});
});

function checkAuthentication(){
	$.get("backend.php", {action: "auth"}, function(data){
		if (data['auth'] == 'no') {
			$("#login").show();
		} else {

		}
	});
}

function switchLoginAndSignup(param){
	if (param == 1) {
		$("#login").hide();
		$("#signup").show();
	} else {
		$("#login").show();
		$("#signup").hide();
	}
}

function login(){
	var email = $("#login-form input[name=email]").val();
	var password = $('#login-form input[name=password]').val();
	$.get('backend.php', {action: "login", email: email, password: password}, function(data){
		console.log(data['status']);
		console.log(data['error']);
		if (data['status'] == 'ok') {
			//hide login form and display home page
			$("#login").hide();
		} else {
			//display error message
			$("#login-form .error").show();
			$("#login-form .error").html(data['error']);
		}
	});
}

function signup(){
	var fname 	= $("#signup-form input[name=fname]").val();
	var lname 	= $("#signup-form input[name=lname]").val();
	var email 	= $("#signup-form input[name=email]").val();
	var password 	= $("#signup-form input[name=password]").val();
	var repassword 	= $("#signup-form input[name=re-password]").val();
	var month 	= $("#signup-form select[name=month]").val();
	var day 	= $("#signup-form select[name=day]").val();
	var year 	= $("#signup-form select[name=year]").val();
	var sex 	= $("#signup-form input[name=sex]").val();
	var news 	= $("#signup-form input[name=news]").val();
	var policy 	= $("#signup-form input[name=policy]").val();

	//user must fill in all fields
	if (!fname || !lname || !email || !password || !repassword || month==0 || day==0 || year==0
		|| !news || !policy) {
		$("#signup-form .error").show();
		$("#signup-form .error").html('Please fill in all required fields.');
	} else if (password != repassword){
		//passwords do not match
		$("#signup-form .error").show();
		$("#signup-form .error").html('Passwords do not match.');
	} else {
		//send data to backend.php to further validate and update database
		$.get('backend.php', {action: "signup", fname: fname, lname: lname, email: email, password:password, month: month, day: day, year: year, sex: sex, news: news, policy: policy}, function(data){
				console.log(data['status']);
				if (data['status'] == 'ok') {
					switchLoginAndSignup(0);
				} else {
					$("#signup-form .error").show();
					$("#signup-form .error").html(data['error']);
				}
		});
	}
}


















