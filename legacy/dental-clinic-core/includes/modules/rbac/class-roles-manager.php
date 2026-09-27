<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class Dental_Roles_Manager
 * مدیریت نقش‌ها و سطوح دسترسی پلاگین
 */
class Dental_Roles_Manager {

    /**
     * تعریف نقش‌های سیستم
     */
    private const ROLES = [
        'dental_admin' => [
            'display_name' => 'مدیر کلینیک',
            'capabilities' => [
                // دسترسی کامل
                'dental_manage_all'            => true,
                'dental_view_all_patients'     => true,
                'dental_edit_all_patients'     => true,
                'dental_delete_patients'       => true,
                'dental_view_financials'       => true,
                'dental_manage_financials'     => true,
                'dental_manage_wallet'         => true,
                'dental_send_sms'              => true,
                'dental_manage_sms_settings'   => true,
                'dental_view_chart'            => true,
                'dental_edit_chart'            => true,
                'dental_manage_lab'            => true,
                'dental_view_reports'          => true,
                'dental_manage_settings'       => true,
                'dental_manage_doctors'        => true,
                'dental_manage_shifts'         => true,
                // وردپرس
                'read'                         => true,
            ],
        ],

        'dental_doctor' => [
            'display_name' => 'دندانپزشک',
            'capabilities' => [
                'dental_view_own_patients'     => true,
                'dental_edit_own_patients'     => true,
                'dental_view_all_patients'     => true,
                'dental_view_chart'            => true,
                'dental_edit_chart'            => true,
                'dental_view_financials'       => true,
                'dental_view_medical_history'  => true,
                'dental_edit_medical_history'  => true,
                'dental_manage_lab'            => true,
                'dental_view_reports'          => true,
                'dental_send_sms'              => true,
                'read'                         => true,
            ],
        ],

        'dental_secretary' => [
            'display_name' => 'منشی کلینیک',
            'capabilities' => [
                'dental_view_all_patients'     => true,
                'dental_edit_basic_patients'   => true,
                'dental_view_chart'            => true,
                'dental_view_financials'       => true,
                'dental_manage_financials'     => true,
                'dental_manage_wallet'         => true,
                'dental_send_sms'              => true,
                'dental_manage_appointments'   => true,
                'dental_view_lab'              => true,
                // منشی به تاریخچه پزشکی کامل دسترسی ندارد
                'dental_view_medical_history'  => false,
                'read'                         => true,
            ],
        ],

        'dental_patient' => [
            'display_name' => 'بیمار',
            'capabilities' => [
                'dental_view_own_profile'      => true,
                'dental_view_own_appointments' => true,
                'dental_view_own_wallet'       => true,
                'dental_view_own_financials'   => true,
                'dental_book_appointment'      => true,
                'read'                         => true,
            ],
        ],
    ];

    /**
     * سازنده — hook های وردپرس
     */
    public function __construct() {
        add_filter( 'user_has_cap',     [ $this, 'filter_user_capabilities' ], 10, 4 );
        add_action( 'pre_get_posts',    [ $this, 'filter_patient_queries' ] );
    }

