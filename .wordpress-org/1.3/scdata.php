<?php
/**
 * Plugin Name: SEO Links Interlinking
 * Plugin URI:
 * Description: SEO Links Interlinking is a powerful plugin that helps you add internal links in your wordpress posts. Automate internal link building with ease!
 * Author: WP SEO Plugins
 * Author URI: https://wpseoplugins.org/
 * Version: 1.2.8
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
define( 'SEOLI_SERVER_REQUEST_URI', $_SERVER['REQUEST_URI']);

#function for add metabox.
function seoli_register_meta_boxes() {
    add_meta_box("seoli-meta-box", "SEO Links Interlinking", "seoli_display_callback", "post", "side", "default", null);

}
add_action( 'add_meta_boxes', 'seoli_register_meta_boxes' );

#function for add metabox callback.
function seoli_display_callback( $post ) {
	?>
    <?php $sc_api_key = get_option('sc_api_key'); ?>
    <?php if(!empty($sc_api_key)) : ?>
    <!-- Pure CSS Loader -->
    <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
    <?php if(isset($_GET['google_error'])) :  ?>
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
            <script>
                jQuery(document).ready(function(){
                    setTimeout(function(){
                        jQuery('#error_modal').modal('show');
                    }, 800);
                });
            </script>
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
        $most_relevant_keyword = get_post_meta( $post->ID, 'most_relevant_keyword', true);
        $seo_links_all_keywords = get_post_meta( $post->ID, 'seo_links_all_keywords', true);
        $most_relevant_keyword = empty( $most_relevant_keyword ) ? $seo_links_all_keywords : $most_relevant_keyword;
        $seo_links_keywords_filtered = get_post_meta( $post->ID, 'seo_links_keywords_filtered', true);
        ?>

        <div id="seo_links_keyword-all" class="tabs-panel" style="display: none;">
            <div id="seo_links_keyword" style="max-height: 250px;">
                <?php if( $seo_links_keywords ) : ?>
                    <input type="text" id="seo_links_keyword_input" onkeyup="keywordResearch('seo_links_keyword')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
                    <table style="margin: 8px 0;">
                        <thead>
                        <tr>
                            <th scope="row" style="width:70%;cursor: pointer;" onclick="sortTable('seo_links_keyword', 0)">Keyword</th>
                            <th scope="row" onclick="sortTable('seo_links_keyword', 1)" style="cursor: pointer;">Avg. pos.</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $seo_keywords = array();
                        foreach( $seo_links_keywords_filtered as $seo_link ) :
                            $seo_keywords[ $seo_link ] = $seo_links_keywords_position[$seo_link];
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
                            $seo_keywords[ $seo_link ] = $seo_links_keywords_position[$seo_link];
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
        <div id="seo_links_keyword-pop" class="tabs-panel">
            <div id="seo_links_most_relevant_keyword" style="max-height: 250px;">
            <?php if( $most_relevant_keyword ) : ?>
                <input type="text" id="seo_links_most_relevant_keyword_input" onkeyup="keywordResearch('seo_links_most_relevant_keyword')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
                <table style="margin: 8px 0;">
                    <thead>
                    <tr>
                        <th scope="row" style="width:70%;cursor: pointer;" onclick="sortTable('seo_links_most_relevant_keyword', 0)">Keyword</th>
                        <th scope="row" onclick="sortTable('seo_links_most_relevant_keyword', 1)" style="cursor: pointer;">Avg. pos.</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $internal_links = array();
                        foreach( $most_relevant_keyword as $seo_link ) :
                            $internal_links[ $seo_link ] = $seo_links_keywords_position[$seo_link];
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

    <!-- metafield Form -->
    <form method="post" action="">
        <input type="hidden" name="post_id" value="<?php if(!empty($_GET['post'])){ echo esc_html( $_GET['post'] ); } ?>" />
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
				// Remove all saved data from sessionStorage
				sessionStorage.clear();
            }

            seoli_UpdateContentAjax();
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
					post_id : _post_id
				};

				jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: data
                }).fail(function(){
                    console.log('Ajax request fails')
                }).done(function(response){

                    if( response.status == -1 || response.status == -3 || response.status == -4 ){
                        window.location.href = window.location.href + '&response_status=' + response.status + '&google_error=' + response.message;
                        return;
                    }

                    if( response.status == -2 ) {
                        <?php
                        $server_uri = ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME . SEOLI_SERVER_REQUEST_URI;
                        ?>
                        window.location.href = '<?php echo esc_url_raw( SEOLI_BACKEND_URL . 'searchconsole?api_key='.$sc_api_key.'&domain='.(SEOLI_SERVER_PORT==80?'http://':'https://').SEOLI_SERVER_NAME.'&remote_server_uri='.base64_encode($server_uri) ); ?>';
                        return;
                    }

                    // Remove saved data from sessionStorage
                    sessionStorage.removeItem('seoli_Session_Key');
                    // Remove all saved data from sessionStorage
                    sessionStorage.clear();
                    jQuery('.lds-roller').hide();
                    jQuery('body').css('overflow', 'scroll');

                    if( response.replaced.length == 0 ) {
                        alert('No link inserted. Try adding some of the suggested words in the right bar and try again to insert automatic links.');
                    } else {
                        alert('Internal Links added. Please refresh the page or update your post in order for the changes to take effect.');
                    }
                    const page = window.location.href;
                    window.location.href = page;

                });
   			}
		}

        function sortTable(id, n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
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
                for (i = 1; i < (rows.length - 1); i++) {
                    // Start by saying there should be no switching:
                    shouldSwitch = false;
                    /* Get the two elements you want to compare,
                    one from current row and one from the next: */
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    /* Check if the two rows should switch place,
                    based on the direction, asc or desc: */
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            // If so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            // If so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
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
    <?php else : ?>
    <p style="color: red;">You must specify an API KEY <a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=seo-links-interlinking">here</a>.</p>
    <?php endif; ?>
