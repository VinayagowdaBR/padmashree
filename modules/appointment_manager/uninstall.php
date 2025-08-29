<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if ($CI->db->table_exists(db_prefix() . 'appmgr_holidays')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_holidays`');
}
if ($CI->db->table_exists(db_prefix() . 'appmgr_locations')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_locations`');
}
if ($CI->db->table_exists(db_prefix() . 'appmgr_appointments')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_appointments`');
}
if ($CI->db->table_exists(db_prefix() . 'appmgr_appointies')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_appointies`');
}
if ($CI->db->table_exists(db_prefix() . 'appmgr_appointee_availibility')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_appointee_availibility`');
}
if ($CI->db->table_exists(db_prefix() . 'appmgr_treatments')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_treatments`');
}
if (total_rows(db_prefix() . 'emailtemplates', ['type' => 'appointment_manager'])) {
    $CI->db->query("DELETE FROM `" . db_prefix() . "emailtemplates` WHERE `type` = 'appointment_manager'");
} 
if ($CI->db->table_exists(db_prefix() . 'appmgr_appointment_status')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_appointment_status`');
}
if ($CI->db->table_exists(db_prefix() . 'appmgr_appointee_unavailibility')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_appointee_unavailibility`');
}
delete_option('am_g_accesstoken');
delete_option('am_g_refreshtoken');
delete_option('am_g_tokenexpirein');
delete_option('appmgr_calendar_id');
if ($CI->db->table_exists(db_prefix() . 'appmgr_service_cats')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'appmgr_service_cats`');
}