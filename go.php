<?php
	require_once '../../../wp-blog-header.php';
	require_once 'class/liked_content.php';

	global $wpdb;
	
	$lc = LikedContent::getLikeById($_REQUEST['id']);
	$lc->incrementClicks();
	$lc->save();

	Header( "HTTP/1.1 301 Moved Permanently" );
	Header( "Location: ".$_REQUEST['link'] ); 

?>
