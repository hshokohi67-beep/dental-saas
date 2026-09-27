<?php
defined('ABSPATH') || exit;

class Dental_API_Router {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    public function register_routes(): void {
        (new Dental_Endpoint_Auth())->register_routes();
        (new Dental_Endpoint_Chart())->register_routes();
        (new Dental_Endpoint_Financial())->register_routes();
    }
}
