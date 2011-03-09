<?php
/*
Plugin Name: NS Like This
Plugin URI: http://www.net-solutions.es
Description: This plugin allows users to like your posts/pages/categories/tags instead of commment it.
Version: 0.1.1
Author: Net Solutions
Author URI: http://www.net-solutions.es

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

class LikedContent{
	
	private $id;
	private $time;
	private $user_id;
	private $content_id;
	private $content_type;
	private $ip;
	private $clicks;
	private $active;
	private $emblem;
	
	// Constructor
	static function create($uid, $cont_id, $cont_type, $ip = null){
		$obj = new LikedContent();
		
		$obj->time = date('Y-m-d H:i:s');
		$obj->user_id = $uid;
		$obj->content_id = $cont_id;
		$obj->content_type = $cont_type;
		$obj->ip = $ip;
		$obj->active = 1;
		$obj->clicks = 0;
		
		return $obj;	
	}
	
	// Static functions
	static function getLikedContentsForUser($uid){
		global $wpdb;
	
		$qry = "SELECT * FROM ".$wpdb->prefix."nslikethis_votes WHERE user_id = '$uid' AND active = 1 ORDER BY time DESC";
		$rs = mysql_query($qry);
		
		$result = array();
		
		if($rs && mysql_num_rows($rs)>0){
			while($lc = mysql_fetch_object($rs, "LikedContent")){
				$result[] = $lc;
			}		
		}
		return $result;
	}
	static function getContentLikes($cont_id, $cont_type){
		global $wpdb;
	
		$qry = "SELECT COUNT(*) as likes FROM ".$wpdb->prefix."nslikethis_votes WHERE content_id = '$cont_id' 
				AND content_type = '$cont_type' AND active = 1 GROUP BY content_id, content_type";
		$rs = mysql_query($qry);
		
		if($rs && mysql_num_rows($rs)>0){
			$row = mysql_fetch_object($rs);
			
			return $row->likes;
		}
		else{
			return 0;
		}	
	}
	static function userLikesThis($uid, $cont_id, $cont_type){
		global $wpdb;
		
		$qry = "SELECT id FROM ".$wpdb->prefix."nslikethis_votes WHERE 
				content_id = '$cont_id' AND content_type = '$cont_type' AND user_id = '$uid' AND active = 1";
		$rs = mysql_query($qry);
		
		if($rs && mysql_num_rows($rs)>0){
			$row = mysql_fetch_object($rs);
			
			return $row->id;
		}
		else{
			return false;
			
		}
	}
	static function checkInactiveLike($uid, $cont_id, $cont_type){
		global $wpdb;
		
		$qry = "SELECT id FROM ".$wpdb->prefix."nslikethis_votes WHERE 
				content_id = '$cont_id' AND content_type = '$cont_type' AND user_id = '$uid' AND active = 0";
		$rs = mysql_query($qry);
		
		if($rs && mysql_num_rows($rs)>0){
			$row = mysql_fetch_object($rs);
			
			return $row->id;
		}
		else{
			return false;
		}
	}
	static function getLikeById($id){
		global $wpdb;
		
		$qry = "SELECT * FROM ".$wpdb->prefix."nslikethis_votes WHERE id = '$id'";
		$rs = mysql_query($qry);
		
		if($rs && mysql_num_rows($rs)>0){
			$row = mysql_fetch_object($rs, "LikedContent");
			
			return $row;
		}
		else{
			return false;
		}
	
	}	
	static function getLikeByTime($time){
		global $wpdb;
		
		$qry = "SELECT * FROM ".$wpdb->prefix."nslikethis_votes WHERE time = '$time'";
		$rs = mysql_query($qry);
		
		if($rs && mysql_num_rows($rs)>0){
			$row = mysql_fetch_object($rs, "LikedContent");
			
			return $row;
		}
		else{
			return false;
		}
	
	}
	static function getMostLiked($limit){
		global $wpdb;
		
		$qry = "SELECT id, count(*) AS c FROM ".$wpdb->prefix."nslikethis_votes 
				WHERE active = 1 
				GROUP BY content_id, content_type 
				ORDER BY c DESC LIMIT 0, ".$limit.";";
		$rs = mysql_query($qry);
		
		$result = array();
		
		if($rs && mysql_num_rows($rs)>0){
			while($lc = mysql_fetch_object($rs)){
				$result[] = LikedContent::getLikeById($lc->id);
			}		
		}
		return $result;
	
	}
	static function getMostClicked($uid, $limit){
		global $wpdb;
		
		$qry = "SELECT * FROM ".$wpdb->prefix."nslikethis_votes 
				WHERE user_id = '$uid' AND active = 1 
				ORDER BY clicks DESC LIMIT 0, ".$limit.";";
		$rs = mysql_query($qry);
		
		$result = array();
		
		if($rs && mysql_num_rows($rs)>0){
			while($lc = mysql_fetch_object($rs, "LikedContent")){
				$result[] = $lc;
			}		
		}
		return $result;
	
	}
	static function getLatestLiked($uid, $limit){
		global $wpdb;
		
		$qry = "SELECT * FROM ".$wpdb->prefix."nslikethis_votes 
				WHERE user_id = '$uid' AND active = 1
				ORDER BY time DESC LIMIT 0, ".$limit.";";
		$rs = mysql_query($qry);
		
		$result = array();
		
		if($rs && mysql_num_rows($rs)>0){
			while($lc = mysql_fetch_object($rs, "LikedContent")){
				$result[] = $lc;
			}		
		}
		return $result;
	
	}
	static function getCurrentId(){
		global $wp_query;
		
		if (is_category()){
			$t = get_term_by('id',get_query_var('cat'), 'category');		
			return $t->term_id;
			
		} else if( is_tag()){
			$t = get_term_by('name',get_query_var('tag'), 'post_tag');
			return $t->term_id;
		
		} else{ // Page or post
			return get_the_ID();
		}
	
	}
	static function getCurrentType(){
		global $wp_query;
		
		if (is_page()){
			return "page";
		
		} else if (is_category()){
			return "category";
			
		} else if( is_tag()){
			return "tag";
		
		} else{
			return "post";	
		}
	
	}
	
	// Attribute Getters
	function getId(){
		return $this->id;
	}
	function getTime(){
		return $this->time;
	}
	function getNiceTime($thehour = 1){
		$time = $this->time;
		$nice_time = "";
		
		setlocale(LC_ALL, 'it_IT@euro', 'it_IT', 'it');
		
		if( date('d-m-Y', strtotime($time)) == date('d-m-Y') ){
    		$nice_time .= __('Today', 'ns-like-this');
    	}
    	else if( date('d-m-Y', strtotime($time)) == date('d-m-Y', strtotime('-1 day')) ){
    		$nice_time .= __('Yesterday', 'ns-like-this');
    	}
    	else if( strtotime($time) > strtotime('-1 week') ){
    		$nice_time .= sprintf(__('Last %s', 'ns-like-this'), htmlentities(gmstrftime('%A', strtotime($time))));
    	}
    	else{
    		$nice_time .= date('d-m-Y', strtotime($time));
    	}
    	
    	if($thehour){
    		$nice_time .= " ".sprintf(__("at %s", 'ns-like-this'), date('H:i' ,strtotime($time)));
    	}
    	
    	return $nice_time;
	}
	function getUserId(){
		return $this->user_id;
	}
	function getContentId(){
		return $this->content_id;
	}
	function getContentType(){
		return $this->content_type;
	}
	function getIp(){
		return $this->ip;	
	}
	function getClicks(){
		return $this->clicks;	
	}
	function isActive(){
		return $this->active;
	}
	function getEmblem(){
		return $this->emblem;	
	}
	
	// Attribute Setters
	function setTime($newval){
		$this->time = $newval;
	}
	function setUserId($newval){
		$this->user_id = $newval;
	}
	function setContentId($newval){
		$this->content_id = $newval;
	}
	function setContentType($newval){
		$this->content_type = $newval;
	}
	function setIp($newval){
		$this->ip = $newval;
	}
	function setClicks($newval){
		$this->clicks = $newval;
	}
	function incrementClicks(){
		$this->setClicks($this->clicks + 1);
	}
	function setActive($newval){
		$this->active = $newval;
	}
	function setEmblem($newval){
		$this->emblem = $newval;
	}
	
	// WP Getters
	function is_a_post(){
		return ($this->content_type == "post");
	}
	function is_a_page(){
		return ($this->content_type == "page");
	}
	function is_a_tag(){
		return ($this->content_type == "tag");
	}
	function is_a_category(){
		return ($this->content_type == "category");
	}
	
	function getName(){
		if($this->is_a_post() || $this->is_a_page()){
			$post = get_post($this->content_id);
			return $post->post_title;
		}
		else if($this->is_a_tag()){
			$term = get_term($this->content_id, 'post_tag');
			return $term->name;
		}
		else if($this->is_a_category()){					
			$term = get_term($this->content_id, 'category');
			return $term->name;
		}
	
	}
	
	function getLink(){
		if($this->is_a_post() || $this->is_a_page()){
			$post = get_post($this->content_id);
			$link = get_permalink($post->ID);
		}
		else if($this->is_a_tag()){
			$link = get_tag_link($this->content_id);
		}
		else if($this->is_a_category()){					
			$link = get_category_link($this->content_id);
		}
		return home_url()."/wp-content/plugins/ns-like-this/go.php?id=".$this->id."&link=".$link;
	}
	
	// Database Storage
	public function save() {
		if ( $this->getId() ){
			return $this->update();
		}
		else{
			return $this->insert();
		}
	}
	
	private function insert() {
		global $wpdb;
		
		if( $id = LikedContent::checkInactiveLike($this->user_id, $this->content_id, $this->content_type) ){
			$this->id = $id;
			
			return $this->update();
		}
		else{
			if($this->emblem == ""){
				$this->emblem = "em-0.png";
			}
		
			$qry = "INSERT INTO ".$wpdb->prefix."nslikethis_votes  (time, user_id, content_id, content_type,	ip, clicks, active, emblem) 
					VALUES ('$this->time', '$this->user_id', '$this->content_id', '$this->content_type','$this->ip', '$this->clicks', 1, '$this->emblem')";
									
			if (mysql_query( $qry )){
				$tmp = LikedContent::getLikeByTime($this->time);
				
				if ($tmp){
					$this->id = $tmp->id;
				
					return $this->id;
				}
				else{
					return false;
				}
			}
			else{
				throw new Exception("Database error: insert");
			}
		}
	}

	public function update() {
		global $wpdb;
		
		if($this->emblem == ""){
			$this->emblem = "em-0.png";
		}
			
		$qry = "UPDATE ".$wpdb->prefix."nslikethis_votes SET time = '$this->time', user_id = '$this->user_id', 
				content_id = '$this->content_id', content_type = '$this->content_type', ip = '$this->ip', clicks = '$this->clicks',
				active = '$this->active', emblem = '$this->emblem' 
				WHERE id = '$this->id'";
		
		if (mysql_query( $qry ) or die(mysql_error(). $qry)){
			return $this->id;
		}
		else{
			throw new Exception("Database error: update");
		}

	}

}



?>