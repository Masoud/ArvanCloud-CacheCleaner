<?php

/*

Plugin Name:  ArvanCloud Cache Cleaner
Description:  افزونه اختصاصی ابر آروان ( نسخه آزمایشی ) در این نسخه امکان حذف کش ها به صورت اتوماتیک وجود دارد
Version:      1.2
Author:       ArvanCloud
Author URI:   https://arvancloud.com
Contributors: arvancloud

*/

define('ar_cache_VERSION', '1.2');

define('ar_cache_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ar_cache_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ar_cache_PLUGIN_BASENAME', plugin_basename(__FILE__));

/* Hide AdminBar */

function ar_function_admin_bar()
{
    $options = get_option('ar_cache_options');
    if ($options['turn_off_menubar'] == true) {
        return false;
    } else {
        return true;
    }
}

add_filter('show_admin_bar', 'ar_function_admin_bar');

/* ArvanCloud Caching */

class ar_cache_API
{
    var $ar_cache_endpoint = "https://api.arvancloud.com/cdn/1.0/domains/domain_slug/caching/purge";
    var $ar_cache_methods = array();
    var $ar_cache_options = array();
    var $ar_cache_url = '';
    var $ar_cache_suppress_debug = false;

    function __construct()
    {
        $this->ar_cache_options = get_option('ar_cache_options');
        isset($this->ar_cache_options['auto_purge']) || $this->ar_cache_options['auto_purge'] = false;
        if (empty($this->ar_cache_options['account'])) {
            $this->ar_cache_options['account'] = '';
            update_option('ar_cache_options', $this->ar_cache_options);
        }
        isset($this->ar_cache_options['turn_off_menubar']) || $this->ar_cache_options['turn_off_menubar'] = false;
        $this->build_api_calls();
        $this->wordpress_upload_dir = wp_upload_dir();
    }

    function build_api_calls()
    {
        $this->api_methods = array(
            "purge_all" => array(
                'purge' => 'all',
            ),
            "ar_purge_url" => array(
                'purge' => 'individual',
            )
        );
    }

    function ar_return_json_success($data = '')
    {
        print json_encode(array("success" => 'true', "data" => $data));
    }

    function ar_return_json_error($error = '')
    {
        print json_encode(array("success" => 'false', 'error' => array("message" => $error)));
    }

    function ar_make_api_request($api_method, $extra_post_variables = null)
    {
        $token = isset($this->ar_cache_options['token']) ? $this->ar_cache_options['token'] : null;
        $email = isset($this->ar_cache_options['email']) ? $this->ar_cache_options['email'] : null;
        $site = isset($this->ar_cache_options['site']) ? $this->ar_cache_options['site'] : null;
        $domain_key = isset($this->ar_cache_options['account']) ? $this->ar_cache_options['account'] : null;
        $headers = array(
            'X-AUTH-KEY' => $token,
            'X-AUTH-EMAIL' => $email,
            'X-AUTH-SERVICE-KEY' => $domain_key,
        );
        $this->ar_cache_endpoint = str_replace('domain_slug', $site, $this->ar_cache_endpoint);

        if (is_array($extra_post_variables)) {
            $post_variables = array_merge($this->api_methods[$api_method], $extra_post_variables);
        } else {
            $post_variables = $this->api_methods[$api_method];
        }
        $results = wp_remote_post($this->ar_cache_endpoint, array('headers' => $headers, 'body' => $post_variables));
        if (is_wp_error($results)) {
            add_filter('https_ssl_verify', '__return_false');
            $results = wp_remote_post($this->ar_cache_endpoint, array('headers' => $headers, 'body' => $post_variables));
        }

        return json_decode($results['body']);
    }

    function ar_purge_entire_cache()
    {
        $results = $this->ar_make_api_request('purge_all');
    }

    function ar_purge_url($url)
    {
        $this->ar_purge_url_after_post_save($url, true);

    }

    function ar_purge_url_after_post_save($url, $ajax = false)
    {
        $results = $this->ar_make_api_request('ar_purge_url', array('purge_urls' => $url));
        if ($ajax) {
            $auto = "Manual";
        } else {
            $auto = "Automatic";
        }
    }

    function ar_get_wordpress_domain()
    {
        $domain = preg_replace('/http:\/\//', '', get_home_url());
        $domain = preg_replace('/www./', '', $domain);
        return $domain;
    }
}

function ar_cache_admin_scripts_styles()
{
    wp_register_script('ar_cache-scripts', ar_cache_PLUGIN_URL . 'lib/ar_cache.js');
    wp_register_style('ar_cache-style', ar_cache_PLUGIN_URL . 'lib/ar_cache.css');

    wp_enqueue_script('ar_cache-scripts');
    wp_enqueue_style('ar_cache-style');
}

add_action('admin_init', 'ar_cache_admin_scripts_styles');

function ar_cache_add_menu_page()
{
    function ar_cache_menu_page()
    {
        $options_page_url = ar_cache_PLUGIN_PATH . '/lib/ar_cache_options.php';
        if (file_exists($options_page_url)) {
            include_once($options_page_url);
        }
    }

    ;
    add_submenu_page('options-general.php', 'ابر آروان', 'ابر آروان', 'switch_themes', 'ar_cache', 'ar_cache_menu_page');
}

;
add_action('admin_menu', 'ar_cache_add_menu_page');