    /**
     * ثبت نقش‌ها در وردپرس و اضافه کردن capability به Super Admin
     */
    public static function register_roles(): void {
        foreach ( self::ROLES as $role_slug => $role_data ) {
            remove_role( $role_slug );
            add_role(
                $role_slug,
                $role_data['display_name'],
                $role_data['capabilities']
            );
        }

        // اضافه کردن همه dental capabilities به نقش administrator وردپرس
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $all_dental_caps = array_keys( self::ROLES['dental_admin']['capabilities'] );
            foreach ( $all_dental_caps as $cap ) {
                $admin_role->add_cap( $cap );
            }
        }
    }

    /**
     * حذف نقش‌ها (هنگام uninstall)
     */
    public static function remove_roles(): void {
        foreach ( array_keys( self::ROLES ) as $role_slug ) {
            remove_role( $role_slug );
        }
    }

    /**
     * بررسی دسترسی کاربر جاری
     *
     * @param string   $capability  نام دسترسی
     * @param int|null $user_id     شناسه کاربر (null = کاربر جاری)
     * @return bool
     */
    public static function current_user_can( string $capability, ?int $user_id = null ): bool {
        if ( $user_id ) {
            $user = get_user_by( 'id', $user_id );
            if ( ! $user ) {
                return false;
            }
            return $user->has_cap( $capability );
        }

        return user_can( get_current_user_id(), $capability );
    }

    /**
     * دریافت نقش دندانپزشکی کاربر
     *
     * @param int|null $user_id
     * @return string|null
     */
    public static function get_dental_role( ?int $user_id = null ): ?string {
        $user_id = $user_id ?? get_current_user_id();
        $user    = get_user_by( 'id', $user_id );

        if ( ! $user ) {
            return null;
        }

        $dental_roles = array_keys( self::ROLES );

        foreach ( $user->roles as $role ) {
            if ( in_array( $role, $dental_roles, true ) ) {
                return $role;
            }
        }

        // ادمین وردپرس = dental_admin
        if ( $user->has_cap( 'manage_options' ) ) {
            return 'dental_admin';
        }

        return null;
    }

    /**
     * بررسی اینکه آیا کاربر پزشک است
     */
    public static function is_doctor( ?int $user_id = null ): bool {
        return self::get_dental_role( $user_id ) === 'dental_doctor';
    }

    /**
     * بررسی اینکه آیا کاربر مدیر است
     */
    public static function is_admin( ?int $user_id = null ): bool {
        $role = self::get_dental_role( $user_id );
        return $role === 'dental_admin' || current_user_can( 'manage_options' );
    }

    /**
     * بررسی اینکه آیا کاربر منشی است
     */
    public static function is_secretary( ?int $user_id = null ): bool {
        return self::get_dental_role( $user_id ) === 'dental_secretary';
    }

    /**
     * بررسی اینکه آیا کاربر بیمار است
     */
    public static function is_patient( ?int $user_id = null ): bool {
        return self::get_dental_role( $user_id ) === 'dental_patient';
    }

    /**
     * فیلتر قابلیت‌های کاربر — محدودیت دسترسی پزشک به بیماران خودش
     */
    public function filter_user_capabilities(
        array   $all_caps,
        array   $caps,
        array   $args,
        WP_User $user
    ): array {
        // پزشک فقط می‌تواند بیماران خود را ویرایش کند
        if (
            in_array( 'dental_edit_patient', $caps, true ) &&
            self::is_doctor( $user->ID ) &&
            ! empty( $args[2] )
        ) {
            $patient_id     = (int) $args[2];
            $patient_doctor = (int) get_post_meta( $patient_id, '_treatment_doctor_id', true );

            if ( $patient_doctor !== $user->ID && ! self::is_admin( $user->ID ) ) {
                $all_caps['dental_edit_patient'] = false;
            }
        }

        return $all_caps;
    }

    /**
     * فیلتر query های بیمار بر اساس نقش
     */
    public function filter_patient_queries( WP_Query $query ): void {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( $query->get( 'post_type' ) !== 'dental_patient' ) {
            return;
        }

        // پزشک فقط بیماران خودش را می‌بیند
        if ( self::is_doctor() && ! self::is_admin() ) {
            $current_doctor_id = get_current_user_id();

            // جستجوی بیمارانی که این پزشک روشان کار کرده
            $query->set( 'meta_query', [
                [
                    'key'     => '_treatment_doctor_id',
                    'value'   => $current_doctor_id,
                    'compare' => '=',
                ],
            ] );
        }
    }

    /**
     * دریافت لیست همه نقش‌ها با اطلاعات
     */
    public static function get_roles_info(): array {
        return self::ROLES;
    }

    /**
     * تبدیل نقش به برچسب فارسی
     */
    public static function get_role_label( string $role ): string {
        return self::ROLES[ $role ]['display_name'] ?? $role;
    }
}
