<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/Parsa-mrz/VerifyWoo
 * @since      1.0.0
 *
 * @package    VerifyWoo
 */

namespace app;

defined( 'ABSPATH' ) || exit;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    VerifyWoo
 * @author     Parsa Mirzaie <Mirzaie_parsa@protonmail.ch>
 */
class Plugin {
	/**
	 * The single instance of the class.
	 *
	 * @var     \app\Plugin $instance
	 * @access  private
	 * @since   1.0.0
	 */
	private static $instance = null;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      \app\Plugin\Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;


	/**
	 * Main Plugin Instance.
	 * Ensures only one instance of the plugin is loaded or can be loaded.
	 *
	 * @since   1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since   1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'VerifyWoo' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since   1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'VerifyWoo' ), '1.0.0' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 * @since   1.0.0
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Define VerifyWoo Constants.
	 *
	 * @since   1.0.0
	 */
	private function define_constants() {
		$this->define( 'POWERSOFT365_VERSION', '1.0.0' );
		$this->define( 'POWERSOFT365_NAME', 'VerifyWoo' );
		$this->define( 'POWERSOFT365_CONTEXT', 'VerifyWoo' );
		$this->define( 'POWERSOFT365_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __DIR__ ) ) );
		$this->define( 'POWERSOFT365_PLUGIN_URL', untrailingslashit( plugin_dir_url( __DIR__ ) ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$this->define( 'POWERSOFT365_CLI', true );
		}
	}

	/**
	 * Define VerifyWoo plugin settings.
	 *
	 * @return void
	 */
	private function define_options() {}


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since   1.0.0
	 */
	private function __construct() {
		$this->define_constants();
		$this->define_options();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->run();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @throws  \Exception   Composer `autoload.php` missing.
	 */
	private function load_dependencies() {
		if ( defined( 'POWERSOFT365_CLI' ) && POWERSOFT365_CLI ) {
			new \app\CLI();
		}

		$this->loader = \app\Plugin\Loader::instance();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function set_locale() {
		$this->loader->add_action( 'plugins_loaded', \app\Plugin\I18n::class, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function define_admin_hooks() {
		$this->loader->add_action( 'before_woocommerce_init', self::class, 'declare_woocommerce_hpos_compatibility' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since   1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since   1.0.0
	 * @return  \app\Plugin\Loader  Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Declare WooCommerce's High-Performance Order Storage (HPOS) compatibility.
	 *
	 * @since   1.0.0
	 * @link https://developer.woocommerce.com/docs/hpos-extension-recipe-book/
	 */
	public static function declare_woocommerce_hpos_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', POWERSOFT365_PLUGIN_FILE, true );
		}
	}
}
