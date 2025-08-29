<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_filter('custom_field_render_input', 'populate_affiliate_dropdown_custom_field', 10, 2);

function populate_affiliate_dropdown_custom_field($field, $field_attrs) {
    // Only apply to field ID 90 on leads, and only if it's a dropdown
    if ($field->id == 90 && $field->fieldto == 'leads' && $field->type == 'select') {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->select('id, first_name, last_name');
        $CI->db->from('tblaffiliate_users');
        $query = $CI->db->get()->result();

        $options = [];
        foreach ($query as $row) {
            $options[$row->id] = $row->first_name . ' ' . $row->last_name;
        }

        $field_attrs['options'] = $options;
    }

    return $field_attrs;
}

// Helper to fetch affiliate name
function get_affiliate_name_by_id($id) {
    $CI =& get_instance();
    $CI->load->database();

    $CI->db->select('first_name, last_name');
    $CI->db->from('tblaffiliate_users');
    $CI->db->where('id', $id);
    $row = $CI->db->get()->row();

    return $row ? $row->first_name . ' ' . $row->last_name : '';
}
