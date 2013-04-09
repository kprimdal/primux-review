<?php
/*
Show and average rating in end of the post
*/
add_filter( 'the_content', 'primux_review_show_average_rating' );
function primux_review_show_average_rating( $content ) {
	global $post;
	$stars = primux_get_stars( $post->ID );
	if( $stars[1] ) {
		$content .= '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
		$content .= '<meta itemprop="ratingValue" value="'. $stars[0] .'">';
		$content .= '<meta itemprop="bestRating" value="5">';
		$content .= '<meta itemprop="reviewCount" value="'. $stars[1] .'">';
		for( $i = 0; $i < $stars[0]; $i++ )  {
			$content .= '<img src="'. plugins_url( 'img/gold-star22.png', __FILE__ ) .'" alt="star">';
		}
		$content .= '<p>'. sprintf(__('Rating: %1$s / 5 stars (%2$s Votes)', 'primux-review'), $stars[0], $stars[1]) .'</p>';
	}
	return $content;
}

/*
Function to get the average rating
*/
function primux_get_stars( $post_ID ) {
	$comments = get_comments(array( 'post_id' => $post_ID ));
	$counter = 0;
	$total = 0;
	foreach ($comments as $comment) {
		if ( $stars =  get_comment_meta($comment->comment_ID,'stars',true) ) {
			$total = $total + $stars;
			$counter++;
		}
	}
	$return = array();
	$return[] = @round( $total / $counter, 1 );
	$return[] = $counter;
	return $return;
}
?>