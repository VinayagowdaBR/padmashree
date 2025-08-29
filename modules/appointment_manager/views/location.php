<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('appmgr_location_name'); ?></h4>
                <?php echo form_open($this->uri->uri_string()); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php $attrs = (isset($location) ? [] : ['autofocus' => true]); ?>
                        <?php $value = (isset($location) ? $location->name : ''); ?>
                        <?php echo render_input('name', 'appmgr_location_title', $value, 'text', $attrs); ?>
                        <?php $value = (isset($location) && isset($location->operation_start_time) ? date('g:i A', strtotime($location->operation_start_time)) : ''); ?>
                        <?php echo render_datetime_input('operation_start_time', 'appmgr_location_operation_from', $value); ?>
                        <?php $value = (isset($location) && isset($location->operation_end_time) ? date('g:i A', strtotime($location->operation_end_time)) : ''); ?>
                        <?php echo render_datetime_input('operation_end_time', 'appmgr_location_operation_to', $value); ?>
                        <?php $value = (isset($location) ? $location->description : ''); ?>
                        <?php echo render_textarea('description', 'appmgr_location_description', $value, $attrs); ?>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary "><?php echo _l('submit'); ?></button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <script>
        var appointmentDateTimePickerOptions = {};
        appointmentDateTimePickerOptions.formatTime = timeFormat;
        appointmentDateTimePickerOptions.timezone = app.options.timezone;
        $(function() {
            appValidateForm($('form'), {
                name: 'required',
                operation_start_time: 'required',
                operation_end_time: 'required',
                description: 'required'
            });
            $("#operation_start_time").datetimepicker({
                datepicker: false,
                format: timeFormat,
                step: 30,
                validateOnBlur: false,
                onShow: function() {

                },
                onGenerate: function(ct) {

                },
                onChangeDateTime: function() {
                    console.log("time changed");
                },
            });
            $("#operation_start_time").datetimepicker(appointmentDateTimePickerOptions);
            $("#operation_end_time").datetimepicker({
                datepicker: false,
                format: timeFormat,
                step: 30,
                validateOnBlur: false,
                onShow: function() {
                    this.setOptions({
                        minTime: convertTo24Hour($("#operation_start_time").val()),
                    });
                },
            });
            $("#operation_end_time").datetimepicker(appointmentDateTimePickerOptions);
        });
    </script>
    </body>
    </html>