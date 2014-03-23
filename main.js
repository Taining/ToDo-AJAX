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
	$("#addtask-option").on("click", function(){
		$("#add-task").show();
		$("#login").hide();
		$("#signup").hide();
		$("#content").hide();		
	});

});

function checkAuthentication(){
	$.get("backend.php", {action: "auth"}, function(data){
		if (data['auth'] == 'no') {
			$("#login").show();
		} else {
			displayTasks();
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
			displayTasks();
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

function getTasks() {
	$.getJSON("backend.php", {action: "gettasks"}, function(data){
		var tasks = data['tasks'];
		generateTasksView(tasks);
	});
}

function displayTasks(){
	$("#login").hide();
	$("#signup").hide();
	$("#content").show();
	getTasks();
}

function generateTasksView(tasks) {
	var html = "<ul>";
	for (var i = 0; i < tasks.length; i++) {
		html += "<li><span class='link'><span class='dscrp'>"+tasks[i]['dscrp']
				+"&nbsp</span>(<a onclick='deleteTask("+tasks[i]['taskid']+")'>remove</a>&nbsp;"
				+"<a onclick='markAsDone("+tasks[i]['taskid']+")'>done</a>)</span>&nbsp&nbsp";
				
		html += "<code>Created at "+tasks[i]['createtime']+"</code>";
		
		html += "<form class='task-form'><table border=1><tr>";
		for (var j = 0; j < tasks[i]['total']; j++) {
			if(j < tasks[i]['progress'] - 1) {
				html += "<td class='completed'></td>";
			} else if (j == tasks[i]['progress'] - 1) {
				html += "<td class='last'>"
							+ "<input type='button' name='undo' onclick='undoTask("+tasks[i]['taskid']+")' value='Undo' class='btn'>";
					  		+ "</td>";
			} else if (j == tasks[i]['progress']){
				html += "<td class='next'>"
							+ "<input type='button' name='doit' onclick='doTask("+tasks[i]['taskid']+")' value='Do it!' class='btn'>";
					  		+ "</td>";
			} else {
				html += "<td class='uncompleted'></td>";
			}
		}
		html += "</table></form></li>";
	}
	html += "</ul>";
	$(".tasks").html(html);
}

function undoTask(taskid) {
	console.log("undo task");
	$.get("backend.php", {action: "undo", taskid:taskid}, function(data){ });
	// update view
	getTasks();
}

function doTask(taskid) {
	$.get("backend.php", {action: "doit", taskid:taskid}, function(data){ });
	// update view
	getTasks();
}

function deleteTask(taskid) {
	$.get("backend.php", {action: "delete", taskid:taskid}, function(data){ });
	// update view
	getTasks();
}

function markAsDone(taskid) {
	$.get("backend.php", {action: "markdone", taskid:taskid}, function(data){ });
	// update view
	getTasks();
}

function addTask() {
	var dscrp 	= $("#addtask-form input[name=dscrp]").val();
	var details = $("#addtask-form textarea[name=details]").val();
	var total 	= $("#addtask-form input[name=total]").val();
	
	if (!dscrp){
		$("#addtask-form .error").show();
		$("#addtask-form .error").html('Description and estimated total time cannot be empty.');
	} else if (!total) {
		$("#addtask-form .error").show();
		$("#addtask-form .error").html('Description and estimated total time cannot be empty.');
	} else if (!$.isNumeric(total)){
		$("#addtask-form .error").show();
		$("#addtask-form .error").html('Please enter a numeric time units');			
	} else {
		if(!details) details = "";
		$.get("backend.php", {action: "addtask", dscrp: dscrp, details: details, total: total}, function(data){ });	
		$("#add-task").hide();
		displayTasks();
	}	
}

















