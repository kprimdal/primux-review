<?
/*
Class taking care of the Admin Page
*/
class primux_review_admin_page {

	public $options;

	public function __construct() {
		$this->options = get_option('primux_review');
		$this->register_settings_and_fields();
	}

	public function add_menu_page()  {
		add_options_page(__('Review Options', 'primux-review'), __('Review Options', 'primux-review'), 'administrator', __FILE__, array('primux_review_admin_page', 'display_options_page'));
	}

	public function display_options_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e('Review Options', 'primux-review'); ?></h2>
			<form action="options.php" method="post" accept-charset="utf-8">
				<?php settings_fields('primux_review'); ?>
				<?php do_settings_sections(__FILE__ ); ?>

				<p class="submit">
					<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes', 'primux-review'); ?>">
				</p>
			</form>
			<div style="margin-top:80px;">
				<strong><?php _e('Made by', 'primux-review') ?></strong><br>
				<a href="http://primux.dk" title="Primux Media"><img src="<?php echo plugins_url('img/primux.png', __FILE__); ?>" alt="Primux Media"></a>
			</div>
		</div>
		<?php
	}

	public function register_settings_and_fields() {
		register_setting('primux_review','primux_review');
		add_settings_section('pr_main_section',__('Main Settings','primux-review'),array($this, 'pr_main_section_cb'), __FILE__);
		add_settings_field('title_wrap',__('Title Wrap', 'primux-review'),array($this, 'title_wrap_setting'), __FILE__, 'pr_main_section');
		add_settings_field('rating_required', __('Rating Required ?', 'primux-review'), array($this, 'rating_required_setting'),__FILE__, 'pr_main_section');
		add_settings_field('title_required', __('Title Required ?','primux-review'), array($this, 'title_required_setting'), __FILE__, 'pr_main_section');	
	}

	public function pr_main_section_cb() {

	}

	// Title Wrap
	public function title_wrap_setting() {
		echo "<select name='primux_review[title_wrap]' />";
		$items = array('H1', 'H2', 'H3', 'H4', 'strong', 'p');
		foreach( $items as $item ) {
			$selected = ( $item == $this->options['title_wrap'] ) ? 'selected="selected"' : '';
			echo '<option value="'. $item .'" '. $selected .'>'. $item .'</option>';
		}  
	}

	// Title Required
	public function title_required_setting() {
		$checked = ( isset($this->options['title_required']) ) ? 'checked="checked"' : '';
		echo '<input name="primux_review[title_required]" type="checkbox" value="1" '. $checked .'> '. __('Yes', 'primux-review');
	}

	// rating Required
	public function rating_required_setting() {
		$checked = ( isset($this->options['rating_required']) ) ? 'checked="checked"' : '';
		echo '<input name="primux_review[rating_required]" type="checkbox" value="1" '. $checked .'> '. __('Yes', 'primux-review');
	}

}

?>