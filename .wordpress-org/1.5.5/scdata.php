<?php
/**
 * Plugin Name: SEO Links Interlinking
 * Plugin URI:
 * Description: SEO Links Interlinking is a powerful plugin that helps you add internal links in your wordpress posts. Automate internal link building with ease!
 * Author: WP SEO Plugins
 * Author URI: https://wpseoplugins.org/
 * Version: 1.5.5
 */
header('Content-Type: text/html; charset=utf-8');

# Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

#Absolute path to the plugin directory.
if (!defined('SEOLI_PATH_ABS')) {
	define('SEOLI_PATH_ABS', plugin_dir_path(__FILE__));
}
if (!defined('SEOLI_PATH_SRC')) {
	define('SEOLI_PATH_SRC', plugin_dir_url(__FILE__));
}

#Define plugin constant.
define( 'SEOLI_PLUGIN_FOLDER', dirname(__FILE__) );
define( 'SEOLI_CORE_FOLDER', SEOLI_PLUGIN_FOLDER.'/sc-data');
define( 'SEOLI_BACKEND_URL', 'https://api.wpseoplugins.org/');
define( 'SEOLI_LICENSE', true );
define( 'SEOLI_SERVER_NAME', $_SERVER['SERVER_NAME']);
define( 'SEOLI_SERVER_PORT', $_SERVER['SERVER_PORT']);
define( 'SEOLI_SITE_URL', ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME );
define( 'SEOLI_SERVER_REQUEST_URI', $_SERVER['REQUEST_URI']);
define( 'SEOLI_VERSION', '1.5.5' );


#function for add metabox.
function seoli_register_meta_boxes() {
    add_meta_box("seoli-meta-box", "SEO Links Interlinking", "seoli_display_callback", "post", "side", "default", null);

}
add_action( 'add_meta_boxes', 'seoli_register_meta_boxes' );

