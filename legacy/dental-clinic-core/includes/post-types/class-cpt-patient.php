<?php
defined('ABSPATH') || exit;

class Dental_CPT_Patient {

    const POST_TYPE = 'dental_patient';

    public function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function register(): void {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => 'بیماران',
                'singular_name' => 'بیمار',
                'add_new'       => 'بیمار جدید',
                'add_new_item'  => 'افزودن بیمار جدید',
                'edit_item'     => 'ویرایش بیمار',
                'search_items'  => 'جستجوی بیمار',
                'not_found'     => 'بیماری یافت نشد',
            ],
            'public'              => false,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_rest'        => false,
            'supports'            => ['title'],
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
        ]);
    }
}
