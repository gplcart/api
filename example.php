<?php
/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * A very basic example of client functions that can be used to request API provided by this module
 * Please adapt to your framework / environment!
 */

/**
 * Log in and get an authorization token
 * @param string $url
 * @param string $client_id
 * @param string $client_secret
 * @return mixed
 */
function login($url, $client_id, $client_secret)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('client_id' => $client_id, 'client_secret' => $client_secret));

    $result = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($result, true);
    return is_array($decoded) ? $decoded : $result;
}

/**
 * Request API with the authorization token
 * @param string $url
 * @param string $access_token
 * @param array $data
 * @return mixed
 */
function request($url, $access_token, array $data = array())
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $access_token"));

    $result = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($result, true);
    return is_array($decoded) ? $decoded : $result;
}

/**
 * Log in and fetch API data
 * @param string $url
 * @param string $client_id
 * @param string $client_secret
 * @param array $post_data
 * @param bool $force_login
 * @return mixed
 */
function fetch($url, $client_id, $client_secret, array $post_data = array(), $force_login = false)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $now = time();

    if (!$force_login && isset($_SESSION['api_access_token']) && $now < $_SESSION['api_token_expires']) {
        return request($url, $_SESSION['api_access_token'], $post_data);
    }

    $result = login($url, $client_id, $client_secret);

    if (isset($result['access_token']) && isset($result['expires_in'])) {

        $_SESSION['api_access_token'] = $result['access_token'];
        $_SESSION['api_token_expires'] = $now + $result['expires_in'];

        return request($url, $result['access_token'], $post_data);
    }

    return $result;
}