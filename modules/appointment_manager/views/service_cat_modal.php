<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="ServiceCatModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('appmgr_ser_cat'); ?></h4>
            </div>
            <?php echo form_open($form_action, ['id' => 'service-cat-form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php 
                            $value = isset($service_category) ? $service_category->service_id : '';
                            echo render_select('service_id', $services,['id','tittle'],'Services', $value);
                         ?>
                        <?php 
                            $value = isset($service_category) ? $service_category->name : '';
                            echo render_input('name', 'category', $value);
                         ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>