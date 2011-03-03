<?php

require_once dirname(__FILE__).'/../../../wp-config.php';
require_once 'class/liked_content.php';

global $wpdb;
global $current_user;
wp_get_current_user();

$user_id = $current_user->ID;

if($_REQUEST['action'] == "checklike"){
	if(LikedContent::userLikesThis($user_id, $_REQUEST["content_id"], $_REQUEST["content_type"])){
		echo 1;
	}
	else{
		echo 0;
	}
	die();
}
if($_REQUEST['action'] == "setemblem"){
	$arr = explode('-', $_REQUEST['id']);
	
	$lc = LikedContent::userLikesThis($user_id, $arr[1], $arr[0]);
	$lc = LikedContent::getLikeById($lc);
	
	$lc->setEmblem($_REQUEST['em']);
	
	$lc->save();
	die();
}



?>
<script type="text/javascript">

jQuery(document).ready(function(){
	
	jQuery('#nsLikeThis-hor-list .most-recent').click(function() {
		return ajaxTableLoad(this, 'recent');
  	});
  	jQuery('#nsLikeThis-hor-list .most-liked').click(function() {
  		return ajaxTableLoad(this, 'liked');
  	});
  	jQuery('#nsLikeThis-hor-list .most-visited').click(function() {
  		return ajaxTableLoad(this, 'visited');
  	});

	// By default, most recent
	ajaxTableLoad('#nsLikeThis-hor-list .most-recent' , 'recent');
	
});

</script>

<?php
global $current_user;

wp_get_current_user();

$user_liked_contents = LikedContent::getLikedContentsForUser($current_user->ID);

?>
	
		<div class="nsLikeThis-order"><?php _e('Order by', 'ns-like-this') ?>:</div>
		<ul id="nsLikeThis-hor-list">
			<li class="most-recent active"><?php _e('Most recent', 'ns-like-this') ?></li>
			<li class="most-visited"><?php _e('Most visited', 'ns-like-this') ?></li>
			<li class="most-liked"><?php _e('Most liked', 'ns-like-this') ?></li>
		</ul>
		
		<table id="dataTable" class="nsLikeThis-table">
		<thead>
		<tr>
			<th style="width:20px;"></th>
			<th style="width:40px;"></th>
			<th><?php _e('Date Liked', 'ns-like-this') ?></th>
			<th style="min-width:50%;"> <span style="margin-left: 18px"><?php _e('Title', 'ns-like-this') ?></span></th>
			<!--<th>Content Type</th>-->
		</tr>
		</thead>
		<tbody>
		
		</tbody>
		</table>
				
				
