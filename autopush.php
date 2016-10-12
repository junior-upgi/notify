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
//Android推播
function sendGoogleCloudMessage( $data, $ids ){
	//Android推播憑證
    $apiKey = 'AIzaSyA-pCTPFOHljKlrzaLOq4Lpo1Sn5H5GqXY';
    $url = 'https://android.googleapis.com/gcm/send';
    $post = array(
		'registration_ids'  => $ids,
		'data'              => $data
	);
    $headers = array( 
		'Authorization: key=' . $apiKey,
		'Content-Type: application/json'
	);
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post ) );
    $result = curl_exec( $ch );
    if ( curl_errno( $ch ) ){
        //echo 'GCM error: ' . curl_error( $ch );
    }
    curl_close( $ch );
	return $result;
}
//iOS憑證密碼
$passphrase = "80408228";
$ctx = stream_context_create();
//憑證位置 放在同目錄下 把xxx.pem替換成你的憑證名稱
//stream_context_set_option($ctx, "ssl", 'local_cert',  dirname(__FILE__)."\\upgi2.pem");
stream_context_set_option($ctx, "ssl", 'local_cert',  "/var/www/html/notify/upgi2.pem");
stream_context_set_option($ctx, "ssl", "passphrase", $passphrase);
//這個是正式的發佈地址
$fp = stream_socket_client('tls://gateway.push.apple.com:2195', $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
//這個是沙盒測試地址 發布到appstore後記得修改
//$fp = stream_socket_client("ssl://gateway.sandbox.push.apple.com:2195", $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
if (!$fp){
	exit("Failed to connect: {$err} {$errstr}" . PHP_EOL);
	echo "Connected to APNS" . PHP_EOL;
}

//Android推播
$query_Recordset = "SELECT * FROM vNotificationList where deviceOS = 'Android' and deviceToken != '' and (notificationStatus = '0' or notificationStatus is null) order by created asc";
$Recordset = mysqli_query($DBSQL,$query_Recordset) or die(mysqli_error());
$i = 0;
while($Row_Recordset = mysqli_fetch_array($Recordset)){
	$query_Recordset1 = "SELECT * FROM vNotificationList where deviceOS = 'Android' and deviceToken = '{$Row_Recordset['deviceToken']}' and (notificationStatus = '0' or notificationStatus is null)";
	echo $query_Recordset1."<br>";
	$Recordset1 = mysqli_query($DBSQL,$query_Recordset1) or die(mysqli_error());
	if($badge[$Row_Recordset['deviceToken']]==""){
		$badge[$Row_Recordset['deviceToken']] = mysqli_num_rows($Recordset1);
	}
	if($Row_Recordset['audioFile']!=""){
		$sound = $Row_Recordset['audioFile'];
	}else{
		$sound = "Default";
	}
	$data = array( 'message' => $Row_Recordset['content'], 'sound' => $sound, 'badge' => $badge[$Row_Recordset['deviceToken']]);
	print_r($data);
	$ids = array($Row_Recordset['deviceToken']);
	$result = json_decode(sendGoogleCloudMessage($data, $ids));
	if($result->success=="1"){
		$query_Recordset1 = "update broadcastStatus set notificationStatus = '1', sent = NOW() where ID = '{$Row_Recordset['broadcastID']}' and (notificationStatus = '0' || notificationStatus is null) ";
		$Recordset1 = mysqli_query($DBSQL,$query_Recordset1) or die(mysqli_error());
	}
	if((++$i)>=50){
		break;
	}
}

//iOS推播
$query_Recordset = "SELECT * FROM vNotificationList where deviceOS = 'iOS' and deviceToken != '' and (notificationStatus = '0' or notificationStatus is null) order by created asc";
$Recordset = mysqli_query($DBSQL,$query_Recordset) or die(mysqli_error());
$i = 0;
while($Row_Recordset = mysqli_fetch_array($Recordset)){
	$query_Recordset1 = "SELECT * FROM vNotificationList where deviceOS = 'iOS' and deviceToken = '{$Row_Recordset['deviceToken']}' and (notificationStatus = '0' or notificationStatus is null)";
	echo $query_Recordset1."<br>";
	$Recordset1 = mysqli_query($DBSQL,$query_Recordset1) or die(mysqli_error());
	if($badge[$Row_Recordset['deviceToken']]==""){
		$badge[$Row_Recordset['deviceToken']] = mysqli_num_rows($Recordset1);
	}
	if($Row_Recordset['audioFile']!=""){
		$sound = str_replace(".mp3",".caf",$Row_Recordset['audioFile']);
	}else{
		$sound = "Default";
	}
	//檢查靜音時段
	$query_Recordset1 = "SELECT * FROM timeSetting where deviceToken = '{$Row_Recordset['deviceToken']}' and ((startTime < endTime and startTime <= NOW() and endTime >= NOW()) or (startTime > endTime and (startTime <= NOW() or endTime >= NOW()))) ";
	echo $query_Recordset1."<br>";
	$Recordset1 = mysqli_query($DBSQL,$query_Recordset1) or die(mysqli_error());
	if($Row_Recordset1 = mysqli_fetch_array($Recordset1)){
		$sound = null;
	}
	
	$body["aps"] = array("alert" => $Row_Recordset['content'] ,"sound" => $sound ,"badge" => $badge[$Row_Recordset['deviceToken']]);
	$payload = json_encode($body);
	echo $payload."<br>";
	$msg = chr(0) . pack("n", 32) . pack("H*", $Row_Recordset['deviceToken']) . pack("n", strlen($payload)) . $payload;
	$result = fwrite($fp, $msg, strlen($msg));
	if ($result){
		$query_Recordset1 = "update broadcastStatus set notificationStatus = '1', sent = NOW() where ID = '{$Row_Recordset['broadcastID']}' and (notificationStatus = '0' || notificationStatus is null) ";
		//echo $query_Recordset1."<br>";
		$Recordset1 = mysqli_query($DBSQL,$query_Recordset1) or die(mysqli_error());
	}
	if((++$i)>=50){
		break;
	}
}
fclose($fp);
mysqli_close($DBSQL);
?>