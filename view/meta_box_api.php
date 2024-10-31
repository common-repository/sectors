<?php
/**
 * @package Sectors
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */

?>

<h3><?php _e('Template Tags','sectors'); ?></h3>

<p><?php _e('Check whether the query is for content in this sector.','sectors'); ?></p>

<code>is_sector( '<?php echo $post->post_name; ?>' )</code>

<p>&nbsp;</p>

<p><?php _e('Get all sectors for the current query.','sectors'); ?></p>

<code>get_current_sectors()</code> 

<p>&nbsp;</p>

<h3><?php _e('Hooks','sectors'); ?></h3>

<p><?php _e('Hook functions on to a specific action or filter in this sector.','sectors'); ?></p>

<p><div><code>add_sector_action('<?php echo $post->post_name; ?>', $tag, $function, $priority = 10, $accepted_args = 1 )</code></p>

<p>Ref: <a target="_blank" href="https://developer.wordpress.org/reference/functions/add_action/">add_action | WordPress Developer Resources</a></p>

<p>&nbsp;</p>

<p><code>remove_sector_action('<?php echo $post->post_name; ?>', $tag, $function, $priority = 10, $accepted_args = 1 )</code></p>

<p>Ref: <a target="_blank" href="https://developer.wordpress.org/reference/functions/remove_action/">remove_action | WordPress Developer Resources</a></p>

<p>&nbsp;</p>

<p><code>add_sector_filter('<?php echo $post->post_name; ?>', $tag, $function, $priority = 10, $accepted_args = 1 )</code></p>

<p>Ref: <a target="_blank" href="https://developer.wordpress.org/reference/functions/add_filter/">add_filter | WordPress Developer Resources</a></p>

<p>&nbsp;</p>

<p><code>remove_sector_filter('<?php echo $post->post_name; ?>', $tag, $function, $priority = 10, $accepted_args = 1 )</code></p>

<p>Ref: <a target="_blank" href="https://developer.wordpress.org/reference/functions/remove_filter/">remove_filter | WordPress Developer Resources</a></p>