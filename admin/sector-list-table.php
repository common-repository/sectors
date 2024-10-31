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

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SCT_Sector_List_Table extends WP_List_Table {

	/**
	 * Trash view
	 * @var boolean
	 */
	private $is_trash;

	public function __construct( $args = array() ) {
		parent::__construct(array(
			'singular' => 'sector',
			'plural'   => 'sectors', 
			'ajax'     => false,
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null
		));
	}

	/**
	 * Load filtered sectors for current query
	 *
	 * @since  1.0
	 * @return void
	 */
	public function prepare_items() {
		global $avail_post_stati, $wp_query;

		$this->_column_headers = $this->get_column_info();

		$avail_post_stati = get_available_post_statuses(SCT_App::TYPE_SECTOR);

		$per_page = $this->get_items_per_page( 'sectors.admin.overview.items_per_page', 20 );
		$current_page = $this->get_pagenum();

		$args = array(
			'post_type'              => SCT_App::TYPE_SECTOR,
			'post_status'            => array(
				SCT_App::STATUS_ACTIVE,
				SCT_App::STATUS_INACTIVE,
				SCT_App::STATUS_SCHEDULED
			),
			'posts_per_page'         => $per_page,
			'paged'                  => $current_page,
			'orderby'                => 'title',
			'order'                  => 'asc',
			'update_post_term_cache' => false
		);

		if(isset($_REQUEST['s']) && strlen($_REQUEST['s'])) {
			$args['s'] = $_REQUEST['s'];
		}

		//Make sure post_status!=all if present to avoid auto-drafts
		if(isset($_REQUEST['post_status']) && $_REQUEST['post_status'] != 'all') {
			$args['post_status'] = $_REQUEST['post_status'];
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$meta = str_replace('meta_', '', $_REQUEST['orderby']);
			if($meta != $_REQUEST['orderby']) {
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = SCT_App::META_PREFIX . $meta;
			} else {
				$args['orderby'] = $_REQUEST['orderby'];
			}
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$args['order'] = $_REQUEST['order'] == 'asc' ? 'asc' : 'desc';
		}

		$wp_query = new WP_Query($args);

		if ( $wp_query->found_posts || $current_page === 1 ) {
			$total_items = $wp_query->found_posts;
		} else {
			$post_counts = (array) wp_count_posts( SCT_App::TYPE_SECTOR );

			if ( isset( $_REQUEST['post_status'] ) && in_array( $_REQUEST['post_status'] , $avail_post_stati ) ) {
				$total_items = $post_counts[ $_REQUEST['post_status'] ];
			} else {
				$total_items = array_sum( $post_counts );

				// Subtract post types that are not included in the admin all list.
				foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
					$total_items -= $post_counts[ $state ];
				}
			}
		}

		$this->items = $wp_query->posts;
		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => ceil( $total_items / $per_page ),
			'per_page'    => $per_page
		) );
	}

	/**
	 * Render on no items
	 *
	 * @since  1.0
	 * @return void
	 */
	public function no_items() {
		if ( $this->is_trash ) {
			echo get_post_type_object( SCT_App::TYPE_SECTOR )->labels->not_found_in_trash;
		} else {
			//todo show more text to get started
			echo get_post_type_object( SCT_App::TYPE_SECTOR )->labels->not_found;
		}
	}

	/**
	 * Get link to view
	 *
	 * @since  1.0
	 * @param  array   $args
	 * @param  string  $label
	 * @param  string  $class
	 * @return string
	 */
	public function get_view_link( $args, $label, $class = '' ) {
		$screen = get_current_screen();
		$args['page'] = $screen->parent_base;
		$url = add_query_arg( $args, 'admin.php' );

		$class_html = '';
		if ( ! empty( $class ) ) {
			 $class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);
		}

		return sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$label
		);
	}

	/**
	 * Get views (sector statuses)
	 *
	 * @since  1.0
	 * @return array
	 */
	public function get_views() {
		global $locked_post_status, $avail_post_stati;

		if ( !empty($locked_post_status) )
			return array();

		$status_links = array();
		$num_posts = wp_count_posts( SCT_App::TYPE_SECTOR ); //do not include private
		$total_posts = array_sum( (array) $num_posts );
		$class = '';

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
			$total_posts -= $num_posts->$state;
		}

		if ( empty( $class ) && ( !isset($_REQUEST['post_status']) || isset( $_REQUEST['all_posts'] ) ) ) {
			$class = 'current';
		}

		$all_inner_html = sprintf(
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$total_posts,
				'sectors',
				'sectors'
			),
			number_format_i18n( $total_posts )
		);

		$status_links['all'] = $this->get_view_link( array(), $all_inner_html, $class );

		//no way to change post status per post type, replace here instead
		$label_replacement = array(
			SCT_App::STATUS_ACTIVE => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'sectors'),
			SCT_App::STATUS_INACTIVE => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'sectors')
		);

		foreach ( get_post_stati(array('show_in_admin_status_list' => true), 'objects') as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( ! in_array( $status_name, $avail_post_stati ) || empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status'] ) {
				$class = 'current';
			}

			$status_args = array(
				'post_status' => $status_name
			);

			$label_count = $status->label_count;
			if(isset($label_replacement[$status->name])) {
				$label_count = $label_replacement[$status->name];
			}

			$status_label = sprintf(
				translate_nooped_plural( $label_count, $num_posts->$status_name ),
				number_format_i18n( $num_posts->$status_name )
			);

			$status_links[ $status_name ] = $this->get_view_link( $status_args, $status_label, $class );
		}

		return $status_links;
	}

	/**
	 * Get bulk actions
	 *
	 * @since  1.0
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array();
		$post_type_obj = get_post_type_object( SCT_App::TYPE_SECTOR);

		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			if ( $this->is_trash ) {
				$actions['untrash'] = __( 'Restore' );
			}
		}

		if ( current_user_can( $post_type_obj->cap->delete_posts ) ) {
			if ( $this->is_trash || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = __( 'Delete Permanently' );
			} else {
				$actions['trash'] = __( 'Move to Trash' );
			}
		}

		//todo: add filter
		return $actions;
	}

	/**
	 * Render extra table navigation and actions
	 *
	 * @since  1.0
	 * @param  string  $which
	 * @return void
	 */
	public function extra_tablenav( $which ) {

		echo '<div class="alignleft actions">';
		if ( $this->is_trash && current_user_can( get_post_type_object( SCT_App::TYPE_SECTOR )->cap->edit_others_posts ) ) {
			submit_button( __( 'Empty Trash' ), 'apply', 'delete_all', false );
		}
		echo '</div>';
	}

	/**
	 * Get current action
	 *
	 * @since  1.0
	 * @return string
	 */
	public function current_action() {
		if ( isset( $_REQUEST['delete_all'] ) || isset( $_REQUEST['delete_all2'] ) )
			return 'delete_all';

		return parent::current_action();
	}

	/**
	 * Get columns
	 *
	 * @since  1.0
	 * @return array
	 */
	public function get_columns() {

		$posts_columns = array();
		$posts_columns['cb'] = '<input type="checkbox" />';
		$posts_columns['title'] = _x( 'Title', 'column name' );
		$posts_columns['slug'] = _x('Sector','option', "sectors");
		$posts_columns['status'] = __( 'Status' );

		return apply_filters('sectors.admin.overview.columns', $posts_columns);
	}

	/**
	 * Get sortable columns
	 *
	 * @since  1.0
	 * @return array
	 */
	public function get_sortable_columns() {
		$columns = array(
			'title'    => array('title', true),
			'status'   => 'post_status'
		);
		return $columns;
	}

	/**
	 * Get default column name
	 *
	 * @since  1.0
	 * @return string
	 */
	protected function get_default_primary_column_name() {
		return 'title';
	}

	/**
	 * Get classes for rows
	 * Older WP versions do not add striped
	 *
	 * @since  1.0
	 * @return array
	 */
	public function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Render checkbox column
	 *
	 * @since  1.0
	 * @param  WP_Post  $post
	 * @return void
	 */
	public function column_cb( $post ) {
		if ( current_user_can( 'edit_post', $post->ID ) ): ?>
			<label class="screen-reader-text" for="cb-select-<?php echo $post->ID; ?>"><?php
				printf( __( 'Select %s' ), _draft_or_post_title($post) );
			?></label>
			<input id="cb-select-<?php echo $post->ID; ?>" type="checkbox" name="post[]" value="<?php echo $post->ID; ?>" />
			<div class="locked-indicator"></div>
		<?php endif;
	}

	/**
	 * Render title column wrapper
	 *
	 * @since  1.0
	 * @param  WP_Post  $post
	 * @param  array    $classes
	 * @param  array    $data
	 * @param  string   $primary
	 * @return void
	 */
	protected function _column_title( $post, $classes, $data, $primary) {
		echo '<td class="' . $classes . ' page-title" ', $data, '>';
		echo $this->column_title( $post );
		echo '</td>';
	}

	/**
	 * Render title column
	 *
	 * @since  1.0
	 * @param  WP_Post  $post
	 * @return void
	 */
	public function column_title( $post ) {

		echo "<strong>";

		$can_edit_post = current_user_can( 'edit_post', $post->ID );
		$title = _draft_or_post_title($post);

		if ( $can_edit_post && $post->post_status != 'trash' ) {
			printf(
				'<a class="" href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $title ) ),
				$title
			);
		} else {
			echo $title;
		}

		echo "</strong>\n";

		if ( $can_edit_post && $post->post_status != 'trash' ) {
			$lock_holder = wp_check_post_lock( $post->ID );

			if ( $lock_holder ) {
				$lock_holder = get_userdata( $lock_holder );
				$locked_avatar = get_avatar( $lock_holder->ID, 18 );
				$locked_text = esc_html( sprintf( __( '%s is currently editing' ), $lock_holder->display_name ) );
			} else {
				$locked_avatar = $locked_text = '';
			}

			echo '<div class="locked-info"><span class="locked-avatar">' . $locked_avatar . '</span> <span class="locked-text">' . $locked_text . "</span></div>\n";
		}

		echo $this->handle_row_actions( $post, 'title', 'title' );
	}

	/**
	 * Render name column
	 *
	 * @since  1.0
	 * @param  WP_Post  $post
	 * @return void
	 */
	public function column_slug($post) {
		echo '<code>'.$post->post_name.'</code>';
	}

	/**
	 * Render status column
	 *
	 * @since  1.0
	 * @param  WP_Post  $post
	 * @return void
	 */
	public function column_status( $post ) {
		switch ($post->post_status) {
			case SCT_App::STATUS_ACTIVE:
				echo '<strong>'.__( 'Active','sectors').'</strong>';
				break;
			case SCT_App::STATUS_SCHEDULED;

				$t_time = get_the_time( __( 'Y/m/d g:i:s a' ), $post );
				$time = get_post_time( 'G', true, $post );

				$time_diff = time() - $time;
				$h_time = mysql2date( __( 'Y/m/d' ), $post->post_date );

				if ( $time_diff > 0 ) {
					echo '<strong class="error-message">' . __( 'Missed schedule' ) . '</strong>';
				} else {
					_e( 'Scheduled' );
				}
				echo '<br /><abbr title="' . $t_time . '">' . $h_time . '</abbr>';
				break;
			default:
				_e( 'Inactive','sectors');
				break;
		}
	}

	/**
	 * Render arbitrary column
	 *
	 * @since  1.0
	 * @param  WP_post  $post
	 * @param  string   $column_name
	 * @return void
	 */
	public function column_default( $post, $column_name ) {
		do_action('sectors.admin.overview.column_default',$post,$column_name);
	}

	/**
	 * Render row
	 *
	 * @since  1.0
	 * @param  WP_Post  $item
	 * @return void
	 */
	public function single_row( $item ) {
		$class = '';
		if($item->post_status == SCT_App::STATUS_ACTIVE) {
			$class = ' class="active"';
		}
		echo '<tr'.$class.'>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Get row actions
	 *
	 * @since  1.0
	 * @param  WP_Post  $post
	 * @param  string  $column_name
	 * @param  string  $primary
	 * @return string
	 */
	protected function handle_row_actions( $post, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		$post_type_object = get_post_type_object( $post->post_type );
		$actions = array();
		$title = _draft_or_post_title();

		if (current_user_can( 'edit_post', $post->ID ) && $post->post_status != 'trash') {
			$actions['edit'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				/* translators: %s: sector title */
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ),
				__( 'Edit' )
			);
		}

		if ( current_user_can( 'delete_post', $post->ID ) ) {
			if ($post->post_status == 'trash') {
				$actions['untrash'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url( get_edit_post_link( $post->ID, 'display' ).'&amp;action=untrash', 'untrash-post_' . $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash' ), $title ) ),
					__( 'Restore' )
				);
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash' ), $title ) ),
					_x( 'Trash', 'verb' )
				);
			}
			if ($post->post_status == 'trash' || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $post->ID, '', true ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently' ), $title ) ),
					__( 'Delete Permanently' )
				);
			}
		}

		return $this->row_actions(
			apply_filters( 'sectors.admin.overview.row_actions', $actions, $post )
		);
	}

}
