<style>
    .form-table th {
        padding: 20px 10px;
    }
</style>
<div style="padding-right: 20px;">
    <?php if(isset($_REQUEST['settings-updated']) && sanitize_text_field( $_REQUEST['settings-updated'] ) == 'true') : ?>
        <div class="notice notice-success is-dismissible">
            <p>Settings saved.</p>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['wp_seo_plugins_status']) && (sanitize_text_field( $_SESSION['wp_seo_plugins_status'] ) == 0 || sanitize_text_field( $_SESSION['wp_seo_plugins_status'] == 1) ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo $_SESSION['wp_seo_plugins_message']; ?></p>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['wp_seo_plugins_status']) && (sanitize_text_field( $_SESSION['wp_seo_plugins_status'] ) < 0 ) ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo $_SESSION['wp_seo_plugins_message']; ?></p>
        </div>
    <?php endif; ?>
    <?php if( isset( $_GET['google_status'] ) ) :  ?>
        <?php if( sanitize_text_field( $_GET['google_status'] ) == 'ok' ) : ?>
            <div class="notice notice-success is-dismissible">
                <strong>SEO Links Interlinking</strong>
                <p>Google account is successfully connected.</p>
            </div>
            <script>
                // Cleaning url from data
                let url = window.location.href;
                url = url.replace(/&google_status(.)*/, '');
                window.history.pushState({}, document.title, url);
            </script>
        <?php endif; ?>
    <?php endif; ?>
    <?php if(isset($_GET['google_error'])) :  ?>
        <div class="notice notice-error is-dismissible">
            <h2>SEO Links Interlinking</h2>
            <p style="font-size: 18px;"><?php echo stripslashes($_GET['google_error']); ?></p>
        </div>
        <script>
            // Cleaning url from data
            let url = window.location.href;
            url = url.replace(/&google_error(.)*/, '');
            window.history.pushState({}, document.title, url);
        </script>
    <?php endif; ?>
    <?php $wp_seo_plugins_user_display_name = get_option( 'wp_seo_plugins_user_display_name' ); ?>

    <?php if( !empty( $wp_seo_plugins_user_display_name ) ) : ?>
        <div style="width: 100%;float: left;margin-bottom: 24px;">
            <div style="float: left;">
                <h4>Hi, <?php echo esc_html( $wp_seo_plugins_user_display_name ); ?></h4>
            </div>
            <div style="float: right;padding: 15px 10px;">
                <form method="POST" action="<?php echo admin_url( 'admin-post.php'); ?>">
                    <input name="action" type="hidden" value="wp_seo_plugins_logout_form_submit">
                    <button type="submit" class="button button-primary">Logout</button>
                </form>
            </div>
        </div>
        <form method="POST" action="options.php">
            <?php wp_nonce_field('update-options') ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="input_id">API KEY</label>
                    </th>
                    <td>
                        <input name="sc_api_key" type="text" id="sc_api_key" class="regular-text" value="<?php echo esc_html( ( isset( $_SESSION['wp_seo_plugins_status'] ) && sanitize_text_field( $_SESSION['wp_seo_plugins_status'] ) == 0 ) ? ( $_SESSION['wp_seo_plugins_api_key'] ?? get_option( 'sc_api_key' ) ) : get_option( 'sc_api_key' ) ); ?>">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right;">
                        <button type="submit" class="button button-primary">Save</button>
                        <input type="hidden" name="action" value="update" />
                        <input type="hidden" name="page_options" value="sc_api_key" />
                    </td>
                </tr>
                </tbody>
            </table>
        </form>

        <h4>Seo Plugins credits for each plugin</h4>
    <?php $credits = wp_seo_plugins_get_credits(); ?>
        <small><i>Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></small>

        <table class="widefat striped form-table">
            <tbody>
            <?php foreach( $credits as $key => $val ): ?>
                <tr>
                    <th scope="row"><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></th>
                    <td>
                        <p>
                            <?php echo esc_html( $val ); ?>
                        </p>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div style="width:100%;float:left;">
            <div style="width: 48%;float:left">
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                    <?php $nonce = wp_create_nonce( 'wp_seo_plugins_login_nonce' ); ?>
                    <input type="hidden" name="security" value="<?php echo esc_html( $nonce ); ?>" />
                    <input type="hidden" name="action" value="wp_seo_plugins_login" />
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th colspan="2"><h3>Login and get an Api Key</h3></th>
                        </tr>
                        <?php if(isset($_SESSION['wp_seo_plugins_status']) &&  sanitize_text_field( $_SESSION['wp_seo_plugins_status'] ) == -1) : ?>
                            <tr>
                                <td colspan="2">
                                    <div class="notice notice-error is-dismissible"><p><?php echo $_SESSION['wp_seo_plugins_message']; ?></p></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if(isset($_SESSION['wp_seo_plugins_status']) && sanitize_text_field( $_SESSION['wp_seo_plugins_status'] ) == 1) : ?>
                            <tr>
                                <td colspan="2">
                                    <div class="notice notice-success is-dismissible"><p><?php echo  $_SESSION['wp_seo_plugins_message']; ?></p></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th scope="row">
                                <label for="email">Email</label>
                            </th>
                            <td><input name="email" type="text" id="email" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="password">Password</label>
                            </th>
                            <td><input name="password" type="password" id="password" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"></th>
                            <td><a class="button button-secondary" href="https://www.wpseoplugins.org/wp-login.php?action=lostpassword" target="_blank">Forgot password?</a></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="text-align: left;">
                                <button type="submit" class="button button-primary">Login</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <div style="width: 48%;float:right">
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                    <?php $nonce = wp_create_nonce( 'wp_seo_plugins_registration_nonce' ); ?>
                    <input type="hidden" name="security" value="<?php echo esc_html( $nonce ); ?>" />
                    <input type="hidden" name="action" value="wp_seo_plugins_registration">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th colspan="2"><h3>Register as new user and get an Api Key</h3></th>
                        </tr>
                        <?php if(isset($_SESSION['wp_seo_plugins_status']) && sanitize_text_field( $_SESSION['wp_seo_plugins_status'] ) == -10) : ?>
                            <tr>
                                <td colspan="2">
                                    <div class="notice notice-error is-dismissible"><p><?php echo $_SESSION['wp_seo_plugins_message']; ?></p></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if(isset($_SESSION['wp_seo_plugins_status']) && sanitize_text_field( $_SESSION['wp_seo_plugins_status'] ) == 0) : ?>
                            <tr>
                                <td colspan="2">
                                    <div class="notice notice-success is-dismissible"><p><?php echo $_SESSION['wp_seo_plugins_message']; ?></p></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th scope="row">
                                <label for="name">Name</label>
                            </th>
                            <td><input name="name" type="text" id="name" class="regular-text"></td>
                        </tr>
                        <!--
                        <tr>
                            <th scope="row">
                                <label for="surname">Surname</label>
                            </th>
                            <td><input name="surname" type="text" id="surname" class="regular-text"></td>
                        </tr>
                        -->
                        <tr>
                            <th scope="row">
                                <label for="email">Email</label>
                            </th>
                            <td><input name="email" type="text" id="email" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="password">Password</label>
                            </th>
                            <td><input name="password" type="password" id="password" class="regular-text"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="text-align: left;">
                                <button type="submit" class="button button-primary">Register</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        <div style="clear: both"></div>
    <?php endif; ?>

    <?php
    unset( $_SESSION['wp_seo_plugins_status'] );
    unset( $_SESSION['wp_seo_plugins_message'] );
    unset( $_SESSION['wp_seo_plugins_api_key'] );

    $sc_api_key = get_option('sc_api_key');
    ?>
</div>
