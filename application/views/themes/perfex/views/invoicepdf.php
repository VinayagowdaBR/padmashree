<?php
defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:13px;">' . _l('PADMASHREE ADVANCED IMAGING SERVICES') . '</span><br />';
$info_right_column .= '<span style="font-weight:bold;font-size:10px;">' . _l('#97,17th Cross, M C Layout,Near Telephone Excange,Vijaynagar,Bangalore') . '</span><br />';
$info_right_column .= '<span style="font-weight:bold;font-size:10px;">' . _l('Tel : +91 80500 22311 / 80500 22411/ 80500 22511') . '</span><br />';

$info_left_column .= '<img src="' . pdf_logo_url() . '" width="120" height="70">';

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->writeHTML('<hr style="border: 1px solid #000; margin: 4px 0;">', false, false, false, false, '');
$pdf->writeHTML('<h2 style="text-align:center; margin-top: 4px; margin-bottom: 4px;">BILL / RECEIPT</h2>', false, false, false, false, '');
$pdf->writeHTML('<hr style="border: 1px solid #000; margin: 4px 0;">', false, false, false, false, '');

$label_width = '100px';

$left_info = '<p><span style="display:inline-block; width:' . $label_width . '; font-weight:bold;">' . _l('Name') . '</span>: ' . $invoice->client->company . '</p>';
// Add this at the top for debugging


$left_info = '<p><span style="display:inline-block; width:' . $label_width . '; font-weight:bold;">' . _l('Name') . '</span>: ' . $invoice->client->company . '</p>';

$age_value = '';
$age_option_value = '';

foreach ($pdf_custom_fields as $field) {
    $field_name_lower = strtolower(trim($field['name']));
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    
    if ($value == '') {
        continue;
    }
    
    if ($field_name_lower === 'age') {
        $age_value = $value;
        continue; // We'll handle age separately with age option
    } elseif ($field_name_lower === 'ageoption') {
        $age_option_value = $value;
        continue; // We'll handle this with age
    } elseif (!in_array($field_name_lower, ['age', 'sex', 'ageoption'])) {
        continue;
    }
    
    $left_info .= '<p><span style="display:inline-block; width:' . $label_width . '; font-weight:bold;">' . $field['name'] . '</span>: ' . $value . '</p>';
}

// Handle age and age option together
if (!empty($age_value)) {
    $age_display = $age_value;
    if (!empty($age_option_value)) {
        $age_display .= ' (' . $age_option_value . ')';
    }
    $left_info .= '<p><span style="display:inline-block; width:' . $label_width . '; font-weight:bold;">Age</span>: ' . $age_display . '</p>';
    
    // Log the combined age display
}



$right_info = '<p><span style="display:inline-block; width:' . $label_width . '; font-weight:bold;">' . _l('Date & Time') . '</span>: ' . _d($invoice->datecreated) . '</p>';
$right_info .= '<p style="color:#4e4e4e;"><span style="display:inline-block; width:' . $label_width . '; font-weight:bold;">BILL NO</span>: ' . $invoice_number . '</p>';

$CI =& get_instance();
$CI->db->select('firstname, lastname');
$CI->db->from('tblaffiliate_users');
$CI->db->where('affiliate_code', $invoice->client->affiliate_code);
$affiliate = $CI->db->get()->row();

if ($affiliate && !empty($affiliate->firstname)) {
    $fullName = trim($affiliate->firstname . ' ' . $affiliate->lastname);
    $right_info .= '<p><span style="display:inline-block; width:' . $label_width . '; font-weight:bold;">Referred By</span>: ' . $fullName . '</p>';
}

$left_info = hooks()->apply_filters('invoice_pdf_header_after_custom_fields', $left_info, $invoice);
$right_info = hooks()->apply_filters('invoice_pdf_header_after_custom_fields', $right_info, $invoice);

$left_info = '<div style="width:48%; float:left; text-align:left; padding-right:10px; box-sizing:border-box;">' . $left_info . '</div>';
$right_info = '<div style="width:48%; float:right; text-align:left; padding-left:10px; box-sizing:border-box;">' . $right_info . '</div>';

