<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://vuacode.io
 * @since      1.0.0
 *
 * @package    WP_VuaCode_AFF
 * @subpackage WP_VuaCode_AFF/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WP_VuaCode_AFF
 * @subpackage WP_VuaCode_AFF/public
 * @author     VuaCode
 */
class WP_VuaCode_AFF_Public
	{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	private $settings;
	private $ref_value;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	public $user = null;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
		{

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		//Add Billing Fields
		add_action( 'woocommerce_checkout_billing', [ $this, 'woocommerce_checkout_billing' ] );
		add_action( 'woocommerce_checkout_create_order', [ $this, 'woocommerce_checkout_create_order' ], 20, 1 );

		//Handle commission 
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'woocommerce_checkout_order_processed' ], 10, 3 );

		//Hanle when order is complete
		add_action( 'woocommerce_order_status_changed', [ $this, 'woocommerce_order_status_changed' ], 10, 4 );

		add_action( 'wp_loaded', [ $this, 'wp_loaded' ] );

		//Auto active Affiliate
		add_action( 'user_register', [ $this, 'user_register' ] );

		//Add Configs to FE
		add_action( 'wp_footer', [ $this, 'wp_footer' ] );

		//Addshortcode UserDashboard
		add_shortcode( 'aff_user_dashboard', [ $this, 'aff_user_dashboard' ] );
		add_shortcode( 'aff_user_dashboard_ver2', [ $this, 'aff_user_dashboard_ver2' ] );
		add_shortcode( 'aff_share_link', [ $this, 'aff_show_link_single_product' ] );
		add_shortcode( 'aff_share_link_2', [ $this, 'copy_affiliate_link_2' ] );
		add_shortcode( 'aff_rank', [ $this, 'aff_rank' ] );


		add_filter( 'woocommerce_account_menu_items', [ $this, 'add_custom_menu_item_to_account_menu' ], 10 );
		add_action( 'init', [ $this, 'add_custom_endpoint' ] );
		add_action( 'template_redirect', [ $this, 'redirect_custom_endpoint_to_custom_page' ] );

		//FunnelKit
		// 1.Change Hook Billing Feilds in checkout page
		// 2.Hook on adding a new comment for upsell
		}

	public function redirect_custom_endpoint_to_custom_page()
		{
		global $wp_query;
		if ( isset($wp_query->query_vars['affliate-page']) ) {
			wp_redirect( AFF_Config::getConfig( 'aff_user_page' ) );
			exit;
			}
		}

	public function add_custom_menu_item_to_account_menu($items)
		{
		$items['affliate-page'] = __( 'Cộng tác viên', 'woocommerce' );
		return $items;
		}

	public function add_custom_endpoint()
		{
		add_rewrite_endpoint( 'affliate-page', EP_ROOT | EP_PAGES );
		}

	public function wp_loaded()
		{
		// debug(AFF_User_Order::getIncomeInMonth(1));
		// if(isset($_GET['sss']))
		if ( is_user_logged_in() ) {
			$this->user = wp_get_current_user();
			if ( $this->user->aff_active ) {
				$this->settings = AFF_Config::getConfigs();
				// debug($this->settings);
				$this->ref_value = isset($this->settings['ref_value_is_id']) && $this->settings['ref_value_is_id'] ? 'ID' : 'user_login';
				// add_action("woocommerce_before_shop_loop_item_title", [$this, "copy_affiliate_link"]);
				add_action( "woocommerce_before_shop_loop_item_title", [ $this, "copy_affiliate_link_2" ] );
				add_action( 'woocommerce_single_product_summary', [ $this, 'aff_show_link_single_product' ], 10 );
				}
			}
		$this->user = wp_get_current_user();
		// Set view
		if ( isset($_GET['sss']) ) {
			$users = MH_Query::init( null, 'users' )->get();
			foreach ( $users as $key => $u ) {
				if ( $u['parent_id'] == 0 ) {
					$r = MH_Query::init( null, 'vuacode_user_relationships' )->where( 'distance', 1 )->where( 'descendant_id', $u['ID'] )->first();
					if ( $r )
						MH_Query::init( null, 'users' )->where( 'ID', $r['descendant_id'] )->update( [ 'parent_id' => $r['ancestor_id'] ] );

					}
				}
			}

		}

	public function aff_rank($atts)
		{
		$ranks = AFF_User_Order::getRankOrder( $atts );
		return MH_Load_view( '/shortcodes/rank.php', [ 'ranks' => $ranks, 'atts' => $atts ], false, false, AFF_PATH );
		}

	public function aff_show_link_single_product()
		{
		if ( !isset($this->user->aff_active) || !$this->user->aff_active )
			return;
		global $product;
		$commission = $this->getCommissionProduct( $product );
		if ( $commission ) {
			if ( is_array( $commission ) )
				$commission_text = wc_price( $commission[0] ) . ' - ' . wc_price( $commission[1] );
			else
				$commission_text = wc_price( $commission );
			ob_start();
			?>
			<div class="aff-text-commission">Chia sẻ ngay để nhận hoa hồng <?php echo $commission_text ?></div>
			<div class="aff-copy-button">
				<input type="text"
					value="<?php echo get_the_permalink() . "?{$this->settings['ref_name']}=" . $this->user->{$this->ref_value} ?>">
				<a type="button" class="aff-copy-link"
					href="<?php echo get_the_permalink() . "?{$this->settings['ref_name']}=" . $this->user->{$this->ref_value} ?>"
					title="Sao chép"><svg style="width: 14px" class="jd Ab Da" xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 448 512">
						<path
							d="M433.941 65.941l-51.882-51.882A48 48 0 0 0 348.118 0H176c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h224c26.51 0 48-21.49 48-48v-48h80c26.51 0 48-21.49 48-48V99.882a48 48 0 0 0-14.059-33.941zM266 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h74v224c0 26.51 21.49 48 48 48h96v42a6 6 0 0 1-6 6zm128-96H182a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h106v88c0 13.255 10.745 24 24 24h88v202a6 6 0 0 1-6 6zm6-256h-64V48h9.632c1.591 0 3.117.632 4.243 1.757l48.368 48.368a6 6 0 0 1 1.757 4.243V112z">
						</path>
					</svg> Sao chép</a>
			</div>

			<?php
			return ob_get_clean();
			}
		}

	public function copy_affiliate_link()
		{
		echo '<a class="aff-copy-link" href="' . get_the_permalink() . "?{$this->settings['ref_name']}=" . $this->user->{$this->ref_value} . '"><img class="aff-image-copy-link" src="' . AFF_URL . '/public/images/link-closed-flat.png" alt="Link free icon" title="Link giới thiệu cho người mua hàng"></a>';
		}
	public function copy_affiliate_link_2()
		{
		global $product;
		$commission = $this->getCommissionProduct( $product );
		if ( $commission ) {
			if ( is_array( $commission ) )
				$commission_text = wc_price( $commission[0] ) . ' - ' . wc_price( $commission[1] );
			else
				$commission_text = wc_price( $commission );
			echo '<a class="aff-copy-link" href="' . get_the_permalink() . "?{$this->settings['ref_name']}=" . $this->user->{$this->ref_value} . '"><img class="aff-image-copy-link" src="' . AFF_URL . '/public/images/link-closed-flat.png" alt="Link free icon" title="Link giới thiệu cho người mua hàng"> Hoa hồng: ' . $commission_text . '</a>';
			}
		}

	public function getCommissionProduct($product)
		{
		if ( !$product )
			return;
		$commission_percent_default = $this->settings['commission_percent_default'];
		$commission_user_levels     = $this->settings['commission_user_levels'];

		$commission_level = $commission_user_levels[$this->user->level]['commission'] > 0 ? $commission_user_levels[$this->user->level]['commission'] : 0;

		if ( $this->settings['aff_mode'] == 'order_mode' ) {
			$commission_percent = $commission_percent_default + $commission_level;
			}
		if ( $this->settings['aff_mode'] == 'product_mode' ) {
			$commission_setting = AFF_Commission_Settings::getCommissionSettingById( $product->get_id() );
			if ( $commission_setting === '0' )
				return 0;

			if ( $commission_setting )
				$commission_percent = $commission_setting + $commission_level;
			else {
				if ( intval( $commission_percent_default ) == 0 )
					return 0;
				else
					$commission_percent = $commission_percent_default + $commission_level;
				}
			}

		if ( $product->is_type( 'variable' ) ) {
			$min_price = $product->get_variation_price( 'min' );
			$max_price = $product->get_variation_price( 'max' );
			if ( $min_price == $max_price )
				$commission = ceil( $max_price / 100 * $commission_percent );
			else {
				$commission_min = $min_price == 0 ? 0 : ceil( $min_price / 100 * $commission_percent );
				$commission_max = $max_price == 0 ? 0 : ceil( $max_price / 100 * $commission_percent );
				$commission     = [ $commission_min, $commission_max ];
				}

			if ( $min_price == 0 && $max_price == 0 )
				return false;
			}

		if ( $product->is_type( 'simple' ) ) {
			$price = $product->get_price();
			if ( !$price )
				return 0;
			$commission = ceil( $price / 100 * $commission_percent );
			}

		return $commission;

		}

	public function view($login)
		{
		$user = get_user_by( 'login', $login );
		$date = date( 'Y-m-d' );
		if ( $user ) {

			$url = aff_reconstruct_url( false );
			AFF_Traffic::setView( $user->ID, $url, $date );
			}
		}

	public function aff_user_dashboard()
		{
		// return MH_Load_view('vuacode-aff-public-display.php');
		include_once AFF_PATH . 'public/partials/vuacode-aff-public-display.php';
		include_once AFF_PATH . 'public/partials/vuacode-aff-public-display_ver2.php';

		}
	public function aff_user_dashboard_ver2()
		{
		include_once AFF_PATH . 'public/partials/vuacode-aff-public-display_ver2.php';
		}

	public function user_register($user_id)
		{

		$check_user_relationship = MH_Query::init( null, 'vuacode_user_relationships' )->where( 'ancestor_id', $user_id )->where( 'descendant_id', $user_id )->first();
		if ( !$check_user_relationship ) {
			MH_Query::init( null, 'vuacode_user_relationships' )->insert( [
				'ancestor_id'   => $user_id,
				'descendant_id' => $user_id,
				'distance'      => 0,
			] );
			}


		$aff_auto_active = AFF_Config::getConfig( 'aff_auto_active' );
		if ( $aff_auto_active == 'true' ) {
			AFF_User::update( [ 'ID' => $user_id, 'aff_active' => 1 ] );
			AFF_User::bonusRegister( $user_id );

			}
		}

	public function woocommerce_order_status_changed($id, $status_transition_from, $status_transition_to, $that)
		{
		if ( $status_transition_to === 'completed' )
			AFF_User_Order::approveCommission( $id, $status_transition_to );
		if ( in_array( $status_transition_to, [ 'cancelled', 'refunded', 'failed' ] ) )
			AFF_User_Order::refundCommission( $id, $status_transition_to );

		AFF_User_Order::updateOrderStatus( $id, $status_transition_to );
		}


	public function woocommerce_checkout_order_processed($order_id, $posted_data, $order)
		{
		// $ref_id = get_post_meta($order_id, '_ref_id', true);
		// $ref_path = get_post_meta($order_id, '_ref_path', true);
		// $ref_coupon = get_post_meta($order_id, '_ref_coupon', true);
		// $ref_product = get_post_meta($order_id, '_ref_product', true);

		$ref_id      = $order->get_meta( '_ref_id' );
		$ref_path    = $order->get_meta( '_ref_path' );
		$ref_coupon  = $order->get_meta( '_ref_coupon' );
		$ref_product = $order->get_meta( '_ref_product' );

		// $user = get_user_by('login', 'haihai');
		$allow_order_self = AFF_Config::getConfig( 'allow_order_self' );
		$ref_value_is_id  = AFF_Config::getConfig( 'ref_value_is_id' );
		$current_user     = wp_get_current_user();

		if ( $ref_value_is_id == 'true' )
			$user = get_user_by( 'id', $ref_id );

		if ( !$user )
			$user = get_user_by( 'login', $ref_id );


		// //Thiết lập cấp cha con để người mua hàng làm cấp dưới 
		if ( get_current_user_id() && $user ) {
			if ( !$current_user->parent_id && $user->ID != $current_user->ID ) {
				AFF_User_Relationship::setRelationship( $current_user->ID, $user->ID );
				MH_Query::init( null, 'users' )->where( 'ID', $current_user->ID )->update( [ 'parent_id' => $user->ID ] );
				}
			}

		if ( $allow_order_self == 'true' && get_current_user_id() ) {
			$ref_path = get_site_url();
			if ( $current_user->aff_active ) {
				$user   = $current_user;
				$ref_id = $ref_value_is_id == 'true' ? $user->ID : $user->user_login;
				add_post_meta( $order_id, '_ref_id', $ref_id, true );
				}
			}

		if ( !$current_user->aff_active && $current_user->parent_id ) {
			$user = get_user_by( 'id', $current_user->parent_id );
			}



		if ( $user && $user->aff_active ) {
			// $args = array(
			//     'customer_id' => $user->ID,
			//     'post_status' => 'completed',
			//     'post_type' => 'shop_order',
			//     'return' => 'ids',
			// );
			// $numorders = count( wc_get_orders( $args ) );
			// if($numorders > 0)

			AFF_User_Order::create( $user, $order, $ref_path, $ref_product, $ref_coupon );
			}

		}


	public function woocommerce_checkout_create_order($order)
		{

		if ( isset($_POST['ref_id']) ) {
			$ref_id = $_POST['ref_id'];
			if ( !empty($ref_id) ) {
				$order->update_meta_data( '_ref_id', $ref_id );
				}
			}

		if ( isset($_POST['ref_path']) ) {
			$ref_path = $_POST['ref_path'];
			if ( !empty($ref_path) ) {
				$order->update_meta_data( '_ref_path', $ref_path );
				}
			}

		if ( isset($_POST['ref_coupon']) ) {
			$ref_coupon = $_POST['ref_coupon'];
			if ( !empty($ref_coupon) ) {
				$order->update_meta_data( '_ref_coupon', $ref_coupon );
				}
			}

		if ( isset($_POST['ref_product']) ) {
			$ref_product = $_POST['ref_product'];
			if ( !empty($ref_product) ) {
				$order->update_meta_data( '_ref_product', $ref_product );
				}
			}

		}

	public function woocommerce_checkout_billing()
		{

		woocommerce_form_field( 'ref_id', array(
			'type'        => 'hidden',
			'class'       => array( 'ref_id' ),
			'label'       => __( '' ),
			'placeholder' => __( '' ),
		), '' );

		woocommerce_form_field( 'ref_path', array(
			'type'        => 'hidden',
			'class'       => array( 'ref_path' ),
			'label'       => __( '' ),
			'placeholder' => __( '' ),
		), '' );


		woocommerce_form_field( 'ref_product', array(
			'type'        => 'hidden',
			'class'       => array( 'ref_product' ),
			'label'       => __( '' ),
			'placeholder' => __( '' ),
		), '' );

		woocommerce_form_field( 'ref_coupon', array(
			'type'        => 'hidden',
			'class'       => array( 'ref_coupon' ),
			'label'       => __( '' ),
			'placeholder' => __( '' ),
		), '' );

		}


	public function wp_footer()
		{

		$time                = AFF_Config::getConfig( 'aff_cookie_time' );
		$time                = $time ? $time : 1;
		$once                = AFF_Config::getConfig( 'aff_cookie_once' );
		$cookie_traffic_mode = AFF_Config::getConfig( 'cookie_traffic_mode' );
		$ref_name            = AFF_Config::getConfig( 'ref_name' );
		$settings            = [
			'time'                => $time,
			'once'                => $once,
			'cookie_traffic_mode' => $cookie_traffic_mode,
			'current_user'        => $this->user->user_login,
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'ref_name'            => $ref_name ? $ref_name : 'ref'
		];

		echo "<input type='hidden' id='aff_settings' data-settings='" . json_encode( $settings ) . "'>";
		}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
		{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_VuaCode_AFF_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_VuaCode_AFF_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/vuacode-aff-public.css', array(), $this->version, 'all' );

		}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
		{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_VuaCode_AFF_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_VuaCode_AFF_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . '-sweet-alert', AFF_URL . '/admin/js/sweetalert2@10.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/vuacode-aff-public.js', array( 'jquery' ), $this->version, false );

		}

	}