<?php
}

#function string replace only first match with strpos
function seoli_replace_first_str($search_str, $replacement_str, $src_str){
    return (false !== ($pos = strpos($src_str, $search_str))) ? substr_replace($src_str, $replacement_str, $pos, strlen($search_str)) : false;
}

#sort by name
function seoli_sort_by_name($a,$b) {
    return strlen( $a->query ) < strlen( $b->query );
}

#function for change keyword.
function seoli_folder_contents() {
	global $wpdb;

	$post_id = sanitize_text_field( $_POST['post_id'] );
    $sc_api_key = get_option('sc_api_key');

	$content_post = get_post( $post_id );
	$post_status = $content_post->post_status;
	$content = $content_post->post_content;
	$content = apply_filters('the_content', $content);
    $content = str_replace("’", "'", $content);
    $content = str_replace('“', '"', $content);
    $content = str_replace('”', '"', $content);
    $content = html_entity_decode( $content );
    $title = strtolower( $content_post->post_title );
    $permalink = get_the_permalink( $post_id );
    $permalink_explode = explode('/', $permalink);
    $permalink_filter = $permalink_explode[3] ?? '';

    $server_uri = ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME . SEOLI_SERVER_REQUEST_URI;
    $remote_get = SEOLI_BACKEND_URL . 'searchconsole/loadData?api_key=' . $sc_api_key . '&domain=' . ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME . '&remote_server_uri=' . base64_encode( $server_uri );

    $args = array(
        'timeout'     => 10,
        'sslverify' => false
    );
    $data = wp_remote_get( $remote_get, $args );

    $rowData = json_decode( $data['body'] );


    if( $rowData->status == -1 || $rowData->status == -2 || $rowData->status == -3 || $rowData->status == -4 ){
        die(json_encode($rowData));
    }

    uasort($rowData,"seoli_sort_by_name");

    $keyword_processed = array();
    $replaced = array();
    $keyword_impressions_threshold = array();
    $keyword_impressions_threshold_title = array();
    $keyword_position = array();
    $most_relevant_keyword = array(); // Tutte le keyword escluse quelle della url corrente
    $seo_link_keywords = array(); // Tutte le keyword della url corrente
    $seo_link_keywords_filtered = array(); // Tutte le keyword che matchano il titolo con il filtro sul numero di caratteri delle parole

	foreach($rowData as $row){
	    $ga_url = $row->page;
		$ga_key = $row->query;
        $ga_key = str_replace("’", "'", $ga_key);
        $ga_key = str_replace('“', '"', $ga_key);
        $ga_key = str_replace('”', '"', $ga_key);

        $url_explode = explode('/', $ga_url );
        $url_filter = $url_explode[3] ?? '';

        // Nelle keyword link tutte le rilevanti ( escluso la URL ). Se il post è in bozza droppo la condizione url_filter = permalink_filter
        if( ( $post_status == 'draft' || $url_filter == $permalink_filter ) && $permalink != $ga_url ) {
            // Se sono in /tecnologia/articolo prendo solo le keyword che hanno url /tecnlogia

            if(!in_array($ga_key, $keyword_processed)) {
                $most_relevant_keyword[] = $ga_key;

                $url_with_content = '<a href="' . filter_var($ga_url, FILTER_SANITIZE_URL) . '">' . $ga_key . '</a>';
                $content_status = seoli_replace_first_str(" " . $ga_key . " ", " " . $url_with_content . " ", $content);
                if( $content_status ) {
                    $content = $content_status;
                    $replaced[] = $ga_key;
                } else {
                     if (false !== strpos($content, $url_with_content)) {
                        // Already replaced
                        $replaced[] = $ga_key;
                    }
                }

                /**
                 * Fix per keyword che hanno la virgola
                 */
                $content_status = seoli_replace_first_str(" " . $ga_key . ", ", " " . $url_with_content . ", ", $content);
                if( $content_status ) {
                    $content = $content_status;
                    $replaced[] = $ga_key;
                }

                /**
                 * Fix per keyword che hanno il punto
                 */
                $content_status = seoli_replace_first_str(" " . $ga_key . ". ", " " . $url_with_content . ". ", $content);
                if( $content_status ) {
                    $content = $content_status;
                    $replaced[] = $ga_key;
                }
            }
        }

        // Nelle keyword seo Solo la URL del post
        if( $permalink == $ga_url ) {
            $seo_link_keywords[] = $ga_key;
        }

        /*
        if( false !== strpos( $title, $ga_key ) ) {
            $keyword_impressions_threshold_title[] = $ga_key;
        } else if( $row->impressions > 5 )  {
            $keyword_impressions_threshold[] = $ga_key;
        }
        */

        /**
         * Esplodo la keyword in parole lunghe almeno 3 caratteri
         * Esplodo il titolo in parole lunghe almeno 3 caratteri
         * Se coincidono le mostro, altrimenti mostro tutte le keyword
         */
        $ga_key_array = explode(' ', $ga_key);
        $ga_key_array_filtered = array_filter($ga_key_array,function($v){ return strlen($v) > 3; });
        $title_array = explode(' ', $title);
        $title_array_filtered = array_filter($title_array,function($v){ return strlen($v) > 3; });
        // Torna gli elementi di ga_key_filtered che non sono presenti in title_array_filtered, quindi mi deve tornare 0 per avere un match completo.
        $array_diff = array_diff( $ga_key_array_filtered, $title_array_filtered );
        if( empty( $array_diff ) ) {
            $seo_link_keywords_filtered[] = $ga_key;
        }

        $keyword_position[$ga_key] = $row->position;
        $keyword_processed[] = $ga_key;
	}

	$data = array(
	    'ID' => $post_id,
	    'post_content' => $content
	);

	if( wp_update_post( $data )){
		$update_data = 1 ;
	} else {
		$update_data = 0 ;
	}

	update_post_meta( $content_post->ID, 'seo_links_keywords', $seo_link_keywords);
    update_post_meta( $content_post->ID, 'seo_links_keywords_filtered', $seo_link_keywords_filtered);
    update_post_meta( $content_post->ID, 'seo_links_keywords_position', $keyword_position);
    update_post_meta( $content_post->ID, 'most_relevant_keyword', $most_relevant_keyword);
    update_post_meta( $content_post->ID, 'seo_links_all_keywords', $keyword_processed);

	die( json_encode( array(
        'status' => 0,
        'update_data' => $update_data,
        'replaced' => $replaced,
        'processed' => $keyword_processed,
        'keyword_position' => $keyword_position,
        'most_relevant_keyword' => array_unique( $most_relevant_keyword ),
        'keyword' => array_values(array_diff( $keyword_processed, $replaced )),
        'seo_links_keywords_filtered' => $seo_link_keywords_filtered
    ) ) );
}