$combined_info = '<div style="overflow:hidden; margin-bottom:4px;">' . $left_info . $right_info . '</div>';
echo $combined_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->writeHTML('<hr style="border: 1px solid #000; margin: 4px 0;">', false, false, false, false, '');
$pdf->writeHTML('<h2 style="text-align:center; margin-top: 4px; margin-bottom: 4px;">INVESTIGATION DETAILS</h2>', false, false, false, false, '');
$pdf->writeHTML('<hr style="border: 1px solid #000; margin: 4px 0;">', false, false, false, false, '');

$pdf->Ln(2);

$items = get_items_table_data($invoice, 'invoice', 'pdf');
$tblhtml = $items->table();
$pdf->writeHTML($tblhtml, true, false, false, false, '');
$pdf->Ln(2);

// $tbltotal = '<table cellpadding="6" style="font-size:' . ($font_size + 2) . 'px">';
// $tbltotal .= '  
// <tr>
//     <td align="right" width="85%"><strong>' . _l('Charges(Rs)') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($invoice->subtotal, $invoice->currency_name) . '</td>
// </tr>';

// if (is_sale_discount_applied($invoice)) {
//     $tbltotal .= '
//     <tr>
//         <td align="right" width="85%"><strong>' . _l('Discount(Rs)');
//     if (is_sale_discount($invoice, 'percent')) {
//         $tbltotal .= ' (' . app_format_number($invoice->discount_percent, true) . '%)';
//     }
//     $tbltotal .= '</strong></td>
//         <td align="right" width="15%">-' . app_format_money($invoice->discount_total, $invoice->currency_name) . '</td>
//     </tr>';
// }

// $tbltotal .= '
// <tr style="background-color:#e5e7eb;">
//     <td align="right" width="85%"><strong>' . _l('Total Charges(Rs)') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($invoice->total, $invoice->currency_name) . '</td>
// </tr>';

// if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
//     $tbltotal .= '
//     <tr>
//         <td align="right" width="85%"><strong>' . _l('Advanced Amount(Rs)') . '</strong></td>
//         <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix() . 'invoicepaymentrecords', [
//         'field' => 'amount',
//         'where' => ['invoiceid' => $invoice->id],
//     ]), $invoice->currency_name) . '</td>
//     </tr>';
// }

// if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) {
//     $tbltotal .= '<tr style="background-color:#e5e7eb;">
//         <td align="right" width="85%"><strong>' . _l('Balance(Rs)') . '</strong></td>
//         <td align="right" width="15%">' . app_format_money($invoice->total_left_to_pay, $invoice->currency_name) . '</td>
//     </tr>';
// }

// $tbltotal .= '</table>';
// $pdf->writeHTML($tbltotal, true, false, false, false, '');


$tbltotal = '<table cellpadding="6" style="font-size:' . ($font_size + 2) . 'px">';
$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('Charges(Rs)') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($invoice->subtotal, $invoice->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($invoice)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('Discount(Rs)');
    if (is_sale_discount($invoice, 'percent')) {
        $tbltotal .= ' (' . app_format_number($invoice->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong></td>
        <td align="right" width="15%">-' . app_format_money($invoice->discount_total, $invoice->currency_name) . '</td>
    </tr>';
}

$tbltotal .= '
<tr style="background-color:#e5e7eb;">
    <td align="right" width="85%"><strong>' . _l('Total Charges(Rs)') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($invoice->total, $invoice->currency_name) . '</td>
</tr>';

if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $total_paid = sum_from_table(db_prefix() . 'invoicepaymentrecords', [
        'field' => 'amount',
        'where' => ['invoiceid' => $invoice->id],
    ]);
    
    // Check if it's a partial payment or full payment
    if ($total_paid < $invoice->total) {
        // Partial payment - show as "Advanced Amount"
        $tbltotal .= '
        <tr>
            <td align="right" width="85%"><strong>' . _l('Advanced Amount(Rs)') . '</strong></td>
            <td align="right" width="15%">-' . app_format_money($total_paid, $invoice->currency_name) . '</td>
        </tr>';
    } else {
        // Full payment - show as "Paid Amount"
        // $tbltotal .= '
        // <tr>
        //     <td align="right" width="85%"><strong>' . _l('Paid Amount(Rs)') . '</strong></td>
        //     <td align="right" width="15%">-' . app_format_money($total_paid, $invoice->currency_name) . '</td>
        // </tr>';
        $tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('Paid Amount(Rs)') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($total_paid, $invoice->currency_name) . '</td>
</tr>';
    }
}

if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) {
    $tbltotal .= '<tr style="background-color:#e5e7eb;">
        <td align="right" width="85%"><strong>' . _l('Balance(Rs)') . '</strong></td>
        <td align="right" width="15%">' . app_format_money($invoice->total_left_to_pay, $invoice->currency_name) . '</td>
    </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');


if (get_option('total_to_words_enabled') == 1) {
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, false, true, 'C', true);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(1);
}


