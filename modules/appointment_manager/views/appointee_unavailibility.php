<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('appmgr_appointee_avail_heading'); ?></h4>
                <?php echo form_open($this->uri->uri_string()); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php $attrs = (isset($unavailibility) ? [] : ['autofocus' => true]); ?>
                        <?php 
                        echo form_hidden('appointee_id', $appointee->id);
                        $value = (isset($unavailibility) && !empty($unavailibility) ? $unavailibility->unavailable_date : ''); ?>
                        <?php echo render_date_input('unavailable_date', 'appmgr_appointee_avil_date', $value); ?>
                        <?php $value = (isset($unavailibility) && !empty($unavailibility) ? $unavailibility->from_time : ''); ?>
                        <?php echo render_datetime_input('from_time', 'appmgr_appointee_avil_from_time', $value); ?>
                        <?php $value = (isset($unavailibility) && !empty($unavailibility) ? $unavailibility->to_time : ''); ?>
                        <?php echo render_datetime_input('to_time', 'appmgr_appointee_avil_to_time', $value); ?>
                        <div class="panel-footer text-right">
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
                unavailable_date: 'required',
                from_time: 'required',
                to_time: 'required'
            });
            $("#from_time").datetimepicker({
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
            $("#from_time").datetimepicker(appointmentDateTimePickerOptions);
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
            $("#to_time").datetimepicker(appointmentDateTimePickerOptions);
        });
    </script>
    </body>

    </html>