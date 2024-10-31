<?php
# Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

#function for change keyword.
add_action('wp_ajax_seoli_folder_contents', 'seoli_folder_contents');
function seoli_folder_contents() {
    global $wpdb;

    if( !current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Not enough privileges.' );
        wp_die();
    }

    if ( ! check_ajax_referer( 'seoli_security_nonce', 'nonce', false ) ) {
        wp_send_json_error( 'Invalid security token sent.' );
        wp_die();
    }

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
    //$permalink_explode = explode('/', $permalink);
    //$permalink_filter = $permalink_explode[3] ?? '';

    $server_uri = SEOLI_SITE_URL . SEOLI_SERVER_REQUEST_URI;
    $remote_get = SEOLI_BACKEND_URL . 'searchconsole/loadData?api_key=' . $sc_api_key . '&domain=' . SEOLI_SITE_URL . '&remote_server_uri=' . base64_encode( $server_uri );

    $args = array(
        'timeout'     => 10,
        'sslverify' => false
    );
    $data = wp_remote_get( $remote_get, $args );

    $rowData = json_decode( $data['body'] );

    if( $rowData->status == -1 || $rowData->status == -2 || $rowData->status == -3 || $rowData->status == -4 ){
        wp_die(json_encode($rowData));
    }

    // Non mi serve più il sort by name, faccio sort by impressions
    //uasort($rowData,"seoli_sort_by_name");

    $keyword_processed = array();
    $replaced = array();
    $keyword_position = array();
    $keyword_impressions = array();
    $most_relevant_keyword = array(); // Tutte le keyword escluse quelle della url corrente
    $seo_link_keywords = array(); // Tutte le keyword della url corrente
    $internal_link_keywords_filtered = array(); // Tutte le keyword che matchano il titolo con il filtro sul numero di caratteri delle parole
    $words = array();

    //$categories = wp_get_post_categories( $post_id );
    foreach($rowData as $row){
        $ga_url = $row->page;
        $ga_key = $row->query;
        $ga_key = str_replace("’", "'", $ga_key);
        $ga_key = str_replace('“', '"', $ga_key);
        $ga_key = str_replace('”', '"', $ga_key);

        //$url_explode = explode('/', $ga_url );
        //$url_filter = $url_explode[3] ?? '';

        // Nelle keyword link tutte le rilevanti ( escluso la URL ). Se il post è in bozza droppo la condizione url_filter = permalink_filter
        // Se la lunghezza della kw è > di 5 la inserisco negli interal links, altrimenti no
        // La condizione url_filter == permalink_filter mi serve per prendere le kw della categoria, quindi:
        // Prendo le categorie del post. Per ogni ga_url prendo le categorie. Se Almeno una categoria di ga_url è presente tra le categorie del post, prendo la kw.

        //$ga_post_id = url_to_postid( $ga_url );
        //$ga_categories = wp_get_post_categories( $ga_post_id );
        //$intersect = array_intersect( $categories, $ga_categories ); // Intersezione: è vuota se le categorie del post e di ga_post sono tutte diverse, se almeno una è in comune invece non sarà vuota ma conterrà appunto la categoria in comune.

        //if( ( $post_status == 'draft' || $url_filter == $permalink_filter ) && $permalink != $ga_url && strlen( $ga_key ) > 5 ) {
        //if( ( $post_status == 'draft' || !empty( $intersect ) ) && $permalink != $ga_url && strlen( $ga_key ) > 5 ) {
        if( $permalink != $ga_url && strlen( $ga_key ) > 5 ) { // Mostro tutte le keywords sempre
            // Se sono in /tecnologia/articolo prendo solo le keyword che hanno url /tecnlogia

            if(!in_array($ga_key, $keyword_processed)) {
                $most_relevant_keyword[] = $ga_key;

                $url_with_content = '<a href="' . filter_var($ga_url, FILTER_SANITIZE_URL) . '">' . $ga_key . '</a>';

                /**
                 * Da capire bene
                 */
                $content_replaced = seoli_replace_regex( $ga_key, $url_with_content, $content );

                if( $content_replaced != $content ) {
                    $content = $content_replaced;
                    $replaced[] = $ga_key;
                    $words[] = seoli_search_replace_regex( $ga_key, $content );
                }
            }

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
            if( empty( $array_diff ) && !empty( $ga_key_array_filtered ) ) {
                $internal_link_keywords_filtered[] = $ga_key;
            }
        }

        // Nelle keyword seo Solo la URL del post
        $ga_url_explode = array_filter( explode('/', str_replace( site_url(), '', $ga_url )) );
        $ga_url_post_name = array_pop( $ga_url_explode );
        //echo $permalink . " --> " . $ga_url_post_name . "\n";
        if( strpos( $permalink, $ga_url_post_name ) !== false ) {
            $seo_link_keywords[] = $ga_key;
        }
        if( $permalink == $ga_url ) {
            //$seo_link_keywords[] = $ga_key;
        }

        $keyword_position[$ga_key] = $row->position;
        $keyword_impressions[$ga_key] += $row->impressions;
        $keyword_processed[] = $ga_key;
    }


    $_this_post = get_post( $post_id );
    $_this_post->post_content = stripslashes( apply_filters( 'the_content', $content ) );

    if( wp_update_post( $_this_post )){
        $update_data = 1 ;
    } else {
        $update_data = 0 ;
    }

    update_post_meta( $content_post->ID, 'seo_links_keywords', $seo_link_keywords);
    update_post_meta( $content_post->ID, 'seo_links_keywords_filtered', $internal_link_keywords_filtered);
    update_post_meta( $content_post->ID, 'internal_links_keywords_filtered', $internal_link_keywords_filtered);
    update_post_meta( $content_post->ID, 'seo_links_keywords_position', $keyword_position);
    update_post_meta( $content_post->ID, 'seo_links_keywords_impressions', $keyword_impressions);
    update_post_meta( $content_post->ID, 'most_relevant_keyword', $most_relevant_keyword);
    update_post_meta( $content_post->ID, 'seo_links_all_keywords', $keyword_processed);

    die( json_encode( array(
        'status' => 0,
        'replaced' => $replaced,
        'post_content' => $content,
        'seo_links_keywords' => $seo_link_keywords,
        'seo_links_keywords_filtered' => $internal_link_keywords_filtered,
        'internal_links_keywords_filtered' => $internal_link_keywords_filtered,
        'seo_links_keywords_position' => $keyword_position,
        'seo_links_keywords_impressions' => $keyword_impressions,
        'most_relevant_keyword' => $most_relevant_keyword,
        'seo_links_all_keywords' => $keyword_processed,
        'words' => $words
    ) ) );
}

add_action('wp_ajax_seoli_savePost', 'seoli_savePost');
function seoli_savePost() {
    if( !current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Not enough privileges.' );
        wp_die();
    }

    if ( ! check_ajax_referer( 'seoli_savePost_nonce', 'nonce', false ) ) {
        wp_send_json_error( 'Invalid security token sent.' );
        wp_die();
    }

    $post_id = (int) sanitize_text_field( isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0 );
    $post_content = isset( $_POST['post_content'] ) ? $_POST['post_content'] : '' ;

    if( $post_id > 0 && $post_content != '' ) {
        $_this_post = get_post( $post_id );
        $_this_post->post_content = stripslashes( apply_filters( 'the_content', $post_content ) );
        wp_update_post( $_this_post );
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }

}