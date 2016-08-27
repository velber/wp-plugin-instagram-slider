<?php

/*
public function get_inst_redirect() {

    $success = false;

    $code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);

    if (isset($code)) {
        $token =  $this->get_instagram_tooken($code);
        if ($token) {
            update_option('instagram_tooken', $token);
            update_option('checking_time', time());
            $success = true;
        }
    }

    return array('success' => $success);
}

/**
 * Makes post request using instagram code and return access
 * tooken if success
 *
 * @param string $code
 * @return bool | string tooken
 *//*
private function get_instagram_tooken($code) {
    $instagramApiURL = 'https://api.instagram.com/oauth/access_token';
    $ku   = curl_init();
    $data = array(
        'client_id'     => get_option('instagram_client_id'),
        'redirect_uri'  => site_url('/api/get_inst_redirect/'),
        'client_secret' => get_option('instagram_client_secret'),
        'code'          => $code,
        'grant_type'    => 'authorization_code',
    );

    curl_setopt($ku, CURLOPT_URL, $instagramApiURL);
    curl_setopt($ku, CURLOPT_POST, true);
    curl_setopt($ku, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ku,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ku, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ku);

    if (!$result) {
        $error = (curl_error($ku));

        return false;
    }

    if ($i = json_decode($result, true)) {

        return $i['access_token'];
    }
}
*/