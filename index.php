<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	
	require 'api/facebook.php';
	
	$facebook = new Facebook(array(
		'appId'  => "1722350238025805",
		'secret' => "3785743700eeb3f2752d39c5825a9ceb",
		"cookie" => true,
		'fileUpload' => true
	));
	
	$user_id = $facebook->getUser();
	
	if($user_id == 0 || $user_id == "")
	{
		$login_url = $facebook->getLoginUrl(array(
		'redirect_uri'         => "http://apps.facebook.com/bidpropic/",
		'scope'      => "email,publish_stream,user_hometown,user_location,user_photos,friends_photos,
					user_photo_video_tags,friends_photo_video_tags,user_videos,video_upload,friends_videos"));
		
		echo "<script type='text/javascript'>top.location.href = '$login_url';</script>";
		exit();
	}
	
	//get profile album
	$albums = $facebook->api("/me/albums");
	$album_id = ""; 
	foreach($albums["data"] as $item){
		if($item["type"] == "profile"){
			$album_id = $item["id"];
			break;
		}
	}
	
	//set photo atributes
	$full_image_path = realpath("Koala.jpg");
	$args = array('message' => 'Uploaded by 4rapiddev.com');
	$args['image'] = '@' . $full_image_path;
	
	//upload photo to Facebook
	$data = $facebook->api("/{$album_id}/photos", 'post', $args);
	$pictue = $facebook->api('/'.$data['id']);
	
	$fb_image_link = $pictue['link']."&makeprofile=1";
	
	//redirect to uploaded photo url and change profile picture
	echo "<script type='text/javascript'>top.location.href = '$fb_image_link';</script>";
?>
