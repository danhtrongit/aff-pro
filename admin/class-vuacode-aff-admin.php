<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://vuacode.io
 * @since      1.0.0
 *
 * @package    WP_VuaCode_AFF
 * @subpackage WP_VuaCode_AFF/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_VuaCode_AFF
 * @subpackage WP_VuaCode_AFF/admin
 * @author     VuaCode
 */
class WP_VuaCode_AFF_Admin
	{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
		{

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		new AFF_Ajax_Admin();

		add_action( 'admin_menu', [ $this, 'add_admin_pages' ] );

		add_filter( 'wp_mail_content_type', function () {
			return "text/html";
			} );


		add_action( 'wp_trash_post', [ $this, 'woocommerce_trash_order' ] );


		//Re commission Order
		add_action( 'add_meta_boxes', function () {
			add_meta_box( 'aff_metabox_1', 'Tính lại Hoa hồng', [ $this, 'custom_order_option_cb' ], 'shop_order' );
			add_meta_box( 'aff_metabox_3', 'Tính lại Hoa hồng', [ $this, 'custom_order_option_cb' ], 'woocommerce_page_wc-orders' );

			if ( isset ($_GET['action']) && $_GET['action'] == 'edit' ) {
				add_meta_box( 'aff_metabox_2', 'Đơn CTV', [ $this, 'custom_order_option_cb_assign_user' ], 'shop_order' );
				add_meta_box( 'aff_metabox_4', 'Gán đơn CTV', [ $this, 'custom_order_option_cb_assign_user' ], 'woocommerce_page_wc-orders' );
				}


			} );

		if ( is_admin() && isset($_GET['setup_db']) ) {
			require_once __DIR__ . "/index.php";
			}
		if ( is_admin() && isset($_GET['aff_update_1']) ) {
			require_once __DIR__ . "/update/update-1.php";
			}

		add_action( 'plugins_loaded', array( 'PageTemplater_MH', 'get_instance' ) );
		add_action( 'admin_enqueue_scripts', [ $this, 'my_media_lib_uploader_enqueue' ] );

		add_action( 'restrict_manage_posts', [ $this, 'display_admin_shop_order_language_filter' ] );
		add_action( 'pre_get_posts', [ $this, 'process_admin_shop_order_language_filter' ] );


		add_filter( 'manage_edit-shop_order_columns', [ $this, 'custom_shop_order_column' ], 20 );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'custom_shop_order_column' ], 20 );
		add_action( 'manage_shop_order_posts_custom_column', [ $this, 'custom_orders_list_column_content' ], 20, 2 );
		add_action( 'woocommerce_shop_order_list_table_custom_column', [ $this, 'custom_orders_list_column_content_hpos' ], 20, 2 );

		}

	public function custom_orders_list_column_content($column, $post_id)
		{
		if ( $column == 'aff_column' ) {
			$ref_id = MH_Query::init( null, 'vuacode_user_order' )->where( 'order_id', $post_id )->first();
			if ( $ref_id )
				$this->showAffIcon();
			}
		}
	public function custom_orders_list_column_content_hpos($column, $order)
		{
		if ( $column == 'aff_column' ) {
			$ref_id = MH_Query::init( null, 'vuacode_user_order' )->where( 'order_id', $order->get_id() )->first();
			if ( $ref_id )
				$this->showAffIcon();
			}
		}

	public function showAffIcon()
		{
		echo '<div><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="24" height="24" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 24 24" xml:space="preserve" class="cQR8pQ"><g><g data-name="Layer 2"><path fill="#2196f3" d="M20.8 8.838a1.445 1.445 0 0 1-.419-1.012V6.548a2.934 2.934 0 0 0-2.931-2.93h-1.276a1.425 1.425 0 0 1-1.012-.418l-1.09-1.091a2.934 2.934 0 0 0-4.144 0L8.838 3.2a1.425 1.425 0 0 1-1.012.419H6.549a2.934 2.934 0 0 0-2.931 2.93v1.277A1.441 1.441 0 0 1 3.2 8.837L2.108 9.928a2.934 2.934 0 0 0 0 4.144l1.092 1.09a1.445 1.445 0 0 1 .419 1.012v1.278a2.934 2.934 0 0 0 2.931 2.93h1.276a1.425 1.425 0 0 1 1.012.419l1.09 1.091a2.933 2.933 0 0 0 4.144 0l1.09-1.091a1.425 1.425 0 0 1 1.012-.419h1.277a2.934 2.934 0 0 0 2.931-2.93v-1.278a1.437 1.437 0 0 1 .419-1.011l1.091-1.091a2.934 2.934 0 0 0 0-4.144z" data-original="#2196f3"></path><circle cx="12" cy="12" r="6.75" fill="#29b3ff" data-original="#29b3ff"></circle><path fill="#ffffff" d="M11 14.75a.744.744 0 0 1-.53-.22l-2-2a.75.75 0 0 1 1.06-1.06L11 12.939l3.47-3.469a.75.75 0 0 1 1.06 1.06l-4 4a.744.744 0 0 1-.53.22z" data-original="#ffffff"></path></g></g></svg></div>';
		}

	public function custom_shop_order_column($columns)
		{
		$reordered_columns = array();
		foreach ( $columns as $key => $column ) {
			$reordered_columns[$key] = $column;
			if ( $key == 'order_total' ) {
				$reordered_columns['aff_column'] = __( 'Đơn hàng AFF', 'theme_domain' );
				}
			}
		return $reordered_columns;
		}

	public function display_admin_shop_order_language_filter()
		{
		global $pagenow, $post_type;

		if ( 'shop_order' === $post_type && 'edit.php' === $pagenow ) {
			$domain    = 'woodmart';
			$languages = array( array( __( 'Đơn cộng tác viên', $domain ), 'ebay_managed_payment' ) );
			$current   = isset($_GET['filter_affiliate_order']) ? $_GET['filter_affiliate_order'] : '';
			echo '<select name="filter_affiliate_order">
	        <option value="">' . __( 'Lọc đơn Cộng tác viên ', $domain ) . '</option>';

			foreach ( $languages as $value ) {
				printf(
					'<option value="%s"%s>%s</option>',
					$value[1],
					$value[1] === $current ? '" selected="selected"' : '',
					$value[0]
				);
				}
			echo '</select>';
			}
		}

	public function process_admin_shop_order_language_filter($query)
		{
		global $pagenow;

		if (
			$query->is_admin && $pagenow == 'edit.php' && isset($_GET['filter_affiliate_order'])
			&& $_GET['filter_affiliate_order'] != '' && $_GET['post_type'] == 'shop_order'
		) {

			$meta_query = array(
				array(
					'key'     => '_ref_id',
					'compare' => 'EXISTS'
				)
			);
			$query->set( 'meta_query', $meta_query );


			}
		}

	public function my_media_lib_uploader_enqueue()
		{
		if ( is_admin() && !empty($_SERVER['PHP_SELF']) && 'upload.php' !== basename( $_SERVER['PHP_SELF'] ) )
			wp_enqueue_media();
		}

	public function custom_order_option_cb_assign_user($post)
		{

		$user_order = MH_Query::init( null, 'vuacode_user_order' )->where( 'order_id', $post->ID )->where( 'level', 0 )->first();
		if ( get_post_status( $post ) == 'wc-completed' && !$user_order ) {
			echo 'Vui lòng chuyển trạng thái đơn hàng về Đang xử lý nếu bạn muốn gán đơn cho CTV nào';
			} else if ( !$user_order ) {
			?>
																															<div>Gán đơn cho Cộng tác viên</div>
																															<div style="margin-top: 10px;">
																																<select id="affiliate-user" name="my_select2_field" class="select2" data-minimum-input-length="3" style="width: 100%;"> </select>
																															</div>
																															<button type="submit" class="button assign-commission button-warning" style="margin-top:10px;">Submit</button>

																															<script>
																																	jQuery( document ).ready( function( $ ) {
																																		$( '#affiliate-user' ).select2( {
																																			ajax: {
																																				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
																																				dataType: 'json',
																																				delay: 250,
																																				data: function( params ) {
																																					return {
																																						action: 'aff_select2_ajax_user_login',
																																						search: params.term
																																					};
																																				},
																																				processResults: function( data ) {
																																					return {
																																						results: data
																																					};
																																				},
																																				cache: true
																																			},
																																			minimumInputLength: 3
																																		} );

																																		$('.assign-commission').click(function (e) { 
																																			e.preventDefault();
																																			const user = $('#affiliate-user').val()
																																			if(!user)
																																				return alert('Vui lòng chọn tài khoản cộng tác viên')
																																			if(!confirm('Bạn có chắc chắn muốn tính hoa hồng cho cộng tác viên này?'))
																																				return;

																																			$.ajax({
																																				type: "POST",
																																				url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
																																				data: {id:  $('#post_ID').val(), user, action: 'aff_assign_commission'},
																																				dataType: "json",
																																				success: function (res) {
																																					alert(res.msg)
																																					if(res.success)
																																						window.location.reload()
																																				}
																																			});
																																		});

																																	} );
																																</script>
																							<?php
			} else echo 'Đơn hàng được giới thiệu bởi CTV ' . $user_order['user_ref'];

		}

	public function custom_order_option_cb($post)
		{
		$user_order = MH_Query::init( null, 'vuacode_user_order' )->where( 'order_id', $post->ID )->where( 'status', 0 )->get();
		if ( $user_order ) {
			?>
																								<div>Tính toán lại hoa hồng cho đơn hàng</div><div>
																								<button type="submit" class="button re-commission button-warning" style="margin-top:10px;">Tính lại hoa hồng</button></div>
																								<script>
																									jQuery(document).ready(function ($) {
																										$('.re-commission').click(function (e) { 
																											e.preventDefault();
																											if(!confirm('Bạn có chắc chắn muốn tính lại hoa hồng cho đơn hàng này?'))
																												return;

																											$.ajax({
																												type: "POST",
																												url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
																												data: {id: <?php echo get_the_ID() ?>, action: 'aff_re_commission'},
																												dataType: "json",
																												success: function (res) {
																													alert(res.msg)
																													if(res.success)
																														window.location.reload()
																												}
																											});
																										});
					
																									});
																								</script>
																							<?php
			}
		echo '<p>Lưu ý: Tính năng này chỉ hoạt động với đơn hàng Đang xử lý. Ví dụ CTV giới thiệu được đơn hàng A, nhưng sau đó khách hàng đổi ý muốn thay đổi sản phẩm khác thì bạn có thể Tính lại hoa hồng cho đơn hàng.</p>';
		}

	public function woocommerce_trash_order($post_id)
		{
		AFF_User_Order::moveOrderToTrash( $post_id );
		}

	public function add_admin_pages()
		{

		$icon   = AFF_URL . 'public/images/box.svg';
		$c_id   = get_current_user_id();
		$user   = new WP_User( $c_id );
		$u_role = $user->roles[0];
		if ( $u_role == 'administrator' ) {
			add_menu_page(
				__( 'VuaCode AFF', 'textdomain' ),
				'VuaCode AFF',
				'manage_options',
				'vuacode-aff',
				[ $this, 'admin_template' ],
				$icon,
				110
			);
			} elseif ( $u_role == 'shop_manager' )
			add_menu_page(
				__( 'VuaCode AFF', 'textdomain' ),
				'VuaCode AFF',
				'shop_manager',
				'vuacode-aff',
				[ $this, 'admin_template' ],
				$icon,
				110
			);



		}

	public function admin_template()
		{
		require_once plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-display.php';
		}
	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/vuacode-aff-admin.css', array(), $this->version, 'all' );

		}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/vuacode-aff-admin.js', array( 'jquery' ), $this->version, false );

		}

	}
