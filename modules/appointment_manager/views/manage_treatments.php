<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'appointment_manager')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="<?php echo admin_url('appointment_manager/service'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('new_treatment_appmgr'); ?>
                        </a>
                        <a href="<?php echo admin_url('appointment_manager/service_categories'); ?>" class="btn btn-primary">
                            <?php echo _l('appmgr_ser_cats'); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php render_datatable([
                            _l('appmgr_treatment_name'),
                            _l('appmgr_treatment_description'),
                            _l('appmgr_added_by'),
                            _l('appmgr_added_date')
                        ], 'appmgr_treatments'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-appmgr_treatments', window.location.href, [], []);
    });
    function _treatment_init_data(data, id) {
        var hash = window.location.hash;
        var $treatmentModal = $("#treatment-modal");
        $treatmentModal.find(".data").html(data.treatmentView.data);
        $treatmentModal.modal({
            show: true,
            backdrop: "static",
        });
    }
</script>
</body>

</html>