<?php

/*

Plugin Name:  ArvanCloud Cache Cleaner
Description:  افزونه اختصاصی ابر آروان ( نسخه آزمایشی ) در این نسخه امکان حذف کش ها به صورت اتوماتیک وجود دارد
Version:      0.8
Author:       ArvanCloud
Author URI:   https://arvancloud.com
Contributors: arvancloud

*/

define('arcache_VERSION', '0.7');

define('arcache_PLUGIN_URL', plugin_dir_url(__FILE__));
define('arcache_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('arcache_PLUGIN_BASENAME', plugin_basename(__FILE__));


/* Hide AdminBar */

function arvancloud_function_admin_bar()
{
    $options = get_option('arcache_options');
    if ($options['turn_off_menubar'] == true) {
        return false;
    } else {
        return ture;
    }
}

add_filter('show_admin_bar', 'arvancloud_function_admin_bar');

/* ArvanCloud Caching */

class arcache_API
{

    var $arcache_endpoint = "https://www.arvancloud.com/api/v1/domains/purge-cache";
    var $arcache_methods = array();
    var $arcache_options = array();
    var $arcache_url = '';
    var $arcache_suppress_debug = false;

    function __construct()
    {
        $this->arcache_options = get_option('arcache_options');
        isset($this->arcache_options['auto_purge']) || $this->arcache_options['auto_purge'] = false;
        if (empty($this->arcache_options['account'])) {
            $this->arcache_options['account'] = '';
            update_option('arcache_options', $this->arcache_options);
        }
        isset($this->arcache_options['turn_off_menubar']) || $this->arcache_options['turn_off_menubar'] = false;
        $this->build_api_calls();
        $this->wordpress_upload_dir = wp_upload_dir();
    }

    function build_api_calls()
    {
        $tkn = isset($this->arcache_options['token']) ? $this->arcache_options['token'] : null;
        $email = isset($this->arcache_options['email']) ? $this->arcache_options['email'] : null;
        $site = isset($this->arcache_options['site']) ? $this->arcache_options['site'] : null;
        $z = isset($this->arcache_options['account']) ? $this->arcache_options['account'] : null;
        $this->api_methods = array(
            "purge_all" => array(
                'cmd' => 'purge-all',
                'key' => $tkn,
                'user' => $email,
                'domain' => $z,
                'site' => $site
            ),
            "purge_url" => array(
                'cmd' => 'purge-files',
                'key' => $tkn,
                'user' => $email,
                'domain' => $z,
                'site' => $site
            )
        );
    }

    function return_json_success($data = '')
    {
        print json_encode(array("success" => 'true', "data" => $data));
    }

    function return_json_error($error = '')
    {
        print json_encode(array("success" => 'false', 'error' => array("message" => $error)));
    }

    function make_api_request($api_method, $extra_post_variables = null)
    {
        $headers = '';
        if (is_array($extra_post_variables)) {
            $post_variables = array_merge($this->api_methods[$api_method], $extra_post_variables);
        } else {
            $post_variables = $this->api_methods[$api_method];
        }
        $results = wp_remote_post($this->arcache_endpoint, array('headers' => $headers, 'body' => $post_variables));
        return json_decode($results['body']);
    }

    function purge_entire_cache()
    {
        $results = $this->make_api_request('purge_all');
    }

    function purge_url($url)
    {
        $this->purge_url_after_post_save($url, true);
    }

    function purge_url_after_post_save($url, $ajax = false)
    {
        $results = $this->make_api_request('purge_url', array('url' => $url));
        if ($ajax) {
            $auto = "Manual";
        } else {
            $auto = "Automatic";
        }
    }

    function get_wordpress_domain()
    {
        $domain = preg_replace('/http:\/\//', '', get_home_url());
        $domain = preg_replace('/www./', '', $domain);
        return $domain;
    }
}

function arcache_admin_scripts_styles()
{
    wp_register_script('arcache-scripts', arcache_PLUGIN_URL . 'lib/arcache.js');
    wp_register_style('arcache-style', arcache_PLUGIN_URL . 'lib/arcache.css');

    wp_enqueue_script('arcache-scripts');
    wp_enqueue_style('arcache-style');
}

add_action('admin_init', 'arcache_admin_scripts_styles');

function arcache_add_menu_page()
{
    function arcache_menu_page()
    {
        $options_page_url = arcache_PLUGIN_PATH . '/lib/arcache_options.php';
        if (file_exists($options_page_url)) {
            include_once($options_page_url);
        }
    }

    ;
    add_submenu_page('options-general.php', 'ابــر آروان', 'ابــر آروان', 'switch_themes', 'arcache', 'arcache_menu_page');
}

;
add_action('admin_menu', 'arcache_add_menu_page');

function arcache_plugin_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=arcache">تنظیمات</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter("plugin_action_links_" . arcache_PLUGIN_BASENAME, 'arcache_plugin_settings_link');

function arcache_purge_after_save_post_hook($post_id)
{
    global $hook_running;
    remove_action('publish_post', 'arcache_purge_after_save_post_hook');
    if ($hook_running)
        return;
    $hook_running = true;
    if (defined('DOING_SAVE') && DOING_SAVE || !$post_id)
        return;
    if (!in_array(get_post_type($post_id), array('post', 'page', 'partners')))
        return;
    $arcache = new arcache_API;
    if ($arcache->arcache_options['auto_purge']) {
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
        $arcache->arcache_suppress_debug = true;
        $arcache->purge_url_after_post_save($permalink);
        if (in_array(get_post_type($post_id), array('post'))) {

            $siteurl = site_url();
            if (is_multisite()) :
                if (function_exists('domain_mapping_siteurl')) :
                    $siteurl = domain_mapping_siteurl(get_current_blog_id());
                endif;
            endif;
            $arcache->purge_url_after_post_save($siteurl);
        }
    }
    add_action('publish_post', 'arcache_purge_after_save_post_hook');
}

add_action('publish_post', 'arcache_purge_after_save_post_hook');

function arcache_purge_after_save_comment_hook($get_comment_id)
{
    global $hook_running;
    remove_action('wp_insert_comment', 'arcache_purge_after_save_comment_hook');
    if ($hook_running)
        return;
    $comment = get_comment($get_comment_id);
    $post_id = $comment->comment_post_ID;
    $hook_running = true;
    if (defined('DOING_SAVE') && DOING_SAVE || !$post_id)
        return;
    if (!in_array(get_post_type($post_id), array('post', 'page', 'partners')))
        return;
    $arcache = new arcache_API;
    if ($arcache->arcache_options['auto_purge']) {
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
        $arcache->arcache_suppress_debug = true;
        $arcache->purge_url_after_post_save($permalink);
    }
    add_action('wp_insert_comment', 'arcache_purge_after_save_comment_hook');
}

add_action('wp_insert_comment', 'arcache_purge_after_save_comment_hook');


function arcache_entire_cache()
{
    $arcache = new arcache_API;
    $arcache->purge_entire_cache();
}

add_action('wp_ajax_arcache_entire_cache', 'arcache_entire_cache');

function arcache_purge_url()
{
    $arcache = new arcache_API;
    $arcache->purge_url($_REQUEST['url']);
}

add_action('wp_ajax_arcache_purge_url', 'arcache_purge_url');