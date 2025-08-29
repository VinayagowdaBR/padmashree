<?php if (affiliate_has_permission('settings', '', 'create')) { ?>
    <a href="#" onclick="new_member_group(); return false;" class="btn btn-info mbot10"><?php echo _l('new'); ?></a>
<?php } ?>

<table class="table table-member-groups">
  <thead>
    <th><?php echo _l('name'); ?></th>
    <th><?php echo _l('options'); ?></th>
  </thead>
  <tbody>
  </tbody>
</table>

<div class="modal fade" id="member_group_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button group="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('member_group_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('member_group_add_heading'); ?></span>
                </h4>
            </div>

            <?php echo form_open('affiliate/member_group', array('id' => 'member-group-modal')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'name'); ?>
                        <?php echo form_hidden('id'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button group="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button group="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<!-- ✅ AJAX Form Script -->
<script>
$(document).ready(function () {
    $('#member-group-modal').on('submit', function (e) {
        e.preventDefault(); // Stop normal submission

        var form = $(this);
        var data = form.serialize();

        $.post(form.attr('action'), data).done(function (response) {
            try {
                response = JSON.parse(response);
            } catch (err) {
                alert_float('danger', 'Invalid server response');
                return;
            }

            if (response.success) {
                alert_float('success', response.message);
                $('#member_group_modal').modal('hide');

                // Reload table if using DataTables
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('.table-member-groups')) {
                    $('.table-member-groups').DataTable().ajax.reload();
                } else {
                    // Optionally reload the page
                    location.href = "<?php echo admin_url('affiliate/settings?group=member_group'); ?>";
                }
            } else {
                alert_float('danger', response.message || 'Failed to save');
            }
        }).fail(function (xhr) {
            console.error('AJAX error:', xhr.responseText);
            alert_float('danger', 'Server error');
        });
    });
});
</script>
