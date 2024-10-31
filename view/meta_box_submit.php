<?php
/**
 * @package Sectors
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */

/* translators: Publish box date format, see http://php.net/date */
$datef = __( 'M j, Y');
$date = date_i18n( $datef, strtotime( $post->post_date ) );

switch ($post->post_status) {
	case SCT_App::STATUS_SCHEDULED:
		$stamp = __('Activates on <b>%1$s</b>','sectors');
		break;
	case SCT_App::STATUS_ACTIVE:
		$stamp = __('Active','sectors');
		break;
	case SCT_App::STATUS_INACTIVE:
		$stamp = __('Inactive','sectors');
		break;
	default:
		$stamp = __('New','sectors');
		$date = date_i18n( $datef, strtotime( current_time('mysql') ) );
		break;
}

?>

<div class="sct-save">
	<div class="wpca-pull-right">
<?php if ( $post->post_status == 'auto-draft' ) {
	submit_button( __( 'Save' ), 'primary button-large', 'publish', false );
} else {
	submit_button( __( 'Update' ), 'primary button-large', 'save', false );
} ?>
	</div>
</div>
<ul class="sct-overview-actions">
	<li><span class="dashicons dashicons-post-status"></span> <?php _e("Status:"); ?>
	<strong><?php printf($stamp,$date); ?></strong> <a class="js-nav-link" href="#top#section-schedule"><?php _e('Edit'); ?></a>
</ul>