<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('invoices_model');

return App_table::find('invoices')->outputUsing(function ($params) {
    extract($params);
    $project_id = $this->ci->input->post('project_id');

    $invoiceTable = db_prefix() . 'invoices';
    $clientsTable = db_prefix() . 'clients';
    $itemableTable = db_prefix() . 'itemable';
    $paymentRecordsTable = db_prefix() . 'invoicepaymentrecords';
    $customFieldsValuesTable = db_prefix() . 'customfieldsvalues';
    $customFieldsTable = db_prefix() . 'customfields';
    $affiliateUsersTable = db_prefix() . 'affiliate_users';

    $aColumns = [
        'number',
        'total',
         'tblinvoices.datecreated as invoice_datecreated',
        get_sql_select_client_company(),
        $clientsTable . '.userid as mrd_no',
        $invoiceTable . '.status',
        'allowed_payment_modes',
        $invoiceTable . '.discount_total',
        "(SELECT GROUP_CONCAT(description SEPARATOR ', ')
            FROM $itemableTable
            WHERE rel_id = $invoiceTable.id AND rel_type = 'invoice') as all_items",
        'subtotal',
        "(SELECT SUM(amount) FROM $paymentRecordsTable WHERE invoiceid = $invoiceTable.id) as total_paid",
        "($invoiceTable.total - IFNULL((SELECT SUM(amount) FROM $paymentRecordsTable WHERE invoiceid = $invoiceTable.id), 0)) as balance",
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

        "CONCAT(au.firstname, ' ', au.lastname) as affiliate_user_name",
    ];

    $sIndexColumn = 'id';
    $sTable = $invoiceTable;

    $join = [
        "LEFT JOIN $clientsTable ON $clientsTable.userid = $invoiceTable.clientid",
        "LEFT JOIN $affiliateUsersTable AS au ON au.affiliate_code = $clientsTable.affiliate_code COLLATE utf8mb4_unicode_ci",
        'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . $invoiceTable . '.currency',
        'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . $invoiceTable . '.project_id',
    ];

    $custom_fields = get_table_custom_fields('invoice');
    $customFieldsColumns = [];

    foreach ($custom_fields as $key => $field) {
        $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
        $customFieldsColumns[] = $selectAs;
        $aColumns[] = 'ctable_' . $key . '.value as ' . $selectAs;
        $join[] = 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . $invoiceTable . '.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id'];
    }

    $where = [];

    if ($filtersWhere = $this->getWhereFromRules()) {
        $where[] = $filtersWhere;
    }

    if ($clientid != '') {
        $where[] = 'AND ' . $invoiceTable . '.clientid=' . $this->ci->db->escape_str($clientid);
    }

    if ($project_id) {
        $where[] = 'AND project_id=' . $this->ci->db->escape_str($project_id);
    }

    if (staff_cant('view', 'invoices')) {
        $where[] = 'AND ' . get_invoices_where_sql_for_staff(get_staff_user_id());
    }

    $aColumns = hooks()->apply_filters('invoices_table_sql_columns', $aColumns);

    if (count($custom_fields) > 4) {
        @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
    }

    $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
        $invoiceTable . '.id',
        $invoiceTable . '.clientid',
        db_prefix() . 'currencies.name as currency_name',
        'formatted_number',
        'project_id',
        'hash',
        'recurring',
        'deleted_customer_name',
        'allowed_payment_modes',
        'discount_total',
    ]);

    $output = $result['output'];
    $rResult = $result['rResult'];

    $total_subtotal = 0;
$total_discount = 0;
$total_total = 0;
$total_paid = 0;
$total_balance = 0;
$total_service_charge = 0;

