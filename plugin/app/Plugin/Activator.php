<?php
/**
 * Fired during plugin activation
 *
 * @link       https://github.com/Parsa-mrz/VerifyWoo
 * @since      1.0.0
 *
 * @package    VerifyWoo
 */

namespace app\Plugin;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    VerifyWoo
 * @author     Parsa Mirzaie <Mirzaie_parsa@protonmail.ch>
 */
class Activator {
	/**
	 * Runs once when plugin is activated.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		self::create_tables();
		self::create_options();
		self::attribute_taxonomy_create();
	}


	/**
	 * Set up the database tables which the plugin needs to function.
	 * WARNING: If you are modifying this method, make sure that its safe to call regardless of the state of database.
	 *
	 * This is called from `install` method and is executed in-sync when WC is installed or updated. This can also be called optionally from `verify_base_tables`.
	 *
	 * TODO: Add all crucial tables that we have created from workers in the past.
	 *
	 * Tables:
	 *      verifyWo_customers - Table for storing attribute taxonomies - these are user defined
	 *      woocommerce_downloadable_product_permissions - Table for storing user and guest download permissions.
	 *          KEY(order_id, product_id, download_id) used for organizing downloads on the My Account page
	 *      woocommerce_order_items - Order line items are stored in a table to make them easily queryable for reports
	 *      woocommerce_order_itemmeta - Order line item meta is stored in a table for storing extra data.
	 *      woocommerce_tax_rates - Tax Rates are stored inside 2 tables making tax queries simple and efficient.
	 *      woocommerce_tax_rate_locations - Each rate can be applied to more than one postcode/city hence the second table.
	 *
	 * @return array Strings containing the results of the various update queries as returned by dbDelta.
	 */
	public static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * Before updating with DBDELTA, remove any primary keys which could be
		 * modified due to schema updates.
		 */
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}woocommerce_downloadable_product_permissions';" ) ) {
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}woocommerce_downloadable_product_permissions` LIKE 'permission_id';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_downloadable_product_permissions DROP PRIMARY KEY, ADD `permission_id` bigint(20) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT;" );
			}
		}

		$db_delta_result = dbDelta( self::get_schema() );

		return $db_delta_result;

		/**
		 * Update indexes.
		 */
		$indexes = array(
			"{$wpdb->prefix}verifyWo_customers" => array(
				'idx_user_id'           => array( 'column' => 'user_id' ),
				'idx_customer_code_365' => array( 'column' => 'customer_code_365' ),
				'idx_email'             => array( 'column' => 'email' ),
			),
		);

		foreach ( $indexes as $table_name => $table_indexes ) {
			foreach ( $table_indexes as $table_index => $columns ) {
				$query        = $wpdb->prepare(
					"SHOW INDEX FROM $table_name WHERE Key_name = %s",
					$index_name
				);
				$index_exists = $wpdb->get_results( $query );

				$index_exists = $wpdb->get_row( "SHOW INDEX FROM {$table_name} WHERE column_name = 'comment_type' and key_name = 'woo_idx_comment_type'" );

			}
		}

		$index_exists = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->comments} WHERE column_name = 'comment_type' and key_name = 'woo_idx_comment_type'" );

		if ( is_null( $index_exists ) ) {
			// Add an index to the field comment_type to improve the response time of the query
			// used by WC_Comments::wp_count_comments() to get the number of comments by type.
			$wpdb->query( "ALTER TABLE {$wpdb->comments} ADD INDEX woo_idx_comment_type (comment_type)" );
		}

		return $db_delta_result;
	}


	/**
	 * Get Table schema.
	 *
	 * See https://github.com/woocommerce/woocommerce/wiki/Database-Description/
	 *
	 * A note on indexes; Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
	 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
	 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
	 *
	 * Changing indexes may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
	 * indexes first causes too much load on some servers/larger DB.
	 *
	 * When adding or removing a table, make sure to update the list of tables in WC_Install::get_tables().
	 *
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "CREATE TABLE {$wpdb->prefix}verifyWo_customers (
			user_id BIGINT(20) UNSIGNED NOT NULL,
			customer_code_365 VARCHAR(255) NOT NULL,
			customer_code_secondary VARCHAR(255) NOT NULL,
			account_code_365 VARCHAR(255) DEFAULT '',
			is_company BOOLEAN NOT NULL,
			company_name VARCHAR(255) DEFAULT '',
			last_name VARCHAR(255) DEFAULT '',
			first_name VARCHAR(255) DEFAULT '',
			short_name VARCHAR(255) DEFAULT '',
			store_code_365 VARCHAR(255) NOT NULL,
			active BOOLEAN NOT NULL,
			date_of_birth VARCHAR(255) DEFAULT '',
			gender VARCHAR(1) DEFAULT '',
			tel_1 VARCHAR(255) DEFAULT '',
			tel_2 VARCHAR(255) DEFAULT '',
			fax VARCHAR(255) DEFAULT '',
			mobile VARCHAR(255) DEFAULT '',
			sms VARCHAR(255) DEFAULT '',
			email VARCHAR(255) DEFAULT '',
			website VARCHAR(255) DEFAULT '',
			category_name VARCHAR(255) DEFAULT '',
			category_code_365 VARCHAR(255) DEFAULT '',
			category_2_name VARCHAR(255) DEFAULT '',
			category_2_code_365 VARCHAR(255) DEFAULT '',
			company_activity_name VARCHAR(255) DEFAULT '',
			company_activity_code_365 VARCHAR(255) DEFAULT '',
			agent_name: VARCHAR(255) DEFAULT 'no',
			agent_code_365 VARCHAR(255) DEFAULT '',
			credit_limit_amount DECIMAL(10,2) DEFAULT 0.00,
			vat_registration_number VARCHAR(255) DEFAULT '',
			customer_id VARCHAR(255) DEFAULT '',
			tax_office VARCHAR(255) DEFAULT '',
			remarks TEXT DEFAULT '',
			address_line_1 VARCHAR(255) DEFAULT '',
			address_line_2 VARCHAR(255) DEFAULT '',
			address_line_3 VARCHAR(255) DEFAULT '',
			postal_code VARCHAR(50) DEFAULT '',
			town VARCHAR(255) DEFAULT '',
			country_code_iso2 VARCHAR(2) DEFAULT '',
			country_name VARCHAR(255) DEFAULT '',
			contact_last_name VARCHAR(255) DEFAULT '',
			contact_first_name VARCHAR(255) DEFAULT '',
			default_price INT(3) DEFAULT 0,
			loyalty_factor DECIMAL(5,2) DEFAULT 1.00,
			from_mobile_device BOOLEAN DEFAULT 0,
			text_field_1_value VARCHAR(255) DEFAULT '',
			text_field_2_value VARCHAR(255) DEFAULT '',
			text_field_3_value VARCHAR(255) DEFAULT '',
			text_field_4_value VARCHAR(255) DEFAULT '',
			text_field_5_value VARCHAR(255) DEFAULT '',
			number_field_1_value DECIMAL(10,2) DEFAULT 0.00,
			number_field_2_value DECIMAL(10,2) DEFAULT 0.00,
			number_field_3_value DECIMAL(10,2) DEFAULT 0.00,
			number_field_4_value DECIMAL(10,2) DEFAULT 0.00,
			number_field_5_value DECIMAL(10,2) DEFAULT 0.00,
			date_field_1_value VARCHAR(255) DEFAULT '',
			date_field_2_value VARCHAR(255) DEFAULT '',
			date_field_3_value VARCHAR(255) DEFAULT '',
			date_field_4_value VARCHAR(255) DEFAULT '',
			date_field_5_value VARCHAR(255) DEFAULT '',
			points_balance DECIMAL(10,2) DEFAULT 0.00,
			points_ever_balance DECIMAL(10,2) DEFAULT 0.00,
			is_euc VARCHAR(255) DEFAULT 'no',
			is_export VARCHAR(255) DEFAULT 'no',
			autoemail BOOLEAN NOT NULL,
			autosms BOOLEAN NOT NULL,
			discount_limit DECIMAL(10,2) DEFAULT 0.00,
			balance DECIMAL(10,2) DEFAULT 0.00,
			b2b BOOLEAN NOT NULL
			PRIMARY KEY (user_id),
			UNIQUE KEY customer_code_365 (customer_code_365)
		) $collate;";

		return $tables;
	}

	/**
	 * Return a list of VerifyWoo tables. Used to make sure all VerifyWoo tables are dropped when uninstalling the plugin
	 * in a single site or multi site environment.
	 *
	 * @return array VerifyWoo tables.
	 */
	public static function get_tables() {
		global $wpdb;

		$tables = array(
			"{$wpdb->prefix}verifyWo_customers",
		);

		return $tables;
	}

	public static function create_options() {
	}

			/**
			 * Creates WooCommerce product attribute taxonomies for predefined types.
			 *
			 * This method defines and creates multiple WooCommerce product attribute taxonomies
			 * such as "Brand," "Season," and "Color." If the taxonomy already exists, it is skipped.
			 *
			 * @return array An associative array of taxonomy statuses, where the key is the taxonomy slug
			 *               and the value is either 'created', 'exists', or an error message.
			 */
	public static function attribute_taxonomy_create() {
		$taxonomies = apply_filters(
			'verifyWo_woocommerce_attributes',
			array(
				'brand'      => array(
					'name'         => 'Brand',
					'slug'         => 'brand',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
				'season'     => array(
					'name'         => 'Season',
					'slug'         => 'season',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
				'color'      => array(
					'name'         => 'Color',
					'slug'         => 'color',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
				// Todo: attribute name should get from settings.
				'attribute1' => array(
					'name'         => 'Attribute1',
					'slug'         => 'attribute1',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
				'attribute2' => array(
					'name'         => 'Attribute2',
					'slug'         => 'attribute2',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
				'attribute3' => array(
					'name'         => 'Attribute3',
					'slug'         => 'attribute3',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
				'attribute4' => array(
					'name'         => 'Attribute4',
					'slug'         => 'attribute4',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
				'attribute5' => array(
					'name'         => 'Attribute5',
					'slug'         => 'attribute5',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
				'attribute6' => array(
					'name'         => 'Attribute6',
					'slug'         => 'attribute6',
					'type'         => 'select',
					'order_by'     => 'name',
					'has_archives' => false,
				),
			)
		);

		$results = array();

		foreach ( $taxonomies as $key => $args ) {
			if ( wc_attribute_taxonomy_id_by_name( $args['slug'] ) ) {
					$results[ $key ] = 'exists';
					continue;
			}

			$taxonomy_id = wc_create_attribute( $args );

			if ( is_wp_error( $taxonomy_id ) ) {
					$error_message   = $taxonomy_id->get_error_message();
					$results[ $key ] = $error_message;
			} else {
				$results[ $key ] = 'created';
			}
		}
		return $results;
	}
}
