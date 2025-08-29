<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12" id="training-add-edit-wrapper">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="mt-0 mbot15"><?php echo _l('appmgr_appointee_title'); ?></h4>
                            </div>
                        </div>
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
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $href =  admin_url('appointment_manager/appointee_availibility/' . $appointee->appointeeid);
                                if (isset($appointee->availid)) {
                                    $href =  admin_url('appointment_manager/appointee_availibility/' . $appointee->appointeeid . '/' . $appointee->availid);
                                }
                                ?>
                                <a class="btn btn-primary" href="<?php echo $href; ?>"><?php echo _l('appmgr_add_appointee_avail_title'); ?></a>
                            </div>
                            <?php render_datatable([
                                _l('appmgr_repeation_avail'),
                                _l('appmgr_from_time_avail'),
                                _l('appmgr_to_time_avail'),
                                _l('appmgr_addedby_avail'),
                                _l('appmgr_addedat_avail')
                            ], 'appmgr_appointee_ailability'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-appmgr_appointee_ailability', window.location.href, [], []);
    });
</script>
</body>
</html>