add_action('wp_ajax_seoli_folder_contents', 'seoli_folder_contents');
add_action('wp_ajax_nopriv_seoli_folder_contents', 'seoli_folder_contents');

/**
 * Search Console Integrations
 */
// Register settings using the Settings API
add_action('admin_menu', 'seoli_admin');
function seoli_admin() {
    add_menu_page( 'SEO Links Interlinking', 'SEO Links Interlinking', 'manage_options', 'seo-links-interlinking', 'seoli_show_menu', 'dashicons-admin-links' );
}

function seoli_show_menu () {
    include SEOLI_PATH_ABS . 'view/settings.php';
}

/**
 * SEO Links Interlinking- Registration Form to WP SEO Plugins
 */
add_action( 'admin_post_seoli_registration', 'seoli_registration');
function seoli_registration(){
    $nonce = sanitize_text_field($_POST['security']);
    if(!wp_verify_nonce($nonce,'seoli_registration_nonce') || !current_user_can( 'administrator' )){
        header('Location:'.$_SERVER["HTTP_REFERER"].'?error=unauthenticated');
        exit();
    }

    $server_uri = ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME;

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

    $_SESSION['status'] = $data->status;
    $_SESSION['message'] = $data->message;
    $_SESSION['api_key'] = $data->api_key ?? '';

    if( $_SESSION['api_key'] != '' ) {
        update_option('sc_api_key', sanitize_text_field( $_SESSION['api_key'] ));
    }

    header('Location: '.site_url().'/wp-admin/admin.php?page=seo-links-interlinking');
    exit();
}

