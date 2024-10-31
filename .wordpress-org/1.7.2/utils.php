<?php
function seoli_replace_first_str($search_str, $replacement_str, $src_str) {
    return (false !== ($pos = strpos($src_str, $search_str))) ? substr_replace($src_str, $replacement_str, $pos, strlen($search_str)) : false;
}

function seoli_replace_regex( $ga_key, $url_with_content, $content ) {
    /**
     * Regex definitiva
     */
    $re = '/((<(h1|h2|h3|h4|h5|h6|a).*?>(.*?)<\/.*?>)|<.*?>|\[(.*)\])(*SKIP)(*FAIL)|\b' . str_replace('/', '\/', preg_quote( $ga_key ) ) . '\b/i';

    $output = preg_replace_callback( $re, function( $m ) use ( $url_with_content, $content ){
        if( strpos( $content, $url_with_content ) !== false ) {
            return $m[0];
        } else {
            return $url_with_content;
        }
    }, $content, 1);

    return $output;
}

function seoli_search_replace_regex( $ga_key, $content ){
    $matches = array();
    $re = '/((<(h1|h2|h3|h4|h5|h6|a).*?>(.*?)<\/.*?>)|<.*?>|\[(.*)\])(*SKIP)(*FAIL)|(\S+)\s*(\S+)\s*' . str_replace('/', '\/', preg_quote( $ga_key ) ) . '\s*(\S+)\s*(\S+)/mi';
    preg_match_all( $re, strip_tags( $content ), $matches, PREG_SET_ORDER, 0);
    if( !empty( $matches ) ) {
        return $matches[0][0];
    }
    return '';
}

#sort by name
function seoli_sort_by_name($a,$b) {
    return strlen( $a->query ) < strlen( $b->query );
}

/**
 * get supported language
 */
function seoli_get_languages() {
    return array('it','es','en','de','nl','da','no','sv','fi','pl','pt','pt-br','tr','ru','zh','zh-tw','ja','hi','ko','en-uk');
}