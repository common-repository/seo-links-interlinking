<h1>
    <?php esc_html('SEO Links Interlinking Settings Page' ); ?>
</h1>
<hr />

<?php if(isset($_REQUEST['settings-updated']) && sanitize_text_field( $_REQUEST['settings-updated'] ) == 'true') : ?>
<div class="notice notice-success is-dismissible">
    <p>Settings saved.</p>
</div>
<?php endif; ?>

<?php if(isset($_SESSION['status']) && (sanitize_text_field( $_SESSION['status'] ) == 0 || sanitize_text_field( $_SESSION['status'] == 1) ) ) : ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html( $_SESSION['message'] ); ?></p>
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
        <strong>SEO Links Interlinking</strong>
        <p><?php echo esc_html( stripslashes($_GET['google_error']) ); ?></p>
    </div>
    <script>
        // Cleaning url from data
        let url = window.location.href;
        url = url.replace(/&google_error(.)*/, '');
        window.history.pushState({}, document.title, url);
    </script>
<?php endif; ?>

<div style="width:100%;float:left;">
    <div style="width: 48%;float:left">
        <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
            <?php $nonce = wp_create_nonce( 'seoli_login_nonce' ); ?>
            <input type="hidden" name="security" value="<?php echo $nonce; ?>" />
            <input type="hidden" name="action" value="seoli_login" />
            <table class="form-table">
                <tbody>
                <tr>
                    <th colspan="2"><h3>Login and get an Api Key</h3></th>
                </tr>
                <?php if(isset($_SESSION['status']) &&  sanitize_text_field( $_SESSION['status'] ) == -1) : ?>
                    <tr>
                        <td colspan="2">
                            <div class="notice notice-error is-dismissible"><p><?php echo esc_html( $_SESSION['message'] ); ?></p></div>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if(isset($_SESSION['status']) && sanitize_text_field( $_SESSION['status'] ) == 1) : ?>
                    <tr>
                        <td colspan="2">
                            <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $_SESSION['message'] ); ?></p></div>
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
        <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">
            <?php $nonce = wp_create_nonce( 'seoli_registration_nonce' ); ?>
            <input type="hidden" name="security" value="<?php echo $nonce; ?>" />
            <input type="hidden" name="action" value="seoli_registration">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th colspan="2"><h3>Register as new user and get an Api Key</h3></th>
                    </tr>
                    <?php if(isset($_SESSION['status']) && sanitize_text_field( $_SESSION['status'] ) == -10) : ?>
                    <tr>
                        <td colspan="2">
                            <div class="notice notice-error is-dismissible"><p><?php echo esc_html( $_SESSION['message'] ); ?></p></div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['status']) && sanitize_text_field( $_SESSION['status'] ) == 0) : ?>
                        <tr>
                            <td colspan="2">
                                <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $_SESSION['message'] ); ?></p></div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row">
                            <label for="name">Name</label>
                        </th>
                        <td><input name="name" type="text" id="name" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="surname">Surname</label>
                        </th>
                        <td><input name="surname" type="text" id="surname" class="regular-text"></td>
                    </tr>
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
<hr />
<form method="POST" action="options.php">
    <?php wp_nonce_field('update-options') ?>
    <table class="form-table">
        <tbody>
        <tr>
            <td colspan="2"><h3>Api Key</h3></td>
        </tr>
        <tr>
            <th scope="row">
                <label for="input_id">API KEY</label>
            </th>
            <td>
                <input name="sc_api_key" type="text" id="sc_api_key" class="regular-text" value="<?php echo esc_html( ( isset( $_SESSION['status'] ) && sanitize_text_field( $_SESSION['status'] ) == 0 ) ? ( $_SESSION['api_key'] ?? get_option( 'sc_api_key' ) ) : get_option( 'sc_api_key' ) ); ?>">
                <p style="text-align: right;"><small><i>You have <span style="color: #ba000d"><?php echo esc_html(seoli_get_credits()); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></small></p>
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
<hr />
<p>In order to use this plugin to automate internal link building and receive keyword suggestions for your posts, you will need to connect to Google Search Console, by clicking the button below.</p>
<p><input onclick="seoli_Connect()" type="button" class="button button-primary" name="button" value="Google Connect" /></p>
<p>If you don't have a Google Search Console account, you can verify and connect your site following the steps <a href="https://www.semrush.com/blog/connect-google-search-console-analytics/" target="_blank">in this guide</a>.</p>


<?php
unset( $_SESSION['status'] );
unset( $_SESSION['message'] );
unset( $_SESSION['api_key'] );

$sc_api_key = get_option('sc_api_key');
?>

<script>
    function seoli_Connect() {
        <?php
        $server_uri = ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME . SEOLI_SERVER_REQUEST_URI;
        ?>
        window.location.href = '<?php echo esc_url_raw( SEOLI_BACKEND_URL . 'searchconsole?api_key=' . $sc_api_key . '&domain=' . ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME . '&remote_server_uri=' . base64_encode($server_uri) ); ?>';
    }
</script>