/**
 * Search Console Integration - Login Form to WP SEO Plugins
 */
add_action( 'admin_post_seoli_login', 'seoli_login');
function seoli_login(){
    $nonce = sanitize_text_field($_POST['security']);
    if(!wp_verify_nonce($nonce,'seoli_login_nonce') || !current_user_can( 'administrator' )){
        header('Location:'.$_SERVER["HTTP_REFERER"].'?error=unauthenticated');
        exit();
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

    $_SESSION['status'] = $data->status;
    $_SESSION['message'] = $data->message;

    if($data->status == 0) {
        // Generating a new api key

        $server_uri = ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME;

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

        $_SESSION['status'] = $data->status;
        $_SESSION['message'] = $data->message;
        $_SESSION['api_key'] = $data->api_key ?? '';

        if( $_SESSION['api_key'] != '' ) {
            update_option('sc_api_key', sanitize_text_field( $_SESSION['api_key']) );
        }
    }

    header('Location: '.site_url().'/wp-admin/admin.php?page=seo-links-interlinking');
    exit();

}

/**
 * Get residual credits
 */
function seoli_get_credits() {
    $sc_api_key = get_option('sc_api_key');
    if( !empty( $sc_api_key ) ) {
        $server_uri = ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME . SEOLI_SERVER_REQUEST_URI;
        $remote_get = SEOLI_BACKEND_URL . 'apikey/credits?api_key=' . $sc_api_key . '&domain=' . ( SEOLI_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEOLI_SERVER_NAME . '&remote_server_uri=' . base64_encode( $server_uri );

        $args = array(
            'timeout'     => 10,
            'sslverify' => false
        );
        $data = wp_remote_get( $remote_get, $args );
        $rowData = json_decode( $data['body'] );

        $api_limit = $rowData->response->api_limit_seo_links ?? 0;
        $api_call = $rowData->response->api_call_seo_links ?? 0;

        return $api_limit - $api_call;
    }
}

/**
 * Starting a session
 */
function seoli_start_session(){
    if (!session_id())
        session_start();
}
add_action("init", "seoli_start_session", 1);

#loads necessary files.
require_once SEOLI_PATH_ABS . 'loader.php';
return ob_get_clean();

?>