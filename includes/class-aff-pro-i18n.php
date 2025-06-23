<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://affpro.dev
 * @since      1.0.0
 *
 * @package    AFF_Pro
 * @subpackage AFF_Pro/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    AFF_Pro
 * @subpackage AFF_Pro/includes
 * @author     AffPro
 */
class AFF_Pro_i18n
	{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
		{

		load_plugin_textdomain(
			'aff-pro',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

		}



	}
