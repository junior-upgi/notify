<?php
if($_GET['contents']!=""){
	$data = array( 'message' => $_GET['contents'] );
	$ids = array($_GET['token']);
	sendGoogleCloudMessage(  $data, $ids );
}
function sendGoogleCloudMessage( $data, $ids )
{
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
    if ( curl_errno( $ch ) )
    {
        echo 'GCM error: ' . curl_error( $ch );
    }
    curl_close( $ch );
    echo $result;
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