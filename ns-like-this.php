<?php
/*
Plugin Name: NS Like This
Plugin URI: http://www.net-solutions.es
Description: This plugin allows users to like your posts/pages/categories/tags instead of commment it.
Version: 1.1
Author: Net Solutions
Author URI: http://www.net-solutions.es
License: GPLv3
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

#### LOAD TRANSLATIONS ####
load_plugin_textdomain('ns-like-this', 'wp-content/plugins/ns-like-this/lang/', 'ns-like-this/lang/');
####


#### INSTALL PROCESS ####
$nslt_dbVersion = "1.0";

#### LOAD LIKEDCONTENT CLASS ####
require_once 'class/liked_content.php';

function my_liked_page_install() {

    global $wpdb;

    $the_page_title = __('My liked content', 'ns-like-this');
    $the_page_name = 'my-liked';

    // the menu entry...
    delete_option("my_liked_content_page_title");
    add_option("my_liked_content_page_title", $the_page_title, '', 'yes');
    // the slug...
    delete_option("my_liked_content_page_name");
    add_option("my_liked_content_page_name", $the_page_name, '', 'yes');
    // the id...
    delete_option("my_liked_content_page_id");
    add_option("my_liked_content_page_id", '0', '', 'yes');

    $the_page = get_page_by_title( $the_page_title );

    if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "[ns-my-liked-content /]";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );
        

    }
    else {
        // the plugin may have been previously active and the page may just be trashed...

        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );

    }
	
	add_post_meta($the_page_id, '_allow_all', true, true) or update_post_meta($the_page_id, '_allow_all', true);
	
    delete_option( 'my_liked_content_page_id' );
    add_option( 'my_liked_content_page_id', $the_page_id );

}

function my_liked_page_remove() {

    global $wpdb;

    $the_page_title = get_option( "my_liked_content_page_title" );
    $the_page_name = get_option( "my_liked_content_page_name" );

    //  the id of our page...
    $the_page_id = get_option( 'my_liked_content_page_id' );
    if( $the_page_id ) {

        wp_delete_post( $the_page_id ); // this will trash, not delete

    }

    delete_option("my_liked_content_page_title");
    delete_option("my_liked_content_page_name");
    delete_option("my_liked_content_id");

}

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'my_liked_page_install'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'my_liked_page_remove' );

function setOptionsNSLT() {
	global $wpdb;
	global $nslt_dbVersion;
	
	$table_name = $wpdb->prefix . "nslikethis_votes";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			time TIMESTAMP NOT NULL,
			user_id BIGINT(20) NOT NULL,
			content_id BIGINT(20) NOT NULL,
			content_type VARCHAR(32) NOT NULL,
			ip VARCHAR(15) NOT NULL,
			clicks INT(11) NOT NULL,
			active TINYINT(1) NOT NULL,
			emblem VARCHAR(128) NOT NULL,
			UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		add_option("nslt_dbVersion", $nslt_dbVersion);
	}
	
	add_option('nslt_jquery', '1', '', 'yes');
	add_option('nslt_onPage', '1', '', 'yes');
	add_option('nslt_useEmblems', '1', '', 'yes');
	add_option('nslt_textOrImage', 'image', '', 'yes');
	add_option('nslt_text', 'NS Like This', '', 'yes');
}

register_activation_hook(__FILE__, 'setOptionsNSLT');

function unsetOptionsNSLT() {
	global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."nslikethis_votes");

	delete_option('nslt_jquery');
	delete_option('nslt_onPage');
	delete_option('nslt_useEmblems');
	delete_option('nslt_textOrImage');
	delete_option('nslt_text');
	delete_option('most_liked_content');
	delete_option('nslt_dbVersion');
}

register_uninstall_hook(__FILE__, 'unsetOptionsNSLT');
####


#### ADMIN OPTIONS ####
function NSLikeThisAdminMenu() {
	add_options_page('NS Like This', 'NS Like This', '10', 'NSLikeThisAdminMenu', 'NSLikeThisAdminContent');
}
add_action('admin_menu', 'NSLikeThisAdminMenu');

function NSLikeThisAdminRegisterSettings() { // whitelist options
	register_setting( 'nslt_options', 'nslt_jquery' );
	register_setting( 'nslt_options', 'nslt_onPage' );
	register_setting( 'nslt_options', 'nslt_useEmblems' );
	register_setting( 'nslt_options', 'nslt_textOrImage' );
	register_setting( 'nslt_options', 'nslt_text' );
}
add_action('admin_init', 'NSLikeThisAdminRegisterSettings');

function NSLikeThisAdminContent() {
?>
<div class="wrap">
	<h2><?php _e('"NS Like This" Options', 'ns-like-this') ?></h2>
	<br class="clear" />
			
	<div id="poststuff" class="ui-sortable meta-box-sortables">
		<div id="nslikethisoptions" class="postbox">
		<h3><?php _e('Configuration', 'ns-like-this'); ?></h3>
			<div class="inside">
			<form method="post" action="options.php">
			<?php settings_fields('nslt_options'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="nslt_jquery"><?php _e('jQuery framework', 'ns-like-this'); ?></label></th>
					<td>
						<select name="nslt_jquery" id="nslt_jquery">
							<?php echo get_option('nslt_jquery') == '1' ? '<option value="1" selected="selected">'.__('Enabled', 'ns-like-this').'</option><option value="0">'.__('Disabled', 'ns-like-this').'</option>' : '<option value="1">'.__('Enabled', 'ns-like-this').'</option><option value="0" selected="selected">'.__('Disabled', 'ns-like-this').'</option>'; ?>
						</select>
						<span class="description"><?php _e('Disable it if you already have the jQuery framework enabled in your theme.', 'ns-like-this'); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><legend><?php _e('Image or text?', 'ns-like-this'); ?></legend></th>
					<td>
						<label for="nslt_textOrImage" style="padding:3px 20px 3px 0; margin-right:20px; background: url(<?php echo WP_PLUGIN_URL.'/ns-like-this/css/add.png'; ?>) no-repeat right center;">
						<?php echo get_option('nslt_textOrImage') == 'image' ? '<input type="radio" name="nslt_textOrImage" id="nslt_textOrImage" value="image" checked="checked">' : '<input type="radio" name="nslt_textOrImage" id="nslt_textOrImage" value="image">'; ?>
						</label>
						<label for="nslt_text">
						<?php echo get_option('nslt_textOrImage') == 'text' ? '<input type="radio" name="nslt_textOrImage" id="nslt_textOrImage" value="text" checked="checked">' : '<input type="radio" name="nslt_textOrImage" id="nslt_textOrImage" value="text">'; ?>
						<input type="text" name="nslt_text" id="nslt_text" value="<?php echo get_option('nslt_text'); ?>" />
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><legend><?php _e('Automatic display', 'ns-like-this'); ?></legend></th>
					<td>
						<label for="nslt_onPage">
						<?php echo get_option('nslt_onPage') == '1' ? '<input type="checkbox" name="nslt_onPage" id="nslt_onPage" value="1" checked="checked">' : '<input type="checkbox" name="nslt_onPage" id="nslt_onPage" value="1">'; ?>
						<?php _e('<strong>On all posts</strong> (home, archives, search) at the bottom of the post', 'ns-like-this'); ?>
						</label>
						<p class="description"><?php _e('If you disable this option, you have to put manually the code', 'ns-like-this'); ?><code>&lt;?php if(function_exists(getNSLikeThis)) getNSLikeThis('get'); ?&gt;</code> <?php _e('wherever you want in your template.', 'ns-like-this'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><legend><?php _e('Use emblems', 'ns-like-this'); ?></legend></th>
					<td>
						<label for="nslt_useEmblems">
						<?php echo get_option('nslt_useEmblems') == '1' ? '<input type="checkbox" name="nslt_useEmblems" id="nslt_useEmblems" value="1" checked="checked">' : '<input type="checkbox" name="nslt_useEmblems" id="nslt_useEmblems" value="1">'; ?>
						<?php _e('Allows you to associate an emblem to each <em>like</em>', 'ns-like-this'); ?>
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options', 'ns-like-this'); ?>" /></th>
					<td></td>
				</tr>
			</table>
			</form>
			</div>
		</div>
	</div>
	
</div>
<?php
}
####


#### WIDGET MOST LIKED ####
function most_liked_content($numberOf, $before, $after, $show_count) {
	global $wpdb;

    $most_liked = LikedContent::getMostLiked($numberOf);

    foreach ($most_liked as $lc) {
    	$title = $lc->getName();
    	$link = $lc->getLink();
    	$count = LikedContent::getContentLikes($lc->getContentId(), $lc->getContentType());
    	
    	echo $before.'<a href="' . $link . '" title="' . $title.'" rel="nofollow">' . $title . '</a>';
		echo $show_count == '1' ? ' ('.$count.')' : '';
		echo $after;
    }
}

function add_widget_most_liked_content() {
	function widget_most_liked_content($args) {
		extract($args);
		$options = get_option("most_liked_content");
		if (!is_array( $options )) {
			$options = array(
			'title' => __('Most liked content', 'ns-like-this'),
			'number' => '5',
			'show_count' => '0'
			);
		}
		$title = $options['title'];
		$numberOf = $options['number'];
		$show_count = $options['show_count'];
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="mostlikedcontent">';

		most_liked_content($numberOf, '<li>', '</li>', $show_count);
		
		echo '</ul>';
		echo $after_widget;
	}	
	register_sidebar_widget(__('Most liked content', 'ns-like-this'), 'widget_most_liked_content');
	
	function options_widget_most_liked_content() {
		$options = get_option("most_liked_content");
		
		if (!is_array( $options )) {
			$options = array(
			'title' => __('Most liked content', 'ns-like-this'),
			'number' => '5',
			'show_count' => '0'
			);
		}
		
		if ($_POST['mlp-submit']) {
			$options['title'] = htmlspecialchars($_POST['mlp-title']);
			$options['number'] = htmlspecialchars($_POST['mlp-number']);
			$options['show_count'] = $_POST['mlp-show-count'];
			if ( $options['number'] > 15) { $options['number'] = 15; }
			
			update_option("most_liked_content", $options);
		}
		?>
		<p><label for="mlp-title"><?php _e('Title', 'ns-like-this'); ?>:<br />
		<input class="widefat" type="text" id="mlp-title" name="mlp-title" value="<?php echo $options['title'];?>" /></label></p>
		
		<p><label for="mlp-number"><?php _e('Number of posts to show', 'ns-like-this'); ?><br />
		<input type="text" id="mlp-number" name="mlp-number" style="width: 25px;" value="<?php echo $options['number'];?>" /> <small>(max. 15)</small></label></p>
		
		<p><label for="mlp-show-count"><input type="checkbox" id="mlp-show-count" name="mlp-show-count" value="1"<?php if($options['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show likes counter', 'ns-like-this'); ?></label></p>
		
		<input type="hidden" id="mlp-submit" name="mlp-submit" value="1" />
		<?php
	}
	register_widget_control('Most liked content', 'options_widget_most_liked_content');
} 

add_action('init', 'add_widget_most_liked_content');
####

#### WIDGET MY LATEST LIKED ####
function latest_liked_content($numberOf, $before, $after, $show_count) {
	global $wpdb;
	global $current_user;
	get_currentuserinfo();
	
	$user_id = $current_user->id;

    $latest_liked = LikedContent::getLatestLiked($user_id, $numberOf);

    foreach ($latest_liked as $lc) {
    	$title = $lc->getName();
    	$link = $lc->getLink();
    	$lc_time = $lc->getNiceTime(0);
    	
    	echo $before.'<a href="' . $link . '" title="' . $title.'" rel="nofollow">' . $title . '</a>';
		echo $show_count == '1' ? ' ('.$lc_time.')' : '';
		echo $after;
    }
}

function add_widget_latest_liked_content() {
	function widget_latest_liked_content($args) {
		extract($args);
		$options = get_option("latest_liked_content");
		if (!is_array( $options )) {
			$options = array(
			'title' => __('Latest liked content', 'ns-like-this'),
			'number' => '5',
			'show_count' => '0'
			);
		}
		$title = $options['title'];
		$numberOf = $options['number'];
		$show_count = $options['show_count'];
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="latestlikedcontent">';

		latest_liked_content($numberOf, '<li>', '</li>', $show_count);
		
		echo '</ul>';
		echo $after_widget;
	}	
	register_sidebar_widget(__('Latest liked content', 'ns-like-this'), 'widget_latest_liked_content');
	
	function options_widget_latest_liked_content() {
		$options = get_option("latest_liked_content");
		
		if (!is_array( $options )) {
			$options = array(
			'title' => __('Latest liked content', 'ns-like-this'),
			'number' => '5',
			'show_count' => '0'
			);
		}
		
		if ($_POST['mlp-submit']) {
			$options['title'] = htmlspecialchars($_POST['mlp-title']);
			$options['number'] = htmlspecialchars($_POST['mlp-number']);
			$options['show_count'] = $_POST['mlp-show-count'];
			if ( $options['number'] > 15) { $options['number'] = 15; }
			
			update_option("latest_liked_content", $options);
		}
		?>
		<p><label for="mlp-title"><?php _e('Title', 'ns-like-this'); ?>:<br />
		<input class="widefat" type="text" id="mlp-title" name="mlp-title" value="<?php echo $options['title'];?>" /></label></p>
		
		<p><label for="mlp-number"><?php _e('Number of posts to show', 'ns-like-this'); ?><br />
		<input type="text" id="mlp-number" name="mlp-number" style="width: 25px;" value="<?php echo $options['number'];?>" /> <small>(max. 15)</small></label></p>
		
		<p><label for="mlp-show-count"><input type="checkbox" id="mlp-show-count" name="mlp-show-count" value="1"<?php if($options['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show dates', 'ns-like-this'); ?></label></p>
		
		<input type="hidden" id="mlp-submit" name="mlp-submit" value="1" />
		<?php
	}
	register_widget_control(__('Latest liked content', 'ns-like-this'), 'options_widget_latest_liked_content');
} 

add_action('init', 'add_widget_latest_liked_content');
####

#### WIDGET MOST VISITED LIKED ####
function most_visited_liked_content($numberOf, $before, $after, $show_count) {
	global $wpdb;
	global $current_user;
	get_currentuserinfo();
	
	$user_id = $current_user->id;

    $most_visited_liked = LikedContent::getMostClicked($user_id, $numberOf);

    foreach ($most_visited_liked as $lc) {
    	$title = $lc->getName();
    	$link = $lc->getLink();
    	$clicks = $lc->getClicks();
    	
    	echo $before.'<a href="' . $link . '" title="' . $title.'" rel="nofollow">' . $title . '</a>';
		echo $show_count == '1' ? ' ('.$clicks.')' : '';
		echo $after;
    }
}

function add_widget_most_visited_liked_content() {
	function widget_most_visited_liked_content($args) {
		extract($args);
		$options = get_option("most_visited_liked_content");
		if (!is_array( $options )) {
			$options = array(
			'title' => __('Most visited liked content', 'ns-like-this'),
			'number' => '5',
			'show_count' => '0'
			);
		}
		$title = $options['title'];
		$numberOf = $options['number'];
		$show_count = $options['show_count'];
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="mostvisitedlikedcontent">';

		most_visited_liked_content($numberOf, '<li>', '</li>', $show_count);
		
		echo '</ul>';
		echo $after_widget;
	}	
	register_sidebar_widget(__('Most visited liked content', 'ns-like-this'), 'widget_most_visited_liked_content');
	
	function options_widget_most_visited_liked_content() {
		$options = get_option("most_visited_liked_content");
		
		if (!is_array( $options )) {
			$options = array(
			'title' => __('Most visited liked content', 'ns-like-this'),
			'number' => '5',
			'show_count' => '0'
			);
		}
		
		if ($_POST['mlp-submit']) {
			$options['title'] = htmlspecialchars($_POST['mlp-title']);
			$options['number'] = htmlspecialchars($_POST['mlp-number']);
			$options['show_count'] = $_POST['mlp-show-count'];
			if ( $options['number'] > 15) { $options['number'] = 15; }
			
			update_option("most_visited_liked_content", $options);
		}
		?>
		<p><label for="mlp-title"><?php _e('Title', 'ns-like-this'); ?>:<br />
		<input class="widefat" type="text" id="mlp-title" name="mlp-title" value="<?php echo $options['title'];?>" /></label></p>
		
		<p><label for="mlp-number"><?php _e('Number of posts to show', 'ns-like-this'); ?>:<br />
		<input type="text" id="mlp-number" name="mlp-number" style="width: 25px;" value="<?php echo $options['number'];?>" /> <small>(max. 15)</small></label></p>
		
		<p><label for="mlp-show-count"><input type="checkbox" id="mlp-show-count" name="mlp-show-count" value="1"<?php if($options['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show count', 'ns-like-this'); ?></label></p>
		
		<input type="hidden" id="mlp-submit" name="mlp-submit" value="1" />
		<?php
	}
	register_widget_control('Most visited liked content', 'options_widget_most_visited_liked_content');
} 

add_action('init', 'add_widget_most_visited_liked_content');
####

#### FRONT-END VIEW ####
function getNSLikeThis($arg) {
	global $wpdb;
	global $current_user;
	get_currentuserinfo();
	
	$user_id = $current_user->id;
		
	$content_id = LikedContent::getCurrentId();
	$content_type = LikedContent::getCurrentType();
	
	$ip = $_SERVER['REMOTE_ADDR'];
	
	$liked = LikedContent::getContentLikes($content_id, $content_type);
			
    if (! LikedContent::userLikesThis($user_id, $content_id, $content_type)) {
    	if (get_option('nslt_textOrImage') == 'image') {
    		$counter = '<a onclick="externalLikeThis('.$content_id.', \''.$content_type.'\');" class="image">'. __('I like this', 'ns-like-this') .'</a>';
    	}
    	else {
    		$counter = $liked.' <a onclick="externalLikeThis('.$content_id.', '.$content_type.');">'.get_option('nslt_text').'</a>';
    	}
    }
    else {
    	$counter = sprintf(_n('%d person like this', '%d people like this', $liked, 'ns-like-this'), $liked);
    }
    
    $nsLikeThis = '<div id="nsLikeThis-'.$content_type.'-'.$content_id.'" class="nsLikeThis">';
    
    
    $nsLikeThis .= '<span class="counter">'.$counter.'</span>';
    
    /*
    
    Michele: Hide Emblems
    
    */
    if (get_option('nslt_useEmblems')){
    
        $nsLikeThis .= '<div id="nsLikeThis-'.$content_type.'-'.$content_id.'-selemblem" class="nsLikeThis-show-emblem">';
        
        if( $lc_id = LikedContent::userLikesThis($user_id, $content_id, $content_type)){
    	    // Show the emblem
    	    
    	    $lc = LikedContent::getLikeById($lc_id);
    	    
    	    $nsLikeThis .= '<img src="'.home_url().'/wp-content/plugins/ns-like-this/css/emblems/'.$lc->getEmblem().'" alt="emblem" />';
    	    
        }
        
        
        $nsLikeThis .= '</div>';
        
    
    
    
    // Begin of emblem box
        $nsLikeThis .= '<div id="'.$content_type.'-'.$content_id.'-iconsel" style="display:none;" class="nsLikeThis-iconbox">';
		$nsLikeThis .= '<ul class="nsLikeThis-emblem-list">';
						if ($handle = opendir(dirname(__FILE__).'/css/emblems')) {
						    while (false !== ($file = readdir($handle))) {
						        if ($file != "." && $file != ".." && $file != "em-0.png" && !preg_match('/^\./', $file)) {
						            $nsLikeThis .= '<li><a href="javascript:;" onclick="setEmblem(\''.$content_type.'-'.$content_id.'\', \''.$file.'\')">';
						            	$nsLikeThis .= '<img src="'.home_url().'/wp-content/plugins/ns-like-this/css/emblems/'.$file.'"'; 
						            	$nsLikeThis .= 'alt="'.$file.'" width="16" height="16" />';
						            	$nsLikeThis .= '</a>';
						            $nsLikeThis .= '</li>';
						        }
						    }
						    closedir($handle);
						}
		$nsLikeThis .= '</ul>';
	    $nsLikeThis .= '</div>';
	// End of emblem for
    
    }
    /*
    * Michele - End :-)
    */
    
    $nsLikeThis .= '</div>';
    
    if ($arg == 'put') {
	    return $nsLikeThis;
    }
    else {
    	echo $nsLikeThis;
    }
}

