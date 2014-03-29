$(function(){

	checkAuthentication();

	$("#account-button").on("click", function(){
		$.getJSON("service/backend-controller.php", {action: "auth"}, function(data){
			if (data['auth'] == 'yes'){
				getAccount();
				switchView("account");
			}
		});
	});

	$("#nav-addtask").on("click", function() {
		$.getJSON("service/backend-controller.php", {action: "auth"}, function(data){
			if (data['auth'] == 'no') {
				// do nothing
			} else {
				switchView("addTask");			
			}
		});	
	});
});

function checkAuthentication(){
	var result = false;
	
	$.getJSON("service/backend-controller.php", {action: "auth"}, function(data){
		if (data['auth'] == 'yes') {
			switchView("tasks");
			result = true;
		} else {
			switchView("login");
		}
	});
	
	return result;
}

function login(){
	var email = $("#login-form input[name=email]").val();
	var password = $('#login-form input[name=password]').val();
	$.getJSON('service/backend-controller.php', {action: "login", email: email, password: password}, function(data){
		console.log(data['status']);
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
	var news 	= 'false';
	var policy 	= 'true';

	//user must fill in all fields
	var html = "";
	if (!fname || !lname || !email || !password || !repassword || month==0 || day==0 || year==0
		|| !news || !policy) {
		html += 'Fill in all fields. ';
	}
	if (password != repassword){
		html += 'Passwords do not match. ';
	} 
	if (!$("#signup-form input[name=policy]").is(":checked")){
		html += 'Agree our Terms. ';
	}
	if(html == '') {
		if($("#signup-form input[name=news]").is(":checked")){
			news = 'true';
		}
		//send data to backend.php to further validate and update database
		$.getJSON('service/backend-controller.php', {action: "signup", fname: fname, lname: lname, email: email, password:password, month: month, day: day, year: year, sex: sex, news: news, policy: policy}, function(data){
				console.log(data['status']);
				if (data['status'] == 'ok') {
					switchView('login');
				} else {
					$("#signup-form .error").show();
					$("#signup-form .error").html(data['error']);
				}
		});
	} else {
		$("#signup-form .error").show();
		$("#signup-form .error").html(html);
	}
}

function generateTasksView(tasks) {
	$(".tasks").html("");
	
	if(tasks == null){
		$(".tasks").html("<ul><li>Please add your first task.</li></ul>");
		return;
	}
	
	var html = "<ul>";
	for (var i = 0; i < tasks.length; i++) {
		html += "<li><span class='link'>"
				+"<span class='dscrp'>"+tasks[i]['dscrp']+"&nbsp</span>"
				+"(<a onclick='deleteTask("+tasks[i]['taskid']+")'>remove</a>&nbsp;"
				+"<a onclick='markAsDone("+tasks[i]['taskid']+")'>done</a>&nbsp"
				+"<a id='open-info-"+tasks[i]['taskid']+"' onclick='openEdit("+tasks[i]['taskid']+")'>info</a>)"
				+"</span>&nbsp&nbsp";
				
		html += "<code>Created at "+tasks[i]['createtime']+"</code>";
		
		html += "<form class='task-form'><table border=1><tr>";
		for (var j = 0; j < tasks[i]['total']; j++) {
			if(j < tasks[i]['progress'] - 1) {
				html += "<td class='completed'></td>";
			} else if (j == tasks[i]['progress'] - 1) {
				html += "<td class='last'>"
						+"<input type='button' name='undo' onclick='undoTask("+tasks[i]['taskid']+")' value='Undo' class='btn'>";
					  	+"</td>";
			} else if (j == tasks[i]['progress']){
				html += "<td class='next'>"
						+"<input type='button' name='doit' onclick='doTask("+tasks[i]['taskid']+")' value='Do it!' class='btn'>";
					  	+"</td>";
			} else {
				html += "<td class='uncompleted'></td>";
			}
		}
		html += "</table></form>"
		
		html += "<div class='edit-box' id='open-edit-" + tasks[i]['taskid'] +"' hidden>" 
				+$("#edit-task").html() 
				+"</div>";
		html += "</li>";
	}
	html += "</ul>";
	$(".tasks").html(html);
}

function getTasks() {
	$.getJSON("service/backend-controller.php", {action: "gettasks"}, function(data){
		var tasks = data['tasks'];
		generateTasksView(tasks);
		displayRateAndRemaining();
	});
	switchTab("home");
}

function displayTasks(){
	switchView("tasks");
	getTasks();
}

function displayRateAndRemaining(){
	$.getJSON("backend-controller.php", {action: "rate"}, function(data){
		$("#rate").html(data['rate']);
		
		if(data['remaining']=="Infinite"){
			$("#remaining").html("&#8734");
		}else{
			$("#remaining").html(data['remaining']);
		}
	});
}

function undoTask(taskid) {
	$.getJSON("service/backend-controller.php", {action: "undo", taskid:taskid}, function(data){ });
	// update task view
	displayTasks();
}

function doTask(taskid) {
	$.getJSON("service/backend-controller.php", {action: "doit", taskid:taskid}, function(data){ });
	// update task view
	displayTasks();
}

function deleteTask(taskid) {
	$.getJSON("service/backend-controller.php", {action: "delete", taskid:taskid}, function(data){ });
	// update task view
	getTasks();
}

function markAsDone(taskid) {
	$.getJSON("service/backend-controller.php", {action: "markdone", taskid:taskid}, function(data){ });
	// update task view
	getTasks();
}

function addTask() {
	var dscrp 	= $("#addtask-form input[name=dscrp]").val();
	var details = $("#addtask-form textarea[name=details]").val();
	var total 	= $("#addtask-form input[name=total]").val();
	
	if ((!dscrp||dscrp.trim()=="") && (!total||dscrp=="")){
		$("#addtask-form .error").show();
		$("#addtask-form .error").html('Description and estimated total time cannot be empty.');
	} else if (!dscrp||dscrp.trim()=="") {
		$("#addtask-form .error").show();
		$("#addtask-form .error").html('Description cannot be empty.');
	} else if (!total||dscrp.trim()=="") {
		$("#addtask-form .error").show();
		$("#addtask-form .error").html('Estimated total time cannot be empty.');
	} else if (!$.isNumeric(total)){
		$("#addtask-form .error").show();
		$("#addtask-form .error").html('Please enter a numeric time units for estimated total time.');			
	} else {
		if(!details) details = "";
		$.getJSON("service/backend-controller.php", {action: "addtask", dscrp: dscrp, details: details, total: total}, function(data){ });	
		displayTasks();
	}	
}

function openEdit(taskid){
	$("#open-edit-"+taskid).slideDown();

	// need to fetch info from backend/database
	$.getJSON("service/backend-controller.php", {action:"getinfo",taskid:taskid}, function(data){
		$("#open-edit-"+taskid+" form input[name=dscrp]").val(data['dscrp']);
		$("#open-edit-"+taskid+" form textarea[name=details]").val(data['details']);
		$("#open-edit-"+taskid+" form input[name=total]").val(data['total']);
		
		var id = Number(taskid);
		$("#open-edit-"+taskid+" form input[name=submit]").on("click",function(){
			editTask(id);
		});
		
		$("#open-edit-"+taskid+" form input[name=close]").on("click",function(){
			$("#open-edit-"+taskid).slideUp();
		});		
		
	});
}

function editTask(taskid) {
	var dscrp = $("#open-edit-"+taskid+" form input[name=dscrp]").val();
	var details = $("#open-edit-"+taskid+" form textarea[name=details]").val();
	var total = $("#open-edit-"+taskid+" form input[name=total]").val();
	
	if((!dscrp || dscrp=="") && (!total || total=="")){
		$("#open-edit-"+taskid+" .error").show();
		$("#open-edit-"+taskid+" .error").html("Description and estimated total time cannot be empty.");
		return;
	}
	if(!dscrp || dscrp==""){
		$("#open-edit-"+taskid+" .error").show();
		$("#open-edit-"+taskid+" .error").html("Description cannot be empty.");
		return;	
	}
	if(!total || total==""){
		$("#open-edit-"+taskid+" .error").show();
		$("#open-edit-"+taskid+" .error").html("Estimated total time cannot be empty.");
		return;	
	}
	if(!details){
		details = "";
	}
	
	var id = Number(taskid);
	$.getJSON("service/backend-controller.php", {action:"edittask",taskid:taskid,dscrp:dscrp,details:details,total:total}, function(data){
		getTasks();
		// $("#close-edit-"+taskid).attr("id")="open-edit-"+id;
	});	
}

function getAccount(){
	$.get("service/backend-controller.php", {action: "getaccount"}, function(data) {
		console.log(data['status']);
		if (data['status'] == 'ok') {
			$("#update-account input[name=fname]").val(data['fname']);
			$("#update-account input[name=lname]").val(data['lname']);
			$("#update-account input[name=email]").val(data['email']);
			$("#update-account select[name=year]").val(data['year']);
			$("#update-account select[name=month]").val(data['month']);
			$("#update-account select[name=day]").val(data['day']);
			$("#update-account input[name=sex][value="+data['sex']+"]").prop("checked", true);
			if (data['news'] == 't') {
				$("#update-account input[name=news]").prop("checked", true);
			} else {
				$("#update-account input[name=news]").prop("checked", false);
			}
		}
	});

	$("#update-account input[name=info]").on("click", function(){
		updateAccount();
	});
	$("#update-account input[name=pwd]").on("click", function(){
		updatePassword();
	});
}

function updateAccount(){
	var fname 	= $("#update-account input[name=fname]").val();
	var lname 	= $("#update-account input[name=lname]").val();
	var email 	= $("#update-account input[name=email]").val();
	var year 	= $("#update-account select[name=year]").val();
	var month 	= $("#update-account select[name=month]").val();
	var day 	= $("#update-account select[name=day]").val();
	var sex 	= $("#update-account input[name=sex]:checked").val();
	var news 	= $("#update-account input[name=news]").is(":checked");

	console.log(fname + " " + lname + " " + email + " " + year + " " + month + " " + day + " " + sex + " " + news);

	$.getJSON("service/backend-controller.php",{action: "updateaccount", fname: fname, lname: lname, email: email, year: year, month: month, day: day, sex: sex, news: news}, function(data){
		if(data['status'] == 'ok'){
			$("#account-info .error").html(data['msg']);
		} else {
			$("#account-info .error").html(data['error']);
		}
		$("#account-info .error").show();
	});
}

function updatePassword(){
	var oldPassword = $("#update-account input[name=old-password]").val();
	var newPassword = $("#update-account input[name=new-password]").val();
	var rePassword = $("#update-account input[name=re-password]").val();

	$.getJSON("service/backend-controller.php", {action: "updatepassword", oldPassword: oldPassword, newPassword: newPassword, rePassword: rePassword}, function(data){
		if(data['status'] == 'ok'){
			$("#password-info .error").html(data['msg']);
		} else {
			$("#password-info .error").html(data['error']);
		}
		$("#password-info .error").show();
	});
}

function logout(){
	$.getJSON("service/backend-controller.php", {action: "logout"}, function(){
		switchView("logout");
	});
}

function initAddTaskView() {
	$("#addtask-form input[name=dscrp]").val("");
	$("#addtask-form textarea[name=details]").val("");
	$("#addtask-form input[name=total]").val("");
}

function switchView(option){
	$(".view-control").hide();
	$(".error").hide();
	if (option == "login") {
		//empty login password field
		var password = $('#login-form input[name=password]').val("");

		$("#login").show();
		switchTab("others");
	} else if(option == "signup"){
		$("#signup").show();
		switchTab("others");
	} else if(option == "tasks"){
		$("#content").show();
		getTasks();
		switchTab("home");
	} else if(option == "account"){
		//empty update password form
		var oldPassword = $("#update-account input[name=old-password]").val("");
		var newPassword = $("#update-account input[name=new-password]").val("");
		var rePassword = $("#update-account input[name=re-password]").val("");

		$("#update-account").show();
		switchTab("others");
	} else if(option == "addTask"){
		$("#add-task").show();
		initAddTaskView();
		switchTab("addtask");
	} else if(option == "editTask"){

	} else if(option == "logout"){
		$("#login").show();
		switchTab("others");
	}
}

function switchTab(option){
	if (option == "home") {
		$("#nav-home").css({"background":"#ededed", "color":"#751B05"});		
		$("#nav-addtask").css({"background":"#751B05", "color":"#ededed"});	
	} else if(option == "addtask") {
		$("#nav-addtask").css({"background":"#ededed", "color":"#751B05"});		
		$("#nav-home").css({"background":"#751B05", "color":"#ededed"});	
	} else if(option == "others") {
		$("#nav-home").css({"background":"#751B05", "color":"#ededed"});
		$("#nav-addtask").css({"background":"#751B05", "color":"#ededed"});		
	}
}















