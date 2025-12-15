<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends AdminController
{
    /**
     * Codeigniter Instance
     * Expenses detailed report filters use $ci
     * @var object
     */
    private $ci;

    public function __construct()
    {
        parent::__construct();
        if (staff_cant('view', 'reports')) {
            access_denied('reports');
        }
        $this->ci = &get_instance();
        $this->load->model('reports_model');
    }

    /* No access on this url */
    public function index()
    {
        redirect(admin_url());
    }

    /* See knowledge base article reports*/
    public function knowledge_base_articles()
    {
        $this->load->model('knowledge_base_model');
        $data['groups'] = $this->knowledge_base_model->get_kbg();
        $data['title']  = _l('kb_reports');
        $this->load->view('admin/reports/knowledge_base_articles', $data);
    }

    /*
        public function tax_summary(){
           $this->load->model('taxes_model');
           $this->load->model('payments_model');
           $this->load->model('invoices_model');
           $data['taxes'] = $this->db->query("SELECT DISTINCT taxname,taxrate FROM ".db_prefix()."item_tax WHERE rel_type='invoice'")->result_array();
            $this->load->view('admin/reports/tax_summary',$data);
        }*/
    /* Repoert leads conversions */
    public function leads()
    {
        $type = 'leads';
        if ($this->input->get('type')) {
            $type                       = $type . '_' . $this->input->get('type');
            $data['leads_staff_report'] = json_encode($this->reports_model->leads_staff_report());
        }
        $this->load->model('leads_model');
        $data['statuses']               = $this->leads_model->get_status();
        $data['leads_this_week_report'] = json_encode($this->reports_model->leads_this_week_report());
        $data['leads_sources_report']   = json_encode($this->reports_model->leads_sources_report());
        $this->load->view('admin/reports/' . $type, $data);
    }

    /* Sales reportts */
    public function sales()
    {
        $data['mysqlVersion'] = $this->db->query('SELECT VERSION() as version')->row();
        $data['sqlMode']      = $this->db->query('SELECT @@sql_mode as mode')->row();

        if (is_using_multiple_currencies() || is_using_multiple_currencies(db_prefix() . 'creditnotes') || is_using_multiple_currencies(db_prefix() . 'estimates') || is_using_multiple_currencies(db_prefix() . 'proposals')) {
            $this->load->model('currencies_model');
            $data['currencies'] = $this->currencies_model->get();
        }
        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');
        $this->load->model('credit_notes_model');

        $data['credit_notes_statuses'] = $this->credit_notes_model->get_statuses();
        $data['invoice_statuses']      = $this->invoices_model->get_statuses();
        $data['estimate_statuses']     = $this->estimates_model->get_statuses();
        $data['payments_years']        = $this->reports_model->get_distinct_payments_years();
        $data['estimates_sale_agents'] = $this->estimates_model->get_sale_agents();

        $data['invoices_sale_agents'] = $this->invoices_model->get_sale_agents();

        $data['proposals_sale_agents'] = $this->proposals_model->get_sale_agents();
        $data['proposals_statuses']    = $this->proposals_model->get_statuses();

        $data['invoice_taxes']     = $this->distinct_taxes('invoice');
        $data['estimate_taxes']    = $this->distinct_taxes('estimate');
        $data['proposal_taxes']    = $this->distinct_taxes('proposal');
        $data['credit_note_taxes'] = $this->distinct_taxes('credit_note');

        $data['title'] = _l('sales_reports');
        $this->load->view('admin/reports/sales', $data);
    }

    /* Customer report */
    public function customers_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $select = [
                get_sql_select_client_company(),
                '(SELECT COUNT(clientid) FROM ' . db_prefix() . 'invoices WHERE ' . db_prefix() . 'invoices.clientid = ' . db_prefix() . 'clients.userid AND status != 5)',
                '(SELECT SUM(subtotal) - SUM(discount_total) FROM ' . db_prefix() . 'invoices WHERE ' . db_prefix() . 'invoices.clientid = ' . db_prefix() . 'clients.userid AND status != 5)',
                '(SELECT SUM(total) FROM ' . db_prefix() . 'invoices WHERE ' . db_prefix() . 'invoices.clientid = ' . db_prefix() . 'clients.userid AND status != 5)',
            ];

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                $i = 0;
                foreach ($select as $_select) {
                    if ($i !== 0) {
                        $_temp = substr($_select, 0, -1);
                        $_temp .= ' ' . $custom_date_select . ')';
                        $select[$i] = $_temp;
                    }
                    $i++;
                }
            }
            $by_currency = $this->input->post('report_currency');
            $currency    = $this->currencies_model->get_base_currency();
            if ($by_currency) {
                $i = 0;
                foreach ($select as $_select) {
                    if ($i !== 0) {
                        $_temp = substr($_select, 0, -1);
                        $_temp .= ' AND currency =' . $this->db->escape_str($by_currency) . ')';
                        $select[$i] = $_temp;
                    }
                    $i++;
                }
                $currency = $this->currencies_model->get($by_currency);
            }
            $aColumns     = $select;
            $sIndexColumn = 'userid';
            $sTable       = db_prefix() . 'clients';
            $where        = [];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [
                'userid',
            ]);
            $output  = $result['output'];
            $rResult = $result['rResult'];
            $x       = 0;
            foreach ($rResult as $aRow) {
                $row = [];
                for ($i = 0; $i < count($aColumns); $i++) {
                    if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                        $_data = $aRow[strafter($aColumns[$i], 'as ')];
                    } else {
                        $_data = $aRow[$aColumns[$i]];
                    }
                    if ($i == 0) {
                        $_data = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . $aRow['company'] . '</a>';
                    } elseif ($aColumns[$i] == $select[2] || $aColumns[$i] == $select[3]) {
                        if ($_data == null) {
                            $_data = 0;
                        }
                        $_data = app_format_money($_data, $currency->name);
                    }
                    $row[] = $_data;
                }
                $output['aaData'][] = $row;
                $x++;
            }
            echo json_encode($output);
            die();
        }
    }

    public function payments_received()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('payment_modes_model');
            $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
            $select           = [
                db_prefix() . 'invoicepaymentrecords.id',
                db_prefix() . 'invoicepaymentrecords.date',
                'invoiceid',
                get_sql_select_client_company(),
                'paymentmode',
                'transactionid',
                'note',
                'amount',
            ];
            $where = [
                'AND status != 5',
            ];

            $custom_date_select = $this->get_where_report_period(db_prefix() . 'invoicepaymentrecords.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'invoicepaymentrecords';
            $join         = [
                'JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid',
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
                'LEFT JOIN ' . db_prefix() . 'payment_modes ON ' . db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode',
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'number',
                'clientid',
                db_prefix() . 'payment_modes.name',
                db_prefix() . 'payment_modes.id as paymentmodeid',
                'paymentmethod',
                'deleted_customer_name',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data['total_amount'] = 0;
            foreach ($rResult as $aRow) {
                $row = [];
                for ($i = 0; $i < count($aColumns); $i++) {
                    if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                        $_data = $aRow[strafter($aColumns[$i], 'as ')];
                    } else {
                        $_data = $aRow[$aColumns[$i]];
                    }
                    if ($aColumns[$i] == 'note') {
                        $_data = process_text_content_for_display($aRow['note']);
                    } else if ($aColumns[$i] == 'paymentmode') {
                        $_data = $aRow['name'];
                        if (is_null($aRow['paymentmodeid'])) {
                            foreach ($payment_gateways as $gateway) {
                                if ($aRow['paymentmode'] == $gateway['id']) {
                                    $_data = e($gateway['name']);
                                }
                            }
                        }
                        if (!empty($aRow['paymentmethod'])) {
                            $_data .= ' - ' . e($aRow['paymentmethod']);
                        }
                    } elseif ($aColumns[$i] == db_prefix() . 'invoicepaymentrecords.id') {
                        $_data = '<a href="' . admin_url('payments/payment/' . $_data) . '" target="_blank">' . e($_data) . '</a>';
                    } elseif ($aColumns[$i] == db_prefix() . 'invoicepaymentrecords.date') {
                        $_data = e(_d($_data));
                    } elseif ($aColumns[$i] == 'invoiceid') {
                        $_data = '<a href="' . admin_url('invoices/list_invoices/' . $aRow[$aColumns[$i]]) . '" target="_blank">' . e(format_invoice_number($aRow['invoiceid'])) . '</a>';
                    } elseif ($i == 3) {
                        if (empty($aRow['deleted_customer_name'])) {
                            $_data = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . e($aRow['company']) . '</a>';
                        } else {
                            $row[] = e($aRow['deleted_customer_name']);
                        }
                    } elseif ($aColumns[$i] == 'amount') {
                        $footer_data['total_amount'] += $_data;
                        $_data = e(app_format_money($_data, $currency->name));
                    }

                    $row[] = $_data;
                }
                $output['aaData'][] = $row;
            }

            $footer_data['total_amount'] = e(app_format_money($footer_data['total_amount'], $currency->name));
            $output['sums']              = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function proposals_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('proposals_model');

            $proposalsTaxes    = $this->distinct_taxes('proposal');
            $totalTaxesColumns = count($proposalsTaxes);

            $select = [
                'id',
                'subject',
                'proposal_to',
                'date',
                'open_till',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                'status',
            ];

            $proposalsTaxesSelect = array_reverse($proposalsTaxes);

            foreach ($proposalsTaxesSelect as $key => $tax) {
                array_splice($select, 8, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
                    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
                    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
                    END
                    FROM ' . db_prefix() . 'itemable
                    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
                    WHERE ' . db_prefix() . 'itemable.rel_type="proposal" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'proposals.id) as total_tax_single_' . $key);
            }

            $where              = [];
            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('proposal_status')) {
                $statuses  = $this->input->post('proposal_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            if ($this->input->post('proposals_sale_agents')) {
                $agents  = $this->input->post('proposals_sale_agents');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND assigned IN (' . implode(', ', $_agents) . ')');
                }
            }


            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'proposals';
            $join         = [];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'rel_id',
                'rel_type',
                'discount_percent',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'          => 0,
                'subtotal'       => 0,
                'total_tax'      => 0,
                'discount_total' => 0,
                'adjustment'     => 0,
            ];

            foreach ($proposalsTaxes as $key => $tax) {
                $footer_data['total_tax_single_' . $key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('proposals/list_proposals/' . $aRow['id']) . '" target="_blank">' . e(format_proposal_number($aRow['id'])) . '</a>';

                $row[] = '<a href="' . admin_url('proposals/list_proposals/' . $aRow['id']) . '" target="_blank">' . e($aRow['subject']) . '</a>';

                if ($aRow['rel_type'] == 'lead') {
                    $row[] = '<a href="#" onclick="init_lead(' . $aRow['rel_id'] . ');return false;" target="_blank" data-toggle="tooltip" data-title="' . _l('lead') . '">' . e($aRow['proposal_to']) . '</a>' . '<span class="hide">' . _l('lead') . '</span>';
                } elseif ($aRow['rel_type'] == 'customer') {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['rel_id']) . '" target="_blank" data-toggle="tooltip" data-title="' . _l('client') . '">' . e($aRow['proposal_to']) . '</a>' . '<span class="hide">' . _l('client') . '</span>';
                } else {
                    $row[] = '';
                }

                $row[] = e(_d($aRow['date']));

                $row[] = e(_d($aRow['open_till']));

                $row[] = e(app_format_money($aRow['subtotal'], $currency->name));
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = e(app_format_money($aRow['total'], $currency->name));
                $footer_data['total'] += $aRow['total'];

                $row[] = e(app_format_money($aRow['total_tax'], $currency->name));
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach ($proposalsTaxes as $tax) {
                    $row[] = e(app_format_money(($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]), $currency->name));
                    $footer_data['total_tax_single_' . $i] += ($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]);
                    $t--;
                    $i++;
                }

                $row[] = e(app_format_money($aRow['discount_total'], $currency->name));
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = e(app_format_money($aRow['adjustment'], $currency->name));
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[]              = format_proposal_status($aRow['status']);
                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = e(app_format_money($total, $currency->name));
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function estimates_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('estimates_model');

            $estimateTaxes     = $this->distinct_taxes('estimate');
            $totalTaxesColumns = count($estimateTaxes);

            $select = [
                'number',
                get_sql_select_client_company(),
                'invoiceid',
                'YEAR(date) as year',
                'date',
                'expirydate',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                'reference_no',
                'status',
            ];

            $estimatesTaxesSelect = array_reverse($estimateTaxes);

            foreach ($estimatesTaxesSelect as $key => $tax) {
                array_splice($select, 9, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
                    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
                    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
                    END
                    FROM ' . db_prefix() . 'itemable
                    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
                    WHERE ' . db_prefix() . 'itemable.rel_type="estimate" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'estimates.id) as total_tax_single_' . $key);
            }

            $where              = [];
            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('estimate_status')) {
                $statuses  = $this->input->post('estimate_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            if ($this->input->post('sale_agent_estimates')) {
                $agents  = $this->input->post('sale_agent_estimates');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'estimates';
            $join         = [
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'estimates.clientid',
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'userid',
                'clientid',
                db_prefix() . 'estimates.id',
                'discount_percent',
                'deleted_customer_name',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'          => 0,
                'subtotal'       => 0,
                'total_tax'      => 0,
                'discount_total' => 0,
                'adjustment'     => 0,
            ];

            foreach ($estimateTaxes as $key => $tax) {
                $footer_data['total_tax_single_' . $key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('estimates/list_estimates/' . $aRow['id']) . '" target="_blank">' . e(format_estimate_number($aRow['id'])) . '</a>';

                if (empty($aRow['deleted_customer_name'])) {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . e($aRow['company']) . '</a>';
                } else {
                    $row[] = e($aRow['deleted_customer_name']);
                }

                if ($aRow['invoiceid'] === null) {
                    $row[] = '';
                } else {
                    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '" target="_blank">' . e(format_invoice_number($aRow['invoiceid'])) . '</a>';
                }

                $row[] = $aRow['year'];

                $row[] = e(_d($aRow['date']));

                $row[] = e(_d($aRow['expirydate']));

                $row[] = e(app_format_money($aRow['subtotal'], $currency->name));
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = e(app_format_money($aRow['total'], $currency->name));
                $footer_data['total'] += $aRow['total'];

                $row[] = e(app_format_money($aRow['total_tax'], $currency->name));
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach ($estimateTaxes as $tax) {
                    $row[] = e(app_format_money(($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]), $currency->name));
                    $footer_data['total_tax_single_' . $i] += ($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]);
                    $t--;
                    $i++;
                }

                $row[] = e(app_format_money($aRow['discount_total'], $currency->name));
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = e(app_format_money($aRow['adjustment'], $currency->name));
                $footer_data['adjustment'] += $aRow['adjustment'];


                $row[] = e($aRow['reference_no']);

                $row[] = format_estimate_status($aRow['status']);

                $output['aaData'][] = $row;
            }
            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = e(app_format_money($total, $currency->name));
            }
            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    private function get_where_report_period($field = 'date')
    {
        $months_report      = $this->input->post('report_months');
        $custom_date_select = '';
        if ($months_report != '') {
            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                date('Y-m-d', strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = 'AND ' . $field . ' = "' . $this->db->escape_str($from_date) . '"';
                } else {
                    $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '")';
                }
            }
        }

        return $custom_date_select;
    }

    public function items()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $v = $this->db->query('SELECT VERSION() as version')->row();
            // 5.6 mysql version don't have the ANY_VALUE function implemented.

            if ($v && strpos($v->version, '5.7') !== false) {
                $aColumns = [
                        'ANY_VALUE(description) as description',
                        'ANY_VALUE((SUM(' . db_prefix() . 'itemable.qty))) as quantity_sold',
                        'ANY_VALUE(SUM(rate*qty)) as rate',
                        'ANY_VALUE(AVG(rate*qty)) as avg_price',
                    ];
            } else {
                $aColumns = [
                        'description as description',
                        '(SUM(' . db_prefix() . 'itemable.qty)) as quantity_sold',
                        'SUM(rate*qty) as rate',
                        'AVG(rate*qty) as avg_price',
                    ];
            }

            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'itemable';
            $join         = ['JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'itemable.rel_id'];

            $where = ['AND rel_type="invoice"', 'AND status != 5', 'AND status=2'];

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }
            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('sale_agent_items')) {
                $agents  = $this->input->post('sale_agent_items');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], 'GROUP by description');

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total_amount' => 0,
                'total_qty'    => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = e($aRow['description']);
                $row[] = $aRow['quantity_sold'];
                $row[] = e(app_format_money($aRow['rate'], $currency->name));
                $row[] = e(app_format_money($aRow['avg_price'], $currency->name));
                $footer_data['total_amount'] += $aRow['rate'];
                $footer_data['total_qty'] += $aRow['quantity_sold'];
                $output['aaData'][] = $row;
            }

            $footer_data['total_amount'] = e(app_format_money($footer_data['total_amount'], $currency->name));

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function credit_notes()
    {
        if ($this->input->is_ajax_request()) {
            $credit_note_taxes = $this->distinct_taxes('credit_note');
            $totalTaxesColumns = count($credit_note_taxes);

            $this->load->model('currencies_model');

            $select = [
                'number',
                'date',
                get_sql_select_client_company(),
                'reference_no',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                '(SELECT ' . db_prefix() . 'creditnotes.total - (
                  (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.credit_id=' . db_prefix() . 'creditnotes.id)
                  +
                  (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'creditnote_refunds WHERE ' . db_prefix() . 'creditnote_refunds.credit_note_id=' . db_prefix() . 'creditnotes.id)
                  )
                ) as remaining_amount',
                '(SELECT SUM(amount) FROM  ' . db_prefix() . 'creditnote_refunds WHERE credit_note_id=' . db_prefix() . 'creditnotes.id) as refund_amount',
                'status',
            ];

            $where = [];
          

            $credit_note_taxes_select = array_reverse($credit_note_taxes);

            foreach ($credit_note_taxes_select as $key => $tax) {
                array_splice($select, 5, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
                    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
                    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
                    END
                    FROM ' . db_prefix() . 'itemable
                    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
                    WHERE ' . db_prefix() . 'itemable.rel_type="credit_note" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'creditnotes.id) as total_tax_single_' . $key);
            }

            $custom_date_select = $this->get_where_report_period();

            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');

            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('credit_note_status')) {
                $statuses  = $this->input->post('credit_note_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'creditnotes';
            $join         = [
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'creditnotes.clientid',
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'userid',
                'clientid',
                db_prefix() . 'creditnotes.id',
                'discount_percent',
                'deleted_customer_name',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'            => 0,
                'subtotal'         => 0,
                'total_tax'        => 0,
                'discount_total'   => 0,
                'adjustment'       => 0,
                'remaining_amount' => 0,
                'refund_amount'    => 0,
            ];

            foreach ($credit_note_taxes as $key => $tax) {
                $footer_data['total_tax_single_' . $key] = 0;
            }
            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('credit_notes/list_credit_notes/' . $aRow['id']) . '" target="_blank">' . e(format_credit_note_number($aRow['id'])) . '</a>';

                $row[] = e(_d($aRow['date']));

                if (empty($aRow['deleted_customer_name'])) {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>';
                } else {
                    $row[] = e($aRow['deleted_customer_name']);
                }

                $row[] = e($aRow['reference_no']);

                $row[] = e(app_format_money($aRow['subtotal'], $currency->name));
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = e(app_format_money($aRow['total'], $currency->name));
                $footer_data['total'] += $aRow['total'];

                $row[] = e(app_format_money($aRow['total_tax'], $currency->name));
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach ($credit_note_taxes as $tax) {
                    $row[] = e(app_format_money(($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]), $currency->name));
                    $footer_data['total_tax_single_' . $i] += ($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]);
                    $t--;
                    $i++;
                }

                $row[] = e(app_format_money($aRow['discount_total'], $currency->name));
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = e(app_format_money($aRow['adjustment'], $currency->name));
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[] = e(app_format_money($aRow['remaining_amount'], $currency->name));
                $footer_data['remaining_amount'] += $aRow['remaining_amount'];

                $row[] = e(app_format_money($aRow['refund_amount'], $currency->name));
                $footer_data['refund_amount'] += $aRow['refund_amount'];

                $row[] = format_credit_note_status($aRow['status']);

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = e(app_format_money($total, $currency->name));
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function invoices_report()
    {
        if ($this->input->is_ajax_request()) {
            $invoice_taxes     = $this->distinct_taxes('invoice');
            $totalTaxesColumns = count($invoice_taxes);

            $this->load->model('currencies_model');
            $this->load->model('invoices_model');

            $select = [
                'number',
                get_sql_select_client_company(),
                'YEAR(date) as year',
                'date',
                'duedate',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id) as credits_applied',
                '(SELECT total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id))',
                'status',
                
               
            ];

            $where = [
                'AND status != 5',
            ];

            $invoiceTaxesSelect = array_reverse($invoice_taxes);

            foreach ($invoiceTaxesSelect as $key => $tax) {
                array_splice($select, 8, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
                    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
                    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
                    END
                    FROM ' . db_prefix() . 'itemable
                    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
                    WHERE ' . db_prefix() . 'itemable.rel_type="invoice" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'invoices.id) as total_tax_single_' . $key);
            }

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('sale_agent_invoices')) {
                $agents  = $this->input->post('sale_agent_invoices');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            $by_currency              = $this->input->post('report_currency');
            $totalPaymentsColumnIndex = (12 + $totalTaxesColumns - 1);

            if ($by_currency) {
                $_temp = substr($select[$totalPaymentsColumnIndex], 0, -2);
                $_temp .= ' AND currency =' . $by_currency . ')) as amount_open';
                $select[$totalPaymentsColumnIndex] = $_temp;

                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency                          = $this->currencies_model->get_base_currency();
                $select[$totalPaymentsColumnIndex] = $select[$totalPaymentsColumnIndex] .= ' as amount_open';
            }

            if ($this->input->post('invoice_status')) {
                $statuses  = $this->input->post('invoice_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'invoices';
            $join         = [
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',

              
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'userid',
                'clientid',
                db_prefix() . 'invoices.id',
                'discount_percent',
                'deleted_customer_name',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'           => 0,
                'subtotal'        => 0,
                'total_tax'       => 0,
                'discount_total'  => 0,
                'adjustment'      => 0,
                'applied_credits' => 0,
                'amount_open'     => 0,
            ];

            foreach ($invoice_taxes as $key => $tax) {
                $footer_data['total_tax_single_' . $key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . e(format_invoice_number($aRow['id'])) . '</a>';

                if (empty($aRow['deleted_customer_name'])) {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . e($aRow['company']) . '</a>';
                } else {
                    $row[] = e($aRow['deleted_customer_name']);
                }

             

                $row[] = $aRow['year'];

                $row[] = e(_d($aRow['date']));

                $row[] = e(_d($aRow['duedate']));

                $row[] = e(app_format_money($aRow['subtotal'], $currency->name));
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = e(app_format_money($aRow['total'], $currency->name));
                $footer_data['total'] += $aRow['total'];

                $row[] = e(app_format_money($aRow['total_tax'], $currency->name));
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach ($invoice_taxes as $tax) {
                    $row[] = e(app_format_money(($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]), $currency->name));
                    $footer_data['total_tax_single_' . $i] += ($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]);
                    $t--;
                    $i++;
                }

                $row[] = e(app_format_money($aRow['discount_total'], $currency->name));
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = e(app_format_money($aRow['adjustment'], $currency->name));
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[] = e(app_format_money($aRow['credits_applied'], $currency->name));
                $footer_data['applied_credits'] += $aRow['credits_applied'];

                $amountOpen = $aRow['amount_open'];
                $row[]      = e(app_format_money($amountOpen, $currency->name));
                $footer_data['amount_open'] += $amountOpen;

                $row[] = format_invoice_status($aRow['status']);

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = e(app_format_money($total, $currency->name));
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function expenses($type = 'simple_report')
    {
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['currencies']    = $this->currencies_model->get();

        $data['title'] = _l('expenses_report');
        if ($type != 'simple_report') {
            $this->load->model('expenses_model');
            $data['categories'] = $this->expenses_model->get_category();
            $data['years']      = $this->expenses_model->get_expenses_years();
            
            $data['table'] = App_table::find('expenses_detailed_report');

            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', [], true);

            if ($this->input->is_ajax_request()) {
                $data['table']->output([
                    'base_currency'=>$data['base_currency'],
                    'currencies'=>$data['currencies'],
                ]);
            }

            $this->load->view('admin/reports/expenses_detailed', $data);
        } else {
            if (!$this->input->get('year')) {
                $data['current_year'] = date('Y');
            } else {
                $data['current_year'] = $this->input->get('year');
            }

            $data['export_not_supported'] = ($this->agent->browser() == 'Internet Explorer' || $this->agent->browser() == 'Spartan');

            $this->load->model('expenses_model');

            $data['chart_not_billable'] = json_encode($this->reports_model->get_stats_chart_data(_l('not_billable_expenses_by_categories'), [
                'billable' => 0,
            ], [
                'backgroundColor' => 'rgba(252,45,66,0.4)',
                'borderColor'     => '#fc2d42',
            ], $data['current_year']));

            $data['chart_billable'] = json_encode($this->reports_model->get_stats_chart_data(_l('billable_expenses_by_categories'), [
                'billable' => 1,
            ], [
                'backgroundColor' => 'rgba(37,155,35,0.2)',
                'borderColor'     => '#84c529',
            ], $data['current_year']));

            $data['expense_years'] = $this->expenses_model->get_expenses_years();

            if (count($data['expense_years']) > 0) {
                // Perhaps no expenses in new year?
                if (!in_array_multidimensional($data['expense_years'], 'year', date('Y'))) {
                    array_unshift($data['expense_years'], ['year' => date('Y')]);
                }
            }

            $data['categories'] = $this->expenses_model->get_category();

            $this->load->view('admin/reports/expenses', $data);
        }
    }

    public function expenses_vs_income($year = '')
    {
        $_expenses_years = [];
        $_years          = [];
        $this->load->model('expenses_model');
        $expenses_years = $this->expenses_model->get_expenses_years();
        $payments_years = $this->reports_model->get_distinct_payments_years();

        foreach ($expenses_years as $y) {
            array_push($_years, $y['year']);
        }
        foreach ($payments_years as $y) {
            array_push($_years, $y['year']);
        }

        $_years = array_map('unserialize', array_unique(array_map('serialize', $_years)));

        if (!in_array(date('Y'), $_years)) {
            $_years[] = date('Y');
        }

        rsort($_years, SORT_NUMERIC);
        $data['report_year'] = $year == '' ? date('Y') : $year;

        $data['years']                           = $_years;
        $data['chart_expenses_vs_income_values'] = json_encode($this->reports_model->get_expenses_vs_income_report($year));
        $data['base_currency']                   = get_base_currency();
        $data['title']                           = _l('als_expenses_vs_income');
        $this->load->view('admin/reports/expenses_vs_income', $data);
    }

    /* Total income report / ajax chart*/
    public function total_income_report()
    {
        echo json_encode($this->reports_model->total_income_report());
    }

    public function report_by_payment_modes()
    {
        echo json_encode($this->reports_model->report_by_payment_modes());
    }

    public function report_by_customer_groups()
    {
        echo json_encode($this->reports_model->report_by_customer_groups());
    }

    /* Leads conversion monthly report / ajax chart*/
    public function leads_monthly_report($month)
    {
        echo json_encode($this->reports_model->leads_monthly_report($month));
    }

    private function distinct_taxes($rel_type)
    {
        return $this->db->query('SELECT DISTINCT taxname,taxrate FROM ' . db_prefix() . "item_tax WHERE rel_type='" . $rel_type . "' ORDER BY taxname ASC")->result_array();
    }

    public function balance_details()
    {
        if (!staff_can('view', 'invoices')) {
            access_denied('invoices');    
            exit;
        }
    
        $data['title'] = _l('Balance Details');
        $this->load->view('admin/reports/balance_details', $data);
    }

    
public function balance_details_table()
{
    if (!staff_can('view', 'invoices')) {
        access_denied('invoices');
        exit;
    }

    $this->load->model('invoices_model');
    $this->load->model('currencies_model');
    $currency = $this->currencies_model->get_base_currency();

    $from_date     = $this->input->post('report_from');
    $to_date       = $this->input->post('report_to');
    $mrd_from      = $this->input->post('mrd_from');
    $mrd_to        = $this->input->post('mrd_to');
    $referral_name = $this->input->post('referral_name');
    $client_name = $this->input->post('client_name');
    $client_mobile = $this->input->post('client_mobile');

    $where = [];

    if ($from_date && $to_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '"';
    } elseif ($from_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) >= "' . $this->db->escape_str($from_date) . '"';
    } elseif ($to_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) <= "' . $this->db->escape_str($to_date) . '"';
    }

    if ($mrd_from && $mrd_to) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid BETWEEN "' . $this->db->escape_str($mrd_from) . '" AND "' . $this->db->escape_str($mrd_to) . '"';
    } elseif ($mrd_from) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid >= "' . $this->db->escape_str($mrd_from) . '"';
    } elseif ($mrd_to) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid <= "' . $this->db->escape_str($mrd_to) . '"';
    }

    if ($referral_name) {
        $referral_name_esc = $this->db->escape_like_str($referral_name);
        $where[] = 'AND CONCAT(au.firstname, " ", au.lastname) LIKE "%' . $referral_name_esc . '%"';
    }

   // Show only invoices with balance > 0
