<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link href="<?php echo module_dir_url('appointment_manager', 'assets/css/public_form.css'); ?>" rel="stylesheet" type="text/css">
<?php
if ($alert) {
    $success =  _l('appmgr_thanks_appointment_created');
    $danger = _l('Failed to create appointment');
    $mgs = ($alert == "success") ? $success : $danger;
?>
    <div id="alert_float_1" class="float-alert animated fadeInRight col-xs-10 col-sm-3 alert alert-<?php echo $alert ?>">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <span class="fa-regular fa-bell" data-notify="icon"></span>
        <span class="alert-title"><?php echo $mgs; ?></span>
    </div>
<?php
}
$appmgr_custom_fields = false;
if (total_rows(db_prefix() . 'customfields', ['fieldto' => 'appmgr', 'active' => 1]) > 0) {
    $appmgr_custom_fields = true;
}
?>

<div class="wrap-form-public">
    <?php echo form_open(site_url('appointment_manager/appointment_manager_client/add_appointments'), array('id' => 'appmgr_appointment_form_public')); ?>
    <?php echo form_hidden('status', $status_id); ?>
    <?php echo form_hidden('isEdit', 0); ?>
    <?php echo form_hidden('iframe', 0); ?>
    <?php echo form_hidden('siteUrl', $site_url); ?>
    <?php echo form_hidden('time_zone', $timeZone); ?>
    <?php echo form_hidden('time_format', $timeFormat); ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group ">
                <label class="control-label"><?php echo _l('appmgr_locations'); ?></label>
                <select id="location" class="selectpicker display-block" data-width="100%" name="location"
                    data-none-selected-text="<?php echo _l('appmgr_locations'); ?>">

                    <option value=""></option>

                    <?php foreach ($locations as $location) { ?>

                        <option value="<?php echo $location['id']; ?>"
                            data-subtext="<?php echo _l('appmgr_loc_operational_hr') . date('g:i a', strtotime($location['operation_start_time'])) . ' - ' . date('g:i a', strtotime($location['operation_end_time'])); ?>"
                            data-Tfrom="<?php echo date('H:i', strtotime($location['operation_start_time'])); ?>"
                            data-Tto="<?php echo date('H:i', strtotime($location['operation_end_time'])); ?>">
                            <?php echo $location['name']; ?>
                        </option>

                    <?php } ?>

                </select>
                <?php echo form_error('location'); ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label"><?php echo _l('appmgr_appointee_label'); ?></label>
                <select name="appointee" id="appointee" class="selectpicker" data-width="100%">
                    <option value=""></option>
                </select>
                <?php echo form_error('appointee'); ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <?php echo render_date_input('appointment_date', 'appmgr_appointm_entdate', '', ['readonly' => true]); ?>
                <?php echo form_error('appointment_date'); ?>
            </div>
        </div>


        <div class="col-md-6">
            <div class="form-group">
                <?php echo render_datetime_input('appointment_start_time', 'appmgr_appoint_start_time', '', ['readonly' => true]); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?php echo render_input('company', 'Your Name', '', 'text'); ?>
                <?php echo form_error('company'); ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <?php echo render_datetime_input('appointment_end_time', 'appmgr_appoint_end_time', '', ['readonly' => true]); ?>

            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="phonenumber"><?php echo _l('appmgr_phone_label'); ?></label>
                <input type="text" class="form-control" name="phonenumber">
                <?php echo form_error('phonenumber'); ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="email"><?php echo _l('appmgr_email_label'); ?></label>
                <input type="email" class="form-control" name="email">
                <?php echo form_error('email'); ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label"><?php echo _l('appmgr_treatment_label'); ?></label>
                <select name="treatment" id="treatment" class="selectpicker" data-width="100%">
                    <option value=""></option>
                    <?php foreach ($treatments as $treatment) { ?>
                        <option value="<?php echo $treatment['id']; ?>">
                            <?php echo $treatment['tittle']; ?>
                        </option>
                    <?php } ?>
                </select>
                <?php echo form_error('treatment'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="email"><?php echo _l('appmgr_ser_cat'); ?></label>
                <select name="service_cat[]" id="service_cat[]" class="selectpicker" data-width="100%" multiple>
                    <option value=""></option>
                </select>
                <?php echo form_error('service_cat'); ?>
            </div>
        </div>


        <div class="col-md-12">
            <div class="form-group">
                <?php echo render_textarea('description', 'appmgr_appointm_description', '', ['rows' => 5]); ?>
                <?php echo form_error('description'); ?>
            </div>
        </div>

        <div class="col-md-12">
            <div class="">
                <label for="reminder_before"><?php echo _l('appmgr_appointment_reminder'); ?></label>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <div class="input-group">
                    <input type="number" class="form-control" name="reminder_before" id="reminder_before">
                    <span class="input-group-addon">
                        <i class="fa-regular fa-circle-question" data-toggle="tooltip"
                            data-title="<?php echo _l('reminder_notification_placeholder'); ?>"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <select name="reminder_before_type" id="reminder_before_type" class="selectpicker"
                    data-width="100%">
                    <option value="minutes"><?php echo _l('minutes'); ?></option>
                    <option value="hours"><?php echo _l('hours'); ?></option>
                    <option value="days"><?php echo _l('days'); ?></option>
                    <option value="weeks"><?php echo _l('weeks'); ?></option>
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <?php
            if ($appmgr_custom_fields) {
                $rel_id = false;
                echo render_custom_fields('appmgr', $rel_id);
            }
            ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary float-right"><?php echo _l('appmgr_btn_book'); ?></button>
    <?php echo form_close(); ?>
</div>