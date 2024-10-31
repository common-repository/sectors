<?php
/**
 * @package Sectors
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */

$activate_date = $post->post_status == SCT_App::STATUS_SCHEDULED ? $post->post_date : '';

?>

<table class="form-table sct-form-table" width="100%"><tbody>
	<tr>
		<td scope="row"><?php _e("Status",'sectors'); ?></td>
		<td>
			<label class="cae-toggle">
				<input class="js-sct-status" type="checkbox" name="post_status" value="<?php echo SCT_App::STATUS_ACTIVE; ?>" <?php checked( in_array($post->post_status,array(SCT_App::STATUS_ACTIVE,'auto-draft')),true); ?> />
				<div class="cae-toggle-bar"></div>
			</label>
		</td>
	</tr>
	<tr>
		<td scope="row"><?php _e("Activate",'sectors'); ?></td>
		<td>
			<span class="js-sct-activation">
				<input type="text" name="sector_activate" value="<?php echo $activate_date; ?>" data-input placeholder="<?php esc_attr_e('Select date','sectors'); ?>">
				<button type="button" class="button button-small" data-toggle><span class="dashicons dashicons-calendar"></span></button>
				<button type="button" class="button button-small" data-clear><span class="dashicons dashicons-no-alt"></span></button>
			</span>
		</td>
	</tr>
</table>