// $where[] = '(' . db_prefix() . 'invoices.total - IFNULL((SELECT SUM(amount) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id), 0)) > 0';


    $aColumns = [
        'number',
        'tblinvoices.datecreated as invoice_datecreated',
        db_prefix() . 'invoices.number as invoice_number',
        db_prefix() . 'clients.company as client',
        db_prefix() . 'clients.userid as mrd_no',
        '(SELECT GROUP_CONCAT(description SEPARATOR ", ") FROM ' . db_prefix() . 'itemable WHERE rel_id = ' . db_prefix() . 'invoices.id AND rel_type = "invoice") as all_items',
        '(SELECT GROUP_CONCAT(long_description SEPARATOR ", ") FROM ' . db_prefix() . 'itemable WHERE rel_id = ' . db_prefix() . 'invoices.id AND rel_type = "invoice") as all_long_descriptions',
        db_prefix() . 'invoices.discount_total as discount',
        db_prefix() . 'invoices.total as total',
        'total',
        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
          JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
          WHERE cfd.name = "Mobile.no" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
          AND cfdv.fieldto = "invoice" LIMIT 1) as Mobile',
        'CONCAT(au.firstname, " ", au.lastname) as affiliate_user_name',
        'subtotal',
        '(SELECT SUM(amount) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as total_paid',
        '(' . db_prefix() . 'invoices.total - IFNULL((SELECT SUM(amount) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id), 0)) as balance',
    ];

    $sIndexColumn = 'id';
    $sTable       = db_prefix() . 'invoices';
    $join = [
        'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
        'LEFT JOIN ' . db_prefix() . 'affiliate_users AS au ON au.affiliate_code = ' . db_prefix() . 'clients.affiliate_code COLLATE utf8mb4_unicode_ci',
        'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
        'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'invoices.project_id',
    ];

    $clientid   = $this->input->post('customer_id');
    $project_id = $this->input->post('project_id');

    if (!empty($clientid)) {
        $where[] = db_prefix() . 'invoices.clientid=' . $this->db->escape($clientid);
    }

    if (!empty($project_id)) {
        $where[] = 'project_id=' . $this->db->escape($project_id);
    }

    if (staff_cant('view', 'invoices')) {
        $where[] = get_invoices_where_sql_for_staff(get_staff_user_id());
    }

    $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
        db_prefix() . 'invoices.id',
        db_prefix() . 'invoices.clientid',
        db_prefix() . 'currencies.name as currency_name',
        'formatted_number',
        'project_id',
        'hash',
        'recurring',
        'allowed_payment_modes',
    ]);

    $output  = $result['output'];
    $rResult = $result['rResult'];

    $serial_number = 1;

    // Totals
    $total_subtotal = 0;
    $total_discount = 0;
    $total_total = 0;
    $total_paid = 0;
    $total_balance = 0;

    foreach ($rResult as $aRow) {

        $balance = $aRow['balance'];
        if ($balance <= 0) {
            continue; // Only show rows where balance > 0
        }
        $row = [];

        $formattedNumber = format_invoice_number($aRow['id']);
        if (empty($aRow['formatted_number']) || $formattedNumber !== $aRow['formatted_number']) {
            $this->invoices_model->save_formatted_number($aRow['id']);
        }

        $row[] = $serial_number++;
        $row[] = '<a href="' . admin_url('invoices/invoice/' . $aRow['id']) . '" target="_blank">' . html_escape($formattedNumber) . '</a>';
        $row[] = !empty($aRow['invoice_datecreated']) ? date('d-m-Y h:i A', strtotime($aRow['invoice_datecreated'])) : '';
        $row[] = str_pad($aRow['mrd_no'], 0, '0', STR_PAD_LEFT);
        $row[] = !empty($aRow['clientid']) ? '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . html_escape($aRow['client']) . '</a>' : '-';
        $row[] = !empty($aRow['Mobile']) ? $aRow['Mobile'] : '-';
        $row[] = !empty($aRow['affiliate_user_name']) ? e($aRow['affiliate_user_name']) : 'N/A';
        $row[] = !empty($aRow['all_items']) ? e($aRow['all_items']) : '-';
        $row[] = !empty($aRow['all_long_descriptions']) ? e($aRow['all_long_descriptions']) : '-';

        $row[] = app_format_money($aRow['subtotal'], $currency->name);
        $row[] = app_format_money($aRow['discount'], $currency->name);
        $row[] = '<span class="tw-font-medium">' . app_format_money($aRow['total'], $aRow['currency_name']) . '</span>';
        $row[] = '<span class="tw-font-medium">' . app_format_money($aRow['total_paid'], $aRow['currency_name']) . '</span>';
        $row[] = '<span class="tw-font-medium">' . app_format_money($aRow['balance'], $aRow['currency_name']) . '</span>';

        // Accumulate totals
        $total_subtotal += $aRow['subtotal'];
        $total_discount += $aRow['discount'];
        $total_total    += $aRow['total'];
        $total_paid     += $aRow['total_paid'];
        $total_balance  += $aRow['balance'];

        $output['aaData'][] = $row;
    }

    // Add footer totals row
    $footerRow = [
        '', '', '', '', '', '', '', '', '<strong>Total:</strong>',
        '<strong>' . app_format_money($total_subtotal, $currency->name) . '</strong>',
        '<strong>' . app_format_money($total_discount, $currency->name) . '</strong>',
        '<strong>' . app_format_money($total_total, $currency->name) . '</strong>',
        '<strong>' . app_format_money($total_paid, $currency->name) . '</strong>',
        '<strong>' . app_format_money($total_balance, $currency->name) . '</strong>',
    ];

    $output['aaData'][] = $footerRow;

    echo json_encode($output);
}



    public function due_paid_details()
    {
    if (!staff_can('view', 'invoices')) {
        access_denied('invoices');
    }
    $data['title'] = _l('Due Paid Details');
    $this->load->view('admin/reports/due_paid_details', $data);
}

