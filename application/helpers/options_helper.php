<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get an option from the options table.
 *
 * @param string $option_name The name of the option to retrieve.
 * @return string|null The option value, or null if not found.
 */
function get_option($option_name) {
    $CI =& get_instance();  // Access the CI instance
    $CI->load->database();  // Load the database

    // Query the database for the option
    $CI->db->select('value');
    $CI->db->from('tbloptions');  // Replace with your actual table name
    $CI->db->where('name', $option_name);
    $query = $CI->db->get();

    // If the option is found, return its value
    if ($query->num_rows() > 0) {
        return $query->row()->value;
    }

    // Return null if the option is not found
    return NULL; // or you can return a default value if needed
}
