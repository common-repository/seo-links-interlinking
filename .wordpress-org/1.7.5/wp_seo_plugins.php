<?php
add_action('admin_menu', 'wp_seo_plugins_admin');
if( !function_exists( 'wp_seo_plugins_admin' ) ) {
    function wp_seo_plugins_admin() {
        if ( empty ( $GLOBALS['admin_page_hooks']['wp-seo-plugins-login'] ) ) {
            add_menu_page( 'SEO Plugins', 'SEO Plugins', 'edit_posts', 'wp-seo-plugins-login', 'wp_seo_plugins_admin_settings', 'dashicons-analytics' );
        }
    }
}

if( !function_exists( 'wp_seo_plugins_admin_settings' ) ) {
    function wp_seo_plugins_admin_settings () {
        include plugin_dir_path(__FILE__) . 'view/settings.php';
    }
}

/**
 * SEO Links Interlinking- Registration Form to WP SEO Plugins
 */
add_action( 'admin_post_wp_seo_plugins_registration', 'wp_seo_plugins_registration');
if( !function_exists( 'wp_seo_plugins_registration' ) ) {
    function wp_seo_plugins_registration(){
        $nonce = sanitize_text_field($_POST['security']);
        if(!wp_verify_nonce($nonce,'wp_seo_plugins_registration_nonce') || !current_user_can( 'administrator' )){
            wp_redirect( $_SERVER["HTTP_REFERER"].'?error=unauthenticated' );
            exit;
        }

        $server_uri = ( $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://' ) . $_SERVER['SERVER_NAME'];

        $post_data = array();
        $post_data['name'] = sanitize_text_field( $_POST['name'] ) ?? '';
        $post_data['surname'] = sanitize_text_field( $_POST['surname'] ) ?? '';
        $post_data['email'] = sanitize_email( $_POST['email'] ) ?? '';
        $post_data['password'] = sanitize_text_field( $_POST['password'] ) ?? '';

        $args = array(
            'body'        => $post_data,
            'timeout'     => '30',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
                'Siteurl' => $server_uri
            ),
            'cookies'     => array(),
        );
        $response = wp_remote_post( WP_SEO_PLUGINS_BACKEND_URL . 'registration', $args );
        $data = json_decode(wp_remote_retrieve_body( $response ));

        $_SESSION['wp_seo_plugins_status'] = $data->status;
        $_SESSION['wp_seo_plugins_message'] = $data->message;
        $_SESSION['wp_seo_plugins_api_key'] = $data->api_key ?? '';

        if( $_SESSION['wp_seo_plugins_api_key'] != '' ) {
            update_option('sc_api_key', sanitize_text_field( $_SESSION['wp_seo_plugins_api_key'] ));
            $user = $data->user ?? new stdClass();
            update_option('wp_seo_plugins_user_display_name', $user->data->display_name );
            update_option('wp_seo_plugins_user_email', $user->data->user_email );
        }

        wp_redirect( admin_url( 'admin.php?page=wp-seo-plugins-login' ) );
        exit;
    }
}
/**
 * Search Console Integration - Login Form to WP SEO Plugins
 */
