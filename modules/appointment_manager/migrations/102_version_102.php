<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $aptTable = db_prefix() . 'appmgr_appointments';
        if (!$CI->db->field_exists('service_cat', $aptTable)) {
            $CI->db->query("ALTER TABLE `" . $aptTable . "` ADD `service_cat` varchar(250) DEFAULT NULL AFTER `treatment`;");
        }
        if (!$CI->db->table_exists(db_prefix() . 'appmgr_service_cats')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "appmgr_service_cats` (
                `id` int(11) NOT NULL,
                `service_id` int(11) NOT NULL,
                `name` varchar(100) NOT NULL,
                `added_at` date NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'appmgr_service_cats` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT');
        }
    }
}
