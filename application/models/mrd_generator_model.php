<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mrd_generator_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate and assign an MRD number to a customer
     *
     * @param int $customer_id
     */
    public function generate_mrd_for_customer($customer_id)
{
    // Check if the customer already has an MRD number
    $this->db->where('relid', $customer_id);
    $this->db->where('fieldid', 88); // Field ID for MRD No
    $existing_mrd = $this->db->get(db_prefix() . 'customfieldsvalues')->row();

    // If MRD number already exists for the customer, exit function
    if ($existing_mrd) {
        return; // No need to insert or update if the MRD number already exists
    }

    // Get the highest MRD number by excluding the "MRD-" prefix (if any)
    $this->db->select("value");
    $this->db->where("fieldid", 88); // Custom field ID for MRD No
    $this->db->where("fieldto", "customers");
    $query = $this->db->get(db_prefix() . "customfieldsvalues");

    // Fetch all MRD numbers and extract the numeric part
    $mrd_numbers = $query->result_array();
    $max_mrd = 0;

    foreach ($mrd_numbers as $mrd) {
        // Extract the numeric part of the MRD number (ignoring "MRD-" prefix)
        $mrd_number = substr($mrd['value'], 4);  // Remove "MRD-" part
        if (is_numeric($mrd_number) && (int)$mrd_number > $max_mrd) {
            $max_mrd = (int)$mrd_number;
        }
    }

    // If no MRD numbers exist, start with MRD-000001
    if ($max_mrd === 0) {
        $new_mrd = "MRD-" . str_pad(1, 6, "0", STR_PAD_LEFT);
    } else {
        // Generate the new MRD number by incrementing the last one
        $new_mrd = "MRD-" . str_pad($max_mrd + 1, 6, "0", STR_PAD_LEFT);
    }

    // Insert the new MRD number into the customfieldsvalues table
    $this->db->insert(db_prefix() . "customfieldsvalues", [
        "relid"   => $customer_id,
        "fieldto" => "customers",
        "fieldid" => 88, // Your MRD custom field ID
        "value"   => $new_mrd,
    ]);
}


    
}
