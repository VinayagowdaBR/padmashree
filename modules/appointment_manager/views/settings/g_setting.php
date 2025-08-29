<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="form-group">
                <label for="am_g_auth_json"
                    class="control-label"><?= _l('appmgr_gauth_json_lbl'); ?>
                </label>
                <small class="" style="float: right;"><b>Integration steps</b> <a href="https://zonvoir.com/appoinment-manager/" target="_blank">https://zonvoir.com/appoinment-manager/</a></small>
                <input type="file" name="am_g_auth_json" class="form-control" value="" accept=".json">
                <?php if (get_option('am_g_auth_json') && file_exists(module_dir_path('appointment_manager', 'config/client_secret_oauth.json'))) { ?>
                    <div class="pull-right mtop10">
                        <a href="<?php echo admin_url('appointment_manager/delete_credential'); ?>" onclick="return confirm('Are you sure?');" style="color:red;"><i class="fa-solid fa-trash"></i></a>
                        <a href="<?php echo admin_url('appointment_manager/download/credential'); ?>">Credential.json <i class="fa-solid fa-download"></i></a>
                    </div>
                <?php } ?>
            </div>
            <?php
                $value = get_option('appmgr_calendar_id');
                echo render_input('settings[appmgr_calendar_id]', 'Google Calendar ID', $value);
                ?>
        </div>
    </div>
</div>