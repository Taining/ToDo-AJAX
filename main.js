$(function(){
	checkAuthentication();

	$("#loginButton").on("click", function(){

	});
});

function checkAuthentication(){
	$.get('backend.php', {action: "auth"}, function(data){
		if (data['auth'] != 'yes') {
			
		}
	});
}