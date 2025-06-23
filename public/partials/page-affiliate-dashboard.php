<?php
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Affiliate User Dashboard
 *
 * @package vuacode-aff
 */

// the_content();
echo do_shortcode( '[aff_user_dashboard]', true );