foreach ($rResult as $aRow) {
    $subtotal = (float) $aRow['subtotal'];
    $discount = (float) $aRow['discount_total'];
    $total = (float) $aRow['total'];
    $paid = (float) $aRow['total_paid'];
    $balance = (float) $aRow['balance'];
    $service_charge = isset($aRow['service_charge']) ? (float) $aRow['service_charge'] : 0;

    // Check allowed payment modes to override service charge
    $payment_mode_names = [];
    if (!empty($aRow['allowed_payment_modes'])) {
        $mode_ids = @unserialize($aRow['allowed_payment_modes']);
        if (is_array($mode_ids)) {
            foreach ($mode_ids as $id) {
                $mode = $this->ci->payment_modes_model->get($id);
                if ($mode) {
                    $payment_mode_names[] = strtolower($mode->name);
                }
            }
        }
    }

    // Accumulate totals
    $total_subtotal += $subtotal;
    $total_discount += $discount;
    $total_total += $total;
    $total_paid += $paid;
    $total_balance += $balance;
    $total_service_charge += $service_charge;
}


    foreach ($rResult as $aRow) {
        $formattedNumber = format_invoice_number($aRow['id']);

        if (empty($aRow['formatted_number']) || $formattedNumber !== $aRow['formatted_number']) {
            $this->ci->invoices_model->save_formatted_number($aRow['id']);
        }

        $row = [];

        $numberOutput = is_numeric($clientid) || $project_id
            ? '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank" class="tw-font-medium">' . e($formattedNumber) . '</a>'
            : '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" onclick="init_invoice(' . $aRow['id'] . '); return false;" class="tw-font-medium">' . e($formattedNumber) . '</a>';

        if ($aRow['recurring'] > 0) {
            $numberOutput .= '<br /><span class="label label-primary inline-block tw-mt-1 tw-font-medium">' . _l('invoice_recurring_indicator') . '</span>';
        }

        $numberOutput .= '<div class="row-options">';
        $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
        if (staff_can('edit', 'invoices')) {
            $numberOutput .= ' | <a href="' . admin_url('invoices/invoice/' . $aRow['id']) . '">' . _l('edit') . '</a>';
        }
        $numberOutput .= '</div>';

        $row[] = $numberOutput;
         $row[] = !empty($aRow['invoice_datecreated']) ? date('d-m-Y h:i A', strtotime($aRow['invoice_datecreated'])) : '';
        $row[] =  str_pad($aRow['mrd_no'], 0, '0', STR_PAD_LEFT);
        $row[] = empty($aRow['deleted_customer_name']) ? '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>' : e($aRow['deleted_customer_name']);
        $row[] = $aRow['affiliate_user_name'] ?: 'N/A';
        $row[] = $aRow['all_items'] ?: '-';

    $payment_modes = '';
$payment_mode_names = []; // initialize as array
if (!empty($aRow['allowed_payment_modes'])) {
    $mode_ids = @unserialize($aRow['allowed_payment_modes']);
    if (is_array($mode_ids)) {
        foreach ($mode_ids as $id) {
            $mode = $this->ci->payment_modes_model->get($id);
            if ($mode) {
                $payment_modes .= '<span class="label label-default mright5">' . $mode->name . '</span>';
                $payment_mode_names[] = strtolower($mode->name); // lowercase for comparison
            }
        }
    }
}
$row[] = $payment_modes;

        $row[] = app_format_money($aRow['subtotal'], $aRow['currency_name']);
        $row[] = app_format_money($aRow['discount_total'], $aRow['currency_name']);
        $row[] = app_format_money($aRow['total'], $aRow['currency_name']);

$service_charge_value = $aRow['service_charge'] ?? 0;

if (!in_array('upi', $payment_mode_names) && !in_array('credit/debit card', $payment_mode_names)) {
    $service_charge = 0; // zero out service charge if payment mode is NOT UPI or credit/debit card
}


$row[] = $service_charge_value;
        $row[] = app_format_money($aRow['total_paid'], $aRow['currency_name']);
        $row[] = app_format_money($aRow['balance'], $aRow['currency_name']);

        foreach ($customFieldsColumns as $customFieldColumn) {
            $row[] = (strpos($customFieldColumn, 'date_picker_') !== false) ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn];
        }

        $row['DT_RowClass'] = 'has-row-options';

        $row = hooks()->apply_filters('invoices_table_row_data', $row, $aRow);

        $output['aaData'][] = $row;
    }

    $output['totals'] = [

    'subtotal' => app_format_money($total_subtotal, get_base_currency()),
    'discount_total' => app_format_money($total_discount, get_base_currency()),
    'total' => app_format_money($total_total, get_base_currency()),
    'total_paid' => app_format_money($total_paid, get_base_currency()),
    'balance' => app_format_money($total_balance, get_base_currency()),
    'service_charge' => app_format_money($total_service_charge, get_base_currency()),
];

