<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Refunds_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get refund by ID
     * @param  int $id refund id
     * @return object|null
     */
    public function get($id)
    {
        $this->db->where('id', $id);
        $refund = $this->db->get(db_prefix() . 'refunds')->row();

        if ($refund) {
            // Get payment mode name if exists
            if ($refund->refund_mode) {
                $this->db->where('id', $refund->refund_mode);
                $mode = $this->db->get(db_prefix() . 'payment_modes')->row();
                $refund->payment_mode_name = $mode ? $mode->name : '';
            } else {
                $refund->payment_mode_name = '';
            }

            // Get staff name
            $refund->staff_name = get_staff_full_name($refund->staffid);
        }

        return $refund;
    }

    /**
     * Get all refunds for an invoice
     * @param  int $invoiceid invoice id
     * @return array
     */
    public function get_invoice_refunds($invoiceid)
    {
        $this->db->where('invoiceid', $invoiceid);
        $this->db->order_by('date', 'DESC');
        $refunds = $this->db->get(db_prefix() . 'refunds')->result_array();

        foreach ($refunds as $key => $refund) {
            // Get payment mode name
            if ($refund['refund_mode']) {
                $this->db->where('id', $refund['refund_mode']);
                $mode = $this->db->get(db_prefix() . 'payment_modes')->row();
                $refunds[$key]['payment_mode_name'] = $mode ? $mode->name : '';
            } else {
                $refunds[$key]['payment_mode_name'] = '';
            }

            // Get staff name
            $refunds[$key]['staff_name'] = get_staff_full_name($refund['staffid']);
        }

        return $refunds;
    }

    /**
     * Get total refunded amount for an invoice
     * @param  int $invoiceid invoice id
     * @return float
     */
    public function get_total_refunded($invoiceid)
    {
        $this->db->select_sum('refund_amount');
        $this->db->where('invoiceid', $invoiceid);
        $result = $this->db->get(db_prefix() . 'refunds')->row();

        return $result->refund_amount ? (float)$result->refund_amount : 0;
    }

    /**
     * Calculate refund amount based on type and value
     * @param  object $invoice invoice object
     * @param  string $type 'amount' or 'percentage'
     * @param  float $value refund value
     * @return float|false calculated amount or false if invalid
     */
    public function calculate_refund_amount($invoice, $type, $value)
    {
        if ($type === 'amount') {
            return (float)$value;
        } elseif ($type === 'percentage') {
            return ($invoice->total * (float)$value) / 100;
        }

        return false;
    }

    /**
     * Get maximum refundable amount for an invoice
     * @param  int $invoiceid invoice id
     * @return float
     */
    public function get_refundable_amount($invoiceid)
    {
        // Get total paid
        $this->db->select_sum('amount');
        $this->db->where('invoiceid', $invoiceid);
        $payments_result = $this->db->get(db_prefix() . 'invoicepayments')->row();
        $total_paid = $payments_result->amount ? (float)$payments_result->amount : 0;

        // Get total refunded
        $total_refunded = $this->get_total_refunded($invoiceid);

        // Calculate refundable amount
        return $total_paid - $total_refunded;
    }

    /**
     * Add a new refund
     * @param  array $data refund data
     * @return int|bool refund id or false
     */
    public function add($data)
    {
        // Validate required fields
        if (!isset($data['invoiceid']) || !isset($data['refund_type']) || !isset($data['refund_value'])) {
            return false;
        }

        // Get invoice details
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($data['invoiceid']);

        if (!$invoice) {
            return false;
        }

        // Calculate refund amount
        $refund_amount = $this->calculate_refund_amount($invoice, $data['refund_type'], $data['refund_value']);

        if ($refund_amount === false || $refund_amount <= 0) {
            set_alert('danger', _l('invalid_refund_amount'));
            return false;
        }

        // Check if refund exceeds refundable amount
        $refundable_amount = $this->get_refundable_amount($data['invoiceid']);

        if ($refund_amount > $refundable_amount) {
            set_alert('danger', _l('refund_exceeds_paid_amount'));
            return false;
        }

        // Prepare data for insertion
        $insert_data = [
            'invoiceid'      => $data['invoiceid'],
            'paymentid'      => isset($data['paymentid']) ? $data['paymentid'] : null,
            'refund_amount'  => $refund_amount,
            'refund_type'    => $data['refund_type'],
            'refund_value'   => $data['refund_value'],
            'refund_mode'    => isset($data['refund_mode']) ? $data['refund_mode'] : null,
            'transaction_id' => isset($data['transaction_id']) ? $data['transaction_id'] : null,
            'date'           => isset($data['date']) ? $data['date'] : date('Y-m-d'),
            'note'           => isset($data['note']) ? $data['note'] : null,
            'staffid'        => isset($data['staffid']) ? $data['staffid'] : get_staff_user_id(),
            'datecreated'    => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . 'refunds', $insert_data);
        $refund_id = $this->db->insert_id();

        if ($refund_id) {
            // Log activity
            $this->load->model('invoices_model');
            $this->invoices_model->log_invoice_activity($data['invoiceid'], 'invoice_refund_recorded', false, serialize([
                '<refund_amount>' => app_format_money($refund_amount, $invoice->currency_name),
                '<refund_type>'   => _l($data['refund_type']),
            ]));

            // Clear invoice cache
            $this->app_object_cache->delete('invoice-' . $data['invoiceid']);

            hooks()->do_action('after_refund_added', $refund_id);

            return $refund_id;
        }

        return false;
    }

    /**
     * Update a refund
     * @param  array $data refund data
     * @param  int $id refund id
     * @return bool
     */
    public function update($data, $id)
    {
        $refund = $this->get($id);

        if (!$refund) {
            return false;
        }

        // Get invoice details
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($refund->invoiceid);

        if (!$invoice) {
            return false;
        }

        // Recalculate refund amount if type or value changed
        if (isset($data['refund_type']) || isset($data['refund_value'])) {
            $type = isset($data['refund_type']) ? $data['refund_type'] : $refund->refund_type;
            $value = isset($data['refund_value']) ? $data['refund_value'] : $refund->refund_value;

            $refund_amount = $this->calculate_refund_amount($invoice, $type, $value);

            if ($refund_amount !== false && $refund_amount > 0) {
                $data['refund_amount'] = $refund_amount;
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'refunds', $data);

        if ($this->db->affected_rows() > 0) {
            // Log activity
            $this->invoices_model->log_invoice_activity($refund->invoiceid, 'invoice_refund_updated');

            // Clear invoice cache
            $this->app_object_cache->delete('invoice-' . $refund->invoiceid);

            hooks()->do_action('after_refund_updated', $id);

            return true;
        }

        return false;
    }

    /**
     * Delete a refund
     * @param  int $id refund id
     * @return bool
     */
    public function delete($id)
    {
        $refund = $this->get($id);

        if (!$refund) {
            return false;
        }

        hooks()->do_action('before_refund_deleted', $id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'refunds');

        if ($this->db->affected_rows() > 0) {
            // Log activity
            $this->load->model('invoices_model');
            $this->invoices_model->log_invoice_activity($refund->invoiceid, 'invoice_refund_deleted', false, serialize([
                '<refund_amount>' => $refund->refund_amount,
            ]));

            // Clear invoice cache
            $this->app_object_cache->delete('invoice-' . $refund->invoiceid);

            hooks()->do_action('after_refund_deleted', $id);

            return true;
        }

        return false;
    }

    /**
     * Send refund notification email to customer
     * @param  int $refund_id refund id
     * @return bool
     */
    public function send_refund_notification($refund_id)
    {
        $refund = $this->get($refund_id);

        if (!$refund) {
            return false;
        }

        // Load invoice
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($refund->invoiceid);

        if (!$invoice) {
            return false;
        }

        // Load email class
        $this->load->model('emails_model');

        // Get contacts
        $contacts = $this->clients_model->get_contacts($invoice->clientid, ['active' => 1, 'invoice_emails' => 1]);

        $sent = false;

        foreach ($contacts as $contact) {
            // Prepare email data
            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($invoice->clientid, $contact['id']));

            $this->emails_model->set_rel_id($refund_id);
            $this->emails_model->set_rel_type('refund');

            $template = mail_template('invoice_refund_notification', $invoice, $contact, $merge_fields);

            // Add refund data to template
            $template->add_merge_fields('refund_amount', app_format_money($refund->refund_amount, $invoice->currency_name));
            $template->add_merge_fields('refund_date', _d($refund->date));
            $template->add_merge_fields('refund_note', $refund->note);
            $template->add_merge_fields('transaction_id', $refund->transaction_id);

            if ($template->send()) {
                $sent = true;
            }
        }

        return $sent;
    }
}
