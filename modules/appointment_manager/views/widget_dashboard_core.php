<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->load->model('appointment_manager/appointment_manager_model', 'tcm');
$locations = $CI->tcm->get_location();
$clients = $CI->clients_model->get();
$locations = $CI->tcm->get_location();
$treatments = $CI->tcm->get_treatment();
$statuses = $CI->tcm->get_appointment_statuses();
$staffs = $CI->staff_model->get('', ['active' => 1]);
?>
<div class="widget" id="widget-<?php echo basename(__FILE__, ".php"); ?>" data-name="<?= _l('appmgr_calendar'); ?>">
  <div class="panel_s todo-panel">
    <div class="panel-body padding-10">
      <div class="widget-dragger"></div>
      <div id="calendar_div">
        <div class="wrap-small-calendar-filter">
          <div id="small-calendar"></div>
          <div class="row mtop15">
            <div class="col-md-12">
              <select id="location" class="selectpicker display-block" data-width="100%" name="location"
                data-none-selected-text="<?php echo _l('appmgr_locations'); ?>">
                <option value=""></option>
                <?php foreach ($locations as $location) { ?>
                  <option value="<?php echo $location['id']; ?>"
                    data-subtext="<?php echo _l('appmgr_loc_operational_hr') . date('g:i a', strtotime($location['operation_start_time'])) . ' - ' . date('g:i a', strtotime($location['operation_end_time'])); ?>"
                    data-Tfrom="<?php echo date('H:i', strtotime($location['operation_start_time'])); ?>"
                    data-Tto="<?php echo date('H:i', strtotime($location['operation_end_time'])); ?>">
                    <?php echo $location['name']; ?>
                  </option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="row mtop15">
            <div class="col-md-12">
              <?php
              if (isset($staffs) && !empty($staffs)) {
                echo render_select('staff', $staffs, ['staffid', ['firstname', 'lastname']], '', '', ['data-none-selected-text' => _l('appmgr_appointee_label')]);
              }
              ?>
            </div>
          </div>
        </div>
        <div class="" id="appointment_manager"></div>
      </div>
      <div id="appoint_div" class="hide">
        <div class="panel_s">
          <div class="panel-body">
            <?php echo $this->load->view('appointment_manager/appointment_form', array('clients' => $clients, 'treatments' => $treatments, 'statuses' => $statuses)); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>