#function for add metabox callback.
function seoli_display_callback( $post ) {
	?>

    <script>
        function seoli_isGutenbergActive() {
            return typeof wp !== 'undefined' && typeof wp.blocks !== 'undefined';
        }

        function seoli_stripslashes(str) {
            return str.replace(/\\'/g,'\'').replace(/\"/g,'"').replace(/\\\\/g,'\\').replace(/\\0/g,'\0');
        }

        function seoli_UpdateContentLink() {
            jQuery(".editor-post-save-draft").addClass('draft-button');
            jQuery('.lds-roller').css('display', 'flex');
            jQuery('body').css('overflow', 'hidden');
            var _post_id = jQuery('input[name="post_id"]').val();
            var _keyword = jQuery('input[name="keyword"]').val();
            if( _post_id == ""){
                sessionStorage.setItem('seoli_Session_Key', 'seoli_Session_True');
                jQuery(".editor-post-save-draft").trigger( "click" );
                jQuery("input#save-post").click().prop('disabled', false);
            } else {
                // Remove saved data from sessionStorage
                sessionStorage.removeItem('seoli_Session_Key');
            }

            var _post_id = jQuery('input[name="post_id"]').val();
            seoli_savePost( _post_id );
        }

        function seoli_savePost( _post_id ) {
            let _post_content = '';
            if( seoli_isGutenbergActive() ) {
                const b = wp.data.select("core/editor");
                const blocks = b.getBlocks();
                for( let i = 0; i < blocks.length; i++ ) {
                    _post_content += blocks[0].attributes.content;
                }
            } else {
                const _post_content_iframe = jQuery('#content_ifr').contents();
                _post_content = _post_content_iframe.find('body').html();
            }

            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                dataType: 'json',
                type: 'POST',
                data: {
                    action: 'seoli_savePost',
                    post_id: _post_id,
                    post_content: _post_content,
                    nonce: '<?php echo wp_create_nonce( 'seoli_savePost_nonce' ); ?>',
                }
            }).fail(function(){
                console.log('Ajax request fails');
            }).done(function( data ){
                console.log( data );
                seoli_UpdateContentAjax();
            });
        }

        function seoli_UpdateContentAjax(){
            var _post_id = jQuery('input[name="post_id"]').val();
            var _keyword = jQuery('input[name="keyword"]').val();


            var pathname = window.location.href;
            var splitUrl = pathname.split('?');
            if(splitUrl[1] != null){
                var pIDUrl = splitUrl[1].split('&');
                var _post_id_url = pIDUrl[0].split('=');
                var _post_id = _post_id_url[1];

                var data = {
                    action: 'seoli_folder_contents',
                    keyword : _keyword,
                    post_id : _post_id,
                    nonce: '<?php echo wp_create_nonce( 'seoli_security_nonce' ); ?>',
                };

                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    context: this,
                    data: data
                }).fail(function(){
                    console.log('Ajax request fails')
                }).done(function(response){
                    console.log('done');
                    console.log( response );

                    if( response.status == -1 || response.status == -3 || response.status == -4 ){
                        window.location.href = window.location.href + '&response_status=' + response.status + '&google_error=' + response.message;
                        return;
                    }

                    if( response.status == -2 ) {
                        <?php
                        $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI;
                        $sc_api_key = get_option('sc_api_key');
                        ?>
                        window.location.href = '<?php echo esc_url_raw( SEOLI_BACKEND_URL . 'searchconsole?api_key='.$sc_api_key.'&domain='. SEOLI_SITE_URL . '&remote_server_uri='.base64_encode($server_uri) ); ?>';
                        return;
                    }

                    // Remove saved data from sessionStorage
                    sessionStorage.removeItem('seoli_Session_Key');
                    jQuery('.lds-roller').hide();
                    jQuery('body').css('overflow', 'scroll');

                    if( response.replaced.length == 0 ) {
                        if( seoli_isGutenbergActive() ) {
                            wp.data.dispatch('core/notices').createWarningNotice('No link inserted. Try adding some of the suggested words in the right bar and try again to insert automatic links.', {
                                isDismissible: true
                            });
                        } else {
                            alert('No link inserted. Try adding some of the suggested words in the right bar and try again to insert automatic links.');
                        }
                    } else {
                        if( seoli_isGutenbergActive() ) {
                            wp.data.dispatch('core/notices').createSuccessNotice('Internal Links added.', {
                                isDismissible: true
                            });
                        } else {
                            const words = response.words;
                            let links_added = '';
                            for( let i = 0; i < words.length; i++ ) {
                                if( words[ i ] != '' ) {
                                    console.log( words[ i ] );
                                    links_added += words[ i ] + '\n';
                                }
                            }
                            alert('Internal Links added.\n' + links_added );
                        }
                    }
                    if( seoli_isGutenbergActive() ) {
                        wp.data.dispatch( 'core/editor' ).resetBlocks( wp.blocks.parse( response.post_content ) );
                    } else {
                        document.getElementById('content_ifr').contentWindow.document.body.innerHTML = response.post_content;
                    }

                    /**
                     * Update metabox
                     */
                    const seo_links_keywords_position = response.seo_links_keywords_position;
                    const seo_links_keywords_impressions = response.seo_links_keywords_impressions;
                    const seo_links_keywords_filtered = response.seo_links_keywords_filtered;
                    const seo_links_keywords = response.seo_links_keywords;
                    let seo_keywords = [];
                    for( let slkf in seo_links_keywords_filtered ) {
                        //seo_keywords[ seo_links_keywords_filtered[ slkf ] ] = seo_links_keywords_position[ seo_links_keywords_filtered[ slkf ] ];
                        seo_keywords[ seo_links_keywords_filtered[ slkf ] ] = seo_links_keywords_impressions[ seo_links_keywords_filtered[ slkf ] ];
                    }

                    let seo_link_keywords_html = `
                    <input type="text" id="seo_links_keyword_input" onkeyup="keywordResearch('seo_links_keyword')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
                    <table style="margin: 8px 0;">
                        <thead>
                        <tr>
                            <th scope="row" style="width:70%;cursor: pointer;">Keyword</th>
                            <th scope="row" style="cursor: pointer;">Impressions</th>
                        </tr>
                        </thead>
                        <tbody>`;

                    for( let sk in seo_keywords ) {
                        seo_link_keywords_html += `
                        <tr class="seo_links_keywords_filtered">
                            <td>
                                `+ seoli_stripslashes( sk ) +`
                            </td>
                            <td style="text-align: center;">
                                `+ seo_keywords[ sk ] + `
                            </td>
                        </tr>
                        `;
                    }

                    seo_link_keywords_html += `
                    <tr class="seo_links_keywords_filtered">
                        <td colspan="2"><a href="javascript:void(0);" onclick="jQuery('.seo_keywords').show();jQuery('.seo_links_keywords_filtered').hide();">Show more keywords</a></td>
                    </tr>`;

                    seo_keywords = [];
                    for( let slk in seo_links_keywords ) {
                        //seo_keywords[ seo_links_keywords[ slk ] ] = seo_links_keywords_position[ seo_links_keywords[ slk ] ];
                        seo_keywords[ seo_links_keywords[ slk ] ] = seo_links_keywords_impressions[ seo_links_keywords[ slk ] ];
                    }
                    for( let sk in seo_keywords ) {
                        seo_link_keywords_html += `
                        <tr class="seo_keywords" style="display:none;">
                            <td>
                                `+ seoli_stripslashes( sk ) +`
                            </td>
                            <td style="text-align: center;">
                                `+ seo_keywords[ sk ] +`
                            </td>
                        </tr>`;
                    }

                    seo_link_keywords_html += `</tbody>
                    </table>`;
                    document.getElementById('seo_links_keyword').innerHTML = seo_link_keywords_html;

                    const seo_links_most_relevant_keyword = response.most_relevant_keyword;
                    let internal_links = [];
                    for( let i = 0; i < seo_links_most_relevant_keyword.length; i++ ){
                        //internal_links[ seo_links_most_relevant_keyword[i] ] = seo_links_keywords_position[ seo_links_most_relevant_keyword[i] ];
                        internal_links[ seo_links_most_relevant_keyword[i] ] = seo_links_keywords_impressions[ seo_links_most_relevant_keyword[i] ];
                    }

                    let seo_links_most_relevant_keyword_html = `
                     <input type="text" id="seo_links_most_relevant_keyword_input" onkeyup="keywordResearch('seo_links_most_relevant_keyword')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
                        <table style="margin: 8px 0;">
                            <thead>
                            <tr>
                                <th scope="row" style="width:70%;cursor: pointer;">Keyword</th>
                                <th scope="row" style="cursor: pointer;">Impressions</th>
                            </tr>
                            </thead>
                            <tbody>`;
                    for( let il in internal_links ) {
                        let dashicon_yes = '';
                        if( response.post_content.includes( il ) ) {
                            dashicon_yes = '<span class="dashicons dashicons-yes"></span>';
                        }
                        seo_links_most_relevant_keyword_html += `
                        <tr>
                            <td>
                                `+ seoli_stripslashes( il ) +`
                            </td>
                            <td style="text-align: center;">
                                `+ internal_links[ il ] +`
                            </td>
                            <td>
                                ` + dashicon_yes + `
                            </td>
                        </tr>
                        `;
                    }
                    seo_links_most_relevant_keyword_html += `
                            </tbody>
                        </table>`;
                    document.getElementById('seo_links_most_relevant_keyword').innerHTML = seo_links_most_relevant_keyword_html;
                });
            }
        }

        function sortTable( id, n ) {
            let table, rows, switching, i, x, y, x_clean, y_clean, shouldSwitch, dir, switchcount = 0;
            table = jQuery('#' + id).find('table')[0];
            switching = true;
            // Set the sorting direction to ascending:
            dir = "asc";
            /* Make a loop that will continue until
            no switching has been done: */
            while (switching) {
                // Start by saying: no switching is done:
                switching = false;
                rows = table.rows;
                /* Loop through all table rows (except the
                first, which contains table headers): */
                for (i = 1; i < ( rows.length - 1 ); i++) {
                    // Start by saying there should be no switching:
                    shouldSwitch = false;
                    /* Get the two elements you want to compare,
                    one from current row and one from the next: */
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    /* Check if the two rows should switch place,
                    based on the direction, asc or desc: */

                    x_clean = stripHtml( x.innerHTML );
                    y_clean = stripHtml( y.innerHTML );

                    if( isNumeric( x_clean ) && isNumeric( y_clean ) ) {
                        if ( dir == "asc" ) {
                            if ( Number( x_clean ) > Number( y_clean ) ) {
                                shouldSwitch = true;
                                break;
                            }
                        } else if (dir == "desc") {
                            if ( Number( x_clean ) < Number( y_clean ) ) {
                                shouldSwitch = true;
                                break;
                            }
                        }
                    } else {
                        if ( dir == "asc" ) {
                            if ( x_clean.toLowerCase() > y_clean.toLowerCase() ) {
                                // If so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        } else if (dir == "desc") {
                            if ( x_clean.toLowerCase() < y_clean.toLowerCase() ) {
                                // If so, mark as a switch and break the loop:
                                shouldSwitch = true;
                                break;
                            }
                        }
                    }
                }
                if ( shouldSwitch ) {
                    /* If a switch has been marked, make the switch
                    and mark that a switch has been done: */
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    // Each time a switch is done, increase this count by 1:
                    switchcount ++;
                } else {
                    /* If no switching has been done AND the direction is "asc",
                    set the direction to "desc" and run the while loop again. */
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }

        function stripHtml(html) {
            let tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        }

        function isNumeric( str ) {
            if ( typeof str != "string" ) return false // we only process strings!
            return !isNaN( str ) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
                !isNaN( parseFloat( str ) ) // ...and ensure strings of whitespace fail
        }

        function keywordResearch( id ) {
            // Declare variables
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById(id + "_input");
            filter = input.value.toUpperCase();
            table = table = jQuery('#' + id).find('table')[0];
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>

    <?php $sc_api_key = get_option('sc_api_key'); ?>
    <?php if(!empty($sc_api_key)) : ?>
    <!-- Pure CSS Loader -->
    <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
    <?php if( isset( $_GET['google_status'] ) ) :  ?>
        <?php if( sanitize_text_field( $_GET['google_status'] ) == 'ok' ) : ?>
            <script>
                if( seoli_isGutenbergActive() ) {
                    wp.data.dispatch('core/notices').createSuccessNotice('Google account is successfully connected, keywords updated.', {
                        isDismissible: true
                    });
                }
                // Cleaning url from data
                let url = window.location.href;
                url = url.replace(/&google_status(.)*/, '');
                window.history.pushState({}, document.title, url);
            </script>
            <div class="notice notice-success is-dismissible">
                <strong>SEO Links Interlinking</strong>
                <p>Google account is successfully connected, keywords updated.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if(isset($_GET['google_error'])) :  ?>
        <script>
            if( seoli_isGutenbergActive() ) {
                wp.data.dispatch('core/notices').createErrorNotice(
                    '<?php echo $_GET['google_error']; ?>',
                    {
                        isDismissible: true,
                        __unstableHTML: true
                    }
                );
            } else {
                jQuery(document).ready(function(){
                    setTimeout(function(){
                        jQuery('#error_modal').modal('show');
                    }, 800);
                });
            }
        </script>
        <div class="notice notice-error is-dismissible">
            <h2>SEO Links Interlinking</h2>
            <p style="font-size: 18px;"><?php echo stripslashes( $_GET['google_error'] ); ?></p>
            <div id="error_modal" class="modal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">SEO Links Interlinking</h5>
                        </div>
                        <div class="modal-body">
                            <p style="font-size: 18px;"><?php echo stripslashes( $_GET['google_error'] ); ?></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="jQuery('#error_modal').modal('hide');">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(isset( $_GET['response_status'] ) && $_GET['response_status'] == -4 ) : ?>
                <p><b><i>You have <span style="color: #ba000d"><?php echo esc_html(seoli_get_credits()); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></b></p>
                <div id="no_more_credits_modal" class="modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">No more credits available</h5>
                            </div>
                            <div class="modal-body">
                                <p><b><i>You have <span style="color: #ba000d"><?php echo esc_html(seoli_get_credits()); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></b></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="jQuery('#no_more_credits_modal').modal('show');">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    jQuery(document).ready(function(){
                        setTimeout(function(){
                            jQuery('#no_more_credits_modal').modal('show');
                        }, 800);
                    });
                </script>
            <?php endif; ?>
        </div>
        <script>
            // Cleaning url from data
            let url = window.location.href;
            url = url.replace(/&google_error(.)*/, '');
            window.history.pushState({}, document.title, url);
        </script>
    <?php endif; ?>
    <script>
        jQuery('#seo_links_keyword-tabs').find('a').on('click', function(evt){
            evt.preventDefault();
            const ref = jQuery(this).attr();
            jQuery('#seo_links_keyword-div').find('.tabs-panel').hide();
            jQuery(ref).show();
        });
        function seoLinksTabs( ref ) {
            jQuery('#seo_links_keyword-div').find('.tabs-panel').hide();
            jQuery(ref).show();
            jQuery('#seo_links_keyword-tabs').find('li').removeClass('tabs');
            jQuery('#seo_links_keyword-tabs').find('li[data-id='+ref.replace('#','')+']').addClass('tabs');
        }
    </script>

    <div id="seo_links_keyword-div" class="categorydiv">
        <p>Add internal links to your content automagically. If no links are found, try using some of the keywords below in your post.</p>
        <ul id="seo_links_keyword-tabs" class="category-tabs">
            <li data-id="seo_links_keyword-pop" class="tabs"><a href="javscript:void(0);" onclick="seoLinksTabs('#seo_links_keyword-pop')">Internal links</a></li>
            <?php if( $post->post_status == 'publish') : ?>
            <li data-id="seo_links_keyword-all" class="hide-if-no-js"><a href="javscript:void(0);" onclick="seoLinksTabs('#seo_links_keyword-all')">Seo keywords</a></li>
            <?php endif; ?>
        </ul>

        <?php
        $seo_links_keywords = get_post_meta( $post->ID, 'seo_links_keywords', true);
        $seo_links_keywords_position = get_post_meta( $post->ID, 'seo_links_keywords_position', true);
        $seo_links_keywords_impressions = get_post_meta( $post->ID, 'seo_links_keywords_impressions', true);
        $most_relevant_keyword = get_post_meta( $post->ID, 'most_relevant_keyword', true);
        $seo_links_all_keywords = get_post_meta( $post->ID, 'seo_links_all_keywords', true);
        $most_relevant_keyword = empty( $most_relevant_keyword ) ? $seo_links_all_keywords : $most_relevant_keyword;
        $seo_links_keywords_filtered = get_post_meta( $post->ID, 'seo_links_keywords_filtered', true);
        $seo_links_last_update = get_option( 'seo_links_last_update' );
        ?>

        <!-- SEO Keywords -->
        <div id="seo_links_keyword-all" class="tabs-panel" style="display: none;">
            <div id="seo_links_keyword" style="max-height: 250px;">
                <?php if( $seo_links_keywords ) : ?>
                    <input type="text" id="seo_links_keyword_input" onkeyup="keywordResearch('seo_links_keyword')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
                    <table style="margin: 8px 0;">
                        <thead>
                        <tr>
                            <th scope="row" style="width:70%;cursor: pointer;">Keyword</th>
                            <th scope="row" style="cursor: pointer;">Impressions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $seo_keywords = array();
                        foreach( $seo_links_keywords_filtered as $seo_link ) :
                            //$seo_keywords[ $seo_link ] = $seo_links_keywords_position[$seo_link];
                            $seo_keywords[ $seo_link ] = $seo_links_keywords_impressions[$seo_link];
                        endforeach;
                        arsort( $seo_keywords );
                        ?>
                        <?php foreach( $seo_keywords as $seo_link => $seo_position ) : ?>
                            <tr class="seo_links_keywords_filtered">
                                <td>
                                    <?php echo esc_html( $seo_link ); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo esc_html( $seo_position ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="seo_links_keywords_filtered">
                            <td colspan="2"><a href="javascript:void(0);" onclick="jQuery('.seo_keywords').show();jQuery('.seo_links_keywords_filtered').hide();">Show more keywords</a></td>
                        </tr>
                        <?php
                        $seo_keywords = array();
                        foreach( $seo_links_keywords as $seo_link ) :
                            //$seo_keywords[ $seo_link ] = $seo_links_keywords_position[$seo_link];
                            $seo_keywords[ $seo_link ] = $seo_links_keywords_impressions[$seo_link];
                        endforeach;
                        arsort( $seo_keywords );
                        ?>
                        <?php foreach( $seo_keywords as $seo_link => $seo_position ) : ?>
                            <tr class="seo_keywords" style="display:none;">
                                <td>
                                    <?php echo esc_html( $seo_link ); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo esc_html( $seo_position ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="margin: 8px 0;">Add those relevant keywords to your content to optimize it for search engines.</p>
                    <p style="margin: 8px 0;">Click "add links" to receive keyword suggestions.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Internal Links -->
        <div id="seo_links_keyword-pop" class="tabs-panel">
            <div id="seo_links_most_relevant_keyword" style="max-height: 250px;">
            <?php if( $most_relevant_keyword ) : ?>
                <input type="text" id="seo_links_most_relevant_keyword_input" onkeyup="keywordResearch('seo_links_most_relevant_keyword')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
                <table style="margin: 8px 0;">
                    <thead>
                    <tr>
                        <th scope="row" style="width:70%;cursor: pointer;">Keyword</th>
                        <th scope="row" style="cursor: pointer;">Impressions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $internal_links = array();
                        foreach( $most_relevant_keyword as $seo_link ) :
                            //$internal_links[ $seo_link ] = $seo_links_keywords_position[$seo_link];
                            $internal_links[ $seo_link ] = $seo_links_keywords_impressions[$seo_link];
                        endforeach;
                        arsort( $internal_links );
                    ?>
                    <?php foreach( $internal_links as $seo_link => $seo_position ) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html( $seo_link ); ?>
                            </td>
                            <td style="text-align: center;">
                                <?php echo esc_html( $seo_position ); ?>
                            </td>
                            <td>
                                <?php if( strpos( $post->post_content, $seo_link ) !== false ) : ?>
                                <span class="dashicons dashicons-yes"></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="margin: 8px 0;">
                    Add internal links to your posts by clicking "Add Links" below.
                    If no links are added, try using some of the suggested keywords we will provide you.
                </p>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if( !empty( $seo_links_last_update )) : ?>
        <p style="margin: 24px 0 0 0;"><i>Keywords updated to <?php echo date('d F Y', strtotime($seo_links_last_update)); ?></i></p>
    <?php endif; ?>

    <?php if(!empty($seo_links_last_update) && ( time() - strtotime($seo_links_last_update) ) < ( 7 * 24 * 60 * 60 ) ) : ?>
        <!-- metafield Form -->
        <form method="post" action="">
            <input type="hidden" name="post_id" value="<?php if(!empty($_GET['post'])){ echo esc_attr( $_GET['post'] ); } ?>" />
            <?php if( !$seo_links_keywords ) : ?>
                <p style="margin: 24px 0 0 0;">
                    By clicking add links you will be redirected to connect your Google search console account.
                    If you did not verify your site, please follow <a href="https://youtu.be/N4PmE3LysUM">this guide</a> to set up search console.
                </p>
            <?php endif; ?>
            <p style="text-align: right;margin-top: 8px;">
                <input onclick="seoli_UpdateContentLink()" type="button" class="button button-primary" name="button" value="Add Links" />
            </p>
            <p style="text-align: right;"><small><i>You have <span style="color: #ba000d"><?php echo esc_html(seoli_get_credits()); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></small></p>
        </form>
    <?php else : ?>
        <?php $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI; ?>
        <p style="margin: 24px 0 0 0;">Your keywords are too old, please refresh them by clicking button below.</p>
        <p style="margin: 12px 0 0 0;"><a class="button button-primary" href="<?php echo esc_url_raw( SEOLI_BACKEND_URL . 'searchconsole?api_key='.$sc_api_key.'&domain='. SEOLI_SITE_URL .'&remote_server_uri='.base64_encode($server_uri) ); ?>">Google Connect</a></p>
    <?php endif; ?>

	<script>
		jQuery( document ).ready(function() {
		    <?php if(isset($_GET['scdata_linking_url']) && sanitize_text_field( $_GET['scdata_linking_url'] ) == 1) : ?>
                seoli_UpdateContentLink();
            <?php endif; ?>

			let data = sessionStorage.getItem('seoli_Session_Key');
			if(data != null) {
				seoli_UpdateContentLink();
			}
		});

        <?php if( isset($_GET['click'])) : ?>
        // Cleaning url from data
        let url = window.location.href;
        url = url.replace(/&click(.)*/, '');
        window.history.pushState({}, document.title, url);
        seoli_UpdateContentLink();
        <?php endif; ?>

	</script>
    <?php else : ?>
    <p style="color: red;">You must specify an API KEY <a href="<?php echo SEOLI_SITE_URL; ?>/wp-admin/admin.php?page=seo-links-interlinking">here</a>.</p>
    <?php endif; ?>
<?php
}

/**
 * Search Console Integrations
 */
// Register settings using the Settings API
add_action('admin_menu', 'seoli_admin');
function seoli_admin() {
    $pages = array();
    $pages[] = add_menu_page( 'SEO Links Interlinking', 'SEO Links Interlinking', 'edit_posts', 'seo-links-interlinking', 'seoli_show_menu', 'dashicons-admin-links' );
    //$pages[] = add_submenu_page('seo-links-interlinking', 'Search Console', 'Search Console', 'edit_posts', 'search-console', 'seoli_search_console');
    //$pages[] = add_submenu_page('seo-links-interlinking', 'Export', 'Export', 'edit_posts', 'search-console-export', 'seoli_search_console_export');

    foreach( $pages as $page ) {
        add_action( "admin_print_styles-{$page}", 'seoli_stylesheet'); // Definita nel loader.php per adesso
    }
    add_action( "admin_print_styles-post.php", 'seoli_stylesheet'); // Definita nel loader.php per adesso

    add_action( 'admin_init', 'seoli_csv_export' );
    add_action( 'admin_init', 'seoli_get_last_update' );
    add_action( 'admin_init', 'seoli_loader_admin_init' );
}

function seoli_get_last_update() {
    $sc_api_key = get_option('sc_api_key');
    $remote_get = SEOLI_BACKEND_URL . 'searchconsole/getLastUpdate?api_key=' . $sc_api_key . '&domain=' . SEOLI_SERVER_NAME;
    $args = array(
        'timeout'     => 10,
        'sslverify' => false
    );
    $data = wp_remote_get( $remote_get, $args );
    if( is_array( $data ) && !is_wp_error( $data ) ) {
        $rowData = json_decode( $data['body'] );

        if( $rowData->status == 0 ) {
            update_option( 'seo_links_last_update', $rowData->data->last_update);
        }
    } else {
        file_put_contents( $_SERVER['DOCUMENT_ROOT'] . '/seoli_debug.log', "Remote Get: " . $remote_get . "\n" . print_r( $data, true ), FILE_APPEND | LOCK_EX );
    }
}

function seoli_show_menu () {
    include SEOLI_PATH_ABS . 'view/settings.php';
}

function seoli_search_console() {
    $url_per_page = 5;
    $p = isset( $_GET['p'] ) ? (int) $_GET['p'] : 1;
    $sc_api_key = get_option('sc_api_key');
    $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI;
    $remote_get = SEOLI_BACKEND_URL . 'searchconsole/loadAllData?p=' . $p . '&url_per_page=' . $url_per_page .'&api_key=' . $sc_api_key . '&domain=' . SEOLI_SITE_URL . '&remote_server_uri=' . base64_encode( $server_uri );

    $args = array(
        'timeout'     => 10,
        'sslverify' => false
    );
    $data = wp_remote_get( $remote_get, $args );

    $rowData = json_decode( $data['body'] );

    if( $rowData->status == -1 || $rowData->status == -2 || $rowData->status == -3 || $rowData->status == -4 ){
        wp_die(json_encode($rowData));
    }

    include SEOLI_PATH_ABS . 'view/search_console.php';
}

function seoli_search_console_export() {
    include SEOLI_PATH_ABS . 'view/search_console_export.php';
}

function seoli_csv_export() {
    if( isset($_GET['seoli_download_report'] ) ) {
        $csv = seoli_generate_csv( $_GET['seoli_data'] );
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"report.csv\";" );
        header("Content-Transfer-Encoding: binary");

        echo $csv;
        wp_die();
    }
}

function seoli_generate_csv( $columns ) {
    $csv_output = implode(',', $columns ) . "\n";

    $sc_api_key = get_option('sc_api_key');
    $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI;
    $remote_get = SEOLI_BACKEND_URL . 'searchconsole/loadAllData?api_key=' . $sc_api_key . '&domain=' . SEOLI_SITE_URL . '&remote_server_uri=' . base64_encode( $server_uri );

    $args = array(
        'timeout'     => 10,
        'sslverify' => false
    );
    $data = wp_remote_get( $remote_get, $args );

    $rowData = json_decode( $data['body'] );

    if( $rowData->status == -1 || $rowData->status == -2 || $rowData->status == -3 || $rowData->status == -4 ){
        wp_die(json_encode($rowData));
    }

    foreach( $rowData as $row ) {
        $csv_row = array();
        $post_id = url_to_postid( $row->page );
        $row->title = get_the_title( $post_id );
        $row->post_date = get_the_date('', $post_id );
        $row->post_modified = get_the_modified_date('', $post_id );

        // Mantengo il controllo su post_id > 0 --> altrimenti se ho un sottodominio/sottocartella prenderei lo stesso il dato sc ma non quello wp (title, etc.)
        if( $post_id > 0 ) {
            foreach( $columns as $column ) {
                $csv_row[] = '"' . str_replace('"', '""', html_entity_decode( $row->$column ) ) . '"';
            }
            $csv_output .= implode(',', $csv_row ) . "\n";
        }
    }

    return $csv_output;
}

/**
 * SEO Links Interlinking- Registration Form to WP SEO Plugins
 */
add_action( 'admin_post_seoli_registration', 'seoli_registration');
function seoli_registration(){
    $nonce = sanitize_text_field($_POST['security']);
    if(!wp_verify_nonce($nonce,'seoli_registration_nonce') || !current_user_can( 'administrator' )){
        wp_redirect( $_SERVER["HTTP_REFERER"].'?error=unauthenticated' );
        exit;
    }

    $server_uri = SEOLI_SITE_URL;

    $post_data = array();
    $post_data['name'] = sanitize_text_field( $_POST['name'] ) ?? '';
    $post_data['surname'] = sanitize_text_field( $_POST['surname'] ) ?? '';
    $post_data['email'] = sanitize_email( $_POST['email'] ) ?? '';
    $post_data['password'] = sanitize_text_field( $_POST['password'] ) ?? '';

    $args = array(
        'body'        => $post_data,
        'timeout'     => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array(
                'Siteurl' => $server_uri
        ),
        'cookies'     => array(),
    );
    $response = wp_remote_post( SEOLI_BACKEND_URL . 'registration', $args );
    $data = json_decode(wp_remote_retrieve_body( $response ));

    $_SESSION['seoli_status'] = $data->status;
    $_SESSION['seoli_message'] = $data->message;
    $_SESSION['seoli_api_key'] = $data->api_key ?? '';

    if( $_SESSION['seoli_api_key'] != '' ) {
        update_option('sc_api_key', sanitize_text_field( $_SESSION['seoli_api_key'] ));
        $user = $data->user ?? new stdClass();
        update_option('seoli_user_display_name', $user->data->display_name );
        update_option('seoli_user_email', $user->data->user_email );
    }

    wp_redirect( admin_url( 'admin.php?page=seo-links-interlinking' ) );
    exit;
}

/**
 * Search Console Integration - Login Form to WP SEO Plugins
 */
add_action( 'admin_post_seoli_login', 'seoli_login');
function seoli_login(){
    $nonce = sanitize_text_field($_POST['security']);
    if(!wp_verify_nonce($nonce,'seoli_login_nonce') || !current_user_can( 'administrator' )){
        wp_redirect( $_SERVER["HTTP_REFERER"].'?error=unauthenticated' );
        exit;
    }

    $post_data = array();
    $post_data['email'] = sanitize_text_field( $_POST['email'] ) ?? '';
    $post_data['password'] = sanitize_text_field( $_POST['password'] ) ?? '';

    $args = array(
        'body'        => $post_data,
        'timeout'     => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking'    => true,
        'cookies'     => array(),
    );
    $response = wp_remote_post( SEOLI_BACKEND_URL . 'login', $args );
    $data = json_decode(wp_remote_retrieve_body( $response ));

    $_SESSION['seoli_status'] = $data->status;
    $_SESSION['seoli_message'] = $data->message;

    if($data->status == 0) {
        // Generating a new api key

        $server_uri = SEOLI_SITE_URL;

        $args = array(
            'body'        => array('user_id' => $data->user_id ?? 0),
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
                'Siteurl' => $server_uri
            ),
            'cookies'     => array(),
        );
        $response = wp_remote_post( SEOLI_BACKEND_URL . 'apikey/generate', $args );
        $data = json_decode(wp_remote_retrieve_body( $response ));

        $_SESSION['seoli_status'] = $data->status;
        $_SESSION['seoli_message'] = $data->message;
        $_SESSION['seoli_api_key'] = $data->api_key ?? '';

        if( $_SESSION['seoli_api_key'] != '' ) {
            update_option('sc_api_key', sanitize_text_field( $_SESSION['seoli_api_key']) );
            $user = $data->user ?? new stdClass();
            update_option('seoli_user_display_name', $user->data->display_name );
            update_option('seoli_user_email', $user->data->user_email );
        }
    }

    wp_redirect( admin_url( 'admin.php?page=seo-links-interlinking' ) );
    exit;
}

/**
 * Get residual credits
 */
function seoli_get_credits() {
    $sc_api_key = get_option('sc_api_key');
    if( !empty( $sc_api_key ) ) {
        $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI;
        $remote_get = SEOLI_BACKEND_URL . 'apikey/credits?api_key=' . $sc_api_key . '&domain=' . SEOLI_SITE_URL . '&remote_server_uri=' . base64_encode( $server_uri );

        $args = array(
            'timeout'     => 10,
            'sslverify' => false
        );
        $data = wp_remote_get( $remote_get, $args );
        if( is_array( $data ) && !is_wp_error( $data ) ) {
            $rowData = json_decode( $data['body'] );

            $api_limit = $rowData->response->api_limit_seo_links ?? 0;
            $api_call = $rowData->response->api_call_seo_links ?? 0;

            return $api_limit - $api_call;
        } else {
            file_put_contents( $_SERVER['DOCUMENT_ROOT'] . '/seoli_debug.log', "Remote Get: " . $remote_get . "\n" . print_r( $data, true ), FILE_APPEND | LOCK_EX );
            return 0;
        }
    }
}

/**
 * Logout
 */
add_action('admin_post_seoli_logout_form_submit','seoli_logout_form_submit');
function seoli_logout_form_submit(){
    delete_option('sc_api_key');
    delete_option('seoli_user_display_name');
    delete_option('seoli_user_email');
    wp_redirect(admin_url('admin.php?page=seo-links-interlinking'));+
    exit;
}

/**
 * Starting a session
 */
function seoli_start_session(){
    if (!session_id())
        session_start();
}
add_action('admin_init', 'seoli_start_session', 1);

#loads necessary files.
require_once SEOLI_PATH_ABS . 'loader.php';
require_once SEOLI_PATH_ABS . 'ajax.php';
require_once SEOLI_PATH_ABS . 'utils.php';