public function due_paid_details_table()
{
    if (!staff_can('view', 'invoices')) {
        access_denied('invoices');
        exit;
    }

      $invoiceTable = db_prefix() . 'invoices';
    $clientsTable = db_prefix() . 'clients';
    $itemableTable = db_prefix() . 'itemable';
    $paymentRecordsTable = db_prefix() . 'invoicepaymentrecords';
    $customFieldsValuesTable = db_prefix() . 'customfieldsvalues';
    $customFieldsTable = db_prefix() . 'customfields';
    $affiliateUsersTable = db_prefix() . 'affiliate_users';
    $staffTable = db_prefix() . 'staff';

    $this->load->model('invoices_model');
    $this->load->model('currencies_model');
    $currency = $this->currencies_model->get_base_currency();

    $from_date = $this->input->post('report_from');
    $to_date   = $this->input->post('report_to');
    $mrd_from  = $this->input->post('mrd_from');
    $mrd_to    = $this->input->post('mrd_to');
    $clientid  = $this->input->post('customer_id');
    $project_id = $this->input->post('project_id');
    $referral_name = $this->input->post('referral_name');
    

  $where[] = 'AND (' . $invoiceTable . '.total - IFNULL((SELECT SUM(amount) FROM ' . $paymentRecordsTable . ' WHERE invoiceid = ' . $invoiceTable . '.id), 0)) > 0';
    $where[] = 'AND (
    (SELECT amount FROM ' . db_prefix() . 'invoicepaymentrecords 
        WHERE invoiceid = ' . db_prefix() . 'invoices.id 
        ORDER BY date ASC, id ASC LIMIT 1
    ) != 
    (SELECT amount FROM ' . db_prefix() . 'invoicepaymentrecords 
        WHERE invoiceid = ' . db_prefix() . 'invoices.id 
        ORDER BY date DESC, id DESC LIMIT 1
    )
    )';

    if ($from_date && $to_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '"';
    } elseif ($from_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) >= "' . $this->db->escape_str($from_date) . '"';
    } elseif ($to_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) <= "' . $this->db->escape_str($to_date) . '"';
    }

    if ($mrd_from && $mrd_to) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid BETWEEN "' . $this->db->escape_str($mrd_from) . '" AND "' . $this->db->escape_str($mrd_to) . '"';
    } elseif ($mrd_from) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid >= "' . $this->db->escape_str($mrd_from) . '"';
    } elseif ($mrd_to) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid <= "' . $this->db->escape_str($mrd_to) . '"';
    }

      if ($referral_name) {
        $referral_name_esc = $this->db->escape_like_str($referral_name);
        $where[] = 'AND CONCAT(au.firstname, " ", au.lastname) LIKE "%' . $referral_name_esc . '%"';
    }


    if (staff_cant('view', 'invoices')) {
        $where[] = get_invoices_where_sql_for_staff(get_staff_user_id());
    }

    log_message('info', 'due_paid_details_table filters: from_date=' . $from_date . ', to_date=' . $to_date . ', mrd_no=, mrd_from=' . $mrd_from . ', mrd_to=' . $mrd_to . ', clientid=' . $clientid . ', project_id=' . $project_id);

    $aColumns = [
        'number',
      'tblinvoices.datecreated as invoice_datecreated',
        db_prefix() . 'invoices.number as invoice_number',
        db_prefix() . 'clients.company as client',

         "(SELECT pm.name 
        FROM " . db_prefix() . "invoicepaymentrecords pr
        JOIN " . db_prefix() . "payment_modes pm ON pr.paymentmode = pm.id
        WHERE pr.invoiceid = " . db_prefix() . "invoices.id 
        ORDER BY pr.date DESC, pr.id DESC 
        LIMIT 1) AS payment_mode",

        // Custom fields
        // Custom fields
        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.slug = "age_years" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as age_years',

        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.slug = "age_months" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as age_months',

        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.name = "Sex" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as Sex',

        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.name = "Mobile.no" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as Mobile',

        '(SELECT GROUP_CONCAT(pr.transactionid SEPARATOR ", ") 
            FROM ' . db_prefix() . 'invoicepaymentrecords pr
            WHERE pr.invoiceid = ' . $invoiceTable . '.id
            ) as payment_details',


        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.name = "Ref.By" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as Refer',

        'CONCAT(au.firstname, " ", au.lastname) as affiliate_user_name',

        "CONCAT($staffTable.firstname, ' ', $staffTable.lastname) as sales_agent_name",

        '(SELECT SUM(amount) FROM ' . db_prefix() . 'invoicepaymentrecords 
            WHERE invoiceid = ' . db_prefix() . 'invoices.id) as paid',

        '(' . db_prefix() . 'invoices.total - 
            (SELECT IFNULL(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords 
            WHERE invoiceid = ' . db_prefix() . 'invoices.id)) as due',

        db_prefix() . 'invoices.discount_total as discount',
        db_prefix() . 'invoices.total as total',
        'total',
        'subtotal',

        '(SELECT amount FROM ' . db_prefix() . 'invoicepaymentrecords 
            WHERE invoiceid = ' . db_prefix() . 'invoices.id 
            ORDER BY date ASC, id ASC LIMIT 1) as last_paid_amount',

        '(SELECT amount FROM ' . db_prefix() . 'invoicepaymentrecords 
            WHERE invoiceid = ' . db_prefix() . 'invoices.id 
            ORDER BY date DESC, id DESC LIMIT 1) as due_paid_amount',

        '(SELECT MAX(date) FROM ' . db_prefix() . 'invoicepaymentrecords 
            WHERE invoiceid = ' . db_prefix() . 'invoices.id) as last_payment_date',

         " (
  SELECT SUM(CAST(cfdv.value AS DECIMAL(10,2)))
  FROM $customFieldsValuesTable cfdv
  JOIN $customFieldsTable cfd ON cfdv.fieldid = cfd.id
  JOIN $itemableTable items ON items.id = cfdv.relid
  WHERE cfd.name = 'Serv.charge'
    AND cfdv.fieldto = 'items'
    AND items.rel_id = $invoiceTable.id
    AND items.rel_type = 'invoice'
) AS service_charge",

        db_prefix() . 'clients.userid as mrd_no',
    ];

    $sIndexColumn = 'id';
    $sTable       = db_prefix() . 'invoices';
    $join = [
        'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
        "LEFT JOIN " . db_prefix() . "staff ON " . $invoiceTable . ".sale_agent = " . db_prefix() . "staff.staffid",
        'LEFT JOIN ' . db_prefix() . 'affiliate_users AS au ON au.affiliate_code = ' . db_prefix() . 'clients.affiliate_code COLLATE utf8mb4_unicode_ci',
        'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
        'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'invoices.project_id',
    ];

    $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
        db_prefix() . 'invoices.id',
        db_prefix() . 'invoices.clientid',
        db_prefix() . 'currencies.name as currency_name',
        'formatted_number',
        'project_id',
        'hash',
        'recurring',
        'allowed_payment_modes',
    ]);

    $output  = $result['output'];
    $rResult = $result['rResult'];

    log_message('info', 'due_paid_details_table returned ' . count($rResult) . ' rows');

    $serial_number = 1;

    foreach ($rResult as $aRow) {
        $row = [];

        $formattedNumber = format_invoice_number($aRow['id']);
        if (empty($aRow['formatted_number']) || $formattedNumber !== $aRow['formatted_number']) {
            $this->invoices_model->save_formatted_number($aRow['id']);
        }

        $row[] = $serial_number++;
        $row[] = '<a href="' . admin_url('invoices/invoice/' . $aRow['id']) . '" target="_blank">' . html_escape($formattedNumber) . '</a>';
         $row[] = !empty($aRow['invoice_datecreated']) ? date('d-m-Y h:i A', strtotime($aRow['invoice_datecreated'])) : '';
        $row[] = str_pad($aRow['mrd_no'], 0, '0', STR_PAD_LEFT);
        $row[] = !empty($aRow['clientid']) ? '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . html_escape($aRow['client']) . '</a>' : '-';
        $age_years = !empty($aRow['age_years']) ? $aRow['age_years'] : '0';
        $age_months = !empty($aRow['age_months']) ? $aRow['age_months'] : '0';
        $row[] = $age_years . ' Y ' . $age_months . ' M';
        // $row[] = !empty($aRow['Age']) ? $aRow['Age'] : '-';
        $row[] = !empty($aRow['Sex']) ? $aRow['Sex'] : '-';
        $row[] = !empty($aRow['Mobile']) ? $aRow['Mobile'] : '-';
        $row[] = !empty($aRow['affiliate_user_name']) ? e($aRow['affiliate_user_name']) : 'N/A';
        $row[] = app_format_money($aRow['subtotal'], $currency->name);
        $row[] = app_format_money($aRow['discount'], $currency->name);
        $row[] = '<span class="tw-font-medium">' . app_format_money($aRow['total'], $aRow['currency_name']) . '</span>';
        
        // Normalize the payment mode string for comparison
        $payment_mode = strtolower(trim($aRow['payment_mode'] ?? ''));
        // Default to 0
        $service_charge_value = 0;
        // Only show service charge if UPI or Credit/Debit Card
        if ($payment_mode === 'upi' || $payment_mode === 'credit/debit card') {
            $service_charge_value = isset($aRow['service_charge']) && is_numeric($aRow['service_charge']) 
                ? $aRow['service_charge'] 
                : 0;
        }
        // Show formatted service charge
        $row[] = app_format_money($service_charge_value, $currency->name);

        $row[] = app_format_money($aRow['last_paid_amount'], $currency->name);
        $row[] = app_format_money($aRow['due_paid_amount'], $currency->name);
        $row[] = app_format_money($aRow['paid'], $currency->name);
        $row[] = app_format_money($aRow['due'], $currency->name);

              // Initialize used payment modes array
        $displayed_modes = [];
        $this->ci->load->model('payments_model');
        $invoice_payments = $this->ci->payments_model->get_invoice_payments($aRow['id']);
        if (!empty($invoice_payments)) {
            foreach ($invoice_payments as $payment) {
                $payment_mode = !empty($payment['paymentmethod']) ? $payment['paymentmethod'] : $payment['name'];
                if (!in_array($payment_mode, $displayed_modes)) {
                    $displayed_modes[] = $payment_mode;
                }
            }
        }
        $all_modes = !empty($displayed_modes) ? implode(', ', $displayed_modes) : '-';
        $row[] = $all_modes;

        $row[] = !empty($aRow['payment_details']) ? html_escape($aRow['payment_details']) : '-';
        $row[] = $aRow['sales_agent_name'] ?: 'N/A';
        $row[] = !empty($aRow['last_payment_date']) ? _d($aRow['last_payment_date']) : '-';

        $output['aaData'][] = $row;
    }

    $footer_data = [
        'total_amount'   => 0,
        'discount'       => 0,
         'bill_amount'    => 0,
        'service_charge' => 0,
        'last_paid'      => 0,
        'due_paid'       => 0,
        'total_paid'     => 0,
        'balance'        => 0,
    ];

    foreach ($rResult as $aRow) {
        $footer_data['bill_amount']    += floatval($aRow['subtotal']);
        $footer_data['discount']       += floatval($aRow['discount']);
        $footer_data['total_amount']   += floatval($aRow['total']);
        
        $payment_mode = strtolower(trim($aRow['payment_mode'] ?? ''));
        if (!in_array($payment_mode, ['upi', 'credit/debit card'])) {
            $service_charge = 0;             // Keep service charge only for UPI or Credit/Debit Card
        }
        $total_service_charge += $service_charge;

        $footer_data['service_charge'] += $service_charge_value;
        $footer_data['last_paid']      += floatval($aRow['last_paid_amount']);
        $footer_data['due_paid']       += floatval($aRow['due_paid_amount']);
        $footer_data['total_paid']     += floatval($aRow['paid']);
        $footer_data['balance']        += floatval($aRow['due']);

        
    }

    foreach ($footer_data as $key => $value) {
        $footer_data[$key] = app_format_money($value, $currency->name);
    }

    log_message('info', 'due_paid_details_table footer totals (formatted): ' . json_encode($footer_data));

    $output['sums'] = $footer_data;
    echo json_encode($output);
    die();
}

