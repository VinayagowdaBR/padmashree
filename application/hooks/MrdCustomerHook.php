<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MrdCustomerHook
{
    public function after_customer_created($data)
    {
        log_message('error', '[HOOK] after_customer_created method triggered');
        
        // Get CI instance
        $CI =& get_instance();
        
        // Check if we have the customer ID from the $data array
        $customer_id = isset($data['userid']) ? $data['userid'] : null;

        if (!$customer_id) {
            log_message('error', '[MRD-HOOK] No customer ID found.');
            return;
        }

        log_message('error', '[MRD-HOOK] Customer ID: ' . $customer_id);

        $field_id = 86; // MRD No field ID

        // Check if MRD No already exists for the customer
        $exists = $CI->db->get_where(db_prefix() . 'customfieldsvalues', [
            'relid'   => $customer_id,
            'fieldid' => $field_id,
            'fieldto' => 'customers',
        ])->row();

        if (!$exists) {
            // Generate MRD No
            $mrd_no = 'MRD-' . str_pad($customer_id, 6, '0', STR_PAD_LEFT);

            // Insert MRD No into the database
            $CI->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid'   => $customer_id,
                'fieldid' => $field_id,
                'fieldto' => 'customers',
                'value'   => $mrd_no
            ]);

            log_message('error', '[MRD-HOOK] MRD No Created: ' . $mrd_no);
        } else {
            log_message('error', '[MRD-HOOK] MRD No already exists for Customer ID: ' . $customer_id);
        }
    }
}
