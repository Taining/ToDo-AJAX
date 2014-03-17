$(function(){
	checkAuthentication();

	$("#loginButton").on("click", function(){
		login();
	});

	$("#linkToSignUp").on("click", function(){
		showSignUp();
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

function showSignUp(){
	$("#login").hide();
	$("#signup").show();
	console.log("testing");
}

function login(){
	var email = $("#login-form input[name=email]").val();
	var password = $('#login-form input[name=password]').val();
	$.get('backend.php', {action: "login", email: email, password: password}, function(data){
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


















