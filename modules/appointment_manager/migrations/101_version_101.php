<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        add_option('am_g_accesstoken');
        add_option('am_g_refreshtoken');
        add_option('am_g_tokenexpirein');
        add_option('appmgr_calendar_id');
        $CI = &get_instance();
        $aptTable = db_prefix() . 'appmgr_appointments';
        if (!$CI->db->field_exists('gcal_eventid', $aptTable)) {
            $CI->db->query("ALTER TABLE `" . $aptTable . "` ADD `gcal_eventid` varchar(255) DEFAULT NULL;");
        }
        if (total_rows(db_prefix() . 'emailtemplates', ['type' => 'appointment_manager', 'slug' => 'appointment-manager-appointment-status-change-to-client']) == 0) {
            $CI->db->query("INSERT INTO `" . db_prefix() . "emailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
                ('appointment_manager',	'appointment-manager-appointment-status-change-to-client',	'english',	'Appointment Other status (Status Notification send To client)',	'{treatment}',	'Hi {client_company}! <br /><br />This is a notification of your appointment <a href=\\\"{appointment_link}\\\">{treatment}</a> which was scheduled at {appointment_date} has {status}. <br /><br />Regards.',	'',	'',	0,	1,	0)");
        }
    }
}
