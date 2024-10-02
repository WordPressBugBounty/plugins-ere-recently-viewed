<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('ERE_RV_Assets')) {
	class ERE_RV_Assets {
		private static $_instance;
		public static function getInstance()
		{
			if (self::$_instance == NULL) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}


		public function init() {
		    add_action('init',array($this,'register_assets'));
			add_action( 'wp_enqueue_scripts', array($this, 'enqueue_assets'));
        }

        public function register_assets(){
            wp_register_style(ERE_RV()->assets_handle('frontend'),ERE_RV()->asset_url('assets/scss/frontend.min.css'),array(),ERE_RV()->plugin_ver());

		}

        public function enqueue_assets() {
            wp_enqueue_style(ERE_RV()->assets_handle('frontend'));
        }
	}
}