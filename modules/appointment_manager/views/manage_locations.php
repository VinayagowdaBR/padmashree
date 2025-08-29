<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'appointment_manager')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="<?php echo admin_url('appointment_manager/location'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('appmgr_new_location'); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php render_datatable([
                            _l('appmgr_location_name'),
                            _l('appmgr_location_operation_from'),
                            _l('appmgr_location_operation_to'),
                            _l('appmgr_location_description'),
                            _l('appmgr_added_by'),
                            _l('appmgr_added_date')
                        ], 'appmgr_locations'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-appmgr_locations', window.location.href, [], []);
    });
</script>
</body>

</html>