function ar_cache_plugin_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=ar_cache">تنظیمات</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter("plugin_action_links_" . ar_cache_PLUGIN_BASENAME, 'ar_cache_plugin_settings_link');

function ar_cache_purge_after_save_post_hook($post_id)
{
    global $hook_running;
    remove_action('publish_post', 'ar_cache_purge_after_save_post_hook');
    if ($hook_running)
        return;
    $hook_running = true;
    if (defined('DOING_SAVE') && DOING_SAVE || !$post_id)
        return;
    if (!in_array(get_post_type($post_id), array('post', 'page', 'partners')))
        return;
    $ar_cache = new ar_cache_API;
    if ($ar_cache->ar_cache_options['auto_purge']) {
        $permalink = get_permalink($post_id);
        if (is_multisite()) :
            if (function_exists('domain_mapping_post_content')) :
                global $wpdb;
                $orig_url = str_replace("https", "http", get_original_url('siteurl'));
                $url = str_replace("https", "http", domain_mapping_siteurl('NA'));
                if ($url == 'NA')
                    return $permalink;
                $permalink = str_replace($orig_url, $url, $permalink);
            endif;
        endif;

        $ar_cache->ar_cache_suppress_debug = true;
        $ar_cache->ar_purge_url_after_post_save($permalink);

        $ar_cacheurl = $_REQUEST['ar_cacheurl'];
        foreach ($ar_cacheurl as $urls_for_cache) {
            if (isset($urls_for_cache) && trim($urls_for_cache) != '') {
                $siteurl = site_url();
                $ar_cache->ar_purge_url_after_post_save($siteurl . $urls_for_cache);
            } elseif (in_array(get_post_type($post_id), array('post'))) {
                $siteurl = site_url();
                if (is_multisite()) :
                    if (function_exists('domain_mapping_siteurl')) :
                        $siteurl = domain_mapping_siteurl(get_current_blog_id());
                    endif;
                endif;
                $ar_cache->ar_purge_url_after_post_save($siteurl);
            }
        }
    }
    add_action('publish_post', 'ar_cache_purge_after_save_post_hook');
}

add_action('publish_post', 'ar_cache_purge_after_save_post_hook');

function ar_cache_purge_after_save_comment_hook($get_comment_id)
{
    global $hook_running;
    remove_action('wp_insert_comment', 'ar_cache_purge_after_save_comment_hook');
    if ($hook_running)
        return;
    $comment = get_comment($get_comment_id);
    $post_id = $comment->comment_post_ID;
    $hook_running = true;
    if (defined('DOING_SAVE') && DOING_SAVE || !$post_id)
        return;
    if (!in_array(get_post_type($post_id), array('post', 'page', 'partners')))
        return;
    $ar_cache = new ar_cache_API;
    if ($ar_cache->ar_cache_options['auto_purge']) {
        $permalink = get_permalink($post_id);
        if (is_multisite()) :
            if (function_exists('domain_mapping_post_content')) :
                global $wpdb;
                $orig_url = str_replace("https", "http", get_original_url('siteurl'));
                $url = str_replace("https", "http", domain_mapping_siteurl('NA'));
                if ($url == 'NA')
                    return $permalink;
                $permalink = str_replace($orig_url, $url, $permalin);
            endif;
        endif;
        $ar_cache->ar_cache_suppress_debug = true;
        $ar_cache->ar_purge_url_after_post_save($permalink);
    }
    add_action('wp_insert_comment', 'ar_cache_purge_after_save_comment_hook');

}

add_action('wp_insert_comment', 'ar_cache_purge_after_save_comment_hook');


function ar_cache_entire_cache()
{
    $ar_cache = new ar_cache_API;
    $ar_cache->ar_purge_entire_cache();
}

add_action('wp_ajax_ar_cache_entire_cache', 'ar_cache_entire_cache');

function ar_cache_ar_purge_url()
{
    $ar_cache = new ar_cache_API;
    $ar_cache->ar_purge_url($_REQUEST['url']);
}

add_action('wp_ajax_ar_cache_ar_purge_url', 'ar_cache_ar_purge_url');


function ar_post_init()
{
    echo "<div id='arvan_meta_box'>
<div style='margin-top: 5px; margin-right: 10px; margin-bottom: 5px;'>
<b><a>حذف کش صفحه(ابرآروان):</a></b></div>
<div style='margin-top: 5px; margin-right: 10px; margin-bottom: 5px;cursor:pointer;float: left; width: 13%;' id='arvan_add'>
<a><span class=\"dashicons dashicons-plus\"></span></a></div><div style='margin-top: 5px; margin-right: 10px; margin-bottom: 5px;cursor:pointer;float: left;' id='arvan_dec'><a><span class=\"dashicons dashicons-minus\"></span></a></div>
<input name='ar_cacheurl[]' id='field_arvan_text' placeholder='/index' style='margin-top: 5px; margin-right: 10px; margin-bottom: 5px;direction: ltr;width:93%'><b><a></a></b></input></div>";

    echo "<script type='text/javascript'>jQuery('#arvan_add').click(function () {jQuery('#field_arvan_text').clone().appendTo('#arvan_meta_box');});jQuery('#arvan_dec').click(function () {jQuery('#arvan_meta_box input:last').remove();});";
    echo "</script>";
}

add_action('post_submitbox_misc_actions', 'ar_post_init');
