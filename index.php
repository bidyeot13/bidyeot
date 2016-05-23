<?php
session_start();
require 'src/config.php'; // application credential open this file and add your app credentials and call back url
require 'src/facebook.php';
 
$facebook = new Facebook(array(
  'appId'  => $config['App_ID'],
  'secret' => $config['App_Secret'],
  'cookie' => true
)); // create Object of Facebook
 
 
if(isset($_POST['upload']))
{
    $target = "upload/";
    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
    $detectedType = exif_imagetype($_FILES['file']['tmp_name']);
    if(!in_array($detectedType, $allowedTypes))
    {
        $message = "Invalid File.";
    }
    else
    {
        $target = $target . basename( $_FILES['file']['name']) ; 
        if(move_uploaded_file($_FILES['file']['tmp_name'], $target)) 
        {
            try
            {
                $image['access_token']  = $_SESSION['token'];
                $image['message']       = 'Upload this image using www.phpgang.com tutorial demo!!';
                $image['image']         = '@'.realpath("upload/".$_FILES['file']['name']);
                $facebook->setFileUploadSupport(true);
                $img = $facebook->api('/me/photos', 'POST', $image);
                $message = "Image Uploaded on facebook <a href='https://www.facebook.com/photo.php?fbid=10202356836865359".$img['id']."' target='_blank'>Click Here</a> to view!!";
            }
            catch(FacebookApiException $e)
            {
                $message = "Sorry, there was a problem uploading your file please try again.";
            }
        } 
        else
        {
            $message = "Sorry, there was a problem uploading your file please try again.";
        }
    }
    $content = '
    <style>
    #form
    {
        margin-left:auto;
        margin-right:auto;
        width: 220px;
    }
    </style>
    
    <form action="index.php" id="form" method="post" enctype="multipart/form-data" >
    <div>'.$message.'</div><br><br>
    <input type="file" name="file" /><br />
    <input type="submit" name="upload" value="  U P L O A D  " style="padding: 5px;" />
    <form>';
}
elseif(isset($_GET['fbTrue']))
{
    $token_url = "https://graph.facebook.com/oauth/access_token?"
       . "client_id=".$config['App_ID']."&redirect_uri=" . urlencode($config['callback_url'])
       . "&client_secret=".$config['App_Secret']."&code=" . $_GET['code']; 
 
    $response = file_get_contents_curl($token_url);
 
    $params = null;
    parse_str($response, $params);
    $_SESSION['token'] = $params['access_token'];
    
    $content = '
    <style>
    #form
    {
        margin-left:auto;
        margin-right:auto;
        width: 220px;
    }
    </style>
    <form action="index.php" id="form" method="post" enctype="multipart/form-data" >
    <input type="file" name="file" /><br />
    <input type="submit" name="upload" value="  U P L O A D  " style="padding: 5px;" />
    <form>';
     
}
else
{
    $content = '<a href="https://www.facebook.com/dialog/oauth?client_id='.$config['App_ID'].'&redirect_uri='.$config['callback_url'].'&scope=email,user_likes,publish_stream"><img src="./images/login-button.png" alt="Sign in with Facebook"/></a>';
}
 
echo $content;
 
 
function file_get_contents_curl($url) { // function used to replace file_get_content and fopen
    $ch = curl_init();
 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_URL, $url);
 
    $data = curl_exec($ch);
    curl_close($ch);
 
    return $data;
}
?>
