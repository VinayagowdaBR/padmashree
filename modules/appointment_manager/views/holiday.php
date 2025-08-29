<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('appmgr_holiday_heading'); ?></h4>
                <?php echo form_open($this->uri->uri_string()); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php $attrs = (isset($holiday) ? [] : ['autofocus' => true]); ?>
                        <?php $value = (isset($holiday) ? $holiday->name : ''); ?>
                        <?php echo render_input('name', 'appmgr_holiday_title', $value, 'text', $attrs); ?>
                        <?php $value = (isset($holiday) ? $holiday->leave_date : ''); ?>
                        <?php echo render_date_input('leave_date', 'appmgr_holiday_date', $value, $attrs); ?>
                        <?php $value = (isset($holiday) ? $holiday->description : ''); ?>
                        <?php echo render_textarea('description', 'appmgr_holiday_desc', $value, $attrs); ?>
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
        $(function() {
            appValidateForm($('form'), {
                name: 'required',
                leave_date: 'required'
            });
        });
    </script>
    </body>

    </html>