<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('appmgr_treatment_heading'); ?></h4>
                <?php echo form_open($this->uri->uri_string()); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php $attrs = (isset($treatment) ? [] : ['autofocus' => true]); ?>

                        <?php $value = (isset($treatment) ? $treatment->tittle : ''); ?>
                        <?php echo render_input('tittle', 'appmgr_treatment_name', $value, 'text', $attrs); ?>

                        <?php $value = (isset($treatment) ? $treatment->description : ''); ?>
                        <?php echo render_textarea('description', 'appmgr_treatment_desc', $value, $attrs); ?>

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
                tittle: 'required'
            });
        });
    </script>