<?php
/**
 * @package Sectors
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */
?>

<?php

$templates = array(
	'sectors/'.$post->post_name.'.php',
	'sector-'.$post->post_name.'.php'
);

?>

<p><?php _e('Override templates for all content in this sector.','sectors'); ?></p>

<p><?php _e('Add one of the following templates to your theme','sectors'); ?>:</p>

<?php foreach($templates as $template) : ?>
	<p><code><?php echo $template; ?></code> <?php locate_template($template) ? 'Found' : ''; ?></p>
<?php endforeach; ?>