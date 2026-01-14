<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="refund_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open('admin/invoices/process_refund', ['id' => 'refund_form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('process_invoice_refund'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="invoiceid" value="<?php echo $invoice->id; ?>">
                
                <div class="alert alert-info">
                    <div><strong><?php echo _l('invoice_total'); ?>:</strong> <?php echo app_format_money($invoice->total, $invoice->currency_name); ?></div>
                    <div><strong><?php echo _l('total_paid'); ?>:</strong> <?php echo app_format_money($invoice->payments_total ?? 0, $invoice->currency_name); ?></div>
                    <div><strong><?php echo _l('refundable_amount'); ?>:</strong> <?php echo app_format_money($refundable_amount, $invoice->currency_name); ?></div>
                </div>
                
                <!-- Refund Type Selection -->
                <div class="form-group">
                    <label for="refund_type"><?php echo _l('refund_type'); ?> <span class="text-danger">*</span></label>
                    <select name="refund_type" id="refund_type" class="form-control selectpicker" required onchange="toggle_refund_input()" data-width="100%">
                        <option value="amount"><?php echo _l('amount'); ?></option>
                        <option value="percentage"><?php echo _l('percentage'); ?></option>
                    </select>
                </div>
                
                <!-- Amount Input -->
                <div class="form-group" id="amount_input_group">
                    <label for="refund_value"><?php echo _l('refund_amount'); ?> <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="refund_value" id="refund_value" class="form-control" 
                           max="<?php echo $refundable_amount; ?>" required>
                    <small class="text-muted"><?php echo _l('max_refundable'); ?>: <?php echo app_format_money($refundable_amount, $invoice->currency_name); ?></small>
                </div>
                
                <!-- Percentage Input -->
                <div class="form-group hide" id="percentage_input_group">
                    <label for="refund_percentage"><?php echo _l('refund_percentage'); ?> <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="refund_percentage" id="refund_percentage" class="form-control" 
                           min="0" max="100">
                    <small class="text-muted"><?php echo _l('enter_percentage_to_refund'); ?></small>
                    <div class="calculated-amount-preview mt-2" style="margin-top: 10px; font-weight: bold;"></div>
                </div>
                
                <!-- Payment Mode -->
                <div class="form-group">
                    <label for="refund_mode"><?php echo _l('payment_mode'); ?></label>
                    <select name="refund_mode" class="form-control selectpicker" data-width="100%">
                        <option value=""><?php echo _l('select_refund_mode'); ?></option>
                        <?php foreach ($payment_modes as $mode) { ?>
                            <option value="<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                
                <!-- Date -->
                <div class="form-group">
                    <label for="refund_date"><?php echo _l('refund_date'); ?> <span class="text-danger">*</span></label>
                    <div class="input-group date">
                        <input type="text" name="date" id="refund_date" class="form-control datepicker" value="<?php echo date(get_option('dateformat')); ?>" required autocomplete="off">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar calendar-icon"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Transaction ID -->
                <div class="form-group">
                    <label for="transaction_id"><?php echo _l('transaction_id'); ?></label>
                    <input type="text" name="transaction_id" class="form-control" placeholder="<?php echo _l('optional'); ?>">
                </div>
                
                <!-- Note -->
                <div class="form-group">
                    <label for="refund_note"><?php echo _l('note'); ?></label>
                    <textarea name="note" id="refund_note" class="form-control" rows="3" placeholder="<?php echo _l('refund_note_placeholder'); ?>"></textarea>
                </div>
                
                <!-- Send Notification -->
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="send_notification" value="1" id="send_notification" checked>
                    <label for="send_notification">
                        <?php echo _l('send_refund_notification_to_customer'); ?>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-warning"><i class="fa fa-undo"></i> <?php echo _l('process_refund'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
function toggle_refund_input() {
    var type = $('#refund_type').val();
    if (type === 'amount') {
        $('#amount_input_group').removeClass('hide');
        $('#percentage_input_group').addClass('hide');
        $('#refund_value').prop('required', true);
        $('#refund_percentage').prop('required', false).val('');
        $('.calculated-amount-preview').html('');
    } else {
        $('#amount_input_group').addClass('hide');
        $('#percentage_input_group').removeClass('hide');
        $('#refund_value').prop('required', false).val('');
        $('#refund_percentage').prop('required', true);
    }
}

// Calculate amount preview for percentage
$('#refund_percentage').on('input', function() {
    var percentage = parseFloat($(this).val()) || 0;
    var invoice_total = <?php echo $invoice->total; ?>;
    var refundable_amount = <?php echo $refundable_amount; ?>;
    var calculated_amount = (invoice_total * percentage) / 100;
    
    // Ensure calculated amount doesn't exceed refundable amount
    if (calculated_amount > refundable_amount) {
        calculated_amount = refundable_amount;
        $(this).val((refundable_amount / invoice_total * 100).toFixed(2));
    }
    
    $('.calculated-amount-preview').html(
        '<?php echo _l('calculated_refund_amount'); ?>: ' + 
        format_money(calculated_amount, '<?php echo $invoice->currency_name; ?>')
    );
});

// Initialize datepicker
$(function() {
    init_datepicker();
    $('#refund_modal .selectpicker').selectpicker('refresh');
});

// Form submission
$('#refund_form').on('submit', function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    var refund_type = $('#refund_type').val();
    
    // Add the correct refund value based on type
    if (refund_type === 'percentage') {
        formData += '&refund_value=' + $('#refund_percentage').val();
    }
    
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert_float('success', '<?php echo _l('refund_processed_successfully'); ?>');
                $('#refund_modal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                alert_float('danger', response.message || '<?php echo _l('refund_process_failed'); ?>');
            }
        },
        error: function() {
            alert_float('danger', '<?php echo _l('something_went_wrong'); ?>');
        }
    });
});
</script>
