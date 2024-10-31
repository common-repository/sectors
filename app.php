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

final class SCT_App
{

    /**
     * Plugin version
     */
    const PLUGIN_VERSION = '1.2';

    /**
     * Post Type for sectors
     */
    const TYPE_SECTOR = 'sector';

    /**
     * Sector statuses
     */
    const STATUS_ACTIVE = 'publish';
    const STATUS_INACTIVE = 'draft';
    const STATUS_SCHEDULED = 'future';

    /**
     * Capability to manage sectors
     */
    const CAPABILITY = 'edit_theme_options';

    /**
     * Base admin screen name
     */
    const BASE_SCREEN = 'wpsct';

    /**
     * Prefix for metadata keys
     */
    const META_PREFIX = '_ca_';

    /**
     * Current sectors
     * @var array
     */
    public $sectors = array();

    /**
     * Class singleton
     * @var SCT_App
     */
    private static $_instance;

    /**
     * Instantiates and returns class singleton
     *
     * @return SCT_App
     */
    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        if (is_admin()) {
            new SCT_Sector_Overview();
            new SCT_Sector_Edit();
        } else {
            add_action(
                'wp',
                array($this,'set_current_sectors'),
                -1
            );
        }

        add_action(
            'admin_bar_menu',
            array($this,'admin_bar_menu'),
            99
        );
        add_action(
            'init',
            array($this,'register_sector_type')
        );

        add_filter(
            'template_include',
            array($this,'template_include'),
            99
        );
        add_filter(
            'body_class',
            array($this,'add_body_class')
        );
    }

    /**
     * Set current sectors and dispatch waiting hooks
     *
     * @since 1.0
     */
    public function set_current_sectors()
    {
        global $wpdb;

        $valid = WPCACore::get_conditions(self::TYPE_SECTOR);

        //$posts = WPCACore::get_posts(self::TYPE_SECTOR);
        if ($valid) {
            $posts = $wpdb->get_results("
				SELECT
					p.post_name,
					p.ID
				FROM $wpdb->posts p
				WHERE
				p.post_type = '".self::TYPE_SECTOR."' AND
				p.post_status = '".self::STATUS_ACTIVE."' AND
				p.ID IN(".implode(',', $valid).')
				ORDER BY p.menu_order ASC, p.post_date DESC
			', OBJECT_K);
            foreach ($posts as $id => $post) {
                //$sector = get_post($id);
                $sector = $post;
                $this->sectors[$sector->post_name] = $sector;
                Sector_Hook::on($sector->post_name)->set_current();
            }
        }
    }

    /**
     * Add body class for sector
     *
     * @since  1.0
     * @param  array  $classes
     * @return array
     */
    public function add_body_class($classes)
    {
        if ($this->sectors) {
            $sectors = $this->sectors;
            $post = array_pop($sectors);
            $classes[] = 'sector';
            $classes[] = 'sector-'.$post->post_name;
        }
        return $classes;
    }

    /**
     * Load template for current sector if available
     *
     * @since  1.0
     * @param  string  $original_template
     * @return string
     */
    public function template_include($original_template)
    {
        if ($this->sectors) {
            $sectors = $this->sectors;
            $post = array_pop($sectors);
            $template = locate_template(array(
                "sectors/{$post->post_name}.php",
                "sector-{$post->post_name}.php"
            ));
            if ($template) {
                $original_template = $template;
            }
        }
        return $original_template;
    }

    /**
     * Add admin bar link to create sectors
     *
     * @since  1.0
     * @param  [type]  $wp_admin_bar
     * @return void
     */
    public function admin_bar_menu($wp_admin_bar)
    {
        $post_type = get_post_type_object(self::TYPE_SECTOR);
        if (current_user_can($post_type->cap->create_posts)) {
            $wp_admin_bar->add_menu(array(
                'parent' => 'new-content',
                'id'     => self::BASE_SCREEN,
                'title'  => $post_type->labels->singular_name,
                'href'   => admin_url('admin.php?page=wpsct-edit')
            ));
        }
    }

    /**
     * Create sector post type
     *
     * @since  1.0
     * @return void
     */
    public function register_sector_type()
    {
        register_post_type(self::TYPE_SECTOR, array(
            'labels' => array(
                'name'               => __('Sectors', 'sectors'),
                'singular_name'      => __('Sector', 'sectors'),
                'add_new'            => _x('Add New', 'section', 'sectors'),
                'add_new_item'       => __('Add New Sector', 'sectors'),
                'edit_item'          => __('Edit Sector', 'sectors'),
                'new_item'           => __('New Sector', 'sectors'),
                'all_items'          => __('Sectors', 'sectors'),
                'view_item'          => __('View Sector', 'sectors'),
                'search_items'       => __('Search Sectors', 'sectors'),
                'not_found'          => __('No Sectors found', 'sectors'),
                'not_found_in_trash' => __('No Sectors found in Trash', 'sectors')
            ),
            'capabilities' => array(
                'edit_post'          => self::CAPABILITY,
                'read_post'          => self::CAPABILITY,
                'delete_post'        => self::CAPABILITY,
                'edit_posts'         => self::CAPABILITY,
                'delete_posts'       => self::CAPABILITY,
                'edit_others_posts'  => self::CAPABILITY,
                'publish_posts'      => self::CAPABILITY,
                'read_private_posts' => self::CAPABILITY
            ),
            'public'              => false,
            'hierarchical'        => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
            'has_archive'         => false,
            'rewrite'             => false,
            'query_var'           => false,
            'supports'            => array('title','slug'),
            'menu_icon'           => plugin_dir_url(__FILE__).'assets/img/logo.png',
            'can_export'          => false,
            'delete_with_user'    => false
        ));

        WPCACore::types()->add(self::TYPE_SECTOR);
    }
}

//eol
