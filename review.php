<?php
/*
Plugin Name: Primux Review Plugin
Plugin URI: http://primux.dk
Description: Simple Review Plugin which allows the user to Add a title and Stars to comments as a Review.
Version: 1.0
Author: Primux Media
Author URI: http://primux.dk/
*/

/*
Adding the admin page
*/
require_once("admin-page.php");
add_action('admin_menu', 'primux_review_add_page');
function primux_review_add_page() {
	primux_review_admin_page::add_menu_page();
}
add_action('admin_init', 'primux_review_class');
function primux_review_class() {
	new primux_review_admin_page();
}

/*
Require the file taking care of showing the stars in the post
*/
require_once("single.php");

/*
Tell where the l18n files is
*/
add_action('plugins_loaded', 'primux_review_lang_init');
function primux_review_lang_init() {
	load_plugin_textdomain( 'primux-review', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
}

/*
Set the Title Wrap to be H2 when plugin is activated.
*/
register_activation_hook( __FILE__, 'primux_review_activation_fn' );
function primux_review_activation_fn() {
	$option_exists = get_option( 'primux_review' );
	if( !$option_exists ) {
		$wrap['title_wrap'] = "H2";
		add_option('primux_review',$wrap);
	}
}

/*
Add fields after default fields above the comment box
*/
add_action( 'comment_form_logged_in_after', 'primux_review_fields' );
add_action( 'comment_form_after_fields', 'primux_review_fields' );
function primux_review_fields () {
	$options = get_option('primux_review');
	$title_required = ( isset( $options['title_required'] ) ) ? '<span class="required">*</span>' : '';
	$rating_required = ( isset( $options['rating_required'] ) ) ? '<span class="required">*</span>' : '';

	echo '<p class="comment-form-rating">'.
	'<label for="rating">'. __('Rating', 'primux-review') . '</label>'. $rating_required;
	echo '<div class="rating">
			<div class="ratings_stars" data-stars="1"></div>  
       		<div class="ratings_stars" data-stars="2"></div>  
			<div class="ratings_stars" data-stars="3"></div>  
			<div class="ratings_stars" data-stars="4"></div>  
			<div class="ratings_stars" data-stars="5"></div> 
			<div style="clear:both"></div> 
		</div>
		<input type="hidden" name="stars" id="stars"></p>';

	echo '<p class="comment-form-title">'.
	'<label for="title">' . __( 'Title', 'primux-review' ) . '</label>'. $title_required .
	'<input id="title" name="title" type="text" size="30" /></p>';

}

/*
Add the filter to check whether the comment meta data has been added, but only if they are required.
*/
add_filter( 'preprocess_comment', 'primux_review_verify_meta_data' );
function primux_review_verify_meta_data( $commentdata ) {
	$options = get_option('primux_review');
	if (  empty( $_POST['stars'] ) && isset( $options['rating_required'] ) ) {
		wp_die( __( 'Error: You did not add a rating. Hit the Back button on your Web browser and resubmit your comment with a rating.', 'primux-review' ) );
	}

	if ( empty( $_POST['title'] ) && isset( $options['title_required'] ) ) {
		wp_die( __( 'Error: You did not add a Title. Hit the Back button on your Web browser and resubmit your comment with a Title.', 'primux-review' ) );
	}

	return $commentdata;
}

/*
Save the comment meta data along with comment
*/
add_action( 'comment_post', 'primux_review_save_meta_data' );
function primux_review_save_meta_data( $comment_id ) {
	if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ) {
		$title = wp_filter_nohtml_kses($_POST['title']);
		add_comment_meta( $comment_id, 'title', $title );
	}

	if ( ( isset( $_POST['stars'] ) ) && ( $_POST['stars'] != '') ) {
		$stars = wp_filter_nohtml_kses($_POST['stars']);
		add_comment_meta( $comment_id, 'stars', $stars );
	}
}

/*
Add the necesarry scripts and style sheets
*/
add_action( 'wp_enqueue_scripts', 'primux_review_scripts' );
function primux_review_scripts() {
	if( is_singular() ) {
    	wp_enqueue_script( 'jquery' );
		wp_enqueue_script('primux-review', plugins_url( 'review.js', __FILE__ ) );
		wp_enqueue_style( 'primux-review-style', plugins_url( 'review.css' , __FILE__ ) );
	}
	
}

/*
Ready to show the title and stars in the commenst
*/
add_filter( 'comment_text', 'primux_review_modify_comment' );
function primux_review_modify_comment( $comment_text ) {
	$options = get_option('primux_review');
    if ( $stars = get_comment_meta( get_comment_ID(), 'stars', true ) ) {
    	$stars = intval($stars);
    	$stars_output = "";
    	// $star = 
    	for( $i = 0; $i < $stars; $i++ ) {
    		$stars_output .= '<img src="'. plugins_url( 'img/gold-star16.png', __FILE__ ) .'">';
    	}
    	$stars_output .= '
					    <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" style="display:none;">
							<meta itemprop="ratingValue" content="'. $stars .'">
					    	<meta itemprop="bestRating" content="5">
					    </div>';
    	$comment_text = $stars_output . '<br />' . $comment_text;
    }
    
    if( $title = get_comment_meta( get_comment_ID(), 'title', true ) ) {
    	$title = '<'. $options['title_wrap'] . '>' . esc_attr( $title ) . '</'. $options['title_wrap'] . '> ';
    	$comment_text = $title . $comment_text;
    }
	return $comment_text;
}

/*
Add Title and Review to the Comment Administration
*/
add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box() {
    add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' , 'primux-review' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}

function extend_comment_meta_box ( $comment ) {
    $title = get_comment_meta( $comment->comment_ID, 'title', true );
    $stars = get_comment_meta( $comment->comment_ID, 'stars', true );
    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    ?>
    <p>
        <label for="title"><?php _e( 'Title', 'primux-review' ); ?></label>
        <input type="text" name="title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
    </p>
    <p>
        <label for="stars"><?php _e( 'Rating: ', 'primux-review' ); ?></label>
			<span class="comment_rating_box">
			<?php for( $i=1; $i <= 5; $i++ ) {
				$checked = ( $stars == $i ) ? 'checked="checked"' : '';
				echo '<span class="comment_rating"><input type="radio" name="stars" id="stars" value="'. $i .'"'. $checked .' />'. $i .' </span>';
				}
			?>
			</span>
    </p>
    <?php
}

/*
Function to take care of updating the title and review in the Administration
*/
add_action( 'edit_comment', 'extend_comment_edit_metafields' );
function extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) 
    	return;

	if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ):
		$title = wp_filter_nohtml_kses($_POST['title']);
		update_comment_meta( $comment_id, 'title', $title );
	else :
		delete_comment_meta( $comment_id, 'title');
	endif;

	if ( ( isset( $_POST['stars'] ) ) && ( $_POST['stars'] != '') ):
		$stars = wp_filter_nohtml_kses($_POST['stars']);
		update_comment_meta( $comment_id, 'stars', $stars );
	else :
		delete_comment_meta( $comment_id, 'stars');
	endif;
}

?>