<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<link rel="stylesheet" type="text/css" id="full-calendar-css"
    href="<?php echo site_url('assets/plugins/fullcalendar/lib/main.min.css?v=3.1.6'); ?>">

<div id="wrapper">

    <div class="content">

        <div class="row">

            <div id="calendar_div" class="col-md-12">

                <?php if (staff_can('create', 'appointment_manager')) { ?>

                    <div class="tw-mb-2 sm:tw-mb-4">

                        <div class="row">

                            <div class="col-md-6">

                                <select id="location" class="selectpicker display-block" data-width="100%" name="location"
                                    data-none-selected-text="<?php echo _l('appmgr_locations'); ?>">

                                    <option value=""></option>

                                    <?php foreach ($locations as $location) { ?>

                                        <option value="<?php echo $location['id']; ?>"
                                            data-subtext="<?php echo _l('appmgr_loc_operational_hr') . date('g:i a', strtotime($location['operation_start_time'])) . ' - ' . date('g:i a', strtotime($location['operation_end_time'])); ?>"
                                            data-Tfrom="<?php echo date($timeFormat, strtotime($location['operation_start_time'])); ?>"
                                            data-Tto="<?php echo date($timeFormat, strtotime($location['operation_end_time'])); ?>">
                                            <?php echo $location['name']; ?></option>

                                    <?php } ?>

                                </select>

                            </div>

                            <div class="col-md-6" id="appointess">

                                <?php

                                echo render_select('staff', $staffs, ['staffid', ['firstname', 'lastname']], '', '', ['data-none-selected-text' => _l('appmgr_appointee_label')]);

                                ?>

                            </div>

                        </div>

                    </div>

                <?php } ?>

                <div class="panel_s">

                    <div class="view-full-clndr">
                        <a href="javascript:void(0);" onclick="copyEmbeddedCode();" data-href="<?php echo site_url('appointment_manager/appointment_manager_client/public_form') ?>"
                            class="btn btn-primary"><i class="fa-solid fa-code"></i> <?php echo _l('appmgr_embcode_lable'); ?></a>
                        <a href="<?php echo site_url('appointment_manager/appointment_manager_client/public_form') ?>"
                            class="btn btn-primary" target="_blank"><i class="fa-solid fa-users"></i> <?php echo _l('appmgr_publink_lable'); ?></a>
                        <button type="button" class="btn full-view clk-evnt"><i
                                class="fa-solid fa-up-right-and-down-left-from-center"></i> <?php echo _l('appmgr_expview_lable'); ?></button>
                        <button type="button" class="btn normal-view clk-evnt"><i
                                class="fa-solid fa-arrows-left-right-to-line"></i><?php echo _l('appmgr_newlink_lable'); ?></button>
                        <button type="button" onclick="addAppointment();" class="btn btn-primary pull-left"> <i
                                class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('appmgr_add_appointment_lable'); ?></button>
                    </div>


                    <div class="panel-body">

                        <div class="wrap-small-calendar-filter">

                            <div id="small-calendar"></div>

                        </div>

                        <div class="" id="appointment_manager">

                        </div>

                        <div id="appoint_div" class="hide">

                            <div class="panel_s">

                                <div class="panel-body">

                                    <?php echo $this->load->view('appointment_manager/appointment_form'); ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</div>

<?php init_tail(); ?>
<script>
    app.lang.appmgr_apt_dtails_heading = '<?php echo _l('appmgr_apt_dtails_heading'); ?>';
    app.lang.appmgr_appointee_label = '<?php echo _l('appmgr_appointee_label'); ?>';
    app.lang.appmgr_location = '<?php echo _l('appmgr_location'); ?>';
    app.lang.appmgr_client_label = '<?php echo _l('appmgr_client_label'); ?>';
    app.lang.appmgr_treatment_heading = '<?php echo _l('appmgr_treatment_heading'); ?>';
    app.lang.appmgr_appointm_description = '<?php echo _l('appmgr_appointm_description'); ?>';
    app.lang.appmgr_room_label = '<?php echo _l('appmgr_room_label'); ?>';
    app.lang.appmgr_ser_cats = '<?php echo _l('appmgr_ser_cats'); ?>';
    app.lang.appmgr_appointment_time = '<?php echo _l('appmgr_appointment_time'); ?>';
    app.lang.appmgr_appointment_duration = '<?php echo _l('appmgr_appointment_duration'); ?>';
</script>
<script type="text/javascript" id="full-calendar-js"
    src="<?php echo site_url('assets/plugins/fullcalendar/lib/main.min.js?v=3.1.6'); ?>"></script>
<script>
    $("#location").change(function() {
        requestGet('appointment_manager/get_practitioner_by_location/' + $(this).val()).done(function(response) {
            $("#appointess").html(response);
            init_selectpicker();
        });
    });
</script>
</body>

</html>