public function referral_details()
{
    if (!staff_can('view', 'invoices')) {
        access_denied('invoices');
    }

    $data['title'] = _l('Referral Details Report');
    $this->load->view('admin/reports/referral_details', $data);
}


    // public function referral_details_table()
    // {
    //     if (!staff_can('view', 'invoices')) {
    //         access_denied('invoices');
    //     }

    //     $invoiceTable = db_prefix() . 'invoices';
    //     $clientsTable = db_prefix() . 'clients';
    //     $itemableTable = db_prefix() . 'itemable';
    //     $paymentRecordsTable = db_prefix() . 'invoicepaymentrecords';
    //     $customFieldsValuesTable = db_prefix() . 'customfieldsvalues';
    //     $customFieldsTable = db_prefix() . 'customfields';
    //     $affiliateUsersTable = db_prefix() . 'affiliate_users';
    //     $staffTable = db_prefix() . 'staff';

    //     $this->load->model('invoices_model');
    //     $this->load->model('currencies_model');
    //     $currency = $this->currencies_model->get_base_currency();
    //     log_message('debug', 'Loaded base currency: ' . print_r($currency, true));

    //     // Filters from POST
    //     $from_date = $this->input->post('report_from');
    //     $to_date   = $this->input->post('report_to');
    //     $mrd_from  = $this->input->post('mrd_from');
    //     $mrd_to    = $this->input->post('mrd_to');
    //     $clientid  = $this->input->post('customer_id');
    //     $project_id = $this->input->post('project_id');
    //     $referral_name = $this->input->post('referral_name');

    //     $where = [];

    //     if ($from_date && $to_date) {
    //         $where[] = 'AND DATE(inv.date) BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '"';
    //     } elseif ($from_date) {
    //         $where[] = 'AND DATE(inv.date) >= "' . $this->db->escape_str($from_date) . '"';
    //     } elseif ($to_date) {
    //         $where[] = 'AND DATE(inv.date) <= "' . $this->db->escape_str($to_date) . '"';
    //     }

    //     if ($mrd_from && $mrd_to) {
    //         $where[] = 'AND c.userid BETWEEN "' . $this->db->escape_str($mrd_from) . '" AND "' . $this->db->escape_str($mrd_to) . '"';
    //     } elseif ($mrd_from) {
    //         $where[] = 'AND c.userid >= "' . $this->db->escape_str($mrd_from) . '"';
    //     } elseif ($mrd_to) {
    //         $where[] = 'AND c.userid <= "' . $this->db->escape_str($mrd_to) . '"';
    //     }

    //     if ($referral_name) {
    //         $referral_name_esc = $this->db->escape_like_str($referral_name);
    //         $where[] = 'AND CONCAT(IFNULL(au.firstname, ""), " ", IFNULL(au.lastname, "")) LIKE "%' . $referral_name_esc . '%"';
    //     }

    //     if (staff_cant('view', 'invoices')) {
    //         $where[] = get_invoices_where_sql_for_staff(get_staff_user_id());
    //     }

    //     $where[] = 'AND (inv.total - IFNULL((SELECT SUM(amount) FROM ' . $paymentRecordsTable . ' WHERE invoiceid = inv.id), 0)) = 0';

    // $sql = "
    //     SELECT 
    //         inv.id as invoice_id,
    //         inv.number as invoice_number,
    //         ii.rate AS item_fixed_rate,
    //         (ii.qty * ii.rate) AS item_subtotal,
    //         inv.discount_total,
    //         inv.discount_percent,
    //         inv.total,
    //         c.company as client_name,
    //         c.userid as mrd_no,
    //         au.firstname,
    //         au.lastname,
    //         ii.id as item_id,
    //         ii.description,
    //         ii.long_description,
    //         (SELECT MAX(date) FROM $paymentRecordsTable WHERE invoiceid = inv.id) as last_payment_date,
    //         (SELECT SUM(amount) FROM $paymentRecordsTable WHERE invoiceid = inv.id) as total_paid,
    //         cur.name as currency_name
    //     FROM $invoiceTable as inv
    //     LEFT JOIN $clientsTable as c ON c.userid = inv.clientid
    //     LEFT JOIN $affiliateUsersTable as au ON au.affiliate_code = c.affiliate_code COLLATE utf8mb4_unicode_ci
    //     LEFT JOIN " . db_prefix() . "currencies as cur ON cur.id = inv.currency
    //     LEFT JOIN $itemableTable as ii ON ii.rel_id = inv.id AND ii.rel_type = 'invoice'
    //     WHERE 1=1 " . implode(' ', $where) . "
    //     ORDER BY inv.id ASC, ii.id ASC
    // ";


    //     log_message('debug', 'Referral Details SQL: ' . $sql);

    //     $results = $this->db->query($sql)->result();

    //     log_message('debug', 'Total Results Found: ' . count($results));
    //     if (!empty($results)) {
    //         log_message('debug', 'Sample Row: ' . print_r($results[0], true));
    //     }

    //     $output['aaData'] = [];
    //     $serial = 1;
    //     $total_subtotal = 0;
    //     $total_discount = 0;
    //     $total_total = 0;
    //     $total_commission = 0;
    //     $total_balance = 0;
    //     $commission_custom_field_id = 91;

    //     $invoice_item_counts = [];
    //     foreach ($results as $r) {
    //         if (!isset($invoice_item_counts[$r->invoice_id])) {
    //             $invoice_item_counts[$r->invoice_id] = 0;
    //         }
    //         $invoice_item_counts[$r->invoice_id]++;
    //     }

    //     foreach ($results as $row) {
    //         log_message('debug', 'Processing item_id: ' . $row->item_id);

    //         $this->db->select('value');
    //         $this->db->from($customFieldsValuesTable);
    //         $this->db->where('relid', $row->item_id);
    //         $this->db->where('fieldid', $commission_custom_field_id);
    //         $this->db->where('fieldto', 'items');
    //         $cf_result = $this->db->get()->row();

    //         log_message('debug', 'Commission query: ' . $this->db->last_query());
    //         log_message('debug', 'Commission result: ' . print_r($cf_result, true));

    //         $commission = isset($cf_result->value) ? (float)$cf_result->value : 0;

    // // Subtotal for this item
    // $item_subtotal = (float)$row->item_fixed_rate;
    // $discount_percent = isset($row->discount_percent) ? (float)$row->discount_percent : 0;
    // $discount_total_item = $item_subtotal * ($discount_percent / 100);
    // $item_total_after_discount = $item_subtotal - $discount_total_item;
    // $commission_after_discount = max($commission - $discount_total_item, 0);


    // // Balance remains invoice-level
    // $balance = (float)$row->total - (float)$row->total_paid;

    //         $output['aaData'][] = [
    //     $serial++,
    //     '<a href="' . admin_url('invoices/invoice/' . $row->invoice_id) . '" target="_blank">' . format_invoice_number($row->invoice_id) . '</a>',
    //     _d($row->last_payment_date),
    //     str_pad($row->mrd_no, 0, '0', STR_PAD_LEFT),
    //     '<a href="' . admin_url('clients/client/' . $row->mrd_no) . '" target="_blank">' . html_escape($row->client_name) . '</a>',
    //     ($row->firstname || $row->lastname) ? html_escape($row->firstname . ' ' . $row->lastname) : 'N/A',
    //     html_escape($row->description),
    //     html_escape($row->long_description),
    //     app_format_money($item_subtotal, $currency->name),
    //     app_format_money($discount_total_item, $currency->name),
    //     app_format_money($item_total_after_discount, $currency->name),
    //     app_format_money($balance, $currency->name),
    //     '<span class="tw-text-green-600">' . app_format_money($commission_after_discount, $currency->name) . '</span>',
    // ];

    //         // Accumulate totals
    //             $total_subtotal += $item_subtotal;
    //             $total_discount += $discount_total_item;
    //             $total_total    += (float)$row->total;
    //             $total_balance  += $balance;
    //             $total_commission += $commission_after_discount;

    //     }

    //     $output['aaData'][] = [
    //         '', '', '', '', '', '', '', '<strong>Total:</strong>',
    //         '<strong>' . app_format_money($total_subtotal, $currency->name) . '</strong>',
    //         '<strong>' . app_format_money($total_discount, $currency->name) . '</strong>',
    //         '<strong>' . app_format_money($total_total, $currency->name) . '</strong>',
    //         '<strong>' . app_format_money($total_balance, $currency->name) . '</strong>',
    //         '<strong>' . app_format_money($total_commission, $currency->name) . '</strong>',
    //     ];

    //     log_message('debug', 'Final Totals: ' . json_encode([
    //         'subtotal' => $total_subtotal,
    //         'discount' => $total_discount,
    //         'total' => $total_total,
    //         'balance' => $total_balance,
    //         'commission' => $total_commission,
    //     ]));

    //     echo json_encode($output);
    // }


    public function referral_details_table()
    {
        log_message('debug', '🚀 Starting referral_details_table method');
    
        if (!staff_can('view', 'invoices')) {
            log_message('debug', '❌ Access denied for viewing invoices');
            access_denied('invoices');
        }
    
        $invoiceTable = db_prefix() . 'invoices';
        $clientsTable = db_prefix() . 'clients';
        $itemableTable = db_prefix() . 'itemable';
        $paymentRecordsTable = db_prefix() . 'invoicepaymentrecords';
        $customFieldsValuesTable = db_prefix() . 'customfieldsvalues';
        $customFieldsTable = db_prefix() . 'customfields';
        $affiliateUsersTable = db_prefix() . 'affiliate_users';
        $staffTable = db_prefix() . 'staff';
    
        $this->load->model('invoices_model');
        $this->load->model('currencies_model');
        $currency = $this->currencies_model->get_base_currency();
        log_message('debug', '💰 Loaded base currency: ' . print_r($currency, true));
    
        // Filters from POST
        $from_date = $this->input->post('report_from');
        $to_date   = $this->input->post('report_to');
        $mrd_from  = $this->input->post('mrd_from');
        $mrd_to    = $this->input->post('mrd_to');
        $clientid  = $this->input->post('customer_id');
        $project_id = $this->input->post('project_id');
        $referral_name = $this->input->post('referral_name');
    
        log_message('debug', '🎯 Received filters:');
        log_message('debug', '   - from_date: ' . $from_date);
        log_message('debug', '   - to_date: ' . $to_date);
        log_message('debug', '   - mrd_from: ' . $mrd_from);
        log_message('debug', '   - mrd_to: ' . $mrd_to);
        log_message('debug', '   - referral_name: ' . $referral_name);
    
        $where = [];
    
        if ($from_date && $to_date) {
            $where[] = 'AND DATE(inv.date) BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '"';
        } elseif ($from_date) {
            $where[] = 'AND DATE(inv.date) >= "' . $this->db->escape_str($from_date) . '"';
        } elseif ($to_date) {
            $where[] = 'AND DATE(inv.date) <= "' . $this->db->escape_str($to_date) . '"';
        }
    
        if ($mrd_from && $mrd_to) {
            $where[] = 'AND c.userid BETWEEN "' . $this->db->escape_str($mrd_from) . '" AND "' . $this->db->escape_str($mrd_to) . '"';
        } elseif ($mrd_from) {
            $where[] = 'AND c.userid >= "' . $this->db->escape_str($mrd_from) . '"';
        } elseif ($mrd_to) {
            $where[] = 'AND c.userid <= "' . $this->db->escape_str($mrd_to) . '"';
        }
    
        if ($referral_name) {
            $referral_name_esc = $this->db->escape_like_str($referral_name);
            $where[] = 'AND CONCAT(IFNULL(au.firstname, ""), " ", IFNULL(au.lastname, "")) LIKE "%' . $referral_name_esc . '%"';
        }
    
        if (staff_cant('view', 'invoices')) {
            $where[] = get_invoices_where_sql_for_staff(get_staff_user_id());
        }
    
        $where[] = 'AND (inv.total - IFNULL((SELECT SUM(amount) FROM ' . $paymentRecordsTable . ' WHERE invoiceid = inv.id), 0)) = 0';
    
        log_message('debug', '📋 WHERE conditions: ' . implode(' ', $where));
    
        $sql = "
        SELECT 
            inv.id as invoice_id,
            inv.number as invoice_number,
            (SELECT cfdv.value FROM tblcustomfieldsvalues cfdv 
            JOIN tblcustomfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.slug = 'age_years' AND cfdv.relid = inv.id 
            AND cfdv.fieldto = 'invoice' LIMIT 1) as age_years,
            (SELECT cfdv.value FROM tblcustomfieldsvalues cfdv 
            JOIN tblcustomfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.slug = 'age_months' AND cfdv.relid = inv.id 
            AND cfdv.fieldto = 'invoice' LIMIT 1) as age_months,
            ii.rate AS item_fixed_rate,
            (ii.qty * ii.rate) AS item_subtotal,
            inv.discount_total,
            inv.discount_percent,
            inv.total,
            inv.subtotal as invoice_subtotal,
            c.company as client_name,
            c.userid as mrd_no,
            au.firstname,
            au.lastname,
            ii.id as item_id,
            ii.description,
            ii.long_description,
            (SELECT MAX(date) FROM $paymentRecordsTable WHERE invoiceid = inv.id) as last_payment_date,
            (SELECT SUM(amount) FROM $paymentRecordsTable WHERE invoiceid = inv.id) as total_paid,
            cur.name as currency_name
        FROM $invoiceTable as inv
        LEFT JOIN $clientsTable as c ON c.userid = inv.clientid
        LEFT JOIN $affiliateUsersTable as au ON au.affiliate_code = c.affiliate_code COLLATE utf8mb4_unicode_ci
        LEFT JOIN " . db_prefix() . "currencies as cur ON cur.id = inv.currency
        LEFT JOIN $itemableTable as ii ON ii.rel_id = inv.id AND ii.rel_type = 'invoice'
        WHERE 1=1 " . implode(' ', $where) . "
        ORDER BY inv.id ASC, ii.id ASC
     ";
    
        log_message('debug', '🔍 Referral Details SQL: ' . $sql);
    
        $results = $this->db->query($sql)->result();
    
        log_message('debug', '📊 Total Results Found: ' . count($results));
        if (!empty($results)) {
            log_message('debug', '📄 Sample Row: ' . print_r($results[0], true));
        } else {
            log_message('debug', '📭 No results found for the given filters');
        }
    
        $output['aaData'] = [];
        $serial = 1;
        $total_subtotal = 0;
        $total_discount = 0;
        $total_total = 0;
        $total_commission = 0;
        $total_balance = 0;
        $commission_custom_field_id = 91;
    
        // Group results by invoice to calculate discount distribution per item
        $invoices_data = [];
        foreach ($results as $row) {
            if (!isset($invoices_data[$row->invoice_id])) {
                $invoices_data[$row->invoice_id] = [
                    'invoice_data' => $row,
                    'items' => [],
                    'total_items_subtotal' => 0
                ];
            }
            $invoices_data[$row->invoice_id]['items'][] = $row;
            $invoices_data[$row->invoice_id]['total_items_subtotal'] += (float)$row->item_subtotal;
        }
    
        log_message('debug', '🔄 Processing ' . count($invoices_data) . ' invoices:');
    
        foreach ($invoices_data as $invoice_id => $invoice_data) {
            $invoice_row = $invoice_data['invoice_data'];
            $items = $invoice_data['items'];
            $total_items_subtotal = $invoice_data['total_items_subtotal'];
            
            // Get the invoice discount total (like in the first method)
            $invoice_discount_total = (float)$invoice_row->discount_total;
            
            log_message('debug', "--- Processing Invoice {$invoice_id} ---");
            log_message('debug', '💰 Invoice discount_total: ' . $invoice_discount_total);
            log_message('debug', '💰 Invoice subtotal: ' . $invoice_row->invoice_subtotal);
            log_message('debug', '💰 Total items subtotal: ' . $total_items_subtotal);
    
            foreach ($items as $index => $row) {
                log_message('debug', "--- Processing Item {$index} for Invoice {$invoice_id} ---");
    
                log_message('debug', '🔍 Querying commission for item_id: ' . $row->item_id);
                
                $this->db->select('value');
                $this->db->from($customFieldsValuesTable);
                $this->db->where('relid', $row->item_id);
                $this->db->where('fieldid', $commission_custom_field_id);
                $this->db->where('fieldto', 'items');
                $cf_result = $this->db->get()->row();
    
                log_message('debug', '💰 Commission query: ' . $this->db->last_query());
                log_message('debug', '💰 Commission result: ' . print_r($cf_result, true));
    
                $commission = isset($cf_result->value) ? (float)$cf_result->value : 0;
                log_message('debug', '💰 Commission value: ' . $commission);
    
                // Calculate item's share of the discount (proportional to item subtotal)
                $item_subtotal = (float)$row->item_subtotal;
                $item_discount_share = 0;
                
                if ($total_items_subtotal > 0) {
                    $item_discount_share = ($item_subtotal / $total_items_subtotal) * $invoice_discount_total;
                }
                
                $item_total_after_discount = $item_subtotal - $item_discount_share;
                // $commission_after_discount = $commission ;
                // Commission after discount should be reduced by the item's discount share
                $commission_after_discount = max($commission - $item_discount_share, 0);

    
                // Balance remains invoice-level
                $balance = (float)$row->total - (float)$row->total_paid;
    
                log_message('debug', '🧮 Financial calculations:');
                log_message('debug', '   - Item subtotal: ' . $item_subtotal);
                log_message('debug', '   - Item discount share: ' . $item_discount_share);
                log_message('debug', '   - Item total after discount: ' . $item_total_after_discount);
                log_message('debug', '   - Commission after discount: ' . $commission_after_discount);
                log_message('debug', '   - Balance: ' . $balance);
    
                $output['aaData'][] = [
                    $serial++,
                    '<a href="' . admin_url('invoices/invoice/' . $row->invoice_id) . '" target="_blank">' . format_invoice_number($row->invoice_id) . '</a>',
                    _d($row->last_payment_date),
                    str_pad($row->mrd_no, 0, '0', STR_PAD_LEFT),
                    ($row->age_years ?? '0') . ' Y ' . ($row->age_months ?? '0') . ' M',
                    '<a href="' . admin_url('clients/client/' . $row->mrd_no) . '" target="_blank">' . html_escape($row->client_name) . '</a>',
                    ($row->firstname || $row->lastname) ? html_escape($row->firstname . ' ' . $row->lastname) : 'N/A',
                    html_escape($row->description),
                    html_escape($row->long_description),
                    app_format_money($item_subtotal, $currency->name),
                    app_format_money($item_discount_share, $currency->name),
                    app_format_money($item_total_after_discount, $currency->name),
                    app_format_money($balance, $currency->name),
                    '<span class="tw-text-green-600">' . app_format_money($commission_after_discount, $currency->name) . '</span>',
                ];
    
                // Accumulate totals
                $total_subtotal += $item_subtotal;
                $total_discount += $item_discount_share;
                $total_total    += ($index === 0) ? (float)$row->total : 0; // Add invoice total only once per invoice
                $total_balance  += ($index === 0) ? $balance : 0; // Add balance only once per invoice
                $total_commission += $commission_after_discount;
    
                log_message('debug', '📊 Current totals after item:');
                log_message('debug', '   - Total Subtotal: ' . $total_subtotal);
                log_message('debug', '   - Total Discount: ' . $total_discount);
                log_message('debug', '   - Total Total: ' . $total_total);
                log_message('debug', '   - Total Balance: ' . $total_balance);
                log_message('debug', '   - Total Commission: ' . $total_commission);
    
                log_message('debug', "✅ Item processed successfully");
            }
        }
    
        // Log final totals
        log_message('debug', '🎯 FINAL TOTALS:');
        log_message('debug', '   - Total Subtotal: ' . $total_subtotal);
        log_message('debug', '   - Total Discount: ' . $total_discount);
        log_message('debug', '   - Total Total: ' . $total_total);
        log_message('debug', '   - Total Balance: ' . $total_balance);
        log_message('debug', '   - Total Commission: ' . $total_commission);
    
        // Add totals row
        $output['aaData'][] = [
            '', '', '', '', '', '', '', '', '<strong>Total:</strong>',
            '<strong>' . app_format_money($total_subtotal, $currency->name) . '</strong>',
            '<strong>' . app_format_money($total_discount, $currency->name) . '</strong>',
            '<strong>' . app_format_money($total_total, $currency->name) . '</strong>',
            '<strong>' . app_format_money($total_balance, $currency->name) . '</strong>',
            '<strong>' . app_format_money($total_commission, $currency->name) . '</strong>',
        ];
    
        log_message('debug', '📋 Final output contains ' . count($output['aaData']) . ' rows');
        log_message('debug', '📄 Final output sample: ' . json_encode(array_slice($output['aaData'], 0, 2)));
    
        log_message('debug', '📤 Outputting JSON response');
        echo json_encode($output);
        
        log_message('debug', '✅ referral_details_table method completed successfully');
    }

