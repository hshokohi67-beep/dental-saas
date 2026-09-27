<?php
defined('ABSPATH') || exit;

class Dental_Autoloader {

    private static array $class_map = [
        // هسته
        'Dental_Core'                  => 'includes/class-dental-core.php',
        'Dental_Installer'             => 'includes/class-dental-installer.php',

        // Post Types
        'Dental_CPT_Patient'           => 'includes/post-types/class-cpt-patient.php',
        'Dental_CPT_Treatment'         => 'includes/post-types/class-cpt-treatment.php',
        'Dental_CPT_Lab_Order'         => 'includes/post-types/class-cpt-lab-order.php',

        // Auth
        'Dental_OTP_Manager'           => 'includes/modules/auth/class-otp-manager.php',
        'Dental_Auth_Manager'          => 'includes/modules/auth/class-auth-manager.php',

        // بیمار
        'Dental_Patient_Wallet'        => 'includes/modules/patient/class-patient-wallet.php',
        'Dental_Medical_History'       => 'includes/modules/medical-history/class-medical-history.php',

        // Admin Pages
        'Dental_Page_Medical'          => 'admin/pages/class-page-medical.php',
        'Dental_Page_Lab'              => 'admin/pages/class-page-lab.php',
        'Dental_Page_Patient_Dashboard'=> 'admin/pages/class-page-patient-dashboard.php',

        // مالی
        'Dental_Installment_Manager'   => 'includes/modules/financial/class-installment-manager.php',

        // SMS
        'Dental_SMS_Dispatcher'        => 'includes/modules/sms/class-sms-dispatcher.php',

        // RBAC
        'Dental_Roles_Manager'         => 'includes/modules/rbac/class-roles-manager.php',

        // API
        'Dental_API_Router'            => 'includes/api/class-api-router.php',
        'Dental_Endpoint_Auth'         => 'includes/api/endpoints/class-endpoint-auth.php',
        'Dental_Endpoint_Chart'        => 'includes/api/endpoints/class-endpoint-chart.php',
        'Dental_Endpoint_Financial'    => 'includes/api/endpoints/class-endpoint-financial.php',

        // Helpers
        'Dental_Jalali'                => 'includes/helpers/class-jalali-date.php',
        'Dental_Dev_Logger'            => 'includes/helpers/class-dev-logger.php',

        // Frontend
        'Dental_Consent_Form'          => 'includes/modules/consent/class-consent-form.php',
        'Dental_Ledger_Manager'        => 'includes/modules/ledger/class-ledger-manager.php',
        'Dental_Page_Ledger'           => 'admin/pages/class-page-ledger.php',
        'Dental_Reception_Manager'     => 'includes/modules/reception/class-reception-manager.php',
        'Dental_Page_Reception'        => 'admin/pages/class-page-reception.php',
        'Dental_Page_Doctor_Desk'      => 'admin/pages/class-page-doctor-desk.php',
        'Dental_Workspace_Manager'     => 'includes/modules/workspace/class-workspace-manager.php',
        'Dental_Attendance_Manager'    => 'includes/modules/attendance/class-attendance-manager.php',
        'Dental_Page_Attendance'       => 'admin/pages/class-page-attendance.php',
        'Dental_Doctor_Activity'       => 'includes/modules/doctor-activity/class-doctor-activity.php',
        'Dental_Service_Catalog'       => 'includes/modules/catalog/class-service-catalog.php',
        'Dental_Catalog_Seed_Data'     => 'includes/modules/catalog/class-catalog-seed-data.php',
        'Dental_Page_Catalog_Pricing'  => 'admin/pages/class-page-catalog-pricing.php',
        'Dental_Page_Performance_Report' => 'admin/pages/class-page-performance-report.php',
        'Dental_Shift_Scheduler'       => 'includes/modules/shift-scheduler/class-shift-scheduler.php',
        'Dental_Page_Shift_Rooms'      => 'admin/pages/class-page-shift-rooms.php',
        'Dental_Page_Monthly_Rota'     => 'admin/pages/class-page-monthly-rota.php',
        'Dental_Page_Shift_Weekly'     => 'admin/pages/class-page-shift-weekly.php',
        'Dental_Page_Shift_Hub'        => 'admin/pages/class-page-shift-hub.php',
        'Dental_Inventory_Manager'      => 'includes/modules/inventory/class-inventory-manager.php',
        'Dental_Page_Inventory_Hub'     => 'admin/pages/class-page-inventory-hub.php',
        'Dental_Page_Inventory_Items'   => 'admin/pages/class-page-inventory-items.php',
        'Dental_Page_Inventory_Log'     => 'admin/pages/class-page-inventory-log.php',
        'Dental_Page_Inventory_Request' => 'admin/pages/class-page-inventory-request.php',
        'Dental_Inventory_Seed_Data'    => 'includes/modules/inventory/class-inventory-seed-data.php',
        'Dental_Changelog'              => 'includes/class-changelog.php',
        'Dental_Recall_Manager'         => 'includes/modules/recall/class-recall-manager.php',
        'Dental_Audit_Log'              => 'includes/class-audit-log.php',
        'Dental_Page_Audit_Log'         => 'admin/pages/class-page-audit-log.php',
        'Dental_Insurance_Manager'      => 'includes/modules/insurance/class-insurance-manager.php',
        'Dental_Page_Insurance'         => 'admin/pages/class-page-insurance.php',
        'Dental_Page_Insurance_Settlement' => 'admin/pages/class-page-insurance-settlement.php',
        'Dental_Imaging_Manager'        => 'includes/modules/imaging/class-imaging-manager.php',
        'Dental_Page_Imaging'           => 'admin/pages/class-page-imaging.php',
        'Dental_Prescription_Manager'   => 'includes/modules/prescription/class-prescription-manager.php',
        'Dental_Page_Drugs'             => 'admin/pages/class-page-drugs.php',
        'Dental_Pathway_Manager'        => 'includes/modules/pathway/class-pathway-manager.php',
        'Dental_Endodontic_Manager'     => 'includes/modules/pathway/class-endodontic-manager.php',
        'Dental_Page_Pathway'           => 'admin/pages/class-page-pathway.php',
        'Dental_Page_Pathway_Templates' => 'admin/pages/class-page-pathway-templates.php',
        'Dental_Page_Recall'            => 'admin/pages/class-page-recall.php',
        'Dental_Portal_Medical_History' => 'frontend/class-portal-medical-history.php',
        'Dental_Analytics_Manager'      => 'includes/modules/analytics/class-analytics-manager.php',
        'Dental_Backup_Manager'         => 'includes/modules/backup/class-backup-manager.php',
        'Dental_Page_Analytics'         => 'admin/pages/class-page-analytics.php',
        'Dental_Page_Messages'          => 'admin/pages/class-page-messages.php',
        'Dental_Page_Invoice_Print'    => 'admin/pages/class-page-invoice-print.php',
        'Dental_Page_Doctor_Activity'  => 'admin/pages/class-page-doctor-activity.php',
        'Dental_POS_Gateway'           => 'includes/modules/pos/class-pos-gateway.php',
        'Dental_Features'              => 'includes/class-dental-features.php',
        'Dental_Setup_Wizard'          => 'admin/class-setup-wizard.php',
        'Dental_Universal_Login'       => 'frontend/class-universal-login.php',
        'Dental_Consent_Print'         => 'includes/modules/consent/class-consent-print.php',
        'Dental_Portal_Consent'        => 'frontend/portal/class-portal-consent.php',
        'Dental_Page_Consent_Admin'    => 'admin/pages/class-page-consent-admin.php',
        'Dental_SMS_Cron'              => 'includes/modules/sms/class-sms-cron.php',
        'Dental_Payment_Gateway'       => 'includes/modules/payment/class-payment-gateway.php',
        'Dental_Frontend'              => 'frontend/class-dental-frontend.php',
        'Dental_Portal_Dashboard'      => 'frontend/portal/class-portal-dashboard.php',
        'Dental_Portal_Record'         => 'frontend/portal/class-portal-record.php',
        'Dental_Portal_Financial'      => 'frontend/portal/class-portal-financial.php',
        'Dental_Portal_Wallet'         => 'frontend/portal/class-portal-wallet.php',
        'Dental_Portal_Profile'        => 'frontend/portal/class-portal-profile.php',
        'Dental_Portal_Print'          => 'frontend/portal/class-portal-print.php',

        // Admin
        'Dental_Admin'                 => 'admin/class-dental-admin.php',
        'Dental_Page_Patients'         => 'admin/pages/class-page-patients.php',
        'Dental_Page_Patient_Profile'  => 'admin/pages/class-page-patient-profile.php',
        'Dental_Page_Financial'        => 'admin/pages/class-page-financial.php',
        'Dental_Page_Settings'         => 'admin/pages/class-page-settings.php',
    ];

    public static function register(): void {
        spl_autoload_register([self::class, 'load']);
    }

    public static function load(string $class_name): void {
        if (!isset(self::$class_map[$class_name])) return;
        $file = DENTAL_CORE_PATH . self::$class_map[$class_name];
        if (file_exists($file)) require_once $file;
    }
}
