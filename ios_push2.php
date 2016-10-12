<?php
ini_set('display_errors', '1');
if($_GET['token']==""){
	$_GET['token'] = "09dcd5b00a7b8a3b9631f38bd652140cf359070d24402ebf1232896250e9bfa6";
}
if($_GET['contents']==""){
	$_GET['contents'] = "test";
}
if($_GET['contents']!=""){
	//iOS推播參數
	$passphrase = "80408228";
	$ctx = stream_context_create();
	//憑證位置 放在同目錄下 把xxx.pem替換成你的憑證名稱
	stream_context_set_option($ctx, "ssl", 'local_cert',  "\\var\\www\\html\\notify\\upgi2.pem");
	stream_context_set_option($ctx, "ssl", "passphrase", $passphrase);
	//這個是正式的發佈地址
	$fp = stream_socket_client('tls://gateway.push.apple.com:2195', $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	//這個是沙盒測試地址 發布到appstore後記得修改
	//$fp = stream_socket_client("ssl://gateway.sandbox.push.apple.com:2195", $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	if (!$fp){
		echo "Failed to connect: {$err} {$errstr}";
	}
	$body["aps"] = array("alert" => $_GET['contents'] ,"sound" => $sound ,"badge" => "1");
	$payload = json_encode($body);
	echo $payload."<br>";
	$msg = chr(0) . pack("n", 32) . pack("H*", $_GET['token']) . pack("n", strlen($payload)) . $payload;
	$result = fwrite($fp, $msg, strlen($msg));
	if ($result){
		echo "傳送成功<br>";
	}else{
		echo "傳送失敗<br>";
	}
	fclose($fp);
}
?>
<form action="" method="get" enctype="multipart/form-data" name="send_order">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="info">
		<tr>
			<td>token: <input type="text" name="token" id="token" class="textinfo" value="<?php echo $_GET['token'];?>" style="width:1000px" /></td>
		</tr>
		<tr>
			<td>message: <input type="text" name="contents" id="contents" class="textinfo" value="<?php echo $_GET['contents'];?>" maxlength="50" /></td>
		</tr>
		<tr>
			<td height="30"><input type="submit" value="確認送出" /></td>
		</tr>
	</table>
</form>