<?php
/**
 * Plugin Name: Instagram-slider
 * Plugin URI: https://github.com/velber/wp-plugin-instagram-slider.git
 * Description: This plugin gets images from instagram and creates slick slider from them.
 * Version: 1.0.0
 * Author: Volodymyr Chupovskyi
 * Author URI:
 * License: GPL2
 * To make this plugin working, it requires to install and activate json-api plugin
 * from https://ru.wordpress.org/plugins/json-api/.
 * Then add 2 methods from json-api-methods.php into /wp-content/plugins/json-api/controllers/core.php
 */

/**
 * @return array of images
 */
function get_instagram_content() {

    // tooken from db, use add_option/update_option to manage it in db
    $tooken = get_option('instagram_tooken');

    // instagram account id (add "?__a=1" to instagram url to get account id)
    $userId = '';

    $requestUrl = sprintf('https://api.instagram.com/v1/users/%s/media/recent/?count=20&access_token=%s', $userId, $tooken);
    $request = json_decode(file_get_contents($requestUrl));
    $images  = array();

    if (false != $request && 200 === $request->meta->code) {
        $mediaItems = $request->data;

        foreach ($mediaItems as $mediaItem) {
            $images[] = array(
                'url' => $mediaItem->link,
                'photo_ulr' => $mediaItem->images->low_resolution->url,
            );
        }
    }

    return $images;
}

/**
 * На сервері директива safe_mode дорівнює false, через че не дозволена директива
 * в curl CURLOPT_FOLLOWLOCATION, яка відповідає за редіректи. Змінити її можна тілки на
 * сервері і не можливо на льоту під час виконання скрипта. Тому на віддаленому сервері прийшлось
 * обновляти ключ вручну, посилаючи силку в повідомленні через мейл.
 * Після добавлення опції CURLOPT_HTTPHEADER вирішилась проблема аутентифікації.
 */
function get_instagram_auth_tooken() {

    $success = true;
    $error   = '';
    $images  = get_instagram_content();

    if (0 == count($images)) {

        // cookie string from browser request headers request
        $cookies = '';

        // get client id (app id at instagram developers) from db
        $instaClientId = get_option('instagram_client_id');

        // api url, use json-api plugin to release api.
        // redirect url must be equal to method name in /json-api/controllers/core.php
        $instaCleintRedirect = site_url('/api/get_inst_redirect/');

        $url = "https://api.instagram.com/oauth/authorize/?client_id={$instaClientId}";
        $url .= "&redirect_uri={$instaCleintRedirect}&response_type=code";

        $referer = '';

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_REFERER        => $referer,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_VERBOSE        => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT      => "Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4",
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_COOKIEFILE     => __DIR__ . '/cookie.txt',
            CURLOPT_COOKIEJAR      => __DIR__ . '/cookie.txt',
            CURLOPT_HTTPHEADER     => array("Cookie: $cookies")
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $error   = curl_error($ch);
        curl_close($ch);

        if ($content && true === json_decode($content)['success']) {
            $images = get_instagram_content();
        }
    }

    if (0 == count($images)) {
        $success = false;
    }

    echo json_encode(array(
        'success' => $success,
        'images'  => $images,
        'error'   => $error,
    ));

    wp_die();
}

add_action('wp_ajax_nopriv_auth_tooken', 'get_instagram_auth_tooken');
add_action('wp_ajax_auth_tooken', 'get_instagram_auth_tooken');

/**
 * Plugs requires assets to site.
 * Updates jQuery to newer version.
 */
function inst_enqueue_content() {

    wp_enqueue_script('inst_js', plugin_dir_url(__FILE__) . 'js/inst-slider.js');
    wp_deregister_script('jquery');
    wp_register_script('jquery',
        'http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js', false, '2.0.s');
    wp_enqueue_script('jquery');
    wp_enqueue_script('slick_slider', plugin_dir_url(__FILE__) . 'slick/slick.min.js');

    wp_enqueue_style('inst_css', plugin_dir_url(__FILE__) . 'css/inst-slider.css');
    wp_enqueue_style('click_css', plugin_dir_url(__FILE__) . 'slick/slick.css');

    //ajax url
    wp_localize_script('inst_js', 'myajax',
        array(
            'url' => admin_url('admin-ajax.php')
        )
    );
}

add_action('wp_enqueue_scripts', 'inst_enqueue_content');
