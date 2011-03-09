function externalLikeThis(postId, type) {

	if (postId != '') {
		jQuery('#nsLikeThis-'+type+'-'+postId+' .counter').text('...');
		
		jQuery.post(blogUrl + "/wp-content/plugins/ns-like-this/like.php",
		{ id: postId, type: type, fulltext: 1 },
			function(data){
				jQuery('#nsLikeThis-'+type+'-'+postId+' .counter').text(data);
		});
		
		var off = jQuery('#nsLikeThis-'+type+'-'+postId).offset();

		var div = jQuery('#'+type+'-'+postId+'-iconsel');
		div.css({ position: "absolute", top: off.top-20, left: off.left-20 });
		
		if(_USEEMBLEMS){
			div.fadeIn(800);
		}
	}
}

function likeThis(postId, type) {
	// Make the like
	realLike(postId, type);

	// Show the icon box
	var off = jQuery("#"+type+"-"+postId).offset();

	var div = jQuery("#"+type+"-"+postId+"-iconsel");
	div.css({ position: "absolute", top: off.top-20, left: off.left-20 });
	
	if(_USEEMBLEMS){
		div.fadeIn(800);
	}
	return false;

}

function dislikeThis(postId, type) {
	
	// Dislike the content
	if (confirm('Are you sure you want to dislike?')){
		jQuery.post(blogUrl + "/wp-content/plugins/ns-like-this/dislike.php",
			{ id: postId, type: type },
			function(data){
				jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").html('<img src="' + blogUrl + '/wp-content/plugins/ns-like-this/css/heart.png" width=16 height=16  style="vertical-align: middle;" />');
				jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").attr('title', 'Like me' );
				jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").attr('onclick', '' );
				jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").attr('onmouseover', "setImage(this, '" + blogUrl + "/wp-content/plugins/ns-like-this/css/heart.png')");
				jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").attr('onmouseout', "setImage(this, '" + blogUrl + "/wp-content/plugins/ns-like-this/css/heart.png')");
				jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").unbind("click");
				jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").click(function(){ 
					var cellid = jQuery(this).parent().attr('id');
    				var typeplusid = cellid.split("-");
					return likeThis(typeplusid[1], typeplusid[0]); 
				});
			}
		);
	}
	return false;
}

function realLike(postId, type){
	// Like and change the icon
	jQuery.post(blogUrl + "/wp-content/plugins/ns-like-this/like.php",
		{ id: postId, type: type },
		function(data){
			jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").html('<img src="' + blogUrl + '/wp-content/plugins/ns-like-this/css/heart.png" width=16 height=16 style="vertical-align: middle;" />');
			jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").attr('title', 'Dislike me' );
			jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").unbind("click");
			jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").attr('onclick', '' );
			jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").attr('onmouseover', "setImage(this, '" + blogUrl + "/wp-content/plugins/ns-like-this/css/heart-cancel.png')");
			jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").attr('onmouseout', "setImage(this, '" + blogUrl + "/wp-content/plugins/ns-like-this/css/heart.png')");
			jQuery("#"+type+"-"+postId+" a.nsLikeThis-likelink").click(function(){ 
				var cellid = jQuery(this).parent().attr('id');
    			var typeplusid = cellid.split("-");
        		return dislikeThis(typeplusid[1], typeplusid[0]); });
		}
	);

}

/*
 *  This hides the emblem box
 */
function hideBox(){
	jQuery('.nsLikeThis-iconbox').fadeOut();
	
}

/*
 * This function set the emblem to "emblem_image" for the specified content.
 * It is required that the like was created before calling this function.
 */
function setEmblem(content, emblem_image){
	jQuery.post(blogUrl + "/wp-content/plugins/ns-like-this/my-liked.php",
		{ action: 'setemblem' , id: content, em: emblem_image },
		function(data){
			hideBox();
			jQuery( '#nsLikeThis-' + content + '-selemblem').html('<img src="' + blogUrl + '/wp-content/plugins/ns-like-this/css/emblems/' + emblem_image + '" alt="emblem" />');
		}
	);

}

/*
 * Loads the table of likes based on a sort criteria.
 */
function ajaxTableLoad( element, sortType ){
	jQuery('#dataTable tbody').load(blogUrl + '/wp-content/plugins/ns-like-this/sort-table.php?sort=' + sortType);
	jQuery('#nsLikeThis-hor-list li').removeClass("active");
	jQuery(element).addClass("active");
	
	return false;
}

function setImage(ele, img){
	jQuery(ele).find('img:first').attr("src",blogUrl + '/wp-content/plugins/ns-like-this/css/' + img);
}
