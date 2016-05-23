<?php
error_reporting(E_ALL & ~E_NOTICE);
$app_id = "1722350238025805";
$app_secret = "3785743700eeb3f2752d39c5825a9ceb";
$my_url = "https://apps.facebook.com/bidpropic"; // mainly, redirect to this page
$perms_str = "publish_actions";
 
$code = $_REQUEST["code"];
 
if(empty($code)) {
    $auth_url = "http://www.facebook.com/dialog/oauth?client_id="
    . $app_id . "&redirect_uri=" . urlencode($my_url)
    . "&scope=" . $perms_str;
    echo("<script>top.location.href='" . $auth_url . "'</script>");
}
 
$token_url = "https://graph.facebook.com/oauth/access_token?client_id="
. $app_id . "&redirect_uri=" . urlencode($my_url)
. "&client_secret=" . $app_secret
. "&code=" . $code;
$response = file_get_contents($token_url);
$p = null;
parse_str($response, $p);
$access_token = $p['access_token'];
$graph_url= "https://graph.facebook.com/me/photos?"
         . "access_token=" .$access_token;
if (!empty($_FILES)) {
    $params = array();
    if( isset($_POST['message']) ) {
        $params['message'] = trim($_POST['message']);
    }
     
    $uploaddir = './uploads/'; // Upload folder
    $uploadfile = $uploaddir . basename($_FILES['source']['name']);
    if (move_uploaded_file($_FILES['source']['tmp_name'], $uploadfile)) {
        $params['source'] = "@" . realpath($uploadfile);
    }
     
    // Start the Graph API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$graph_url);
     
    /*
        Next option is only used for 
        user from a local (WAMP) 
        machine. This should be removed
        when used on a live server!
        refer:https://github.com/facebook/php-sdk/issues/7
    */
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
     
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($ch);
    $decoded = json_decode($result, true);
    curl_close($ch);
    if(is_array($decoded) && isset($decoded['id'])) {
        /*
            Picture is uploaded successfully:
            1) show success message
            2) optionally, delete image from our server
        */
        $msg = "Image uploaded successfully: {$decoded['id']}";
    }
}
?>
<!doctype html>
<html>
<head>
<title>Upload</title>
<style>
label {float: left; width: 100px;}
input[type=text],textarea {width: 210px;}
#msg {border: 1px solid #000; padding: 5px; color: red;}
</style>
</head>
<body>
<?php if( isset($msg) ) { ?>
<p id="msg"><?php echo $msg; ?></p>
<?php } ?>
<form enctype="multipart/form-data" action="" method="post">
    <p><label for="name">Caption</label><input type="text" name="message" value="" /></p>
    <p><label for="source">Photo</label><input type="file" name="source" /></p>
    <p><input type="submit" value="Upload" /></p>
</form>
</body>
</html>
