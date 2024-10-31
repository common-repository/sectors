<?php
/**
 * @package Sectors
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */

if (!defined('ABSPATH')) {
	exit;
}

final class SCT_Sector_Edit extends SCT_Admin {

	/**
	 * Add filters and actions for admin dashboard
	 * e.g. AJAX calls
	 *
	 * @since  1.0
	 * @return void
	 */
	public function admin_hooks() {

		add_action('sectors.admin.edit.meta_boxes',
			array($this,'create_meta_boxes'));

		add_filter( 'get_edit_post_link',
			array($this,'get_edit_post_link'), 10, 3 );
		add_filter( 'get_delete_post_link',
			array($this,'get_delete_post_link'), 10, 3 );
	}

	/**
	 * Add filters and actions for frontend
	 *
	 * @since  1.0
	 * @return void
	 */
	public function frontend_hooks() {

	}

	/**
	 * Set up admin menu and get current screen
	 *
	 * @since  1.0
	 * @return string
	 */
	public function get_screen() {
		$post_type_object = get_post_type_object(SCT_App::TYPE_SECTOR);
		return add_submenu_page(
			SCT_App::BASE_SCREEN,
			$post_type_object->labels->add_new_item,
			$post_type_object->labels->add_new,
			$post_type_object->cap->edit_posts,
			SCT_App::BASE_SCREEN.'-edit',
			array($this,'render_screen')
		);
	}

	/**
	 * Authorize user for screen
	 *
	 * @since  1.0
	 * @return boolean
	 */
	public function authorize_user() {
		return true;
	}

	/**
	 * Prepare screen load
	 *
	 * @since  1.0
	 * @return void
	 */
	public function prepare_screen() {

		global $nav_tabs, $post, $title, $active_post_lock;

		$post_type = SCT_App::TYPE_SECTOR;
		$post_type_object = get_post_type_object( $post_type );
		$post_id = isset($_REQUEST['sector_id']) ? $_REQUEST['sector_id'] : 0;

		//process actions
		$this->process_actions($post_id);

		if ( is_multisite() ) {
			add_action( 'admin_footer', '_admin_notice_post_locked' );
		} else {
			$check_users = get_users( array( 'fields' => 'ID', 'number' => 2 ) );
			if ( count( $check_users ) > 1 )
				add_action( 'admin_footer', '_admin_notice_post_locked' );
			unset( $check_users );
		}

		wp_enqueue_script('post');

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}

		// Add the local autosave notice HTML
		//add_action( 'admin_footer', '_local_storage_notice' );

		/**
		 * Edit mode
		 */
		if($post_id) {
			$post = get_post($post_id, OBJECT, 'edit');

			if ( ! $post )
				wp_die( __( 'The sector no longer exists.' ) );
			if ( ! current_user_can( 'edit_post', $post_id ) )
				wp_die( __( 'You are not allowed to edit this sector.' ) );
			if ( 'trash' == $post->post_status )
				wp_die( __( 'You cannot edit this sector because it is in the Trash. Please restore it and try again.' ) );

			if ( ! empty( $_GET['get-post-lock'] ) ) {
				check_admin_referer( 'lock-post_' . $post_id );
				wp_set_post_lock( $post_id );
				wp_redirect( get_edit_post_link( $post_id, 'url' ) );
				exit();
			}

			if ( ! wp_check_post_lock( $post->ID ) ) {
				$active_post_lock = wp_set_post_lock( $post->ID );
				//wp_enqueue_script('autosave');
			}

			$title = $post_type_object->labels->edit_item;

		/**
		 * New Mode
		 */
		} else {

			if ( ! current_user_can( $post_type_object->cap->edit_posts ) || ! current_user_can( $post_type_object->cap->create_posts ) ) {
				wp_die(
					'<p>' . __( 'You are not allowed to create sectors.', 'sectors' ) . '</p>',
					403
				);
			}

			//wp_enqueue_script( 'autosave' );
			$post = get_default_post_to_edit( $post_type, true );
			$title = $post_type_object->labels->add_new_item;
		}

		$nav_tabs = array(
			'conditions' => __('Conditions','sectors'),
			'schedule'   => __('Schedule','sectors'),
			'template'   => __('Template','sectors'),
			'api'        => __('API','sectors')
		);
		$nav_tabs = apply_filters('sectors.admin.edit.tabs', $nav_tabs);

