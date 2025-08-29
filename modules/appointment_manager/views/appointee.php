<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('appmgr_appointee_heading'); ?></h4>
                <?php echo form_open($this->uri->uri_string()); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php $attrs = (isset($appointee) ? [] : ['autofocus' => true]); ?>
                        <?php $value = (isset($appointee) ? $appointee->staff : ''); ?>
                        <?php echo render_select('staff', $staffs, ['staffid', 'full_name'], 'appmgr_appointee_name', $value); ?>

                        <?php $value = (isset($appointee) ? $appointee->location : ''); ?>
                        <?php echo render_select('location', $locations, ['id', 'name'], 'appmgr_appointee_location', $value); ?>

                        <?php $value = (isset($appointee) ? $appointee->description : ''); ?>
                        <?php echo render_textarea('description', 'appmgr_appointee_desc', $value, $attrs); ?>

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
        $(function() {
            appValidateForm($('form'), {
                staff: 'required',
                location: 'required',
            });
        });
    </script>
    </body>

    </html>