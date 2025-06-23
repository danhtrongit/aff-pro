<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://vuacode.io
 * @since      1.0.0
 *
 * @package    WP_VuaCode_AFF
 * @subpackage WP_VuaCode_AFF/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WP_VuaCode_AFF
 * @subpackage WP_VuaCode_AFF/includes
 * @author     VuaCode
 */
class WP_VuaCode_AFF_i18n
	{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
		{

		load_plugin_textdomain(
			'vuacode-aff',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

		}



	}
