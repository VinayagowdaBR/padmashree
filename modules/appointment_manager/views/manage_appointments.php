<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons">
                    <?php if (staff_can('create', 'appointment_manager')) { ?>
                        <div class="tw-mb-2 sm:tw-mb-4">
                            <a href="<?php echo admin_url('appointment_manager/import'); ?>"
                                class="btn btn-primary hidden-xs">
                                <i class="fa-solid fa-upload tw-mr-1"></i>
                                <?php echo _l('import_appmgr_appointments'); ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row mbot15">
                            <div class="tw-mb-2">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <span>
                                        <?php echo _l('appointment_summary'); ?>
                                    </span>
                                </h4>
                            </div>
                        </div>
                        <hr class="hr-panel-separator" />

                        <?php
                        $table_data = [];
                        $_table_data = [
                            _l('appmgr_appointment_id'),

                            _l('appmgr_appointee_name'),

                            _l('appmgr_appointment_client'),

                            _l('appmgr_appointment_date'),

                            _l('appmgr_appointment_time'),

                            _l('appmgr_appointment_duration'),

                            _l('appmgr_location'),
                            _l('appmgr_treatment_heading'),
                            _l('appmgr_ser_cat'),

                            _l('appmgr_status'),

                            _l('appmgr_added_by'),

                            _l('Mobile')  ,

                            _l('ref_by') 

                        ];
                        foreach ($_table_data as $_t) {
                            array_push($table_data, $_t);
                        }
                        $custom_fields = get_custom_fields('appmgr', ['show_on_table' => 1]);
                        foreach ($custom_fields as $field) {
                            array_push($table_data, [
                                'name'     => $field['name'],
                                'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                            ]);
                        }
                        render_datatable($table_data, 'appmgr_bookings'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewappointment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title inline-block" id="exampleModalLabel"><?php echo _l('appmgr_apt_dtails_heading'); ?></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th><strong><?php echo _l('appmgr_appointee_label'); ?>:</strong></th>
                            <td><span id="modal-practitioner"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('clients'); ?>:</strong></th>
                            <td><span id="modal-client"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('appmgr_appointm_entdate'); ?>:</strong></th>
                            <td><span id="modal-appointment-date"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('appmgr_appointment_time'); ?>:</strong></th>
                            <td><span id="modal-time"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('appmgr_appointment_duration'); ?>:</strong></th>
                            <td><span id="modal-duration"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('appmgr_location'); ?>:</strong></th>
                            <td><span id="modal-location"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('appmgr_treatment_heading'); ?>:</strong></th>
                            <td><span id="modal-service"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('appmgr_ser_cat'); ?>:</strong></th>
                            <td><span id="modal-cats"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('appmgr_status'); ?>:</strong></th>
                            <td><span id="modal-status"></span></td>
                        </tr>
                        <tr>
                            <th><strong><?php echo _l('appmgr_added_by'); ?>:</strong></th>
                            <td><span id="modal-added-by"></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="appointmentNewModal" tabindex="-1" role="dialog" aria-labelledby="appointmentNewModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('appointment_edit_appointment'); ?></h4>
            </div>
            <?php echo form_open('appointment_manager/update_appointments/', ['id' => 'edit-form-appointment']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo form_hidden('isEdit', '', ['id' => 'isEdit']); ?>
                        <select id="apt_location" class="selectpicker display-block" data-width="100%" name="location"
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
                        <div class="form-group select-placeholder mtop15">
                            <select id="appointee" name="appointee" data-live-search="true" data-width="100%"
                                class="ajax-search"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
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
                            <div class="col-md-6 itm-wrap">
                                <?php echo render_select('client', $clients, ['userid', 'company'], 'appmgr_client_label'); ?>
                            </div>
                            <div class="col-md-6 itm-wrap">
                                <?php if (staff_can('approve', 'appointment_manager')) { ?>
                                    <?php echo render_select('status', $statuses, ['id', 'name'], 'appmgr_status'); ?>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="row cstm-group">
                            <div class="col-md-6 itm-wrap">
                                <?php echo render_select('treatment', $treatments, ['id', 'tittle'], 'appmgr_treatment_label'); ?>
                            </div>
                            <div class="col-md-6 itm-wrap">
                                <?php echo render_select('service_cat[]', $categories, ['id', 'name'], 'appmgr_ser_cat','', ['multiple' => true]); ?>
                            </div>
                        </div>

                        <?php echo render_textarea('description', 'appmgr_appointm_description', '', ['rows' => 5]); ?>

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
<div id="modal-wrapper-ta"></div>
<?php init_tail(); ?>
<script src="<?php echo base_url('modules/' . APPOINTMENT_MANAGER_MODULE_NAME . '/assets/js/appointment_summery.js'); ?>"></script>
</body>

</html>