<?php
/**
 * Plugin Name: SEO Links Interlinking
 * Plugin URI: https://wpseoplugins.org/seo-links-interlinking/
 * Description: SEO Links Interlinking is a powerful plugin that helps you add internal links in your wordpress posts. Automate internal link building with ease!
 * Author: WP SEO Plugins
 * Author URI: https://wpseoplugins.org/
 * Version: 1.7.0
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
if( !defined( 'WP_SEO_PLUGINS_BACKEND_URL' ) ) {
    define( 'WP_SEO_PLUGINS_BACKEND_URL', 'https://api.wpseoplugins.org/');
}
define( 'SEOLI_LICENSE', true );
define( 'SEOLI_SERVER_NAME', $_SERVER['SERVER_NAME']);
define( 'SEOLI_SERVER_PORT', $_SERVER['SERVER_PORT']);
define( 'SEOLI_SITE_URL', ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME );
define( 'SEOLI_SERVER_REQUEST_URI', $_SERVER['REQUEST_URI']);
define( 'SEOLI_VERSION', '1.7.0' );

#function for add metabox.
function seoli_register_meta_boxes() {
    add_meta_box("seoli-meta-box", "SEO Links Interlinking", "seoli_display_callback", ["post", "page"], "side", "default", null);

}
add_action( 'add_meta_boxes', 'seoli_register_meta_boxes' );

#function for add metabox callback.
function seoli_display_callback( $post ) {
	?>
    <script>

        const _internaLinksPaginationLimit = 100;

        function seoli_isGutenbergActive() {
            return document.body.classList.contains( 'block-editor-page' );
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
                        window.location.href = '<?php echo esc_url_raw( WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole?api_key='.$sc_api_key.'&domain='. SEOLI_SITE_URL . '&remote_server_uri='.base64_encode($server_uri) ); ?>';
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
                                    links_added += words[ i ] + '\n';
                                    //links_added += '<p>'  + words[ i ] + '</p>';
                                }
                            }
                            alert('Internal Links added.\n' + links_added );
                            /*let notice = `<div class="notice notice-success is-dismissible">
                                <strong>SEO Links Interlinking</strong>
                                `+ links_added +`
                            </div>`;*/
                            //jQuery('#post').parent().prepend( notice );
                            //document.getElementById( 'notice' ).innerHTML = notice;
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
                    const internal_links_keywords_filtered = response.internal_links_keywords_filtered;
                    const seo_links_keywords = response.seo_links_keywords;

                    <!-- SEO Keywords -->
                    let seo_keywords = [];
                    for( let slkf in seo_links_keywords_filtered ) {
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

                    seo_keywords = [];
                    for( let slk in seo_links_keywords ) {
                        seo_keywords[ seo_links_keywords[ slk ] ] = seo_links_keywords_impressions[ seo_links_keywords[ slk ] ];
                    }
                    for( let sk in seo_keywords ) {
                        seo_link_keywords_html += `
                        <tr class="seo_keywords">
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

                    <!-- Internal Links -->
                    let internal_links = [];

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

                    if( internal_links_keywords_filtered.length > 0 ) {
                        for( let slkf in internal_links_keywords_filtered ) {
                            internal_links[ internal_links_keywords_filtered[ slkf ] ] = seo_links_keywords_impressions[ internal_links_keywords_filtered[ slkf ] ];
                        }

                        for( let sk in internal_links ) {
                            seo_links_most_relevant_keyword_html += `
                                <tr class="internal_links_keywords_filtered">
                                    <td>
                                        `+ seoli_stripslashes( sk ) +`
                                    </td>
                                    <td style="text-align: center;">
                                        `+ internal_links[ sk ] + `
                                    </td>
                                </tr>
                                `;
                        }

                        seo_links_most_relevant_keyword_html += `
                            <tr class="internal_links_keywords_filtered paginate">
                                <td colspan="2"><a href="javascript:void(0);" onclick="paginateInternalLinks()">Show more keywords</a></td>
                            </tr>`;
                    }

                    const seo_links_most_relevant_keyword = response.most_relevant_keyword;
                    internal_links = [];
                    for( let i = 0; i < seo_links_most_relevant_keyword.length; i++ ){
                        internal_links[ seo_links_most_relevant_keyword[i] ] = seo_links_keywords_impressions[ seo_links_most_relevant_keyword[i] ];
                    }

                    let j = 0;
                    let _display = '';
                    for( let il in internal_links ) {
                        let dashicon_yes = '';
                        if( response.post_content.includes( il ) ) {
                            dashicon_yes = '<span class="dashicons dashicons-yes"></span>';
                        }
                        if( internal_links_keywords_filtered.length > 0 ) {
                            _display = 'display:none;';
                        }
                        if( j++ >= _internaLinksPaginationLimit ) {
                            _display = 'display:none;';
                        }

                        seo_links_most_relevant_keyword_html += `
                        <tr class="internal_links" style="` + _display  + `">
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
                    if( j > _internaLinksPaginationLimit ) {
                        if( internal_links_keywords_filtered.length == 0 ) {
                            _display = '';
                        }
                        seo_links_most_relevant_keyword_html += `
                            <tr class="internal_links paginate" style="` + _display  + `">
                                <td colspan="2"><a href="javascript:void(0);" onclick="paginateInternalLinks()">Show more keywords</a></td>
                            </tr>`;
                    }
                    seo_links_most_relevant_keyword_html += `
                            </tbody>
                        </table>`;
                    document.getElementById('seo_links_most_relevant_keyword').innerHTML = seo_links_most_relevant_keyword_html;
                });
            }
        }

        function paginateInternalLinks() {
            jQuery('.internal_links_keywords_filtered').hide();
            const internal_links = jQuery('.internal_links:not(:visible)');
            for( let i = 0; i < _internaLinksPaginationLimit; i++ ) {
                jQuery( internal_links[ i ] ).show();
            }
            if( internal_links.length <= _internaLinksPaginationLimit ) {
                jQuery( '.internal_links.paginate' ).hide();
            } else {
                jQuery( '.internal_links.paginate' ).show();
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
                    if( tr[i].className == 'internal_links_keywords_filtered' ) {
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
        <?php
            if( sanitize_text_field( $_GET['google_status'] ) == 'ok' ) :
            $seo_links_last_update = date('Y-m-d H:i:s');
            update_option( 'seo_links_last_update', $seo_links_last_update );
        ?>
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
                <?php $credits = wp_seo_plugins_get_credits(); ?>
                <p><b><i>You have <span style="color: #ba000d"><?php echo esc_html( $credits->seo_links ); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></b></p>
                <div id="no_more_credits_modal" class="modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">No more credits available</h5>
                            </div>
                            <div class="modal-body">
                                <?php $credits = wp_seo_plugins_get_credits(); ?>
                                <p><b><i>You have <span style="color: #ba000d"><?php echo esc_html( $credits->seo_links ); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i></b></p>
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
        $seo_links_keywords_impressions = $seo_links_keywords_impressions != '' ? $seo_links_keywords_impressions : array();
        $most_relevant_keyword = get_post_meta( $post->ID, 'most_relevant_keyword', true);
        $seo_links_all_keywords = get_post_meta( $post->ID, 'seo_links_all_keywords', true);
        $most_relevant_keyword = empty( $most_relevant_keyword ) ? $seo_links_all_keywords : $most_relevant_keyword;
        $seo_links_keywords_filtered = get_post_meta( $post->ID, 'seo_links_keywords_filtered', true);
        $internal_links_keywords_filtered = get_post_meta( $post->ID, 'internal_links_keywords_filtered', true);
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
                        foreach( $seo_links_keywords as $seo_link ) :
                            //$seo_keywords[ $seo_link ] = $seo_links_keywords_position[$seo_link];
                            $seo_keywords[ $seo_link ] = $seo_links_keywords_impressions[$seo_link];
                        endforeach;
                        arsort( $seo_keywords );
                        ?>
                        <?php foreach( $seo_keywords as $seo_link => $seo_position ) : ?>
                            <tr class="seo_keywords">
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
                    $paginationLimit = 100;
                    if( !empty( $internal_links_keywords_filtered ) ) :
                        foreach( $internal_links_keywords_filtered as $internal_link ) :
                            $internal_links[ $internal_link ] = $seo_links_keywords_impressions[ $internal_link ];
                        endforeach;
                        arsort( $internal_links );
                        ?>
                        <?php foreach( $internal_links as $seo_link => $seo_position ) : ?>
                            <tr class="internal_links_keywords_filtered">
                                <td>
                                    <?php echo esc_html( $seo_link ); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo esc_html( $seo_position ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="internal_links_keywords_filtered paginate">
                            <td colspan="2"><a href="javascript:void(0);" onclick="paginateInternalLinks()">Show more keywords</a></td>
                        </tr>
                    <?php endif; ?>

                    <?php
                        $internal_links = array();
                        foreach( $most_relevant_keyword as $seo_link ) :
                            //$internal_links[ $seo_link ] = $seo_links_keywords_position[$seo_link];
                            $internal_links[ $seo_link ] = $seo_links_keywords_impressions[$seo_link];
                        endforeach;
                        arsort( $internal_links );
                    ?>
                    <?php $j = 0; ?>
                    <?php foreach( $internal_links as $seo_link => $seo_position ) : ?>
                        <?php
                        $display = '' ;
                        if( !empty( $internal_links_keywords_filtered ) ) :
                            $display = 'display:none;';
                        endif;
                        if( $j++ >= $paginationLimit ) :
                            $display = 'display:none;';
                        endif;
                        ?>
                        <tr class="internal_links" style="<?php echo $display; ?>">
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
                    <?php if( count( $internal_links )  > $paginationLimit ) : ?>
                        <?php
                            if( count( $internal_links_keywords_filtered ) == 0 ) :
                                $display = '';
                            endif;
                        ?>
                    <tr class="internal_links paginate" style="<?php echo $display; ?>">
                        <td colspan="2"><a href="javascript:void(0);" onclick="paginateInternalLinks()">Show more keywords</a></td>
                    </tr>
                    <?php endif; ?>
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
        <?php  if( ( time() - strtotime($seo_links_last_update) ) < ( 7 * 24 * 60 * 60 ) ) : ?>
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
                <p style="text-align: right;">
                    <small>
                        <?php $credits = wp_seo_plugins_get_credits(); ?>
                        <i>You have <span style="color: #ba000d"><?php echo esc_html( $credits->seo_links ); ?> credits left</span> - Click <a href="https://wpseoplugins.org/" target="_blank">here</a> to purchase more credits.</i>
                    </small>
                </p>
            </form>
        <?php else : ?>
            <?php $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI; ?>
            <p style="margin: 24px 0 0 0;">Your keywords are too old, please refresh them by clicking button below.</p>
            <p style="margin: 12px 0 0 0;"><a class="button button-primary" href="<?php echo esc_url_raw( WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole?api_key='.$sc_api_key.'&domain='. SEOLI_SITE_URL .'&remote_server_uri='.base64_encode($server_uri) ); ?>">Google Connect</a></p>
        <?php endif; ?>
    <?php else : ?>
        <?php $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI; ?>
        <p style="margin: 24px 0 0 0;"><a class="button button-primary" href="<?php echo esc_url_raw( WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole?api_key='.$sc_api_key.'&domain='. SEOLI_SITE_URL .'&remote_server_uri='.base64_encode($server_uri) ); ?>">Google Connect</a></p>
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
    <p style="color: red;">You must login and get an API KEY <a href="<?php echo SEOLI_SITE_URL; ?>/wp-admin/admin.php?page=wp-seo-plugins-login">here</a>.</p>
    <?php endif; ?>
<?php
}

/**
 * Search Console Integrations
 */
// Register settings using the Settings API
add_action('admin_menu', 'seoli_admin', 100, 2);
function seoli_admin() {
    $pages = array();
    $pages[] = add_submenu_page('wp-seo-plugins-login', 'Links', 'Links', 'edit_posts', 'seo-links', 'seoli_settings' );
    //$pages[] = add_menu_page('Search Console', 'Search Console', 'edit_posts', 'search-console', 'seoli_search_console');
    //$pages[] = add_submenu_page('seo-links', 'Export', 'Export', 'edit_posts', 'search-console-export', 'seoli_search_console_export');

    foreach( $pages as $page ) {
        add_action( "admin_print_styles-{$page}", 'seoli_stylesheet'); // Definita nel loader.php per adesso
    }
    add_action( "admin_print_styles-post.php", 'seoli_stylesheet'); // Definita nel loader.php per adesso

    add_action( 'admin_init', 'seoli_csv_export' );
    //add_action( 'admin_init', 'seoli_get_last_update' );
    add_action( 'admin_init', 'seoli_loader_admin_init' );
}

function seoli_settings(){
    include SEOLI_PATH_ABS . 'view/seo_links_settings.php';
}

/************************************
function seoli_get_last_update() {
    $sc_api_key = get_option('sc_api_key');
    $remote_get = WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole/getLastUpdate?api_key=' . $sc_api_key . '&domain=' . SEOLI_SERVER_NAME;
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
**********************************/

function seoli_search_console() {
    $url_per_page = 1000;
    $p = isset( $_GET['p'] ) ? (int) $_GET['p'] : 1;
    $sc_api_key = get_option('sc_api_key');
    $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI;
    $remote_get = WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole/loadAllData?p=' . $p . '&url_per_page=' . $url_per_page .'&api_key=' . $sc_api_key . '&domain=' . SEOLI_SITE_URL . '&remote_server_uri=' . base64_encode( $server_uri );

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
    $remote_get = WP_SEO_PLUGINS_BACKEND_URL . 'searchconsole/loadAllData?api_key=' . $sc_api_key . '&domain=' . SEOLI_SITE_URL . '&remote_server_uri=' . base64_encode( $server_uri );

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
require_once SEOLI_PATH_ABS . 'wp_seo_plugins.php';