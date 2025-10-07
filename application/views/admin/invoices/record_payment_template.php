<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?= form_open(admin_url('invoices/record_payment'), ['id' => 'record_payment_form']); ?>
<div class="col-md-12 no-padding animated fadeIn">
    <div class="panel_s">
        <?= form_hidden('invoiceid', $invoice->id); ?>
        <div class="panel-heading">
            <h4 class="panel-title">
                <?= _l('record_payment_for_invoice'); ?>
                <?= e(format_invoice_number($invoice->id)); ?>
            </h4>
        </div>
        <div class="panel-body">
            <div class="row">

                <!-- MULTI PAYMENT MODES START -->
                <div class="col-md-12">
                    <h5 class="bold"><?= _l('payment_splits'); ?></h5>
                    <div id="payment-modes-wrapper">

                        <div class="payment-row row">
                            <div class="col-md-4">
                                <?php
                                $amount = $invoice->total_left_to_pay;
                                echo render_input('payments[0][amount]', 'record_payment_amount_received', $amount, 'number', ['max' => $amount]);
                                ?>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payments[0][paymentmode]" class="control-label"><?= _l('payment_mode'); ?></label>
                                    <select class="selectpicker" name="payments[0][paymentmode]" data-width="100%"
                                        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($payment_modes as $mode) { ?>
                                            <?php if (is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) { ?>
                                                <option value="<?= e($mode['id']); ?>"><?= e($mode['name']); ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <?= render_input('payments[0][transactionid]', 'payment_transaction_id'); ?>
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-danger btn-sm remove-payment-row" style="margin-top:25px;display:none;">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                    <button type="button" id="add-payment-row" class="btn btn-info btn-sm mtop10">
                        <i class="fa fa-plus"></i> <?= _l('add_payment_mode'); ?>
                    </button>
                </div>
                <!-- MULTI PAYMENT MODES END -->

                <div class="col-md-6 mtop20">
                    <?= render_date_input('date', 'record_payment_date', _d(date('Y-m-d'))); ?>
                </div>

                <div class="col-md-6 mtop20">
                    <div class="form-group">
                        <label for="note" class="control-label"><?= _l('record_payment_leave_note'); ?></label>
                        <textarea name="note" class="form-control" rows="6"
                            placeholder="<?= _l('invoice_record_payment_note_placeholder'); ?>" id="note"></textarea>
                    </div>
                </div>

                <div class="col-md-12 tw-mt-3">
                    <?php
                    $pr_template = is_email_template_active('invoice-payment-recorded');
                    $sms_trigger = is_sms_trigger_active(SMS_TRIGGER_PAYMENT_RECORDED);
                    if ($pr_template || $sms_trigger) { ?>
                        <div class="checkbox checkbox-primary mtop15">
                            <input type="checkbox" name="do_not_send_email_template" id="do_not_send_email_template">
                            <label for="do_not_send_email_template">
                                <?php
                                if ($pr_template) {
                                    echo _l('do_not_send_invoice_payment_email_template_contact');
                                    if ($sms_trigger) {
                                        echo '/';
                                    }
                                }
                                if ($sms_trigger) {
                                    echo 'SMS ' . _l('invoice_payment_recorded');
                                }
                                ?>
                            </label>
                        </div>
                    <?php } ?>
                    <div class="checkbox checkbox-primary mtop15 do_not_redirect hide">
                        <input type="checkbox" name="do_not_redirect" id="do_not_redirect" checked>
                        <label for="do_not_redirect"><?= _l('do_not_redirect_payment'); ?></label>
                    </div>
                </div>
            </div>

            <?php hooks()->do_action('after_admin_last_record_payment_form_field', $invoice); ?>

            <?php if ($payments) { ?>
                <div class="mtop25 inline-block full-width">
                    <h5 class="bold"><?= _l('invoice_payments_received'); ?></h5>
                    <?php include_once APPPATH . 'views/admin/invoices/invoice_payments_table.php'; ?>
                </div>
            <?php } ?>

            <?php hooks()->do_action('before_admin_add_payment_form_submit', $invoice); ?>
        </div>

        <div class="panel-footer text-right">
            <a href="#" class="btn btn-danger" onclick="init_invoice(<?= e($invoice->id); ?>); return false;"><?= _l('cancel'); ?></a>
            <button type="submit" autocomplete="off" data-loading-text="<?= _l('wait_text'); ?>"
                data-form="#record_payment_form" class="btn btn-success"><?= _l('submit'); ?></button>
        </div>
    </div>
</div>
<?= form_close(); ?>

<script>
jQuery(document).ready(function ($) {
    init_selectpicker();
    init_datepicker();

    let rowIndex = 1;

    // add new payment row
    $('#add-payment-row').on('click', function () {
        // Get the first payment row as template
        let templateRow = $('.payment-row:first');
        
        // Create new row HTML structure instead of cloning
        let newRowHtml = `
            <div class="payment-row row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="payments[${rowIndex}][amount]" class="control-label"><?= _l('record_payment_amount_received'); ?></label>
                        <input type="number" id="payments[${rowIndex}][amount]" name="payments[${rowIndex}][amount]" class="form-control" value="" max="<?= $invoice->total_left_to_pay; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="payments[${rowIndex}][paymentmode]" class="control-label"><?= _l('payment_mode'); ?></label>
                        <select class="selectpicker" name="payments[${rowIndex}][paymentmode]" data-width="100%" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                            <option value=""></option>
                            <?php foreach ($payment_modes as $mode) { 
                                // log_message('debug', 'Available Payment Modes: ' . print_r($payment_modes, true)); 
                                ?>
                                 <option value="<?= e($mode['id']); ?>"><?= e($mode['name']); ?></option>
                                // <?php if (is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) { ?>
                                   
                                // <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="payments[${rowIndex}][transactionid]" class="control-label"><?= _l('payment_transaction_id'); ?></label>
                        <input type="text" id="payments[${rowIndex}][transactionid]" name="payments[${rowIndex}][transactionid]" class="form-control" value="">
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-center">
                    <button type="button" class="btn btn-danger btn-sm remove-payment-row" style="margin-top:25px;">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

        // Append to wrapper
        $('#payment-modes-wrapper').append(newRowHtml);

        // Initialize selectpicker for the new row only
        $('#payment-modes-wrapper .payment-row:last .selectpicker').selectpicker();

        rowIndex++;
    });

    // remove row
    $(document).on('click', '.remove-payment-row', function () {
        let paymentRow = $(this).closest('.payment-row');
        
        // Destroy selectpicker before removing
        paymentRow.find('.selectpicker').selectpicker('destroy');
        
        // Remove the row
        paymentRow.remove();
    });

    // custom submit handling
    $(document).on('submit', '#record_payment_form', function (e) {
        e.preventDefault();

        let rows = [];
        $('.payment-row').each(function () {
            let amount = $(this).find('[name*="[amount]"]').val();
            let mode = $(this).find('[name*="[paymentmode]"]').val();
            let txn = $(this).find('[name*="[transactionid]"]').val();

            if (amount && mode) {
                rows.push({
                    amount: amount,
                    paymentmode: mode,
                    transactionid: txn
                });
            }
        });

        if (rows.length === 0) {
            alert('Please enter at least one valid payment row.');
            return false;
        }

        let commonData = {
            invoiceid: $('input[name="invoiceid"]').val(),
            date: $('input[name="date"]').val(),
            note: $('#note').val(),
            do_not_send_email_template: $('#do_not_send_email_template').is(':checked') ? 1 : 0,
            do_not_redirect: $('#do_not_redirect').is(':checked') ? 1 : 0,
        };

        // process payments one by one
        function saveNext(i) {
            if (i >= rows.length) {
                // reload invoice after all
                init_invoice(commonData.invoiceid);
                return;
            }

            let payload = $.extend({}, commonData, rows[i]);

            $.post('<?= admin_url('invoices/record_payment'); ?>', payload)
                .done(function () {
                    saveNext(i + 1);
                })
                .fail(function () {
                    alert('Error saving payment row ' + (i + 1));
                });
        }

        saveNext(0);
    });

});
</script>