// public function referral_details_table()
// {
//     if (!staff_can('view', 'invoices')) {
//         access_denied('invoices');
//     }

//     $invoiceTable = db_prefix() . 'invoices';
//     $clientsTable = db_prefix() . 'clients';
//     $itemableTable = db_prefix() . 'itemable';
//     $paymentRecordsTable = db_prefix() . 'invoicepaymentrecords';
//     $customFieldsValuesTable = db_prefix() . 'customfieldsvalues';
//     $customFieldsTable = db_prefix() . 'customfields';
//     $affiliateUsersTable = db_prefix() . 'affiliate_users';
//     $staffTable = db_prefix() . 'staff   ';


//     $this->load->model('invoices_model');
//     $this->load->model('currencies_model');
//     $currency = $this->currencies_model->get_base_currency();
//     log_message('debug', 'Loaded base currency: ' . print_r($currency, true));

//     // Filters from POST
//      $from_date = $this->input->post('report_from');
//     $to_date   = $this->input->post('report_to');
//     $mrd_from  = $this->input->post('mrd_from');
//     $mrd_to    = $this->input->post('mrd_to');
//     $clientid  = $this->input->post('customer_id');
//     $project_id = $this->input->post('project_id');
//     $referral_name = $this->input->post('referral_name');

//     $where = [];

//     if ($from_date && $to_date) {
//         $where[] = 'AND DATE(inv.date) BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '"';
//     } elseif ($from_date) {
//         $where[] = 'AND DATE(inv.date) >= "' . $this->db->escape_str($from_date) . '"';
//     } elseif ($to_date) {
//         $where[] = 'AND DATE(inv.date) <= "' . $this->db->escape_str($to_date) . '"';
//     }

//     if ($mrd_from && $mrd_to) {
//         $where[] = 'AND c.userid BETWEEN "' . $this->db->escape_str($mrd_from) . '" AND "' . $this->db->escape_str($mrd_to) . '"';
//     } elseif ($mrd_from) {
//         $where[] = 'AND c.userid >= "' . $this->db->escape_str($mrd_from) . '"';
//     } elseif ($mrd_to) {
//         $where[] = 'AND c.userid <= "' . $this->db->escape_str($mrd_to) . '"';
//     }

//     if ($referral_name) {
//     $referral_name_esc = $this->db->escape_like_str($referral_name);
//     $where[] = 'AND CONCAT(IFNULL(au.firstname, ""), " ", IFNULL(au.lastname, "")) LIKE "%' . $referral_name_esc . '%"';
//     }
    
//     if (staff_cant('view', 'invoices')) {
//         $where[] = get_invoices_where_sql_for_staff(get_staff_user_id());
//     }

//     $where[] = 'AND (inv.total - IFNULL((SELECT SUM(amount) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = inv.id), 0)) = 0';

//     $sql = "
//         SELECT 
//             inv.id as invoice_id,
//             inv.number as invoice_number,
//             ti.rate AS item_fixed_rate,
//             (ii.qty * ti.rate) AS item_subtotal
//             inv.discount_total,
//             inv.total,
//             c.company as client_name,
//             c.userid as mrd_no,
//             au.firstname,
//             au.lastname,
//             ii.id as item_id,
//             ii.description,
//             ii.long_description,
//             (SELECT MAX(date) FROM " . db_prefix() . "invoicepaymentrecords WHERE invoiceid = inv.id) as last_payment_date,
//             (SELECT SUM(amount) FROM " . db_prefix() . "invoicepaymentrecords WHERE invoiceid = inv.id) as total_paid,
//             cur.name as currency_name
//         FROM " . db_prefix() . "invoices as inv
//         LEFT JOIN " . db_prefix() . "clients as c ON c.userid = inv.clientid
//         LEFT JOIN " . db_prefix() . "affiliate_users as au ON au.affiliate_code = c.affiliate_code COLLATE utf8mb4_unicode_ci
//         LEFT JOIN " . db_prefix() . "currencies as cur ON cur.id = inv.currency
        
//         LEFT JOIN " . db_prefix() . "itemable as ii ON ii.rel_id = inv.id AND ii.rel_type = 'invoice'
//         WHERE 1=1 " . implode(' ', $where) . "
//         ORDER BY inv.id ASC, ii.id ASC
//     ";

//     $results = $this->db->query($sql)->result();

//     $output['aaData'] = [];
//     $serial = 1;
//     $total_subtotal = 0;
//     $total_discount = 0;
//     $total_total = 0;
//     $total_commission = 0;
//     $total_balance = 0;
//     $commission_custom_field_id = 91;

//     // Group results by invoice ID to count items per invoice
//     $invoice_item_counts = [];
//     foreach ($results as $r) {
//         if (!isset($invoice_item_counts[$r->invoice_id])) {
//             $invoice_item_counts[$r->invoice_id] = 0;
//         }
//         $invoice_item_counts[$r->invoice_id]++;
//     }

//     foreach ($results as $row) {
//         // Get commission from custom fields
//         $this->db->select('value');
//         $this->db->from(db_prefix() . 'customfieldsvalues');
//         $this->db->where('relid', $row->item_id);
//         $this->db->where('fieldid', $commission_custom_field_id);
//         $this->db->where('fieldto', 'items');
//         $cf_result = $this->db->get()->row();

//         $commission = isset($cf_result->value) ? (float)$cf_result->value : 0;