if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
    $pdf->Ln(1);

$displayed_modes = [];

foreach ($invoice->payments as $payment) {
    $payment_mode = !empty($payment['paymentmethod']) ? $payment['paymentmethod'] : $payment['name'];

    // Only for UPI or Credit/Debit card
    if (in_array(strtolower($payment_mode), ['upi', 'credit/debit card'])) {
        $CI->db->select('value');
        $CI->db->from(db_prefix() . 'customfieldsvalues');
        $CI->db->where('relid', $invoice->id); // 🔁 Match with invoice ID
        $CI->db->where('fieldto', 'invoice');  // ✅ It’s an invoice-level field
        $CI->db->where('fieldid', 93);         // ✅ Payment Details custom field
        $custom = $CI->db->get()->row();

        if ($custom && !empty($custom->value)) {
            $payment_mode .= ' (' . $custom->value . ')';
        }
    }

    if (!in_array($payment_mode, $displayed_modes)) {
        $displayed_modes[] = $payment_mode;
    }
}



if (!empty($displayed_modes)) {
    $all_modes = implode(', ', $displayed_modes);
    $tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr>
         <td><strong>Received by : </strong>'  . $invoice->generated_by_email . '</td>
        </tr>
        <tr>
        <td><strong>Payment Details : < /strong>' .  $all_modes . '</td>
        </tr>
    </table>
    <hr style="border:0.5px solid #000; margin:3px 0;">';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

    // ➕ Show clinic name right-aligned with smaller text and no spacing above
    $pdf->SetFont($font_name, '', $font_size - 2); // smaller and not bold
    $pdf->Cell(0, 0, 'PADMASHREE ADVANCED IMAGING SERVICES', 0, 1, 'R', 0, '', 0); // right aligned
    $pdf->SetFont($font_name, '', $font_size); // reset to normal

    // Add spacing
$pdf->Ln(15); // Adds vertical space (adjust as needed)

}

if (!empty($invoice->sale_agent)) {
    $CI = &get_instance();
    $CI->load->model('staff_model');
    $agent = $CI->staff_model->get($invoice->sale_agent);
    if ($agent) {
        $pdf->Ln(3); // Slight spacing before the row
        $pdf->SetFont($font_name, '', $font_size);

        // Create two cells: one left-aligned, one right-aligned
        $pdf->MultiCell(100, 0, 'Sale Agent: ' . get_staff_full_name($agent->staffid), 0, 'L', 0, 0);
        // $pdf->MultiCell(0, 0, 'Authorised Signatory', 0, 'R', 0, 1);
    }
}


if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
    $pdf->Ln(2);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_html_offline_payment') . ':', 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);

    foreach ($payment_modes as $mode) {
        if (is_numeric($mode['id']) && !is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
            continue;
        }
        if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
            $pdf->Ln(1);
            $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
            $pdf->Ln(1);
            $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
        }
    }
}