		do_action( 'sectors.admin.edit.meta_boxes', $post );

	}

	/**
	 * Process actions
	 *
	 * @since  1.0
	 * @param  int  $post_id
	 * @return void
	 */
	public function process_actions($post_id) {
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
		if ( isset( $_POST['deletepost'] ) )
			$action = 'delete';

		if($action && $post_id) {
			//wp_reset_vars( array( 'action' ) );
			$sendback = wp_get_referer();
			$sendback = remove_query_arg(
				array('action','trashed', 'untrashed', 'deleted', 'ids'), 
				$sendback
			);

			$post = get_post( $post_id );
			if ( ! $post ) {
				wp_die( __( 'The sector no longer exists.', 'sectors' ) );
			}

			switch($action) {
				case 'editpost':
					check_admin_referer('update-post_' . $post_id);

					$post_id = $this->update_sector_type();

					// Session cookie flag that the post was saved
					if ( isset( $_COOKIE['wp-saving-post'] ) && $_COOKIE['wp-saving-post'] === $post_id . '-check' ) {
						setcookie( 'wp-saving-post', $post_id . '-saved', time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl() );
					}

					$status = get_post_status( $post_id );
					if(isset($_POST['original_post_status']) && $_POST['original_post_status'] == $status) {
						$message = 1;
					} else {
						switch ( $status ) {
							case SCT_App::STATUS_SCHEDULED:
								//gets scheduled
								$message = 9;
								break;
							case SCT_App::STATUS_INACTIVE:
								//gets deactivated
								$message = 10;
								break;
							case SCT_App::STATUS_ACTIVE:
								//gets activated
								$message = 6;
								break;
							default:
								$message = 1;
						}
					}

					$sendback = add_query_arg(array(
						'sector_id' => $post_id,
						'message'    => $message,
						'page'       => SCT_App::BASE_SCREEN.'-edit'
					), $sendback);
					wp_safe_redirect($sendback);
					exit();
				case 'trash':
					check_admin_referer('trash-post_' . $post_id);

					if ( ! current_user_can( 'delete_post', $post_id ) )
						wp_die( __( 'You are not allowed to move this sector to the Trash.', 'sectors' ) );

					if ( $user_id = wp_check_post_lock( $post_id ) ) {
						$user = get_userdata( $user_id );
						wp_die( sprintf( __( 'You cannot move this sector to the Trash. %s is currently editing.', 'sectors' ), $user->display_name ) );
					}

					if ( ! wp_trash_post( $post_id ) )
						wp_die( __( 'Error in moving to Trash.' ) );

					$sendback = remove_query_arg('sector_id',$sendback);

					wp_safe_redirect(add_query_arg(
						array(
							'page'    => SCT_App::BASE_SCREEN,
							'trashed' => 1,
							'ids'     => $post_id
						), $sendback ));
					exit();
				case 'untrash':
					check_admin_referer('untrash-post_' . $post_id);

					if ( ! current_user_can( 'delete_post', $post_id ) )
						wp_die( __( 'You are not allowed to restore this sector from the Trash.', 'sectors' ) );

					if ( ! wp_untrash_post( $post_id ) )
						wp_die( __( 'Error in restoring from Trash.' ) );

					wp_safe_redirect( add_query_arg('untrashed', 1, $sendback) );
					exit();
				case 'delete':
					check_admin_referer('delete-post_' . $post_id);

					if ( ! current_user_can( 'delete_post', $post_id ) )
						wp_die( __( 'You are not allowed to delete this sector.', 'sectors' ) );

					if ( ! wp_delete_post( $post_id, true ) )
						wp_die( __( 'Error in deleting.' ) );

					$sendback = remove_query_arg('sector_id',$sendback);
					wp_safe_redirect( add_query_arg(array(
						'page' => SCT_App::BASE_SCREEN,
						'deleted' => 1
					), $sendback ));
					exit();
				default:
					do_action('sectors.admin.action', $action, $post);
					break;
			}
		}
	}

	/**
	 * Render screen
	 *
	 * @since  1.0
	 * @return void
	 */
	public function render_screen() {

		global $nav_tabs, $post, $title, $active_post_lock;

		$post_type_object = get_post_type_object( $post->post_type );

		$message = false;
		if ( isset($_GET['message']) ) {
			$messages = $this->sector_updated_messages($post);
			$_GET['message'] = absint( $_GET['message'] );
			if ( isset($messages[$_GET['message']]) )
				$message = $messages[$_GET['message']];
		}

		$notice = false;
		$form_extra = '';
		if ( 'auto-draft' == $post->post_status ) {
			if (isset($_REQUEST['sector_id']) ) {
				$post->post_title = '';
			}
			//$autosave = false;
			$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
		}
		// else {
		// 	$autosave = wp_get_post_autosave( $post->ID );
		// }

		// Detect if there exists an autosave newer than the post and if that autosave is different than the post
		// if ( $autosave && mysql2date( 'U', $autosave->post_modified_gmt, false ) > mysql2date( 'U', $post->post_modified_gmt, false ) ) {
		// 	foreach ( _wp_post_revision_fields( $post ) as $autosave_field => $_autosave_field ) {
		// 		if ( normalize_whitespace( $autosave->$autosave_field ) != normalize_whitespace( $post->$autosave_field ) ) {
		// 			$notice = sprintf( __( 'There is an autosave of this post that is more recent than the version below. <a href="%s">View the autosave</a>' ), get_edit_post_link( $autosave->ID ) );
		// 			break;
		// 		}
		// 	}
		// 	// If this autosave isn't different from the current post, begone.
		// 	if ( ! $notice )
		// 		wp_delete_post_revision( $autosave->ID );
		// 	unset($autosave_field, $_autosave_field);
		// }

		$tag = 'h1';


		echo '<div class="wrap">';
		echo '<'.$tag.'>';
		echo esc_html( $title );
		if ( isset($_REQUEST['sector_id']) && current_user_can( $post_type_object->cap->create_posts ) ) {
			echo ' <a href="' . esc_url( admin_url( 'admin.php?page='.SCT_App::BASE_SCREEN.'-edit' ) ) . '" class="page-title-action add-new-h2">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
		}
		echo '</'.$tag.'>';
		if ( $message ) {
			echo '<div id="message" class="updated notice notice-success is-dismissible"><p>'.$message.'</p></div>';
		} 
		echo '<form name="post" action="admin.php?page='.SCT_App::BASE_SCREEN.'-edit" method="post" id="post">';
		$referer = wp_get_referer();
		wp_nonce_field('update-post_' . $post->ID);
		echo '<input type="hidden" id="user-id" name="user_ID" value="'.(int)get_current_user_id().'" />';
		echo '<input type="hidden" id="hiddenaction" name="action" value="editpost" />';
		echo '<input type="hidden" id="post_author" name="post_author" value="'.esc_attr($post->post_author).'" />';
		echo '<input type="hidden" id="original_post_status" name="original_post_status" value="'.esc_attr( $post->post_status).'" />';
		echo '<input type="hidden" id="referredby" name="referredby" value="'.($referer ? esc_url( $referer ) : '').'" />';
		echo '<input type="hidden" id="post_ID" name="sector_id" value="'.esc_attr($post->ID).'" />';
		if ( ! empty( $active_post_lock ) ) {
			echo '<input type="hidden" id="active_post_lock" value="'.esc_attr(implode( ':', $active_post_lock )).'" />';
		}
		if ( get_post_status( $post ) != SCT_App::STATUS_INACTIVE) {
			wp_original_referer_field(true, 'previous');
		}
		echo $form_extra;

		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

		echo '<div id="poststuff">';
		echo '<div id="post-body" class="metabox-holder columns-'.(1 == get_current_screen()->get_columns() ? '1' : '2').'">';
		echo '<div id="post-body-content">';
		echo '<div id="titlediv">';
		echo '<div id="titlewrap">';
		echo '<label class="screen-reader-text" id="title-prompt-text" for="title">'.__( 'Enter title here' ).'</label>';
		echo '<input type="text" name="post_title" size="30" value="'.esc_attr( $post->post_title ).'" id="title" spellcheck="true" autocomplete="off" />';
		echo '</div></div>';
		$this->render_section_nav($nav_tabs);
		echo '</div>';
		$this->render_sections($nav_tabs,$post);
		echo '</div>';
		echo '<br class="clear" />';
		echo '</div></form></div>';
	}

	/**
	 * Render tab navigation
	 *
	 * @since  1.0
	 * @param  array  $tabs
	 * @return void
	 */
	public function render_section_nav($tabs) {
		echo '<h2 class="nav-tab-wrapper js-sct-tabs hide-if-no-js " style="padding-bottom:0;">';
		foreach ($tabs as $id => $label) {
			echo '<a class="js-nav-link nav-tab" href="#top#section-'.$id.'">'.$label.'</a>';
		}
		echo '</h2>';
	}

	/**
	 * Render meta box sections
	 *
	 * @since  1.0
	 * @param  array    $tabs
	 * @param  WP_Post  $post
	 * @return void
	 */
	public function render_sections($tabs, $post) {
		echo '<div id="postbox-container-1" class="postbox-container">';
		do_meta_boxes(SCT_App::BASE_SCREEN.'-edit', 'side', $post);
		echo '</div>';
		echo '<div id="postbox-container-2" class="postbox-container">';
		foreach ($tabs as $id => $label) {
			$name = 'section-'.$id;
			echo '<div id="'.$name.'" class="sct-section">';
			do_meta_boxes(SCT_App::BASE_SCREEN.'-edit', $name, $post);
			echo '</div>';
		}
		//boxes across sections
		do_meta_boxes(SCT_App::BASE_SCREEN.'-edit', 'normal', $post);
		echo '</div>';
	}

	/**
	 * Update sector post type
	 *
	 * @since  1.0
	 * @return int
	 */
	public function update_sector_type() {
		global $wpdb;
 
		$post_ID = (int) $_POST['sector_id'];
		$post = get_post( $post_ID );
		$post_data['post_type'] = SCT_App::TYPE_SECTOR;
		$post_data['ID'] = (int) $post_ID;
		$post_data['post_title'] = $_POST['post_title'];
		$post_data['comment_status'] = 'closed';
		$post_data['ping_status'] = 'closed';
		$post_data['post_author'] = get_current_user_id();
		$post_data['menu_order'] = 0;

		$ptype = get_post_type_object($post_data['post_type']);

		if ( !current_user_can( 'edit_post', $post_ID ) ) {
				wp_die( __('You are not allowed to edit this sector.', 'sectors' ));
		} elseif (! current_user_can( $ptype->cap->create_posts ) ) {
				return new WP_Error( 'edit_others_posts', __( 'You are not allowed to create sectors.', 'sectors' ) );
		} elseif ( $post_data['post_author'] != $_POST['post_author'] 
			 && ! current_user_can( $ptype->cap->edit_others_posts ) ) {
			return new WP_Error( 'edit_others_posts', __( 'You are not allowed to edit this sector.', 'sectors' ) );
		}
	 
		if ( isset($_POST['post_status']) ) {
			 $post_data['post_status'] = SCT_App::STATUS_ACTIVE;
			//if sector has been future before, we need to reset date
			if($_POST['post_status'] != $_POST['original_post_status']) {
				$post_data['post_date'] = current_time( 'mysql' );
			}
		} elseif($_POST['sector_activate']) {
			$_POST['post_status'] = SCT_App::STATUS_SCHEDULED; //yoast seo expects this
			$post_data['post_status'] = SCT_App::STATUS_SCHEDULED;
			$post_data['post_date'] = $_POST['sector_activate'];
		} else {
			$_POST['post_status'] = SCT_App::STATUS_INACTIVE;
			$post_data['post_status'] = SCT_App::STATUS_INACTIVE;
		}

		if(isset($post_data['post_date'])) {
			$post_data['post_date_gmt'] = get_gmt_from_date( $post_data['post_date'] );
		}
	 
		if ( post_type_supports( SCT_App::TYPE_SECTOR, 'revisions' ) ) {
			$revisions = wp_get_post_revisions( $post_ID, array(
				'order'          => 'ASC',
				'posts_per_page' => 1
			));
			$revision = current( $revisions );
			// Check if the revisions have been upgraded
			if ( $revisions && _wp_get_post_revision_version( $revision ) < 1 )
				_wp_upgrade_revisions_of_post( $post, wp_get_post_revisions( $post_ID ) );
		}
	 
		update_post_meta( $post_ID, '_edit_last', $post_data['post_author'] );
		$success = wp_update_post( $post_data );
		wp_set_post_lock( $post_ID );

		return $post_ID;
	}

	/**
	 * Get update messages
	 *
	 * @since  1.0
	 * @param  WP_Post  $post
	 * @return array
	 */
	public function sector_updated_messages($post) {
		return array(
			1 => __('Sector updated.','sectors'),
			6 => __('Sector activated.','sectors'),
			9 => sprintf(__('Sector scheduled for: <strong>%1$s</strong>.','sectors'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n(__('M j, Y @ G:i'),strtotime($post->post_date))),
			10 => __('Sector deactivated.','sectors'),
		);
	}

	/**
	 * Add meta boxes
	 *
	 * @since  1.0
	 * @param  WP_Post  $post
	 * @return void
	 */
	public function create_meta_boxes($post) {

		$boxes = array();
		$boxes[] = array(
			'id'       => 'submitdiv',
			'title'    => __('Publish'),
			'view'     => 'submit',
			'context'  => 'side',
			'priority' => 'high'
		);
		$boxes[] = array(
			'id'       => 'sct-status',
			'title'    => __('Status', 'sectors'),
			'view'     => 'status',
			'context'  => 'section-schedule',
			'priority' => 'default'
		);
		$boxes[] = array(
			'id'       => 'sct-template',
			'title'    => __('Template', 'sectors'),
			'view'     => 'template',
			'context'  => 'section-template',
			'priority' => 'default'
		);
		$boxes[] = array(
			'id'       => 'sct-api',
			'title'    => __('API', 'sectors'),
			'view'     => 'api',
			'context'  => 'section-api',
			'priority' => 'default'
		);

		$path = plugin_dir_path( __FILE__ ).'../view/';

		//Add meta boxes
		foreach($boxes as $box) {

			$view = WPCAView::make($path.'meta_box_'.$box['view'].'.php',array(
				'post'=> $post
			));

			add_meta_box(
				$box['id'],
				$box['title'],
				array($view,'render'),
				SCT_App::BASE_SCREEN.'-edit',
				$box['context'],
				$box['priority']
			);
		}

		//todo: refactor add of meta box
		//with new bootstrapper, legacy core might be loaded
		if(method_exists('WPCACore', 'render_group_meta_box')) {
			WPCACore::render_group_meta_box($post,SCT_App::BASE_SCREEN.'-edit','section-conditions','default');
		}

	}

	/**
	 * Get sector edit link
	 * TODO: Consider changing post type _edit_link instead
	 *
	 * @since  1.0
	 * @param  string  $link
	 * @param  int     $post_id
	 * @param  string  $context
	 * @return string
	 */
	public function get_edit_post_link($link, $post_id, $context) {
		$post = get_post($post_id);
		if($post->post_type == SCT_App::TYPE_SECTOR) {
			$sep = '&';
			if($context == 'display') {
				$sep = '&amp;';
			}
			$link = admin_url('admin.php?page='.SCT_App::BASE_SCREEN.'-edit'.$sep.'sector_id='.$post_id);
		}
		return $link;
	}

	/**
	 * Get sector delete link
	 * TODO: Consider changing post type _edit_link instead
	 *
	 * @since  1.0
	 * @param  string   $link
	 * @param  int      $post_id
	 * @param  boolean  $force_delete
	 * @return string
	 */
	public function get_delete_post_link($link, $post_id, $force_delete) {
		$post = get_post($post_id);
		if($post->post_type == SCT_App::TYPE_SECTOR) {

			$action = ( $force_delete || !EMPTY_TRASH_DAYS ) ? 'delete' : 'trash';

			$link = add_query_arg(
				'action',
				$action,
				admin_url('admin.php?page='.SCT_App::BASE_SCREEN.'-edit&sector_id='.$post_id)
			);
			$link = wp_nonce_url( $link, "$action-post_{$post_id}" );
		}
		return $link;
	}

	/**
	 * Register and enqueue scripts styles
	 * for screen
	 *
	 * @since 1.0
	 */
	public function add_scripts_styles() {

		WPCACore::enqueue_scripts_styles('');

		wp_register_script('flatpickr', plugins_url('../assets/js/flatpickr.min.js', __FILE__), array(), '3.0.6', false);

		wp_register_script('sectors.admin.edit', plugins_url('../assets/js/edit.js', __FILE__), array('jquery','flatpickr'), SCT_App::PLUGIN_VERSION, false);
		wp_register_style('flatpickr', plugins_url('../assets/css/flatpickr.dark.min.css', __FILE__), array(), '3.0.6');
		wp_register_style('sectors.admin.style', plugins_url('../assets/css/style.css', __FILE__), array('flatpickr'), SCT_App::PLUGIN_VERSION);

		wp_enqueue_script('sectors.admin.edit');
		wp_enqueue_style('sectors.admin.style');

		global $wp_locale;

		wp_localize_script( 'sectors.admin.edit', 'SCTAdmin', array(
			'weekdays' => array(
				'shorthand' => array_values($wp_locale->weekday_abbrev),
				'longhand' => array_values($wp_locale->weekday)
			),
			'months' => array(
				'shorthand' => array_values($wp_locale->month_abbrev),
				'longhand' => array_values($wp_locale->month)
			),
			'weekStart' => get_option('start_of_week',0),
			'dateFormat' => __( 'F j, Y' ) //default long date
		));

	}

}

//eol