//         // Split invoice-level discount across items
//         $item_count = $invoice_item_counts[$row->invoice_id] ?? 1;
//         $discount_per_item = (float)$row->discount_total / $item_count;

//         // Adjust commission after proportional discount
//         $commission_after_discount = max($commission - $discount_per_item, 0);

//         // Calculate balance
//         $balance = (float)$row->total - (float)$row->total_paid;

//         $output['aaData'][] = [
//             $serial++,
//             '<a href="' . admin_url('invoices/invoice/' . $row->invoice_id) . '" target="_blank">' . format_invoice_number($row->invoice_id) . '</a>',
//             _d($row->last_payment_date),
//             str_pad($row->mrd_no, 0, '0', STR_PAD_LEFT),
//             '<a href="' . admin_url('clients/client/' . $row->mrd_no) . '" target="_blank">' . html_escape($row->client_name) . '</a>',
//             ($row->firstname || $row->lastname) ? html_escape($row->firstname . ' ' . $row->lastname) : 'N/A',
//             html_escape($row->description),
//             html_escape($row->long_description),
//             app_format_money($row->subtotal, $currency->name),
//             app_format_money($discount_per_item, $currency->name),
//             app_format_money($row->total, $currency->name),
//             app_format_money($balance, $currency->name),
//             '<span class="tw-text-green-600">' . app_format_money($commission_after_discount, $currency->name) . '</span>',
//         ];

//         $total_subtotal += (float)$row->subtotal;
//         $total_discount += $discount_per_item;
//         $total_total    += (float)$row->total;
//         $total_balance  += $balance;
//         $total_commission += $commission_after_discount;
//     }

//     $output['aaData'][] = [
//         '', '', '', '', '', '', '', '<strong>Total:</strong>',
//         '<strong>' . app_format_money($total_subtotal, $currency->name) . '</strong>',
//         '<strong>' . app_format_money($total_discount, $currency->name) . '</strong>',
//         '<strong>' . app_format_money($total_total, $currency->name) . '</strong>',
//         '<strong>' . app_format_money($total_balance, $currency->name) . '</strong>',
//         '<strong>' . app_format_money($total_commission, $currency->name) . '</strong>',
//     ];

//     echo json_encode($output);
// }



public function outpatient_bill_report()
{
    if (!staff_can('view', 'invoices')) {
        access_denied('invoices');
    }

    $data['title'] = _l('Outpatient Bill Report');
    $this->load->view('admin/reports/outpatient_bill_report', $data);
    
}


//  public function outpatient_bill_table()
// {
//     log_message('debug', 'Starting outpatient_bill_table method');

//     if (!staff_can('view', 'invoices')) {
//         log_message('debug', 'Access denied for viewing invoices');
//         access_denied('invoices');
//     }

//       $invoiceTable = db_prefix() . 'invoices';
//     $clientsTable = db_prefix() . 'clients';
//     $itemableTable = db_prefix() . 'itemable';
//     $paymentRecordsTable = db_prefix() . 'invoicepaymentrecords';
//     $customFieldsValuesTable = db_prefix() . 'customfieldsvalues';
//     $customFieldsTable = db_prefix() . 'customfields';
//     $affiliateUsersTable = db_prefix() . 'affiliate_users';
//     $staffTable = db_prefix() . 'staff';

//     $this->load->model('invoices_model');
//     $this->load->model('currencies_model');
//     $currency = $this->currencies_model->get_base_currency();
//     log_message('debug', 'Loaded base currency: ' . print_r($currency, true));

  

//     // Filters from POST
//      $from_date = $this->input->post('report_from');
//     $to_date   = $this->input->post('report_to');
//     $mrd_from  = $this->input->post('mrd_from');
//     $mrd_to    = $this->input->post('mrd_to');
//     $clientid  = $this->input->post('customer_id');
//     $project_id = $this->input->post('project_id');
//     $referral_name = $this->input->post('referral_name');

//     $where = [];

//     if ($from_date && $to_date) {
//         $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '"';
//     } elseif ($from_date) {
//         $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) >= "' . $this->db->escape_str($from_date) . '"';
//     } elseif ($to_date) {
//         $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) <= "' . $this->db->escape_str($to_date) . '"';
//     }

//     if ($mrd_from && $mrd_to) {
//         $where[] = 'AND ' . db_prefix() . 'clients.userid BETWEEN "' . $this->db->escape_str($mrd_from) . '" AND "' . $this->db->escape_str($mrd_to) . '"';
//     } elseif ($mrd_from) {
//         $where[] = 'AND ' . db_prefix() . 'clients.userid >= "' . $this->db->escape_str($mrd_from) . '"';
//     } elseif ($mrd_to) {
//         $where[] = 'AND ' . db_prefix() . 'clients.userid <= "' . $this->db->escape_str($mrd_to) . '"';
//     }

//       if ($referral_name) {
//         $referral_name_esc = $this->db->escape_like_str($referral_name);
//         $where[] = 'AND CONCAT(au.firstname, " ", au.lastname) LIKE "%' . $referral_name_esc . '%"';
//     }


//     if (staff_cant('view', 'invoices')) {
//         $where[] = get_invoices_where_sql_for_staff(get_staff_user_id());
//     }

//     log_message('info', 'due_paid_details_table filters: from_date=' . $from_date . ', to_date=' . $to_date . ', mrd_no=, mrd_from=' . $mrd_from . ', mrd_to=' . $mrd_to . ', clientid=' . $clientid . ', project_id=' . $project_id);

//     $aColumns = [
//         'number',
//         'total',
//         'tblinvoices.datecreated as invoice_datecreated',
//         get_sql_select_client_company(),
//         $clientsTable . '.userid as mrd_no',
//         $invoiceTable . '.status',

//         "(SELECT pm.name 
//         FROM " . db_prefix() . "invoicepaymentrecords pr
//         JOIN " . db_prefix() . "payment_modes pm ON pr.paymentmode = pm.id
//         WHERE pr.invoiceid = " . db_prefix() . "invoices.id 
//         ORDER BY pr.date DESC, pr.id DESC 
//         LIMIT 1) AS payment_mode",


//         db_prefix() . 'invoices.discount_total as discount',

//         "(SELECT GROUP_CONCAT(description SEPARATOR ', ')
//             FROM $itemableTable
//             WHERE rel_id = $invoiceTable.id AND rel_type = 'invoice') as all_items",
//         'subtotal',
//         "(SELECT SUM(amount) FROM $paymentRecordsTable WHERE invoiceid = $invoiceTable.id) as total_paid",
//         "($invoiceTable.total - IFNULL((SELECT SUM(amount) FROM $paymentRecordsTable WHERE invoiceid = $invoiceTable.id), 0)) as balance",
        
//         '(SELECT IFNULL(SUM(CAST(cfdv.value AS DECIMAL(10,2))), 0)
//         FROM ' . db_prefix() . 'customfieldsvalues cfdv
//         JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id
//         JOIN ' . db_prefix() . 'itemable items ON items.id = cfdv.relid
//         WHERE cfd.name = "Serv.charge"
//             AND cfdv.fieldto = "items"
//             AND items.rel_id = ' . db_prefix() . 'invoices.id
//             AND items.rel_type = "invoice") AS service_charge',


//              '(SELECT GROUP_CONCAT(pr.transactionid SEPARATOR ", ") 
//                 FROM ' . db_prefix() . 'invoicepaymentrecords pr
//                 WHERE pr.invoiceid = ' . $invoiceTable . '.id
//                 ) as payment_details',


//                '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
//             JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
//             WHERE cfd.name = "Age" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
//             AND cfdv.fieldto = "invoice" LIMIT 1) as Age',

//         '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
//             JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
//             WHERE cfd.name = "Sex" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
//             AND cfdv.fieldto = "invoice" LIMIT 1) as Sex',

//         '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
//             JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
//             WHERE cfd.name = "Mobile.no" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
//             AND cfdv.fieldto = "invoice" LIMIT 1) as Mobile',

//              '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
//             JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
//             WHERE cfd.name = "Ref.By" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
//             AND cfdv.fieldto = "invoice" LIMIT 1) as Refer',

//                  "(SELECT SUM(amount) 
//                 FROM " . db_prefix() . "invoicepaymentrecords 
//                 WHERE invoiceid = $invoiceTable.id AND paymentmode = 2) as Cash_Amount",

//                 "(SELECT SUM(amount) 
//                 FROM " . db_prefix() . "invoicepaymentrecords 
//                 WHERE invoiceid = $invoiceTable.id AND paymentmode = 3) as Cheque_Amount",

//                 "(SELECT SUM(amount) 
//                 FROM " . db_prefix() . "invoicepaymentrecords 
//                 WHERE invoiceid = $invoiceTable.id AND paymentmode = 4) as Card",

//                 "(SELECT SUM(amount) 
//                 FROM " . db_prefix() . "invoicepaymentrecords 
//                 WHERE invoiceid = $invoiceTable.id AND paymentmode = 5) as UPI",

//        "CONCAT($staffTable.firstname, ' ', $staffTable.lastname) as sales_agent_name",

//         "CONCAT(au.firstname, ' ', au.lastname) as affiliate_user_name",
//     ];
//     $sIndexColumn = 'id';
//     $sTable = db_prefix() . 'invoices';

//     $join = [
//         "LEFT JOIN $clientsTable ON $clientsTable.userid = $invoiceTable.clientid",
//         "LEFT JOIN " . db_prefix() . "staff ON " . $invoiceTable . ".sale_agent = " . db_prefix() . "staff.staffid",
//         "LEFT JOIN $affiliateUsersTable AS au ON au.affiliate_code = $clientsTable.affiliate_code COLLATE utf8mb4_unicode_ci",
//         'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . $invoiceTable . '.currency',
//         'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . $invoiceTable . '.project_id',
//     ];
//     log_message('debug', 'Joins set up: ' . print_r($join, true));

//     $custom_fields = get_table_custom_fields('invoice');
//     $customFieldsColumns = [];

//     foreach ($custom_fields as $key => $field) {
//         $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
//         $customFieldsColumns[] = $selectAs;
//         $aColumns[] = 'ctable_' . $key . '.value as ' . $selectAs;
//         $join[] = 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . $invoiceTable . '.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id'];
//     }
//     log_message('debug', 'Custom fields joins added: ' . print_r($join, true));

//     $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
//         db_prefix() . 'invoices.id',
//         db_prefix() . 'invoices.clientid',
//         db_prefix() . 'currencies.name as currency_name',
//         'formatted_number',
//     ]);
//     log_message('debug', 'DataTables result obtained');

//     $output  = $result['output'];
//     $rResult = $result['rResult'];

//     $serial_number = 1;

//     $total_subtotal = 0;
//     $total_discount = 0;
//     $total_total = 0;
//     $total_service_charge = 0;
//     $total_paid_amount = 0;
//     $total_balance = 0;
//     $cash_total = 0;
//     $cheque_total = 0;
//     $card_total = 0;
//     $upi_total = 0;

//     foreach ($rResult as $aRow) {
//         $row = [];

//         $formattedNumber = format_invoice_number($aRow['id']);
//         if (empty($aRow['formatted_number']) || $formattedNumber !== $aRow['formatted_number']) {
//             $this->invoices_model->save_formatted_number($aRow['id']);
//             log_message('debug', 'Saved formatted number for invoice ID: ' . $aRow['id']);
//         }
//         $row[] = '<a href="' . admin_url('invoices/invoice/' . $aRow['id']) . '" target="_blank">' . html_escape($formattedNumber) . '</a>';
//         $row[] = !empty($aRow['invoice_datecreated']) ? date('d-m-Y h:i A', strtotime($aRow['invoice_datecreated'])) : '';
//         $row[] = str_pad($aRow['mrd_no'], 0, '0', STR_PAD_LEFT);
//         $row[] = empty($aRow['deleted_customer_name']) ? '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>' : e($aRow['deleted_customer_name']);
//         $row[] = $aRow['affiliate_user_name'] ?: 'N/A';
//         $row[] = $aRow['all_items'] ?: '-';

//       // Initialize used payment modes array
//         $displayed_modes = [];
//         $this->ci->load->model('payments_model');
//         $invoice_payments = $this->ci->payments_model->get_invoice_payments($aRow['id']);
//         if (!empty($invoice_payments)) {
//             foreach ($invoice_payments as $payment) {
//                 $payment_mode = !empty($payment['paymentmethod']) ? $payment['paymentmethod'] : $payment['name'];
//                 if (!in_array($payment_mode, $displayed_modes)) {
//                     $displayed_modes[] = $payment_mode;
//                 }
//             }
//         }
//         $all_modes = !empty($displayed_modes) ? implode(', ', $displayed_modes) : '-';
//         $row[] = $all_modes;

//         $row[] = !empty($aRow['Age']) ? $aRow['Age'] : '-';
//         $row[] = !empty($aRow['Sex']) ? $aRow['Sex'] : '-';
//         $row[] = !empty($aRow['Mobile']) ? $aRow['Mobile'] : '-'; 
//         $row[] = app_format_money($aRow['subtotal'], $aRow['currency_name']);
//         $row[] = app_format_money($aRow['discount'], $currency->name);
//         $row[] = app_format_money($aRow['total'], $aRow['currency_name']);

//         // Normalize the payment mode string for comparison
//       $has_service_charge_mode = false;
// $invoice_payments = $this->ci->payments_model->get_invoice_payments($aRow['id']);

// foreach ($invoice_payments as $payment) {
//     $mode = strtolower(trim($payment['paymentmethod'] ?? $payment['name']));
//     if (strpos($mode, 'upi') !== false || strpos($mode, 'credit/debit card') !== false) {
//         $has_service_charge_mode = true;
//         break;
//     }
// }

// $service_charge_value = ($has_service_charge_mode && is_numeric($aRow['service_charge']))
//     ? $aRow['service_charge']
//     : 0;


//         // Show formatted service charge
//         $row[] = app_format_money($service_charge_value, $currency->name);

//         $row[] = app_format_money($aRow['total_paid'], $aRow['currency_name']);
//         $row[] = app_format_money($aRow['balance'], $aRow['currency_name']);   
//         $row[] = !empty($aRow['Cash_Amount']) ? $aRow['Cash_Amount'] : '0';
//         $row[] = !empty($aRow['Cheque_Amount']) ? $aRow['Cheque_Amount'] : '0';
//         $row[] = !empty($aRow['Card']) ? $aRow['Card'] : '0';
//         $row[] = !empty($aRow['UPI']) ? $aRow['UPI'] : '0';
//         $row[] = !empty($aRow['payment_details']) ? html_escape($aRow['payment_details']) : '-';
//         $row[] = $aRow['sales_agent_name'] ?: 'N/A';

