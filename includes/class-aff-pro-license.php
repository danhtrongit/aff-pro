<?php

/**
 * AFF Pro License Management Class
 *
 * Handles license validation, updates, and plugin information management.
 *
 * @since      1.0.0
 * @package    AFF_Pro
 * @subpackage AFF_Pro/includes
 * @author     AFF Pro Team
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AFF_Pro_License_Manager' ) ) {
    class AFF_Pro_License_Manager
    {
        /**
         * Cache key for plugin data
         * @var string|null
         */
        public $cache_key = null;

        /**
         * Whether caching is allowed
         * @var bool
         */
        public $cache_allowed = true;

        /**
         * Plugin information
         * @var array|null
         */
        protected $plugin = null;

        /**
         * License information
         * @var array|null
         */
        protected $license = null;

        /**
         * Constructor
         *
         * @param string $plugin_file Path to plugin file
         */
        public function __construct( $plugin_file ) {
            // Check if plugin file exists
            if ( ! file_exists( $plugin_file ) ) {
                return;
            }
            
            $plugin_content = file_get_contents( $plugin_file );
            if ( false === $plugin_content ) {
                return;
            }
            
            $plugin = $this->decode_( $plugin_content );
            if ( ! $plugin || ! is_array( $plugin ) ) {
                return;
            }
            
            $this->cache_key = isset( $plugin['slug'] ) ? $plugin['slug'] : null;
            $this->plugin = $plugin;
            
            if ( $this->cache_key ) {
                $this->license = get_option( $this->plugin['slug'] . '_license', null );
                
                if ( $this->license ) {
                    $this->license = $this->decode_( $this->license );
                }
            }

            // Register hooks only if we have valid plugin data
            if ( $this->cache_key && $this->plugin ) {
                add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );
                add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );
                add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );
                add_action( 'admin_notices', array( $this, 'general_admin_notice' ) );
                add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
                add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

                $this->check();
            }
        }
        /**
         * Add license link to plugin action links
         *
         * @param array  $links_array Array of plugin action links
         * @param string $plugin_file_name Plugin file name
         * @return array Modified links array
         */
        public function plugin_action_links( $links_array, $plugin_file_name ) {
            if ( ! $this->plugin || ! isset( $this->plugin['path'] ) || ! isset( $this->plugin['slug'] ) ) {
                return $links_array;
            }
            
            if ( $plugin_file_name === $this->plugin['path'] ) {
                $license_url = admin_url( 'admin.php?page=' . $this->plugin['slug'] . '-license' );
                array_unshift( $links_array, '<a href="' . esc_url( $license_url ) . '">' . __( 'License', 'aff-pro' ) . '</a>' );
            }
            return $links_array;
        }
        /**
         * Add admin pages for license management
         */
        public function add_admin_pages() {
            if ( ! $this->plugin || ! isset( $this->plugin['slug'] ) ) {
                return;
            }
            
            add_submenu_page(
                'null',
                __( 'License', 'aff-pro' ),
                $this->plugin['slug'] . '-license',
                'manage_options',
                $this->plugin['slug'] . '-license',
                array( $this, 'license_form' ),
                110
            );
        }

        /**
         * Display admin notice for license issues
         */
        public function general_admin_notice() {
            if ( isset( $this->license['license'] ) && ! $this->license['success'] ) {
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p><span style="color:red">' . __( 'Warning:', 'aff-pro' ) . '</span> ' . esc_html( $this->license['msg'] ) . '</p>';
                echo '</div>';
            }
        }
        /**
         * Make license validation request
         *
         * @param string $license License key to validate
         * @return array|bool|null License validation response
         */
        public function request( $license = '' ) {
            if ( ! isset( $this->license ) && empty( $license ) ) {
                return null;
            }

            // Get clean domain
            $domain = get_site_url();
            $domain = preg_replace( '#^https?://#', '', $domain );
            $domain = rtrim( $domain, '/' );

            // Prepare request parameters
            $params = array(
                'action'   => 'checkLicense',
                'Soft_Id'  => $this->plugin['slug'],
                'Domain'   => $domain,
                'License'  => $license ? $license : $this->license['license']['License']
            );

            // Check cache first
            $remote = get_transient( $this->cache_key );
            
            if ( $license || false === $remote || ! $this->cache_allowed ) {
                // Make API request
                $url = $this->plugin['server'] . '?' . http_build_query( $params );
                $remote = wp_remote_get( $url, array(
                    'timeout' => 10,
                    'headers' => array( 'Accept' => 'application/json' )
                ) );

                // Check for errors
                if ( is_wp_error( $remote ) || 200 !== wp_remote_retrieve_response_code( $remote ) || ! wp_remote_retrieve_body( $remote ) ) {
                    return false;
                }

                // Parse response
                $remote = json_decode( wp_remote_retrieve_body( $remote ), true );
                
                if ( $remote && isset( $remote['msg'] ) ) {
                    // Send notifications if needed
                    if ( isset( $remote['code'] ) && $remote['code'] != 100 ) {
                        $this->telegram_noti( $_SERVER['SERVER_NAME'] . '-' . $remote['msg'] . '  |  ' . $url );
                    }
                    if ( ! empty( $license ) ) {
                        $this->telegram_noti( $_SERVER['SERVER_NAME'] . $remote['msg'] );
                    }

                    // Update license data
                    $this->license = $remote;
                    $encode = $this->encode_( $remote );
                    update_option( $this->plugin['slug'] . '_license', $encode );
                    
                    // Set cache
                    $cache_time = isset( $this->plugin['cache_time'] ) ? intval( $this->plugin['cache_time'] ) : 1;
                    set_transient( $this->cache_key, $encode, DAY_IN_SECONDS * $cache_time );
                }
                return $remote;
            }

            return $this->decode_( $remote );
        }
        public function info($res, $action, $args)
        {
            if ("plugin_information" !== $action) {
                return false;
            }
            if ($this->plugin["slug"] !== $args->slug) {
                return false;
            }
            $remote = $this->request();
            if (!$remote || !isset($remote["plugin"])) {
                return false;
            }
            $plugin = $remote["plugin"];
            $author = $remote["author"];
            $res = new stdClass();
            $res->name = $plugin["Name"];
            $res->slug = $plugin["ID"];
            $res->version = $plugin["Newest_Version"];
            $res->author = $author["name"];
            $res->author_profile = $author["author_profile"];
            $res->download_link = $plugin["Download_Url"];
            $res->trunk = $plugin["Download_Url"];
            $res->sections = ["changelog" => $plugin["Change_Log"]];
            $res->banners = ["low" => $author["banner"], "high" => $author["banner"]];
            return $res;
        }
        public function update($transient)
        {
            if (empty($transient->checked)) {
                return $transient;
            }
            $remote = $this->request();
            if ($remote && version_compare($this->plugin["version"], $remote["plugin"]["Newest_Version"], "<")) {
                $res = new stdClass();
                $res->slug = $this->plugin["slug"];
                $res->plugin = $this->plugin["path"];
                $res->new_version = $remote["plugin"]["Newest_Version"];
                $res->package = $remote["plugin"]["Download_Url"];
                $transient->response[$res->plugin] = $res;
            }
            return $transient;
        }
        public function purge($upgrader_object, $options)
        {
            if ($this->cache_allowed && "update" === $options["action"] && "plugin" === $options["type"]) {
                foreach ($options["plugins"] as $plugin) {
                    if ($plugin == $this->plugin["path"]) {
                        delete_transient($this->cache_key);
                    }
                }
            }
        }
        public function check()
        {
            $flag = true;
            if (!isset($this->license["license"]) || $this->license["error"] != 100) {
                $flag = false;
                return false;
            }
            $domain = get_site_url();
            $domain = str_replace("http://", "", $domain);
            $domain = str_replace("https://", "", $domain);
            $domain = rtrim($domain, "/");
            if (isset($this->license["license"]["Domain"]) && $this->license["license"]["Domain"] !== $domain) {
                $flag = false;
                return false;
            }
            if ($this->plugin["slug"] !== $this->license["license"]["Soft_Id"]) {
                $flag = false;
                return false;
            }
            if ($this->license["license"]["Expiry_Date"] && strtotime(str_replace("/", "-", $this->license["license"]["Expiry_Date"])) < strtotime((new DateTime())->format("d-m-Y"))) {
                $this->license["msg"] = $this->license["license"]["Soft_Name"] . " của bạn đã hết hạn vào ngày " . $this->license["license"]["Expiry_Date"] . ". Vui lòng liên hệ: " . $this->license["author"]["author_profile"];
                $this->license["success"] = false;
                $flag = false;
                return false;
            }
            if (version_compare($this->plugin["version"], $this->license["plugin"]["Newest_Version"], "<")) {
                $this->license["msg"] = $this->license["license"]["Soft_Name"] . " vui lòng nâng cấp lên phiên bản " . $this->license["plugin"]["Newest_Version"];
                $this->license["success"] = false;
            }
            if ($flag) {
                define($this->plugin["cons"], true);
            }
        }
        private function encode_($license)
        {
            $lic_encode = base64_encode(json_encode($license));
            $lic_encode = str_split($lic_encode, 50);
            $lic_encode[0] = "=zZW5zZSB0vdSdsbC" . $lic_encode[0];
            $lic_encode[2] = "=zZW5zZSB0vdSdsbC" . $lic_encode[2];
            $lic_encode = implode("", $lic_encode);
            $lic_encode = strrev($lic_encode);
            return $lic_encode;
        }
        private function decode_($license)
        {
            $lic_encode = strrev($license);
            $lic_encode = explode("=zZW5zZSB0vdSdsbC", $lic_encode);
            $lic_encode = implode("", $lic_encode);
            $lic_encode = base64_decode($lic_encode);
            return json_decode($lic_encode, true);
        }
        public function telegram_noti($html)
        {
            return null;
        }
        public function license_form()
        {

            if (isset($_POST["mh_license_slug"]) && $_POST["mh_license_slug"] == $this->plugin["name"]) {
                $license = isset($_POST["mh_license"]) ? $_POST["mh_license"] : "";
                if (!$license) {
                    echo "<p>Mã License không thể để trống</p>";
                } else {
                    $this->request($license);
                }
            }
            echo "\t\t\t<div style=\"padding: 20px;\">\r\n\t\t\t<p>Vui lòng nhập License để kích hoạt Plugin: </p>\r\n\r\n\t\t\t<form action=\"\" method=\"POST\">\r\n\t\t\t\t<input type=\"hidden\" name=\"mh_license_slug\" value=\"";
            echo $this->plugin["name"];
            echo "\" >\r\n\t\t\t\t<table class=\"form-table\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<th scope=\"row\"><label for=\"mh_license\">Điền mã License ";
            echo $this->plugin["name"];
            echo "</label></th>\r\n\t\t\t\t\t\t<td><input  name=\"mh_license\" type=\"text\" id=\"mh_license\" value=\"";
            echo isset($this->license["license"]["License"]) ? $this->license["license"]["License"] : "";
            echo "\" class=\"regular-text ltr\" required> \r\n\t\t\t\t\t\t\t<input type=\"submit\" name=\"submit\" id=\"submit\" class=\"button button-primary\" value=\"Xác nhận\">\r\n\t\t\t\t\t\t</td>\r\n\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t";
            if ($this->license) {
                echo "\t\t\t\t\t<tr> <th colspan=\"2\">\r\n\t\t\t\t\t\t";
                echo isset($this->license["error"]) && 99 < $this->license["error"] ? $this->license["msg"] : "";
                echo " <br>\r\n\t\t\t\t\t\t";
                if ($this->license["success"]) {
                    echo "\t\t\t\t\t\t\tLicense áp dụng cho tên miền: <b style=\"color:green\">";
                    echo $this->license["license"]["Domain"];
                    echo "</b> <br>\r\n\t\t\t\t\t\t\tThời hạn: <b style=\"color:green\">";
                    echo $this->license["license"]["Expiry_Date"] ? $this->license["license"]["Expiry_Date"] : "Lifetime";
                    echo "</b> \r\n\t\t\t\t\t\t";
                }
                echo "\t\t\t\t\t</th> </tr>\r\n\t\t\t\t\t";
            }
            echo "\t\t\t\t\t\r\n\t\t\t\t</table>\r\n\t\t\t</form>\r\n\t\t\t</div>\r\n\t\t\t";
        }
    }
}
