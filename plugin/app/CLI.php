<?php
/**
 * The file that defines the core CLI class.
 *
 * @link       https://github.com/Parsa-mrz/VerifyWoo
 * @since      1.0.0
 *
 * @package    VerifyWoo
 */

namespace app;

defined( 'ABSPATH' ) || exit;

use WP_CLI;

/**
 * The core CLI class.
 *
 * @since      1.0.0
 * @package    VerifyWoo
 * @author     Parsa Mirzaie <Mirzaie_parsa@protonmail.ch>
 */
class CLI {
	/**
	 * Load required files and hooks to make the CLI work.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Sets up and hooks WP CLI to our CLI code.
	 */
	private function hooks() {
	}
}