add_action( 'admin_post_wp_seo_plugins_login', 'wp_seo_plugins_login');
if( !function_exists( 'wp_seo_plugins_login' ) ) {
    function wp_seo_plugins_login(){
        $nonce = sanitize_text_field($_POST['security']);
        if(!wp_verify_nonce($nonce,'wp_seo_plugins_login_nonce') || !current_user_can( 'administrator' )){
            wp_redirect( $_SERVER["HTTP_REFERER"].'?error=unauthenticated' );
            exit;
        }

        $post_data = array();
        $post_data['email'] = sanitize_text_field( $_POST['email'] ) ?? '';
        $post_data['password'] = sanitize_text_field( $_POST['password'] ) ?? '';

        $args = array(
            'body'        => $post_data,
            'timeout'     => '30',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'cookies'     => array(),
        );
        $response = wp_remote_post( WP_SEO_PLUGINS_BACKEND_URL . 'login', $args );
        $data = json_decode(wp_remote_retrieve_body( $response ));

        $_SESSION['wp_seo_plugins_status'] = $data->status;
        $_SESSION['wp_seo_plugins_message'] = $data->message;

        if($data->status == 0) {
            // Generating a new api key

            $server_uri = ( $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://' ) . $_SERVER['SERVER_NAME'];

            $args = array(
                'body'        => array('user_id' => $data->user_id ?? 0),
                'timeout'     => '30',
                'redirection' => '5',
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array(
                    'Siteurl' => $server_uri
                ),
                'cookies'     => array(),
            );
            $response = wp_remote_post( WP_SEO_PLUGINS_BACKEND_URL . 'apikey/generate', $args );
            $data = json_decode(wp_remote_retrieve_body( $response ));

            $_SESSION['wp_seo_plugins_status'] = $data->status;
            $_SESSION['wp_seo_plugins_message'] = $data->message;
            $_SESSION['wp_seo_plugins_api_key'] = $data->api_key ?? '';

            if( $_SESSION['wp_seo_plugins_api_key'] != '' ) {
                update_option('sc_api_key', sanitize_text_field( $_SESSION['wp_seo_plugins_api_key']) );
                $user = $data->user ?? new stdClass();
                update_option('wp_seo_plugins_user_display_name', $user->data->display_name );
                update_option('wp_seo_plugins_user_email', $user->data->user_email );
            }
        }

        wp_redirect( admin_url( 'admin.php?page=wp-seo-plugins-login' ) );
        exit;
    }
}

/**
 * Get residual credits
 */
if( !function_exists( 'wp_seo_plugins_get_credits' ) ) {
    function wp_seo_plugins_get_credits() {
        $sc_api_key = get_option('sc_api_key');
        if( !empty( $sc_api_key ) ) {
            $server_uri = ( $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://' ) . $_SERVER['SERVER_NAME']  . $_SERVER['REQUEST_URI'];
            $remote_get = WP_SEO_PLUGINS_BACKEND_URL . 'apikey/credits?api_key=' . $sc_api_key . '&domain=' . ( $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://' ) . $_SERVER['SERVER_NAME'] . '&remote_server_uri=' . base64_encode( $server_uri );

            $args = array(
                'timeout'     => 30,
                'sslverify' => false
            );
            $data = wp_remote_get( $remote_get, $args );
            if( is_array( $data ) && !is_wp_error( $data ) ) {
                $rowData = json_decode( $data['body'] );

                $response = $rowData->response;
                $credits = new stdClass();
                foreach( $response as $key => $val ) {
                    if( strpos( $key, 'api_limit') !== false ) {
                        $p = str_replace( 'api_limit_', '', $key );
                        $api_limit = $response->{ $key } ?? 0;
                        $api_call = $response->{ 'api_call_' . $p } ?? 0;
                        $credits->{ $p } = $api_limit - $api_call;
                    }
                }

                return $credits;
            } else {
                file_put_contents( $_SERVER['DOCUMENT_ROOT'] . '/wp_seo_plugins_debug.log', "Remote Get: " . $remote_get . "\n" . print_r( $data, true ), FILE_APPEND | LOCK_EX );
                return 0;
            }
        }
    }
}

/**
 * Logout
 */
add_action('admin_post_wp_seo_plugins_logout_form_submit','wp_seo_plugins_logout_form_submit');
if( !function_exists( 'wp_seo_plugins_logout_form_submit' ) ) {
    function wp_seo_plugins_logout_form_submit(){
        delete_option('sc_api_key');
        delete_option('wp_seo_plugins_user_display_name');
        delete_option('wp_seo_plugins_user_email');
        wp_redirect(admin_url('admin.php?page=wp-seo-plugins-login'));
        exit;
    }
}