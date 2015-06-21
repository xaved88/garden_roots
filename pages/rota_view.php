<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Rota Mod</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>
<script src="../js/calendar.js"></script>

<link rel="shortcut icon" href="../images/favicon.ico">
<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
<link href="../css/calendar.css" rel="stylesheet">

<style>
	table td, table th{border:1px solid black;}
	
	div.staff_dialog{ 
		position: absolute; 
		background-color: white;
		border: 1px solid black;
		width: 350px;
		height: 350px;
		overflow: scroll;
	}
	
	div.staff_dialog div.top_bar{
		background-color: #333333;
		color: white;
		text-align: center;
		border: 2px solid black;
	}
	
	div.staff_dialog button{
		height: 20;
		width: 20px;
		padding: 0px;
	}
	div.staff_dialog button.save, div.staff_dialog button.close{
		float:right;
	}
	
	div.staff_dialog button.toggle{
		float:left;
		margin-right:8px;
	}
	div.staff_dialog div.staff.conflict{
		background-color: red;
	}
	div.staff_dialog div.staff.scheduled{
		font-weight:bold;
	}
	div.staff_dialog div.staff.av_0, div.staff_dialog div.staff.av_-1{
		background-color: black;
		color: white;
	}
	div.staff_dialog div.staff.av_1{
		border: 1px solid red;
	}
	div.staff_dialog div.staff.av_2{
		border: 1px solid black;
	}
	div.staff_dialog div.staff.av_3{
		border: 1px solid green;
	}
	
	table#rota_cal td.highlight{
		border: 1px solid blue;
	}
</style>
<script>

function remove_staff_member(obj){
	alert('bang');
}

function add_staff_member(obj){
	position = $(obj).position();
	alert('Left: ' + position.left + ' Top: ' + position.top);
	
	$('div.staff_dialog').remove();
	$('body').prepend("<div class='staff_dialog'>DATA HERE</div>");
	$('div.staff_dialog').css('left',position.left);
	$('div.staff_dialog').css('top',position.top);
}

function open_dialog(td){	
	close_dialog();
	
	$(td).addClass('highlight');
	action = 'fill_dialog';
	date = $(td).attr('data-date');
	shift_id = $(td).attr('data-shift');
	pos_id = $(td).attr('data-pos');
	
	$.ajax({
		type:'POST',
		url:'../disp/d_rota_view.php',
		data:{
			action: action,
			date: date,
			shift_id: shift_id,
			pos_id: pos_id	
		},
		success:function(data){
			$('body').append(data);
			y = ($(window).height() - $('div.staff_dialog').height())/2;
			x = ($(window).width()  - $('div.staff_dialog').width())/2;
			
			$('div.staff_dialog').css('left',x);
			$('div.staff_dialog').css('top',y);
			dialog_listener();
		}
	});
	
	//$('body').append("<div class='staff_dialog'>" + content + "</div>");
	
}
function close_dialog(){
	$('div.staff_dialog').remove();
	$('td.highlight').removeClass('highlight');
}
function save_dialog(){
	dialog = $('div.staff_dialog');
	
	staff_id = new Array();
	staff_names = new Array();
	date = dialog.attr('data-date');
	shift_id = dialog.attr('data-shift_id');
	pos_id = dialog.attr('data-pos_id');
	info = dialog.find('div.top_bar span.text').html();
	
	dialog.find('div.staff.scheduled').each(function(){
		staff_id.push($(this).attr('data-staff_id'));
		staff_names.push($(this).find('span.text').html());
	});
	
	$.ajax({
		type:'POST',
		url:'../actions/a_rota_view.php',
		data:{
			staff_id: staff_id,
			date: date,
			shift_id: shift_id,
			pos_id: pos_id,
			info: info
		},
		success: function(data){
			$('div#action_response').html(data);
			$('table#rota_cal td[data-date=' + date + '][data-shift=' + shift_id + '][data-pos=' + pos_id + ']').html(staff_names.join('<br/>'));
			close_dialog();
		}
	});
	
}
function toggle_staff(obj){
	staff = $(obj).closest('div.staff');
	if(staff.hasClass('scheduled')){
		staff.removeClass('scheduled');
		$(obj).html('+');
	}
	else{
		staff.addClass('scheduled');
		$(obj).html('X');
	}
}


function dialog_listener(){
	$('div.staff_dialog button').off();
	$('div.staff_dialog button.save').click(function(){
		save_dialog();
	});
	$('div.staff_dialog button.close').click(function(){
		close_dialog();
	});
	$('div.staff_dialog button.toggle').click(function(){
		toggle_staff(this);
	});
}
function listener(){
	$('table#rota_cal td').off();
	$('table#rota_cal td').click(function(){
		open_dialog(this);
	});
	
	
}

function after_push_date(){
	listener();
}

$(function(){
	listener();
});
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
?>

<div id='action_response'></div>
<div>
<?php
require_once('../includes/rota_classes.php');

$rota = new Rota($con);
$rota->group = new dGroup($con,2);
$rota->init();
$rota->show();
?>
<div>
</div>
</div>
</body>

</html>