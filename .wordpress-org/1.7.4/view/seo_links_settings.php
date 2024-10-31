<?php if( isset( $_GET['google_status'] ) ) :  ?>
    <?php
        if( sanitize_text_field( $_GET['google_status'] ) == 'ok' ) :
            $seo_links_last_update = date('Y-m-d H:i:s');
            update_option( 'seo_links_last_update', $seo_links_last_update );
    ?>
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
<div style="padding-right: 20px">
    <h3>Links</h3>
    <form method="POST" action="options.php">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="seo_links_multilang" />
        <?php wp_nonce_field('update-options') ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="select_id">Is your site multilingual? <small>beta</small></label></th>
                    <td>
                        <select name="seo_links_multilang" id="seo_links_multilang">
                            <?php $option_multi_lang = get_option("seo_links_multilang"); ?>
                            <option value="no" <?php echo $option_multi_lang == "no" ? 'selected' : '' ?>>No</option>
                            <option value="yes" <?php echo $option_multi_lang == "yes" ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Connect to Google Search Console</th>
                    <td>
                        <p class="description">
                            In order to use this plugin to automate internal link building and receive keyword suggestions for your posts, you will need to connect to Google Search Console, by clicking the button below.
                            <br />
                            <br />
                            <input onclick="wp_seo_plugins_connect()" type="button" class="button button-primary" name="button" value="Google Connect" />
                            <br />
                            <br />
                            If you don't have a Google Search Console account, you can verify and connect your site following the steps <a href="https://www.semrush.com/blog/connect-google-search-console-analytics/" target="_blank">in this guide</a>.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: left;">
                        <button type="submit" class="button button-primary">Save</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<script>
    function wp_seo_plugins_connect() {
        <?php
        $sc_api_key = get_option('sc_api_key');
        $server_uri = ( $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://' ) . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        ?>
        window.location.href = '<?php echo esc_url_raw( WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole?api_key=' . $sc_api_key . '&domain=' . ( $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://' ) . $_SERVER['SERVER_NAME'] . '&remote_server_uri=' . base64_encode($server_uri) ); ?>';
    }
</script>