//         // Accumulate totals - log current row values for these keys
//         log_message('debug', 'Accumulating totals from row - total_amount: ' . ($aRow['total_amount'] ?? 'N/A') . ', discount: ' . ($aRow['discount'] ?? 'N/A') . ', bill_amount: ' . ($aRow['bill_amount'] ?? 'N/A') . ', paid_amount: ' . ($aRow['paid_amount'] ?? 'N/A') . ', balance: ' . ($aRow['balance'] ?? 'N/A'));

//         $total_total_amount += $aRow['subtotal'] ?? 0;
//         $total_discount += $aRow['discount'] ?? 0;
//         $total_bill_amount += $aRow['total'] ?? 0;
//       $payment_mode = strtolower(trim($aRow['payment_mode'] ?? ''));
// $service_charge = isset($aRow['service_charge']) ? (float) $aRow['service_charge'] : 0;

// if (strpos($payment_mode, 'upi') === false && strpos($payment_mode, 'credit/debit card') === false) {
//     $service_charge = 0;
// }
// $total_service_charge += $service_charge;

//         $total_paid_amount += $aRow['total_paid'] ?? 0;
//         $total_balance += $aRow['balance'] ?? 0;
//         $total_cash += $aRow['Cash_Amount'] ?? 0;
//         $total_cheque += $aRow['Cheque_Amount'] ?? 0;
//         $total_card += $aRow['Card'] ?? 0;
//         $total_upi += $aRow['UPI'] ?? 0;


//         $output['aaData'][] = $row;
//     }

//     // Append totals row at the end (optional)
//     $totals_row = [
//         '<strong>Total</strong>',
//         '',
//         '',
//         '',
//         '',
//         '',
//         '',
//         '',
//         '',
//         '',
//         app_format_money($total_total_amount, $currency->name),
//         app_format_money($total_discount, $currency->name),
//         app_format_money($total_bill_amount, $currency->name),
//         app_format_money($total_service_charge, $currency->name),
//         app_format_money($total_paid_amount, $currency->name),
//         app_format_money($total_balance, $currency->name),
//         app_format_money($total_cash, $currency->name),         // Cash Amount
//         app_format_money($total_cheque, $currency->name),       // Cheque Amount
//         app_format_money($total_card, $currency->name),         // Credit/Debit card
//         app_format_money($total_upi, $currency->name),          // UPI
//         '',
//         '',
//         '',
//         '',
//     ];

//     $output['aaData'][] = $totals_row;

//     log_message('debug', 'Outputting JSON response with total rows');

//     echo json_encode($output);
// }


public function outpatient_bill_table()
{
    log_message('debug', '🚀 Starting outpatient_bill_table method');

    if (!staff_can('view', 'invoices')) {
        log_message('debug', '❌ Access denied for viewing invoices');
        access_denied('invoices');
    }

    $invoiceTable = db_prefix() . 'invoices';
    $clientsTable = db_prefix() . 'clients';
    $itemableTable = db_prefix() . 'itemable';
    $paymentRecordsTable = db_prefix() . 'invoicepaymentrecords';
    $customFieldsValuesTable = db_prefix() . 'customfieldsvalues';
    $customFieldsTable = db_prefix() . 'customfields';
    $affiliateUsersTable = db_prefix() . 'affiliate_users';
    $staffTable = db_prefix() . 'staff';

    $this->load->model('invoices_model');
    $this->load->model('currencies_model');
    $this->load->model('payments_model'); // Added payments model
    $currency = $this->currencies_model->get_base_currency();
    log_message('debug', '💰 Loaded base currency: ' . print_r($currency, true));

    // Filters from POST
    $from_date = $this->input->post('report_from');
    $to_date   = $this->input->post('report_to');
    $mrd_from  = $this->input->post('mrd_from');
    $mrd_to    = $this->input->post('mrd_to');
    $clientid  = $this->input->post('customer_id');
    $project_id = $this->input->post('project_id');
    $referral_name = $this->input->post('referral_name');

    log_message('debug', '🎯 Received filters:');
    log_message('debug', '   - from_date: ' . $from_date);
    log_message('debug', '   - to_date: ' . $to_date);
    log_message('debug', '   - mrd_from: ' . $mrd_from);
    log_message('debug', '   - mrd_to: ' . $mrd_to);
    log_message('debug', '   - referral_name: ' . $referral_name);

    $where = [];

    if ($from_date && $to_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '"';
    } elseif ($from_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) >= "' . $this->db->escape_str($from_date) . '"';
    } elseif ($to_date) {
        $where[] = 'AND DATE(' . db_prefix() . 'invoices.date) <= "' . $this->db->escape_str($to_date) . '"';
    }

    if ($mrd_from && $mrd_to) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid BETWEEN "' . $this->db->escape_str($mrd_from) . '" AND "' . $this->db->escape_str($mrd_to) . '"';
    } elseif ($mrd_from) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid >= "' . $this->db->escape_str($mrd_from) . '"';
    } elseif ($mrd_to) {
        $where[] = 'AND ' . db_prefix() . 'clients.userid <= "' . $this->db->escape_str($mrd_to) . '"';
    }

    if ($referral_name) {
        $referral_name_esc = $this->db->escape_like_str($referral_name);
        $where[] = 'AND CONCAT(au.firstname, " ", au.lastname) LIKE "%' . $referral_name_esc . '%"';
    }

    if (staff_cant('view', 'invoices')) {
        $where[] = get_invoices_where_sql_for_staff(get_staff_user_id());
    }

    log_message('debug', '📋 WHERE conditions: ' . implode(' ', $where));

    $aColumns = [
        'number',
        'total',
        'tblinvoices.datecreated as invoice_datecreated',
        get_sql_select_client_company(),
        $clientsTable . '.userid as mrd_no',
        $invoiceTable . '.status',

        "(SELECT pm.name 
        FROM " . db_prefix() . "invoicepaymentrecords pr
        JOIN " . db_prefix() . "payment_modes pm ON pr.paymentmode = pm.id
        WHERE pr.invoiceid = " . db_prefix() . "invoices.id 
        ORDER BY pr.date DESC, pr.id DESC 
        LIMIT 1) AS payment_mode",

        db_prefix() . 'invoices.discount_total as discount',

        "(SELECT GROUP_CONCAT(description SEPARATOR ', ')
            FROM $itemableTable
            WHERE rel_id = $invoiceTable.id AND rel_type = 'invoice') as all_items",
        'subtotal',
        "(SELECT SUM(amount) FROM $paymentRecordsTable WHERE invoiceid = $invoiceTable.id) as total_paid",
        "($invoiceTable.total - IFNULL((SELECT SUM(amount) FROM $paymentRecordsTable WHERE invoiceid = $invoiceTable.id), 0)) as balance",
        
        '(SELECT IFNULL(SUM(CAST(cfdv.value AS DECIMAL(10,2))), 0)
        FROM ' . db_prefix() . 'customfieldsvalues cfdv
        JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id
        JOIN ' . db_prefix() . 'itemable items ON items.id = cfdv.relid
        WHERE cfd.name = "Serv.charge"
            AND cfdv.fieldto = "items"
            AND items.rel_id = ' . db_prefix() . 'invoices.id
            AND items.rel_type = "invoice") AS service_charge',

        '(SELECT GROUP_CONCAT(pr.transactionid SEPARATOR ", ") 
            FROM ' . db_prefix() . 'invoicepaymentrecords pr
            WHERE pr.invoiceid = ' . $invoiceTable . '.id
            ) as payment_details',

        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.slug = "age_years" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as age_years',

        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.slug = "age_months" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as age_months',
            '(
  SELECT cfdv.value 
  FROM tblcustomfieldsvalues cfdv
  JOIN tblcustomfields cfd ON cfdv.fieldid = cfd.id 
  WHERE cfd.name = "AgeOption" 
    AND cfdv.relid = tblinvoices.id 
    AND cfdv.fieldto = "invoice" 
  LIMIT 1
 ) AS AgeOption',

        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.name = "Sex" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as Sex',

        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.name = "Mobile.no" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as Mobile',

        '(SELECT cfdv.value FROM ' . db_prefix() . 'customfieldsvalues cfdv 
            JOIN ' . db_prefix() . 'customfields cfd ON cfdv.fieldid = cfd.id 
            WHERE cfd.name = "Ref.By" AND cfdv.relid = ' . db_prefix() . 'invoices.id 
            AND cfdv.fieldto = "invoice" LIMIT 1) as Refer',

        "(SELECT SUM(amount) 
            FROM " . db_prefix() . "invoicepaymentrecords 
            WHERE invoiceid = $invoiceTable.id AND paymentmode = 2) as Cash_Amount",

        "(SELECT SUM(amount) 
            FROM " . db_prefix() . "invoicepaymentrecords 
            WHERE invoiceid = $invoiceTable.id AND paymentmode = 3) as Cheque_Amount",

        "(SELECT SUM(amount) 
            FROM " . db_prefix() . "invoicepaymentrecords 
            WHERE invoiceid = $invoiceTable.id AND paymentmode = 6) as Card",

        "(SELECT SUM(amount) 
            FROM " . db_prefix() . "invoicepaymentrecords 
            WHERE invoiceid = $invoiceTable.id AND paymentmode = 10) as UPI",

        "CONCAT($staffTable.firstname, ' ', $staffTable.lastname) as sales_agent_name",
        "CONCAT(au.firstname, ' ', au.lastname) as affiliate_user_name",
    ];

    log_message('debug', '📊 aColumns defined: ' . print_r($aColumns, true));

    $sIndexColumn = 'id';
    $sTable = db_prefix() . 'invoices';

    $join = [
        "LEFT JOIN $clientsTable ON $clientsTable.userid = $invoiceTable.clientid",
        "LEFT JOIN " . db_prefix() . "staff ON " . $invoiceTable . ".sale_agent = " . db_prefix() . "staff.staffid",
        "LEFT JOIN $affiliateUsersTable AS au ON au.affiliate_code = $clientsTable.affiliate_code COLLATE utf8mb4_unicode_ci",
        'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . $invoiceTable . '.currency',
        'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . $invoiceTable . '.project_id',
    ];

    log_message('debug', '🔗 Joins set up: ' . print_r($join, true));

    $custom_fields = get_table_custom_fields('invoice');
    $customFieldsColumns = [];

    foreach ($custom_fields as $key => $field) {
        $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
        $customFieldsColumns[] = $selectAs;
        $aColumns[] = 'ctable_' . $key . '.value as ' . $selectAs;
        $join[] = 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . $invoiceTable . '.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id'];
    }

    log_message('debug', '🏷️ Custom fields processed: ' . count($custom_fields));

    $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
        db_prefix() . 'invoices.id',
        db_prefix() . 'invoices.clientid',
        db_prefix() . 'currencies.name as currency_name',
        'formatted_number',
    ]);

    log_message('debug', '📦 DataTables result obtained');
    log_message('debug', '📊 Number of records found: ' . count($result['rResult']));

    $output  = $result['output'];
    $rResult = $result['rResult'];

    // Log the raw SQL query for debugging
    log_message('debug', '🔍 Raw SQL Query: ' . $this->db->last_query());

    $serial_number = 1;

    $total_total_amount = 0;
    $total_discount = 0;
    $total_bill_amount = 0;
    $total_service_charge = 0;
    $total_paid_amount = 0;
    $total_balance = 0;
    $total_cash = 0;
    $total_cheque = 0;
    $total_card = 0;
    $total_upi = 0;

    log_message('debug', '📋 Processing ' . count($rResult) . ' rows:');

    foreach ($rResult as $index => $aRow) {
        log_message('debug', "--- Processing Row {$index} ---");
        log_message('debug', '📄 Raw row data: ' . json_encode($aRow));

        $row = [];

        // Format invoice number
        $formattedNumber = format_invoice_number($aRow['id']);
        if (empty($aRow['formatted_number']) || $formattedNumber !== $aRow['formatted_number']) {
            $this->invoices_model->save_formatted_number($aRow['id']);
            log_message('debug', '💾 Saved formatted number for invoice ID: ' . $aRow['id']);
        }
        $row[] = '<a href="' . admin_url('invoices/invoice/' . $aRow['id']) . '" target="_blank">' . html_escape($formattedNumber) . '</a>';
        log_message('debug', '🔢 Invoice Number: ' . $formattedNumber);

        $row[] = !empty($aRow['invoice_datecreated']) ? date('d/m/y', strtotime($aRow['invoice_datecreated'])) : '';
        log_message('debug', '📅 Date: ' . $row[1]);

        $row[] = str_pad($aRow['mrd_no'], 0, '0', STR_PAD_LEFT);
        log_message('debug', '🏥 MRD No: ' . $aRow['mrd_no']);

        $row[] = empty($aRow['deleted_customer_name']) ? '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>' : e($aRow['deleted_customer_name']);
        log_message('debug', '👤 Customer: ' . $aRow['company']);

        $row[] = $aRow['affiliate_user_name'] ?: 'N/A';
        log_message('debug', '👥 Referral: ' . $aRow['affiliate_user_name']);

        $age_years = !empty($aRow['age_years']) ? $aRow['age_years'] : '0';
        $age_months = !empty($aRow['age_months']) ? $aRow['age_months'] : '0';
        $row[] = $age_years . '/' . $age_months;

        $row[] = $aRow['all_items'] ?: '-';
        log_message('debug', '📦 Items: ' . substr($aRow['all_items'] ?? '-', 0, 50) . '...');

        // Payment modes
        $displayed_modes = [];
        $invoice_payments = $this->payments_model->get_invoice_payments($aRow['id']);
        log_message('debug', '💳 Found ' . count($invoice_payments) . ' payment records for invoice ' . $aRow['id']);
        
        if (!empty($invoice_payments)) {
            foreach ($invoice_payments as $payment) {
                $payment_mode = !empty($payment['paymentmethod']) ? $payment['paymentmethod'] : $payment['name'];
                if (!in_array($payment_mode, $displayed_modes)) {
                    $displayed_modes[] = $payment_mode;
                }
            }
        }
        $all_modes = !empty($displayed_modes) ? implode(', ', $displayed_modes) : '-';
        $row[] = $all_modes;
        log_message('debug', '💳 Payment Modes: ' . $all_modes);
        

        
        $row[] = !empty($aRow['Sex']) ? $aRow['Sex'] : '-';
        log_message('debug', '🚻 Sex: ' . $aRow['Sex']);

        $row[] = !empty($aRow['Mobile']) ? $aRow['Mobile'] : '-';
        log_message('debug', '📱 Mobile: ' . $aRow['Mobile']);

        $row[] = number_format($aRow['subtotal'], 2);
        log_message('debug', '💰 Subtotal: ' . $aRow['subtotal']);

        $row[] = number_format($aRow['discount'], 2);
        log_message('debug', '💸 Discount: ' . $aRow['discount']);

        $row[] = number_format($aRow['total'], 2);
        log_message('debug', '💵 Total: ' . $aRow['total']);

        $service_charge_value = $aRow['service_charge'] ?? 0;
        $row[] = number_format($service_charge_value, 2);
        log_message('debug', '⚡ Service Charge: ' . $service_charge_value);

        $row[] = number_format($aRow['total_paid'], 2);
        log_message('debug', '💳 Paid Amount: ' . $aRow['total_paid']);

        $row[] = number_format($aRow['balance'], 2);
        log_message('debug', '⚖️ Balance: ' . $aRow['balance']);

        $row[] = !empty($aRow['Cash_Amount']) ? $aRow['Cash_Amount'] : '0';
        log_message('debug', '💵 Cash Amount: ' . $aRow['Cash_Amount']);

        $row[] = !empty($aRow['Cheque_Amount']) ? $aRow['Cheque_Amount'] : '0';
        log_message('debug', '🏦 Cheque Amount: ' . $aRow['Cheque_Amount']);

        $row[] = !empty($aRow['Card']) ? $aRow['Card'] : '0';
        log_message('debug', '💳 Card Amount: ' . $aRow['Card']);

        $row[] = !empty($aRow['UPI']) ? $aRow['UPI'] : '0';
        log_message('debug', '📱 UPI Amount: ' . $aRow['UPI']);

        $row[] = !empty($aRow['payment_details']) ? html_escape($aRow['payment_details']) : '-';
        log_message('debug', '📝 Payment Details: ' . substr($aRow['payment_details'] ?? '-', 0, 50) . '...');



        // Accumulate totals - FIXED SERVICE CHARGE LOGIC
        log_message('debug', '🧮 Accumulating totals for row ' . $index);
        
        $total_total_amount += $aRow['subtotal'] ?? 0;
        $total_discount += $aRow['discount'] ?? 0;
        $total_bill_amount += $aRow['total'] ?? 0;
        
        $service_charge_value = $aRow['service_charge'] ?? 0;

        // Get payment modes from the displayed_modes array we already created
        $payment_modes = !empty($displayed_modes) ? $displayed_modes : [];
        $payment_modes_lower = array_map('strtolower', $payment_modes);

        // Check if service charge should be applied
        $should_apply_service_charge = false;
        foreach ($payment_modes_lower as $mode) {
            if (strpos($mode, 'upi') !== false || strpos($mode, 'card') !== false || 
                strpos($mode, 'credit') !== false || strpos($mode, 'debit') !== false ||
                strpos($mode, 'cc') !== false) {
                $should_apply_service_charge = true;
                break;
            }
        }

        // Apply service charge only for relevant payment modes
        if ($should_apply_service_charge) {
            $total_service_charge += (float) $service_charge_value;
        } else {
            $total_service_charge += 0;
        }

        log_message('debug', '💡 Service Charge Calculation:');
        log_message('debug', '   - Raw Service Charge: ' . $service_charge_value);
        log_message('debug', '   - Payment Modes: ' . implode(', ', $payment_modes));
        log_message('debug', '   - Should Apply Service Charge: ' . ($should_apply_service_charge ? 'Yes' : 'No'));
        log_message('debug', '   - Service Charge Added: ' . ($should_apply_service_charge ? $service_charge_value : 0));

        $total_paid_amount += $aRow['total_paid'] ?? 0;
        $total_balance += $aRow['balance'] ?? 0;
        $total_cash += $aRow['Cash_Amount'] ?? 0;
        $total_cheque += $aRow['Cheque_Amount'] ?? 0;
        $total_card += $aRow['Card'] ?? 0;
        $total_upi += $aRow['UPI'] ?? 0;

        log_message('debug', '📊 Current totals after row ' . $index . ':');
        log_message('debug', '   - Total Amount: ' . $total_total_amount);
        log_message('debug', '   - Total Discount: ' . $total_discount);
        log_message('debug', '   - Total Bill: ' . $total_bill_amount);
        log_message('debug', '   - Total Service Charge: ' . $total_service_charge);
        log_message('debug', '   - Total Paid: ' . $total_paid_amount);
        log_message('debug', '   - Total Balance: ' . $total_balance);

        $output['aaData'][] = $row;
        log_message('debug', "✅ Row {$index} processed successfully");
    }

    // Log final totals
    log_message('debug', '🎯 FINAL TOTALS:');
    log_message('debug', '   - Total Amount: ' . $total_total_amount);
    log_message('debug', '   - Total Discount: ' . $total_discount);
    log_message('debug', '   - Total Bill: ' . $total_bill_amount);
    log_message('debug', '   - Total Service Charge: ' . $total_service_charge);
    log_message('debug', '   - Total Paid: ' . $total_paid_amount);
    log_message('debug', '   - Total Balance: ' . $total_balance);
    log_message('debug', '   - Total Cash: ' . $total_cash);
    log_message('debug', '   - Total Cheque: ' . $total_cheque);
    log_message('debug', '   - Total Card: ' . $total_card);
    log_message('debug', '   - Total UPI: ' . $total_upi);

    // Append totals row
    $totals_row = [
        '<strong>Total</strong>',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        number_format($total_total_amount, 2),
        number_format($total_discount, 2),
        number_format($total_bill_amount, 2),
        number_format($total_service_charge, 2),
        number_format($total_paid_amount, 2),
        number_format($total_balance, 2),
        number_format($total_cash, 2),
        number_format($total_cheque, 2),
        number_format($total_card, 2),
        number_format($total_upi, 2),
        '',
    ];

    $output['aaData'][] = $totals_row;
    log_message('debug', '📋 Totals row added to output');

    log_message('debug', '📤 Outputting JSON response with ' . count($output['aaData']) . ' rows');
    log_message('debug', '📄 Final output sample: ' . json_encode(array_slice($output['aaData'], 0, 2)));

    echo json_encode($output);
    log_message('debug', '✅ outpatient_bill_table method completed successfully');
}

















