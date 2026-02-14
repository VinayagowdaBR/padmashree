<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="postPaymentDiscountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-gift"></i> <?php echo _l('apply_post_payment_discount'); ?>
                </h4>
            </div>
            <?php echo form_open(admin_url('invoices/apply_post_payment_discount'), ['id' => 'post_payment_discount_form']); ?>
            <div class="modal-body">
                <input type="hidden" name="invoice_id" value="<?php echo $invoice->id; ?>">
                
                <div class="alert alert-info">
                    <strong><?php echo _l('invoice'); ?>:</strong> <?php echo format_invoice_number($invoice->id); ?><br>
                    <strong><?php echo _l('invoice_total'); ?>:</strong> <?php echo app_format_money($invoice->total, $invoice->currency_name); ?><br>
                    <strong><?php echo _l('status'); ?>:</strong> <span class="label label-success"><?php echo _l('invoice_status_paid'); ?></span>
                </div>

                <div class="form-group">
                    <label for="discount_amount" class="control-label">
                        <?php echo _l('discount_amount'); ?> <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-addon"><?php echo $invoice->symbol; ?></span>
                        <input type="number" 
                               id="discount_amount" 
                               name="discount_amount" 
                               class="form-control" 
                               min="0.01" 
                               max="<?php echo $invoice->total; ?>" 
                               step="0.01" 
                               placeholder="0.00"
                               required>
                    </div>
                    <p class="text-muted mtop5">
                        <small><?php echo _l('post_payment_discount_help'); ?></small>
                    </p>
                </div>

                <div class="form-group">
                    <label for="payment_mode" class="control-label">
                        <?php echo _l('refund_payment_mode'); ?> <span class="text-danger">*</span>
                    </label>
                    <select name="payment_mode" id="payment_mode" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
                        <option value=""></option>
                        <?php foreach ($payment_modes as $mode) { ?>
                            <option value="<?php echo e($mode['id']); ?>"><?php echo e($mode['name']); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="post_payment_note" class="control-label"><?php echo _l('note'); ?></label>
                    <textarea name="note" id="post_payment_note" class="form-control" rows="3" 
                              placeholder="<?php echo _l('post_payment_discount_note_placeholder'); ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary" id="submit_post_payment_discount">
                    <i class="fa fa-check"></i> <?php echo _l('apply_discount_and_create_refund'); ?>
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
$(function() {
    $('#post_payment_discount_form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submit_post_payment_discount');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('#postPaymentDiscountModal').modal('hide');
                    
                    // Reload the invoice preview to show updated data
                    if (typeof init_invoice === 'function') {
                        init_invoice(<?php echo $invoice->id; ?>);
                    } else {
                        // Fallback: reload the page
                        window.location.reload();
                    }
                } else {
                    alert_float('danger', response.message || 'Error applying discount');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                alert_float('danger', 'An error occurred: ' + error);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
