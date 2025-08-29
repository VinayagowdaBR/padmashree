<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$CI = &get_instance();
$CI->load->model('appointment_manager/appointment_manager_model', 'tcm');
$locations = $CI->tcm->get_location();
$locations = $CI->tcm->get_location();
$treatments = $CI->tcm->get_treatment();
$categories = [];
$statuses = $CI->tcm->get_appointment_statuses();
$staffs = $CI->staff_model->get('', ['active' => 1]);
?>
<button type="button" onclick="add_clients_appointment();" class="btn btn-primary pull-right tw-mb-4"> <i class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('appmgr_add_appointment_lable'); ?></button>
<br>
<?php if (isset($client)) { ?>
    <h4 class="customer-profile-group-heading"><?php echo _l('appmgr_appointments'); ?></h4>
    <input type="hidden" name="client_user_id" value="<?php echo $client->userid; ?>">
    <?php render_datatable([
        _l('appmgr_appointment_id'),
        _l('appmgr_appointee_name'),

        _l('appmgr_appointment_client'),

        _l('appmgr_appointment_date'),

        _l('appmgr_appointment_time'),

        _l('appmgr_appointment_duration'),

        _l('appmgr_location'),

        _l('appmgr_status'),

        _l('appmgr_added_by')

    ], 'appmgr_client_bookings'); ?>
    <div class="modal fade" id="newAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="newAppointmentModalLabel">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo _l('appmgr_appointment'); ?></h4>
                </div>
                <?php echo form_open('appointment_manager/add_update_clients_appointments/', ['id' => 'add-appointment-form']); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">

                            <?php echo form_hidden('location'); ?>

                            <?php echo form_hidden('isEdit', 0); ?>

                            <div class="select-placeholder mbot15">

                                <select id="clocation" class="selectpicker display-block" data-width="100%" name="location" data-none-selected-text="<?php echo _l('appmgr_locations'); ?>">
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
                            </div>
                            <div class="select-placeholder mbot15">
                                <select name="appointee" id="appointee" class="ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('practitioners'); ?>" required>
                                    <option value=""></option>
                                </select>
                            </div>

                            <?php echo render_date_input('appointment_date', 'appmgr_appointm_entdate'); ?>

                            <div class="row cstm-group">
                                <div class="col-md-6 itm-wrap">
                                    <?php echo render_datetime_input('appointment_start_time', 'appmgr_appoint_start_time'); ?>
                                </div>
                                <div class="col-md-6 itm-wrap">
                                    <?php echo render_datetime_input('appointment_end_time', 'appmgr_appoint_end_time'); ?>
                                </div>

                            </div>

                            <div class="row cstm-group">
                                <?php echo form_hidden('client', $client->userid); ?>

                                <div class="col-md-6 itm-wrap">
                                    <?php echo render_select('treatment', $treatments, ['id', 'tittle'], 'appmgr_treatment_label'); ?>
                                </div>
                                <div class="col-md-6 itm-wrap">
                                    <?php echo render_select('service_cat[]', $categories, ['id', 'name'], 'appmgr_ser_cat', '', ['multiple' => true]); ?>
                                </div>


                            </div>

                            <?php echo render_textarea('description', 'appmgr_appointm_description', '', ['rows' => 5]); ?>

                            <?php if (staff_can('approve', 'appointment_manager')) { ?>
                                <?php echo render_select('status', $statuses, ['id', 'name'], 'appmgr_status'); ?>
                            <?php } ?>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="reminder_before"><?php echo _l('appmgr_appointment_reminder'); ?></label>
                                    </div>
                                </div>

                                <div class="row cstm-group">
                                    <div class="col-md-6 itm-wrap">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="reminder_before" value=""
                                                id="reminder_before">
                                            <span class="input-group-addon"><i class="fa-regular fa-circle-question"
                                                    data-toggle="tooltip"
                                                    data-title="<?php echo _l('reminder_notification_placeholder'); ?>"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 itm-wrap apt-cl">
                                        <select name="reminder_before_type" id="reminder_before_type" class="selectpicker"
                                            data-width="100%">
                                            <option value="minutes"><?php echo _l('minutes'); ?></option>
                                            <option value="hours"><?php echo _l('hours'); ?></option>
                                            <option value="days"><?php echo _l('days'); ?></option>
                                            <option value="weeks"><?php echo _l('weeks'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default close_btn"
                        data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="newAppointmentModalLabel">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo _l('appmgr_appointment'); ?></h4>
                </div>
                <?php echo form_open('appointment_manager/add_update_clients_appointments/', ['id' => 'edit-appointment-form']); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">

                            <?php echo form_hidden('location'); ?>

                            <?php echo form_hidden('isEdit', 0); ?>

                            <div class="select-placeholder mbot15">

                                <select id="clocation" class="selectpicker display-block" data-width="100%" name="location" data-none-selected-text="<?php echo _l('appmgr_locations'); ?>">
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
                            </div>
                            <div class="select-placeholder mbot15">
                                <select name="appointee" id="appointee" class="ajax-search" data-live-search="true" data-width="100%" required>
                                    <option value=""></option>
                                </select>
                            </div>
                            <?php echo render_date_input('appointment_date', 'appmgr_appointm_entdate'); ?>

                            <div class="row cstm-group">
                                <div class="col-md-6 itm-wrap">
                                    <?php echo render_datetime_input('appointment_start_time', 'appmgr_appoint_start_time'); ?>
                                </div>
                                <div class="col-md-6 itm-wrap">
                                    <?php echo render_datetime_input('appointment_end_time', 'appmgr_appoint_end_time'); ?>
                                </div>
                            </div>

                            <div class="row cstm-group">
                                <?php echo form_hidden('client', $client->userid); ?>
                                <div class="col-md-6 itm-wrap">
                                    <?php echo render_select('treatment', $treatments, ['id', 'tittle'], 'appmgr_treatment_label'); ?>
                                </div>
                                <div class="col-md-6 itm-wrap">
                                    <?php echo render_select('service_cat[]', $categories, ['id', 'name'], 'appmgr_ser_cat', '', ['multiple' => true]); ?>
                                </div>
                            </div>

                            <?php echo render_textarea('description', 'appmgr_appointm_description', '', ['rows' => 5]); ?>

                            <?php if (staff_can('approve', 'appointment_manager')) { ?>
                                <?php echo render_select('status', $statuses, ['id', 'name'], 'appmgr_status'); ?>
                            <?php } ?>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="reminder_before"><?php echo _l('appmgr_appointment_reminder'); ?></label>
                                    </div>
                                </div>

                                <div class="row cstm-group">
                                    <div class="col-md-6 itm-wrap">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="reminder_before" value=""
                                                id="reminder_before">
                                            <span class="input-group-addon"><i class="fa-regular fa-circle-question"
                                                    data-toggle="tooltip"
                                                    data-title="<?php echo _l('reminder_notification_placeholder'); ?>"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 itm-wrap apt-cl">
                                        <select name="reminder_before_type" id="reminder_before_type" class="selectpicker"
                                            data-width="100%">
                                            <option value="minutes"><?php echo _l('minutes'); ?></option>
                                            <option value="hours"><?php echo _l('hours'); ?></option>
                                            <option value="days"><?php echo _l('days'); ?></option>
                                            <option value="weeks"><?php echo _l('weeks'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row cstm-group">
                                    <div class="col-md-12" id="cust_fields_area">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default close_btn"
                        data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
<?php } ?>