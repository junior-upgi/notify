<?php
date_default_timezone_set('Asia/Taipei');
$hostname_DBSQL = "localhost";
$database_DBSQL = "mobileMessagingSystem";
$username_DBSQL = "notification";
$password_DBSQL = "@notification";
$DBSQL = mysqli_connect($hostname_DBSQL, $username_DBSQL, $password_DBSQL, $database_DBSQL);
if (mysqli_connect_errno()){
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
Session_start();
header("Content-type: text/html; charset=utf-8");
header("Vary: Accept-Language");
mysqli_query($DBSQL,"SET NAMES UTF8;");
mysqli_query($DBSQL,"SET CHARACTER_SET_CLIENT=UTF8;");
mysqli_query($DBSQL,"SET CHARACTER_SET_RESULTS=UTF8;");
?>
<?php
if(count($_GET)>0){
	$_POST = $_GET;
}

if($_POST['type']=="list" and $_POST['token']!="" and $_POST['time']!=""){
	if(strlen($_POST['time'])=="14"){
		$time = substr($_POST['time'],0,4)."-".substr($_POST['time'],4,2)."-".substr($_POST['time'],6,2)." ".substr($_POST['time'],8,2).":".substr($_POST['time'],10,2).":".substr($_POST['time'],12,2);
	}else{
		$time = "0";
	}
	$query_Recordset = "SELECT * FROM vNotificationList LEFT JOIN messageCategory ON messageCategoryID = ID where deviceToken = '{$_POST['token']}' and created > '{$time}' order by created asc ";
	$Recordset = mysqli_query($DBSQL,$query_Recordset) or die(mysqli_error());
	$i = 0;
	while($Row_Recordset = mysqli_fetch_array($Recordset)){
		$data[$i]["broadcastID"] = $Row_Recordset['broadcastID'];
		$data[$i]["Permanent"] = $Row_Recordset['permanent'];
		$data[$i]["CategoryID"] = $Row_Recordset['messageCategoryID'];
		$data[$i]["Category"] = $Row_Recordset['messageCategoryName'];
		if(strlen($Row_Recordset['color'])==7){
			$data[$i]["ColorRed"] = hexdec(substr($Row_Recordset['color'],1,2));
			$data[$i]["ColorGreen"] = hexdec(substr($Row_Recordset['color'],3,2));
			$data[$i]["ColorBlue"] = hexdec(substr($Row_Recordset['color'],5,2));			
		}else{
			$data[$i]["ColorRed"] = "255";
			$data[$i]["ColorGreen"] = "255";
			$data[$i]["ColorBlue"] = "255";
		}
		$data[$i]["Title"] = $Row_Recordset['manualTopic'];
		$data[$i]["Time"] = $Row_Recordset['created'];
		$data[$i]["Content"] = $Row_Recordset['content'];
		if($Row_Recordset['url']==null){
			$Row_Recordset['url'] = "";
		}
		$data[$i]["Url"] = $Row_Recordset['url'];
		$i++;
		$query_Recordset1 = "update broadcastStatus set acknowledged = NOW() where ID = '{$Row_Recordset['broadcastID']}' ";
		$Recordset1 = mysqli_query($DBSQL,$query_Recordset1) or die(mysqli_error());
	}
	echo json_encode($data);
}
if($_POST['type']=="del" and $_POST['token']!="" and $_POST['time']!=""){
	if(strlen($_POST['time'])=="14"){
		$time = substr($_POST['time'],0,4)."-".substr($_POST['time'],4,2)."-".substr($_POST['time'],6,2)." ".substr($_POST['time'],8,2).":".substr($_POST['time'],10,2).":".substr($_POST['time'],12,2);
	}else{
		$time = "0";
	}
	$query_Recordset = "SELECT * FROM vNotificationList LEFT JOIN messageCategory ON messageCategoryID = ID where permanent = '1' and deviceToken = '{$_POST['token']}' and created <= '{$time}' order by created asc ";
	$Recordset = mysqli_query($DBSQL,$query_Recordset) or die(mysqli_error());
	$i = 0;
	while($Row_Recordset = mysqli_fetch_array($Recordset)){
		$data[$i]["broadcastID"] = $Row_Recordset['broadcastID'];
		$data[$i]["Permanent"] = $Row_Recordset['permanent'];
		$data[$i]["CategoryID"] = $Row_Recordset['messageCategoryID'];
		$data[$i]["Category"] = $Row_Recordset['messageCategoryName'];
		if(strlen($Row_Recordset['color'])==7){
			$data[$i]["ColorRed"] = hexdec(substr($Row_Recordset['color'],1,2));
			$data[$i]["ColorGreen"] = hexdec(substr($Row_Recordset['color'],3,2));
			$data[$i]["ColorBlue"] = hexdec(substr($Row_Recordset['color'],5,2));			
		}else{
			$data[$i]["ColorRed"] = "255";
			$data[$i]["ColorGreen"] = "255";
			$data[$i]["ColorBlue"] = "255";
		}
		$data[$i]["Title"] = $Row_Recordset['manualTopic'];
		$data[$i]["Time"] = $Row_Recordset['created'];
		$data[$i]["Content"] = $Row_Recordset['content'];
		if($Row_Recordset['url']==null){
			$Row_Recordset['url'] = "";
		}
		$data[$i]["Url"] = $Row_Recordset['url'];
		$i++;
	}
	echo json_encode($data);
}
if($_POST['type']=="time" and $_POST['token']!=""){
	$query_Recordset = "SELECT * FROM timeSetting where deviceToken = '{$_POST['token']}' ";
	$Recordset = mysqli_query($DBSQL,$query_Recordset) or die(mysqli_error());
	$i = 0;
	while($Row_Recordset = mysqli_fetch_array($Recordset)){
		$data[$i]["startTime"] = substr($Row_Recordset['startTime'],0,5);
		$data[$i]["endTime"] = substr($Row_Recordset['endTime'],0,5);
		$i++;
	}
	echo json_encode($data);
}
if($_POST['type']=="addtime" and $_POST['token']!="" and $_POST['stime']!="" and $_POST['etime']!=""){
	if(strlen($_POST['stime'])==5){
		$_POST['stime'] = "{$_POST['stime']}:00";
	}
	if(strlen($_POST['etime'])==5){
		$_POST['etime'] = "{$_POST['etime']}:00";
	}
	$query_Recordset = "insert into timeSetting(deviceToken,startTime,endTime) values('{$_POST['token']}','{$_POST['stime']}','{$_POST['etime']}')";
	$Recordset = mysqli_query($DBSQL,$query_Recordset) or die(mysqli_error());
	$data["success"] = true;
	echo json_encode($data);
}
if($_POST['type']=="deltime" and $_POST['token']!="" and $_POST['stime']!="" and $_POST['etime']!=""){
	if(strlen($_POST['stime'])==5){
		$_POST['stime'] = "{$_POST['stime']}:00.000";
	}
	if(strlen($_POST['etime'])==5){
		$_POST['etime'] = "{$_POST['etime']}:00.000";
	}
	$query_Recordset = "delete from timeSetting where deviceToken = '{$_POST['token']}' and startTime = '{$_POST['stime']}' and endTime = '{$_POST['etime']}'";
	//echo $query_Recordset;
	$Recordset = mysqli_query($DBSQL,$query_Recordset) or die(mysqli_error());
	$data["success"] = true;
	echo json_encode($data);
}
mysqli_close($DBSQL);
?>