log_message('debug', 'Totals Calculated: Subtotal = ' . $total_subtotal);
log_message('debug', 'Totals Calculated: Discount Total = ' . $total_discount);
log_message('debug', 'Totals Calculated: Total = ' . $total_total);
log_message('debug', 'Totals Calculated: Total Paid = ' . $total_paid);
log_message('debug', 'Totals Calculated: Balance = ' . $total_balance);
log_message('debug', 'Totals Calculated: Service Charge = ' . $total_service_charge);


    return $output;

    })->setRules([
        App_table_filter::new('number', 'NumberRule')->label(_l('invoice_add_edit_number')),
        App_table_filter::new('total', 'NumberRule')->label(_l('invoice_total')),
        App_table_filter::new('subtotal', 'NumberRule')->label(_l('invoice_subtotal')),
        App_table_filter::new('date', 'DateRule')->label(_l('invoice_add_edit_date')),
        App_table_filter::new('duedate', 'DateRule')
            ->label(_l('invoice_dt_table_heading_duedate'))
            ->withEmptyOperators(),
        App_table_filter::new('sent', 'BooleanRule')->label(_l('estimate_status_sent'))->raw(function ($value) {
            if ($value == '1') {
                return 'sent = 1';
            }

            return 'sent = 0 and ' . db_prefix() . 'invoices.status NOT IN (' . Invoices_model::STATUS_PAID . ',' . Invoices_model::STATUS_CANCELLED . ')';
        }),
        App_table_filter::new('sale_agent', 'SelectRule')->label(_l('sale_agent_string'))
            ->withEmptyOperators()
            ->emptyOperatorValue(0)
            ->isVisible(fn () => staff_can('view', 'invoices'))
            ->options(function ($ci) {
                return collect($ci->invoices_model->get_sale_agents())->map(function ($data) {
                    return [
                        'value' => $data['sale_agent'],
                        'label' => get_staff_full_name($data['sale_agent']),
                    ];
                })->all();
            }),

        App_table_filter::new('status', 'MultiSelectRule')
            ->label(_l('invoice_dt_table_heading_status'))
            ->options(function ($ci) {
                return collect($ci->invoices_model->get_statuses())->map(fn ($status) => [
                    'value' => (string) $status,
                    'label' => format_invoice_status($status, '', false),
                ])->all();
            }),

        App_table_filter::new('year', 'MultiSelectRule')
            ->label(_l('year'))
            ->raw(function ($value, $operator) {
                if ($operator == 'in') {
                    return 'YEAR(date) IN (' . implode(',', $value) . ')';
                }

                return 'YEAR(date) NOT IN (' . implode(',', $value) . ')';
            })
            ->options(function ($ci) {
                return collect($ci->invoices_model->get_invoices_years())->map(fn ($data) => [
                    'value' => $data['year'],
                    'label' => $data['year'],
                ])->all();
            }),
        App_table_filter::new('recurring', 'BooleanRule')->label(_l('invoices_list_recurring'))->raw(function ($value) {
            return $value == '1' ? 'recurring > 0' : 'recurring = 0';
        }),
        App_table_filter::new('not_have_payment', 'BooleanRule')->label(_l('invoices_list_not_have_payment'))->raw(function ($value) {
            return '(' . db_prefix() . 'invoices.id ' . ($value == '1' ? 'NOT IN' : 'IN') . ' (SELECT invoiceid FROM ' . db_prefix() . 'invoicepaymentrecords UNION ALL SELECT invoice_id FROM ' . db_prefix() . 'credits) AND ' . db_prefix() . 'invoices.status != ' . Invoices_model::STATUS_CANCELLED . ')';
        }),
        App_table_filter::new('made_payment_by', 'MultiSelectRule')->label(str_replace(' %s', '', _l('invoices_list_made_payment_by')))->options(function ($ci) {
            return collect($ci->payment_modes_model->get('', [], true))->map(fn ($mode) => [
                'value' => $mode['id'],
                'label' => $mode['name'],
            ])->all();
        })->raw(function ($value, $operator, $sqlOperator) {
            $dbPrefix    = db_prefix();
            $sqlOperator = $sqlOperator['operator'];

            return "({$dbPrefix}invoices.id IN (SELECT invoiceid FROM {$dbPrefix}invoicepaymentrecords WHERE paymentmode {$sqlOperator} ('" . implode("','", $value) . "')))";
        }),
    ]);