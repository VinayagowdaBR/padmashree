<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'appointment_manager')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="#" onclick="addServiceCat();" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('appmgr_ser_cat'); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php render_datatable([
                            _l('appmgr_treatment_name'),
                            _l('appmgr_treatment_heading'),
                            _l('appmgr_added_date')
                        ], 'appmgr_ser_cats'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-wrapper"></div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-appmgr_ser_cats', window.location.href, [], []);
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

    function addServiceCat() {
        $("#modal-wrapper").load(
            admin_url + "appointment_manager/service_category", {
                'modal': true,
                'edit': false
            },
            function() {
                $("#ServiceCatModal").modal({
                    backdrop: "static",
                    show: true,
                });
                init_selectpicker();
                appValidateForm($("#service-cat-form"), {
                    service_id: "required",
                    name: "required",
                });
            }
        );
    }
    function editServiceCat(id) {
        $("#modal-wrapper").load(
            admin_url + "appointment_manager/service_category", {
                'modal': true,
                'edit': id
            },
            function() {
                $("#ServiceCatModal").modal({
                    backdrop: "static",
                    show: true,
                });
                init_selectpicker();
                appValidateForm($("#service-cat-form"), {
                    service_id: "required",
                    name: "required",
                });
            }
        );
    }
</script>
</body>

</html>