if (get_option('nslt_onPage') == '1') {
	function putNSLikeThis($content) {
		if( (is_single() || is_page()) && get_the_ID() != get_option("my_liked_content_page_id") ) {
			$formatted_content = getNSLikeThis('put');
			$formatted_content .= $content;
			$content = $formatted_content;
		}
	    return $content;
	}

	add_filter('the_content', putNSLikeThis);
}

function filterNSTag($content) {
	global $wpdb;
	global $current_user;
	get_currentuserinfo();
	
	$user_id = $current_user->id;
    
	$tag = "[ns-my-liked-content /]";
	
	ob_start();
		include( dirname(__FILE__)."/my-liked.php");
		$table = ob_get_contents();
	ob_clean();
		
	$new_content = str_replace($tag, $table, $content);
	
	return $new_content;
	
}

function enqueueScripts() {
	if (get_option('nslt_jquery') == '1') {
	    wp_enqueue_script('nsLikeThis', WP_PLUGIN_URL.'/ns-like-this/js/ns-like-this.js', array('jquery'));	
	}
	else {
	    wp_enqueue_script('nsLikeThis', WP_PLUGIN_URL.'/ns-like-this/js/ns-like-this.js');	
	}	
}

function addHeaderLinks() {
	echo '<link rel="stylesheet" type="text/css" href="'.WP_PLUGIN_URL.'/ns-like-this/css/ns-like-this.css" media="screen" />'."\n";
	echo '<script type="text/javascript">var blogUrl = \''.get_bloginfo('wpurl').'\'</script>'."\n";
	echo '<script> var _USEEMBLEMS = "'.get_option('nslt_useEmblems').'";'."\n".'</script>'."\n";
}

function getFavouritesLink(){
    return get_bloginfo('url') . "/?page_id=" . get_option("my_liked_content_page_id");          

}

add_filter('the_content', filterNSTag);
add_action('init', enqueueScripts);
add_action('wp_head', addHeaderLinks);
?>