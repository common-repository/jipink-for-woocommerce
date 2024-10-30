<?php

namespace Jipink\Api;

use Jipink\Helper\Helper;

class JipinkApi
{
    const DEV_BASE_URL = 'https://dev-api.jipink.com';
    const PROD_BASE_URL = 'https://api.jipink.com';

    public function __construct(string $customer_id, string $pat, string $environment)
    {
        $this->auth_header = null;
        $this->account_header = null;
        $this->environment = $environment;
        if (!is_null($pat) && !empty($pat)) {
            $this->auth_header = "Bearer $pat";
        }
        if (!is_null($customer_id) && !empty($customer_id)) {
            $this->account_header = $customer_id;
        }
    }

    protected function exec(string $method, string $url, array $body, array $headers)
    {
        if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
            $body = json_encode($body);
        }
        $args = [
            'method' => $method,
            'headers' => $headers,
            'body' => $body
        ];
        $response = wp_safe_remote_request($url, $args);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            Helper::log_error(sprintf(__('%s - Error in remote request: %s', 'jipink-for-woocommerce'), __FUNCTION__, $error_message));
            return false;
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code > 299) {
            Helper::log_error(sprintf(__('%s - Error in remote request: %s', 'jipink-for-woocommerce'), __FUNCTION__, $response_code));
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function get(string $endpoint, array $body = [], array $headers = [])
    {
        $url = $this->get_base_url() . $endpoint;
        $headers['Authorization'] = $this->auth_header;
        $headers['Account'] = $this->account_header;
        return $this->exec('GET', $url, [], $headers);
    }

    public function post(string $endpoint, array $body = [], array $headers = [])
    {
        $url = $this->get_base_url() . $endpoint;
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $this->auth_header;
        $headers['Account'] = $this->account_header;
        return $this->exec('POST', $url, $body, $headers);
    }

    public function put(string $endpoint, array $body = [], array $headers = [])
    {
        $url = $this->get_base_url() . $endpoint;
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $this->auth_header;
        $headers['Account'] = $this->account_header;
        return $this->exec('PUT', $url, $body, $headers);
    }

    public function patch(string $endpoint, array $body = [], array $headers = [])
    {
        $url = $this->get_base_url() . $endpoint;
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $this->auth_header;
        $headers['Account'] = $this->account_header;
        return $this->exec('PATCH', $url, $body, $headers);
    }

    public function delete(string $endpoint, array $body = [], array $headers = [])
    {
        $url = $this->get_base_url() . $endpoint;
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $this->auth_header;
        $headers['Account'] = $this->account_header;
        return $this->exec('DELETE', $url, $body, $headers);
    }

    public function get_base_url()
    {
        if ($this->environment === 'test') {
            return self::DEV_BASE_URL;
        }
        return self::PROD_BASE_URL;
    }
}
