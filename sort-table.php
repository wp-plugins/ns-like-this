<?php
	require_once '../../../wp-config.php';
	require_once 'class/liked_content.php';
	
	global $wpdb;
	global $current_user;
	wp_get_current_user();
	
	if($_REQUEST['sort'] == 'recent'){
		$liked_content = LikedContent::getLikedContentsForUser($current_user->ID);
	}
	else if($_REQUEST['sort'] == 'liked'){
		$liked_content = LikedContent::getMostLiked(10);
	}
	else if($_REQUEST['sort'] == 'visited'){
		$liked_content = LikedContent::getMostClicked($current_user->ID, 10);
	}
?>

<?php foreach($liked_content as $lc){ 
		$like = LikedContent::userLikesThis($current_user->ID, $lc->getContentId(), $lc->getContentType());
		
		if($like){
			$likeText = __("Dislike This", 'ns-like-this');
			$likeImage = "heart.png";
			$likeImageHover = "heart-cancel.png";
			$likeFunction = "return dislikeThis(".$lc->getContentId().", '".$lc->getContentType()."');";
		}
		else{
			$likeText = __("Like This", 'ns-like-this');
			$likeImage = "add.png";
			$likeImageHover = "add-hover.png";
			$likeFunction = "return likeThis(".$lc->getContentId().", '".$lc->getContentType()."');";
		}
		
?>
	<tr>
		<td id="<?=$lc->getContentType()?>-<?=$lc->getContentId()?>" class="ns-like-click-el">
			<a class="nsLikeThis-likelink" href="javascript:;" title="<?=$likeText?>" onclick="<?=$likeFunction?>" onmouseover="setImage(this, '<?=$likeImageHover ?>')" onmouseout="setImage(this, '<?=$likeImage?>')">
				<img src="<?=home_url();?>/wp-content/plugins/ns-like-this/css/<?=$likeImage?>" alt="<?=$likeText?>" width=16 height=16 style="vertical-align: middle;" />
			</a>
			<?php 
            if (get_option('nslt_useEmblems')):
            ?>
            <div id="<?=$lc->getContentType()?>-<?=$lc->getContentId()?>-iconsel" style="display:none;" class="nsLikeThis-iconbox">
				<ul class="nsLikeThis-emblem-list">
					<?php 
						if ($handle = opendir('./css/emblems')) {
						    while (false !== ($file = readdir($handle))) {
						        if ($file != "." && $file != ".." && $file != "em-0.png" && !preg_match('/^\./', $file)) { ?>
						            <li><a href="javascript:;" onclick="setEmblem('<?=$lc->getContentType()?>-<?=$lc->getContentId()?>', '<?=$file?>')">
						            	<img src="<?=home_url();?>/wp-content/plugins/ns-like-this/css/emblems/<?=$file?>" 
						            		 alt="<?=$file?>" width="16" height="16" />
						            	</a>
						            </li>
					<?php	        }
						    }
						    closedir($handle);
						}
					?>
				</ul>
			</div>
			<?php
            endif;
            ?>
		</td>
		<td><?=LikedContent::getContentLikes($lc->getContentId(), $lc->getContentType());?><img src="<?=home_url();?>/wp-content/plugins/ns-like-this/css/people_12.png" style="vertical-align: middle;margin-left:3px;" /></td>
		<td><?=$lc->getNiceTime()?></td>
		<td>
        <?php 
            if (get_option('nslt_useEmblems')):
        ?>
        <img src="<?=home_url();?>/wp-content/plugins/ns-like-this/css/emblems/<?= $lc->getEmblem(); ?>" alt="Emblem" width="16" height="16" style="vertical-align: middle;" />&nbsp;
            <?php
            endif;
            ?>
            <a href="<?=$lc->getLink()?>"><?=$lc->getName()?></a></td>
		<!--<td><?=$lc->getContentType()?></td>-->
	</tr>
<?php } ?>

