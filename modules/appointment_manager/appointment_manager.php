<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Appointment manager
Description: Module for defining appointments via calendar
Version: 1.0.2
Requires at least: 2.3.*
Author: Zonvoir Technologies Pvt Ltd.
*/

define('APPOINTMENT_MANAGER_MODULE_NAME', 'appointment_manager');
hooks()->add_action('admin_init', 'appointment_manager_module_init_menu_items');
/**
 * Register activation module hook
 */
register_activation_hook(APPOINTMENT_MANAGER_MODULE_NAME, 'appointment_manager_module_activation_hook');
function appointment_manager_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}
register_uninstall_hook(APPOINTMENT_MANAGER_MODULE_NAME, 'appointment_manager_module_uninstall_hook');
function appointment_manager_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/uninstall.php');
}
/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(APPOINTMENT_MANAGER_MODULE_NAME, [APPOINTMENT_MANAGER_MODULE_NAME]);
function appointment_manager_module_init_menu_items()
{
    $CI = &get_instance();
    if (staff_can('view', 'settings')) {

        $perfex_version = get_app_version();
        if (version_compare($perfex_version, '3.2.1', '<')) {
            $CI->app_tabs->add_settings_tab('appointment_manager', [
                'name'     => _l('appointment_manager'),
                'view'     => 'appointment_manager/settings/g_setting',
                'position' => 36,
            ]);
        } else {
            $CI->app->add_settings_section('appointment_manager', [
                'title' => _l('appointment_manager'),
                'position' => 36,
                'children' => []
            ]);
            $CI->app->add_settings_section_child('appointment_manager', 'appmgr_google', [
                'name'     => 'Google Auth',
                'icon'     => 'fa-brands fa-google',
                'position' => 5,
                'view' => 'appointment_manager/settings/g_setting'
            ]);
        }
    }
    $CI->app_menu->add_sidebar_menu_item('appointment_manager', [
        'name'     => _l('appointment_manager'),
        'icon'     => 'fa-solid fa-calendar',
        'position' => 5,
    ]);
    $CI->app_menu->add_sidebar_children_item('appointment_manager', [
        'slug'     => 'appmgr_dashboard',
        'name'     => _l('appmgr_dashboard'),
        'icon'     => 'fa-solid fa-gauge',
        'href'     => admin_url('appointment_manager'),
        'position' => 10,
    ]);
    $CI->app_menu->add_sidebar_children_item('appointment_manager', [
        'slug'     => 'appmgr_appointments',
        'name'     => _l('appmgr_appointments'),
        'icon'     => 'fa-solid fa-calendar',
        'href'     => admin_url('appointment_manager/all_appointments'),
        'position' => 15,
    ]);
    $CI->app_menu->add_sidebar_children_item('appointment_manager', [
        'slug'     => 'appmgr_holidays',
        'name'     => _l('appmgr_holidays'),
        'icon'     => 'fa-solid fa-mug-hot',
        'href'     => admin_url('appointment_manager/holidays'),
        'position' => 20,
    ]);

    $CI->app_menu->add_sidebar_children_item('appointment_manager', [
        'slug'     => 'appmgr_locations',
        'name'     => _l('appmgr_locations'),
        'icon'     => 'fa-solid fa-location-dot',
        'href'     => admin_url('appointment_manager/locations'),
        'position' => 25,
    ]);
    $CI->app_menu->add_sidebar_children_item('appointment_manager', [
        'slug'     => 'appmgr_appointies',
        'name'     => _l('appmgr_appointies'),
        'icon'     => 'fa-solid fa-user',
        'href'     => admin_url('appointment_manager/appointies'),
        'position' => 30,
    ]);
    $CI->app_menu->add_sidebar_children_item('appointment_manager', [
        'slug'     => 'appmgr_treatments',
        'name'     => _l('appmgr_treatments'),
        'icon'     => 'fa-solid fa-taxi',
        'href'     => admin_url('appointment_manager/services'),
        'position' => 31,
    ]);
    $CI->app_tabs->add_customer_profile_tab('appointment_manager', [
        'name'     => _l('appointment_manager'),
        'view'     => 'appointment_manager/client_appointments',
        'position' => 37,
        'icon'     => 'fa-solid fa-gauge'
    ]);
    $CI->app_menu->add_setup_menu_item('appointment-manager-setup', [
        'collapse' => true,
        'name'     => _l('appointment_manager'),
        'position' => 66,
    ]);

    $CI->app_menu->add_setup_children_item('appointment-manager-setup', [
        'slug'     => 'ta-appointment-status',
        'name'     => _l('appmgr_appointment_statuses'),
        'href'     => admin_url('appointment_manager/statuses'),
        'position' => 5,
    ]);
    $CI->app_menu->add_sidebar_children_item('appointment_manager', [
        'slug' => 'appmgr_reports',
        'name' => _l('appmgr_reports'),
        'icon' => 'fa  fa-file',
        'href' => admin_url('appointment_manager/appmgr_reports'),
        'position' => 40,
    ]);
    $CI->app_scripts->add(APPOINTMENT_MANAGER_MODULE_NAME . '-js', base_url('modules/' . APPOINTMENT_MANAGER_MODULE_NAME . '/assets/js/' . APPOINTMENT_MANAGER_MODULE_NAME . '.js'));
    $CI->app_scripts->add(APPOINTMENT_MANAGER_MODULE_NAME . 'popper-js', base_url('modules/' . APPOINTMENT_MANAGER_MODULE_NAME . '/assets/js/popper.min.js'));
    $capabilities['capabilities'] = [
        'view_own' => _l('permission_view_own'),
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create'   => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
        'approve' => _l('appmgr_permission_approve'),
    ];
    register_staff_capabilities(APPOINTMENT_MANAGER_MODULE_NAME, $capabilities, _l('appointment_manager'));
}
register_merge_fields('appointment_manager/merge_fields/appointment_manager_merge_fields');
$CI = &get_instance();
$CI->load->helper(APPOINTMENT_MANAGER_MODULE_NAME . '/appointment_manager');
hooks()->add_filter('calendar_data', 'core_calandar_handle', 10, 2);
hooks()->add_action('after_cron_run', 'send_appmgr_appointments_reminder');
hooks()->add_action('after_email_templates', 'render_appointment_manager_templates');
hooks()->add_filter('other_merge_fields_available_for', 'appointment_manager_register_other_merge_fields');
hooks()->add_filter('available_merge_fields', 'appointment_manager_allow_staff_merge_fields_for_appointment_manager_reminder_templates');
hooks()->add_filter('available_merge_fields', 'appointment_manager_allow_client_merge_fields_for_appointment_manager_reminder_templates');
hooks()->add_filter('get_dashboard_widgets', 'render_appointment_manager_dashboard_widget');
hooks()->add_action('app_admin_assets', function () {
    $CI = get_instance();
    $CI->app_css->add(APPOINTMENT_MANAGER_MODULE_NAME . '-css', base_url('modules/' . APPOINTMENT_MANAGER_MODULE_NAME . '/assets/css/' . APPOINTMENT_MANAGER_MODULE_NAME . '.css?v=' . $CI->app_css->core_version()));
});
$CI->app_scripts->theme(
    'appointment-manager-client-js',
    base_url('modules/' . APPOINTMENT_MANAGER_MODULE_NAME . '/assets/js/appointment_manager_clients.js') . '?v=' . $CI->app_css->core_version()
);
hooks()->add_action('after_cron_run', 'update_dates_for_new_month');
hooks()->add_action('after_custom_fields_select_options', 'am_custom_fileds_select');
$perfex_version = get_app_version();
if (version_compare($perfex_version, '3.2.1', '<')) {
    hooks()->add_filter('before_settings_updated', 'handle_post_settings_appmgr');
} else {
    hooks()->add_action('before_update_system_options', 'handle_post_settings_appmgr');
}
hooks()->add_action('app_customers_head', function () {
    echo '<link rel="manifest" href="' . base_url('modules/' . APPOINTMENT_MANAGER_MODULE_NAME . '/manifest.json') . '">';
});
