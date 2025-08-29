<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_filters _hidden_inputs hidden">
                            <?php
                            echo form_hidden('period_from');
                            echo form_hidden('period_to');
                            ?>
                        </div>
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php echo render_date_input('date_f', '', '', ['placeholder' => _l('appmgr_from_date')]); ?>
                                </div>
                                <div class="col-md-4">
                                    <?php echo render_date_input('date_t', '', '', ['placeholder' => _l('appmgr_to_date')]); ?>
                                </div>
                                <div class="col-md-4">
                                    <?php
                                    echo render_select('status', $status, ['id', ['name']], '', '', ['data-none-selected-text' => 'Status']);
                                    ?>
                                </div>
                                <div class="col-md-4">
                                    <?php
                                    $value = !is_admin() ? get_staff_user_id() : '';
                                    echo render_select('staff', $staffs, ['staffid', ['firstname', 'lastname']], '', $value, ['data-none-selected-text' => _l('practitioners'), 'disabled' => $value ? true : false]);
                                    ?>
                                </div>
                                <div class="col-md-4">
                                    <select id="location" class="selectpicker display-block" data-width="100%"
                                        name="location" data-none-selected-text="<?php echo _l('appmgr_locations'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($locations as $location) { ?>
                                            <option value="<?php echo $location['id']; ?>">
                                                <?php echo $location['name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php render_datatable([

                            _l('appmgr_appointee_name'),

                            _l('appmgr_appointment_client'),

                            _l('appmgr_appointment_date'),

                            _l('appmgr_location'),

                            _l('appmgr_status'),

                            _l('appmgr_added_by')

                        ], 'appmgr_reports'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        var reportBookingServerParams = [];
        reportBookingServerParams["date_f"] = '[name="date_f"]';
        reportBookingServerParams["date_t"] = '[name="date_t"]';
        reportBookingServerParams["staff"] = '[name="staff"]';
        reportBookingServerParams["location"] = '[name="location"]';
        reportBookingServerParams["status"] = '[name="status"]';
        initDataTable('.table-appmgr_reports', window.location.href, [], [], reportBookingServerParams);

    });
    function filterByDateRange() {
        var dateFrom = $('input[name="date_f"]').val();
        var dateTo = $('input[name="date_t"]').val();
        var staff = $('select[name="staff"]').val();
        var location = $('select[name="location"]').val();
        var status = $('select[name="status"]').val();
        $('.table-appmgr_reports').DataTable().ajax.reload(null, false);
    }
    $('input[name="date_f"], input[name="date_t"], select[name="staff"], select[name="location"],select[name="status"]').on('change', function() {
        filterByDateRange();
    });
</script>
</body>

</html>