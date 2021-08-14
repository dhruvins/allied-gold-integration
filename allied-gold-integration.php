<?php
/**
 * Plugin Name: Allied Gold Integration
 * Plugin URI: https://imaginate-solutions.com/
 * Description: Plugin to integrate Allied Gold iframe.
 * Version: 1.0.0
 * Author: Imaginate Solutions
 * Author URI: https://imaginate-solutions.com/
 * Text Domain: allied-gold-integration
 * Domain Path: /i18n/languages/
 * Requires at least: 5.5
 * Requires PHP: 7.0
 *
 * @package AlliedGold
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Allied_Gold_Integration' ) ) {

	/**
	 * Main class to initiate the plugin.
	 */
	class Allied_Gold_Integration {

		/**
		 * TH March Integration.
		 *
		 * @var string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected $plugin_name = 'Allied Gold Integration for WooCommerce';

		/**
		 * Version 1.0.0
		 *
		 * @var string $version The current version of the plugin.
		 */
		protected $version = '1.0.0';

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->define_constants();

			if ( is_admin() ) {
				$this->load_admin();
			}

			$this->load_frontend();

		}

		/**
		 * Define constants to be used in the plugin.
		 *
		 * @return void
		 */
		public function define_constants() {
			$this->define( 'AG_PLUGIN_SLUG', 'ag-integration' );
			$this->define( 'AG_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
			$this->define( 'AG_PLUGIN_VERSION', '1.0.0' );
			$this->define( 'AG_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			$this->define( 'AG_TEMPLATE_PATH', AG_PLUGIN_PATH . '/templates/' );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param string      $name  Constant Name.
		 * @param string|bool $value Constant Value.
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Set Locale
		 */
		private function set_locale() {
			load_plugin_textdomain( 'allied-gold-integration', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages/' );
		}

		/**
		 * Load frontend.
		 *
		 * @return void
		 */
		public function load_frontend() {
			require_once 'includes/class-ag-frontend.php';
		}

		/**
		 * Load Admin.
		 *
		 * @return void
		 */
		public function load_admin() {
			require_once 'includes/class-ag-admin.php';
		}
	}
}

$allied_gold_int = new Allied_Gold_Integration();
