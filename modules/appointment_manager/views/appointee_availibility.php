<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <table class="table border table-striped table-margintop">
                    <tbody>
                        <tr class="project-overview">
                            <td class="bold" width="30%"><?php echo _l('appmgr_appointee_name'); ?></td>
                            <td><?php echo $appointee->firstname . ' ' . $appointee->lastname; ?></td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('appmgr_appointee_avail_heading'); ?></h4>
                <?php echo form_open($this->uri->uri_string(), ['id' => 'availibility-form']); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php $attrs = (isset($availibility) ? [] : ['autofocus' => true]); ?>
                        <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip"
                            data-title="<?php echo _l('appmgr_repeation_help'); ?>"></i>
                        <?php
                        echo form_hidden('appointee_id', $appointee->appointeeid);
                        $value = (isset($availibility) && !empty($availibility) ? $availibility->repetition : 'monthly');
                        echo render_input('repetition', _l('appmgr_appointee_repeat'), 'monthly', '', ['readonly' => true]);
                        ?>
                        <div class="row dates_range <?php echo $value != 'monthly' ? '' : ''; ?>">
                            <div class="col-md-6">
                                <?php
                                $value = (isset($availibility) && !empty($availibility) ? $availibility->available_date_from : '');
                                echo render_date_input('available_date_from', 'appmgr_appointee_avil_date_from', $value);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                $value = (isset($availibility) && !empty($availibility) ? $availibility->available_date_to : '');
                                echo render_date_input('available_date_to', 'appmgr_appointee_avil_date_to', $value);
                                ?>
                            </div>
                        </div>
                        <div class="row hide">
                            <div class="col-md-6">
                                <?php
                                $value = (isset($availibility) && !empty($availibility) ? date('g:i A', strtotime($availibility->from_time)) : '');
                                echo render_datetime_input('from_time', 'appmgr_appointee_avil_from_time', $value);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                $value = (isset($availibility) && !empty($availibility) ? date('g:i A', strtotime($availibility->to_time)) : '');
                                echo render_datetime_input('to_time', 'appmgr_appointee_avil_to_time', $value);
                                ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary "><?php echo _l('submit'); ?></button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <div class="col-md-6">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('appmgr_appointee_unavail_heading'); ?></h4>
                <?php echo form_open($this->uri->uri_string(), ['id' => 'unavailibility-form']); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo form_hidden('unavailable', 1); ?>
                                <?php echo render_date_input('unavailable_date', '', '', ['placeholder' => _l('appmgr_unavailable_date_lable')]); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_textarea('description', '', '', ['placeholder' => _l('appmgr_unavailable_desc_lable'), 'style' => 'height:35px;']); ?>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary pull-right"><?php echo _l('Add'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                    <div class="panel-body">
                        <table class="table dt-table" data-order-col="1" data-order-type="desc">
                            <thead>
                                <tr>
                                    <th class="th-unaval-id"><?php echo _l('appmgr_appointment_id'); ?></th>
                                    <th class="th-unaval-date"><?php echo _l('appmgr_unavailable_date_lable'); ?></th>
                                    <th class="th-unaval-date"><?php echo _l('appmgr_unavailable_desc_lable'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($unavailibilities) && !empty($unavailibilities)) { ?>
                                    <?php foreach ($unavailibilities as $unavailibility) { ?>
                                        <tr>
                                            <td data-order="<?php echo $unavailibility['id']; ?>"><?php echo $unavailibility['id'] . '<div class="row-options"><a href="' . admin_url('appointment_manager/del_unavailibility/' . $unavailibility['id']) . '" class="text-danger _delete">' . _l('delete') . '</a></div>'; ?></td>

                                            <td data-order="<?php echo e($unavailibility['unavailable_date']); ?>"><?php echo _d($unavailibility['unavailable_date']); ?></td>
                                            <td><?php echo $unavailibility['description']; ?></td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php init_tail(); ?>
        <script>
            var availibilityDateTimePickerOptions = {};
            availibilityDateTimePickerOptions.formatTime = timeFormat;
            availibilityDateTimePickerOptions.timezone = app.options.timezone;
            $(function() {
                appValidateForm($('#availibility-form'), {
                    repetition: 'required',
                    available_date_from: {
                        required: {
                            depends: function(element) {
                                if ($("#repetition").val() == 'monthly') {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        }
                    },
                    available_date_to: {
                        required: {
                            depends: function(element) {
                                if ($("#repetition").val() == 'monthly') {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        }
                    },
                });
                appValidateForm($('#unavailibility-form'), {
                    unavailable_date: 'required',
                });
                $("#from_time").datetimepicker({
                    datepicker: false,
                    format: timeFormat,
                    step: 30,
                    validateOnBlur: false,
                    onShow: function() {
                        console.log("show");
                    },
                    onGenerate: function(ct) {
                        console.log("time generated");
                    },
                    onChangeDateTime: function() {
                        console.log("time changed");
                    },
                });
                $("#from_time").datetimepicker(availibilityDateTimePickerOptions);
                $("#to_time").datetimepicker({
                    datepicker: false,
                    format: timeFormat,
                    step: 30,
                    validateOnBlur: false,
                    onShow: function() {
                        this.setOptions({
                            minTime: convertTo24Hour($("#from_time").val()),
                        });
                    },
                });
                $("#to_time").datetimepicker(availibilityDateTimePickerOptions);
                $('#available_date_from').change(function() {
                    var selectedDate = $(this).val();
                    $('#available_date_to').val(selectedDate);
                    $('#available_date_to').datetimepicker({
                        minDate: selectedDate,
                        timepicker: false,
                        format: app.options.date_format,
                    });
                });
                $("select#repetition").change(function() {
                    if ($(this).val() == 'monthly') {
                        $(".dates_range").removeClass('hide');
                    } else {
                        $(".dates_range").addClass('hide');
                    }
                });
            });
        </script>
        </body>

        </html>