public function summary_report()
{
    if (!staff_can('view', 'invoices')) {
        access_denied('invoices');
    }

    $data['title'] = _l('Summary Report');
    $this->load->view('admin/reports/summary_report', $data);
}

public function summary_details_table()
{
    if (!staff_can('view', 'invoices')) {
        access_denied('invoices');
    }

    $from_date = $this->input->post('report_from');
    $to_date   = $this->input->post('report_to');
    $clientid  = $this->input->post('customer_id');

    // Log the input filters
    log_message('debug', 'summary_details_table called with filters: ' . json_encode([
        'report_from' => $from_date,
        'report_to'   => $to_date,
        'customer_id' => $clientid,
        'staff_id'    => get_staff_user_id(),
    ]));

    $where = [];

    if ($from_date && $to_date) {
        $where[] = 'AND DATE(inv.date) BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '"';
    } elseif ($from_date) {
        $where[] = 'AND DATE(inv.date) >= "' . $this->db->escape_str($from_date) . '"';
    } elseif ($to_date) {
        $where[] = 'AND DATE(inv.date) <= "' . $this->db->escape_str($to_date) . '"';
    }

    if (!empty($clientid)) {
        $where[] = 'AND inv.clientid = ' . $this->db->escape($clientid);
    }

    if (staff_cant('view', 'invoices')) {
        $where[] = get_invoices_where_sql_for_staff(get_staff_user_id());
    }

    // Log the constructed WHERE clause
    log_message('debug', 'summary_details_table WHERE condition: ' . implode(' ', $where));

    $sql = "
        SELECT
            DATE(inv.date) AS invoice_date,
            SUM(inv.total) AS invoice_total,
            SUM(inv.discount_total) AS total_discount,

            -- MR
            SUM(items.mr_total) AS mr_total,
            SUM(items.mr_total_excluding_contrast) AS mr_total_excluding_contrast,
            SUM(CASE WHEN items.has_mr = 1 THEN inv.discount_total ELSE 0 END) AS mr_discount,

            -- MR-CONTRAST
            SUM(items.mr_contrast_total) AS mr_contrast_total,
            SUM(
                CASE 
                    WHEN items.mr_total > 0 THEN 
                        (items.mr_contrast_total * (inv.discount_total * mr_total_excluding_contrast / NULLIF(inv.total, 0)) / mr_total_excluding_contrast)
                    ELSE 0 
                END
            ) AS mr_contrast_discount,

            -- CT
            SUM(items.ct_total) AS ct_total,
            SUM(CASE WHEN items.has_ct = 1 THEN inv.discount_total ELSE 0 END) AS ct_discount,

            -- CT-CONTRAST
            SUM(items.ct_contrast_total) AS ct_contrast_total,
            SUM(
                CASE 
                    WHEN items.ct_total > 0 THEN 
                        (items.ct_contrast_total * (inv.discount_total * items.ct_total / NULLIF(inv.total, 0)) / items.ct_total)
                    ELSE 0 
                END
            ) AS ct_contrast_discount

        FROM tblinvoices inv
        LEFT JOIN (
            SELECT
                rel_id,
                SUM(CASE WHEN TRIM(description) = 'MR' THEN rate ELSE 0 END) AS mr_total,
                SUM(CASE WHEN TRIM(description) = 'MR' AND TRIM(long_description) != 'MR-CONTRAST' THEN rate ELSE 0 END) AS mr_total_excluding_contrast,
                SUM(CASE WHEN TRIM(long_description) = 'MR-CONTRAST' THEN rate ELSE 0 END) AS mr_contrast_total,
                SUM(CASE WHEN TRIM(description) = 'CT' THEN rate ELSE 0 END) AS ct_total,
                SUM(CASE WHEN TRIM(long_description) = 'CT-CONTRAST' THEN rate ELSE 0 END) AS ct_contrast_total,
                MAX(CASE WHEN TRIM(description) = 'MR' THEN 1 ELSE 0 END) AS has_mr,
                MAX(CASE WHEN TRIM(long_description) = 'MR-CONTRAST' THEN 1 ELSE 0 END) AS has_mr_contrast,
                MAX(CASE WHEN TRIM(description) = 'CT' THEN 1 ELSE 0 END) AS has_ct,
                MAX(CASE WHEN TRIM(long_description) = 'CT-CONTRAST' THEN 1 ELSE 0 END) AS has_ct_contrast
            FROM tblitemable
            WHERE rel_type = 'invoice'
            GROUP BY rel_id
        ) items ON items.rel_id = inv.id
        WHERE 1=1 " . implode(' ', $where) . "
        GROUP BY DATE(inv.date)
        ORDER BY invoice_date ASC
    ";

    // Log the full SQL query
    log_message('debug', 'Executing SQL in summary_details_table: ' . $sql);

    $query = $this->db->query($sql);
    $results = $query->result();

    // Log how many rows returned
    log_message('debug', 'summary_details_table returned rows: ' . count($results));

    $data = [];
    $totals = array_fill_keys([
        'mr_total', 'mr_discount', 'mr_net',
        'mr_contrast_total', 'mr_contrast_discount', 'mr_contrast_net',
        'mr_diff_total', 'mr_diff_discount', 'mr_diff_net',
        'ct_total', 'ct_discount', 'ct_net',
        'ct_contrast_total', 'ct_contrast_discount', 'ct_contrast_net',
        'ct_diff_total', 'ct_diff_discount', 'ct_diff_net',
        'total_gross', 'total_discount', 'total_net'
    ], 0);

    $index = 1;
    foreach ($results as $row) {
        $mr_net = $row->mr_total - $row->mr_discount;
        $mr_contrast_net = $row->mr_contrast_total - $row->mr_contrast_discount;
        $ct_net = $row->ct_total - $row->ct_discount;
        $ct_contrast_net = $row->ct_contrast_total - $row->ct_contrast_discount;

        $mr_diff_total = $row->mr_total - $row->mr_contrast_total;
        $mr_diff_discount = $row->mr_discount - $row->mr_contrast_discount;
        $mr_diff_net = $mr_net - $mr_contrast_net;

        $ct_diff_total = $row->ct_total - $row->ct_contrast_total;
        $ct_diff_discount = $row->ct_discount - $row->ct_contrast_discount;
        $ct_diff_net = $ct_net - $ct_contrast_net;

        $total_gross = $row->mr_total + $row->ct_total;
        $total_discount = $row->mr_discount + $row->ct_discount;
        $total_net = $mr_net + $ct_net;

        foreach ([
            'mr_total' => $row->mr_total,
            'mr_discount' => $row->mr_discount,
            'mr_net' => $mr_net,
            'mr_contrast_total' => $row->mr_contrast_total,
            'mr_contrast_discount' => $row->mr_contrast_discount,
            'mr_contrast_net' => $mr_contrast_net,
            'mr_diff_total' => $mr_diff_total,
            'mr_diff_discount' => $mr_diff_discount,
            'mr_diff_net' => $mr_diff_net,
            'ct_total' => $row->ct_total,
            'ct_discount' => $row->ct_discount,
            'ct_net' => $ct_net,
            'ct_contrast_total' => $row->ct_contrast_total,
            'ct_contrast_discount' => $row->ct_contrast_discount,
            'ct_contrast_net' => $ct_contrast_net,
            'ct_diff_total' => $ct_diff_total,
            'ct_diff_discount' => $ct_diff_discount,
            'ct_diff_net' => $ct_diff_net,
            'total_gross' => $total_gross,
            'total_discount' => $total_discount,
            'total_net' => $total_net
        ] as $key => $val) {
            $totals[$key] += $val;
        }

        $data[] = [
            $index++,
            _d($row->invoice_date),
            app_format_money($row->mr_total, get_base_currency()),
            app_format_money($row->mr_discount, get_base_currency()),
            app_format_money($mr_net, get_base_currency()),

            app_format_money($row->mr_contrast_total, get_base_currency()),
            app_format_money($row->mr_contrast_discount, get_base_currency()),
            app_format_money($mr_contrast_net, get_base_currency()),

            app_format_money($mr_diff_total, get_base_currency()),
            app_format_money($mr_diff_discount, get_base_currency()),
            app_format_money($mr_diff_net, get_base_currency()),

            app_format_money($row->ct_total, get_base_currency()),
            app_format_money($row->ct_discount, get_base_currency()),
            app_format_money($ct_net, get_base_currency()),

            app_format_money($row->ct_contrast_total, get_base_currency()),
            app_format_money($row->ct_contrast_discount, get_base_currency()),
            app_format_money($ct_contrast_net, get_base_currency()),

            app_format_money($ct_diff_total, get_base_currency()),
            app_format_money($ct_diff_discount, get_base_currency()),
            app_format_money($ct_diff_net, get_base_currency()),

            app_format_money($total_gross, get_base_currency()),
            app_format_money($total_discount, get_base_currency()),
            app_format_money($total_net, get_base_currency()),
        ];
    }

    $footer = [];
    foreach ($totals as $key => $val) {
        $footer[$key] = app_format_money($val, get_base_currency());
    }

    echo json_encode([
        'aaData' => $data,
        'footer' => $footer,
    ]);
    die;
}



}