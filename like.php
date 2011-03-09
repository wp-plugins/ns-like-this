<?php
require_once '../../../wp-config.php';
require_once 'class/liked_content.php';

global $wpdb;
global $current_user;
wp_get_current_user();

$user_id = $current_user->ID;
$content_id = $_POST['id'];
$content_type = $_POST['type'];
$ip = $_SERVER['REMOTE_ADDR'];

if($content_id != ''){
    if (! LikedContent::userLikesThis($user_id, $content_id, $content_type)) {
		$like = LikedContent::create($user_id, $content_id, $content_type, $ip);
		$like->save();
	}
	
	$count = LikedContent::getContentLikes($content_id, $content_type);
	
	if($_POST['fulltext']){
		echo sprintf(_n('%d person like this', '%d people like this', $count, 'ns-like-this'), $count);
	}
	else{
		echo $count;
	}
}
?>