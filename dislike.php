<?php
require_once '../../../wp-config.php';
require_once 'class/liked_content.php';

global $wpdb;
global $current_user;
wp_get_current_user();

$user_id = $current_user->ID;
$content_id = $_POST['id'];
$content_type = $_POST['type'];

if($content_id != ''){
    if ($id = LikedContent::userLikesThis($user_id, $content_id, $content_type)) {
		$like = LikedContent::getLikeById($id);
		$like->setActive(0);
		$like->save();
	}
	
	echo LikedContent::getContentLikes($content_id, $content_type);
}
?>