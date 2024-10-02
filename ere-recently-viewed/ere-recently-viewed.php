<?php
/**
 * Plugin Name: ERE Recently Viewed - Essential Real Estate Add-On
 * Plugin URI: https://wordpress.org/plugins/ere-recently-viewed
 * Description: ERE Recently Viewed plugin shows properties viewed by a visitor as a responsive sidebar widget or in post/page using shortcode
 * Version: 2.1
 * Author: G5Theme
 * Author URI: http://themeforest.net/user/g5theme
 * Text Domain: ere-recently-viewed
 * Domain Path: /languages/
 * License: GPLv2 or later
 */
/*
Copyright 2018 by G5Theme

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if (!defined('ERE_RECENTLY_VIEWED_COOKIE_KEY')) {
    define('ERE_RECENTLY_VIEWED_COOKIE_KEY', 'ere_recently_viewed_key');
}

if ( ! class_exists( 'ERE_Recently_Viewed' ) ) {
    class ERE_Recently_Viewed {
        private static $_instance;

        public static function getInstance() {
            if ( self::$_instance == null ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        private $_cookie_key = 'ere_recently_viewed_key';

        public function __construct() {
            add_action('plugins_loaded',array($this,'init'));
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );
            add_action( 'plugins_loaded', array($this,'load_text_domain'));
            spl_autoload_register(array($this, 'auto_load'));
        }

        public function init() {
            if (function_exists( 'ERE' )) {
                remove_action( 'admin_notices', array( $this, 'admin_notices' ) );
                $this->includes();
            }
        }

        public function admin_notices() {
            ?>
            <div class="error">
                <p><?php esc_html_e( 'ERE Recently Viewed is enabled but not effective. It requires Essential Real Estate in order to work.', 'ere-recently-viewed' ); ?></p>
            </div>
            <?php
        }
        public function load_text_domain() {
            load_plugin_textdomain( 'ere-recently-viewed', false, $this->plugin_dir('languages'));
        }

        public function includes() {
            add_action('widgets_init', array($this,'register_widgets'));
            ERE_RV_Assets::getInstance()->init();
            ERE_RV_Shortcode_Recently_Viewed::getInstance()->init();
            add_action('template_redirect', array($this,'set_recently_viewed_property'));
        }

        public function register_widgets() {
            register_widget('ERE_RV_Widget_Recently_Viewed');
        }

        public function auto_load($class){
            $file_name = preg_replace('/^ERE_RV_/', '', $class);
            if ($file_name !== $class) {
                $path  = '';
                $file_name = strtolower($file_name);
                $file_name = str_replace('_', '-', $file_name);
                $this->load_file($this->plugin_dir("inc/{$path}{$file_name}.class.php"));
            }
        }

        public function load_file($path) {
            if ( $path && is_readable($path) ) {
                include_once $path;
                return true;
            }
            return false;
        }

        public function plugin_dir($path = '') {
            return plugin_dir_path(__FILE__) . $path;
        }

        public function plugin_url($path = '') {
            return trailingslashit( plugins_url( '/', __FILE__ ) ) . $path;
        }

        public function template_path() {
            return apply_filters('ere_recently_viewed_template_path', 'ere-recently-viewed/');
        }

        public function locate_template($template_name, $args = array()) {
            $located = '';

            // Theme or child theme template
            $template = trailingslashit(get_stylesheet_directory()) . $this->template_path() . $template_name;
            if (file_exists($template)) {
                $located = $template;
            } else {
                $template = trailingslashit(get_template_directory()) . $this->template_path() . $template_name;
                if (file_exists($template)) {
                    $located = $template;
                }
            }

            // Plugin template
            if (! $located) {
                $located = $this->plugin_dir() . 'templates/' . $template_name;
            }

            $located = apply_filters( 'ere_recently_viewed_locate_template', $located, $template_name, $args);

            // Return what we found.
            return $located;
        }


        public function get_template( $template_name, $args = array() ) {
            $located = $this->locate_template($template_name, $args);
            $action_args = array(
                'template_name' => $template_name,
                'located'       => $located,
                'args'          => $args,
            );

            if ( ! empty( $args ) && is_array( $args ) ) {
                if ( isset( $args['action_args'] ) ) {
                    _doing_it_wrong( __FUNCTION__, __( 'action_args should not be overwritten when calling get_template.', 'ere-recently-viewed' ), $this->plugin_ver() );
                    unset( $args['action_args'] );
                }
                extract( $args ); // @codingStandardsIgnoreLine
            }

            if ($action_args['located'] !== '') {
                do_action( 'ere_recently_viewed_before_template_part', $action_args['template_name'], $action_args['located'], $action_args['args'] );
                include( $action_args['located'] );
                do_action( 'ere_recently_viewed_after_template_part', $action_args['template_name'], $action_args['located'], $action_args['args'] );
            }
        }

        public function assets_handle($handle = '') {
            return apply_filters('ere_recently_viewed_assets_prefix', 'ere_recently_viewed_') . $handle;
        }

        public function asset_url($file) {
            if (!file_exists($this->plugin_dir($file)) || (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)) {
                $ext = explode('.', $file);
                $ext = end($ext);
                $normal_file = preg_replace('/((\.min\.css)|(\.min\.js))$/', '', $file);
                if ($normal_file != $file) {
                    $normal_file = untrailingslashit($normal_file) . ".{$ext}";
                    if (file_exists($this->plugin_dir($normal_file))) {
                        return $this->plugin_url(untrailingslashit($normal_file));
                    }
                }
            }
            return $this->plugin_url(untrailingslashit($file));
        }

        public function plugin_ver() {
            $plugin_ver = wp_cache_get('plugin_version','ere_recently_viewed');
            if ($plugin_ver) {
                return $plugin_ver;
            }

            if (!function_exists('get_plugin_data')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            $plugin_data = get_plugin_data( __FILE__ );
            $plugin_ver = isset($plugin_data['Version']) ? $plugin_data['Version'] : '1.0.0';
            if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG === true) {
                $plugin_ver = mt_rand() . '';
            }
            wp_cache_add('plugin_version',$plugin_ver,'ere_recently_viewed');
            return $plugin_ver;
        }

        public function get_list_recently_viewed_property() {
            $property_ids = array();
            if (isset($_COOKIE[$this->_cookie_key]) && !empty($_COOKIE[$this->_cookie_key])) {
                $property_ids = json_decode($_COOKIE[$this->_cookie_key]);
            }
            return $property_ids;
        }

        public function set_recently_viewed_property() {
            if (is_singular('property')) {
                $property_id = get_the_ID();
                $property_ids = $this->get_list_recently_viewed_property();
                $property_ids = array_diff($property_ids, array($property_id));
                array_unshift($property_ids, $property_id);
                setcookie($this->_cookie_key, json_encode($property_ids), time() + (DAY_IN_SECONDS * 31), '/');
            }
        }
    }

    /**
     * @return ERE_Recently_Viewed
     */
    function ERE_RV() {
        return ERE_Recently_Viewed::getInstance();
    }

    ERE_RV()->init();
}