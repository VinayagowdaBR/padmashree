<?php init_head();?>
<div id="wrapper" class="commission">
  <div class="content">
    <div class="row">
      <div class="panel_s">
        <div class="panel-body">
          <?php $arrAtt = array(); $arrAtt['data-type']='currency'; ?>
          <?php echo form_open($this->uri->uri_string(),array('id'=>'member-form','autocomplete'=>'off')); ?>
          
          <h4 class="no-margin font-bold"><?php echo _l($title); ?></h4>
          <hr />
          
          <div class="row">
            <div class="col-md-6">
              <?php $value = (isset($member) ? $member->firstname : ''); ?>
              <?php $attrs = (isset($member) ? array() : array('autofocus'=>true)); ?>
              <?php echo render_input('firstname','staff_add_edit_firstname',$value,'text',$attrs); ?>
            </div>
            <div class="col-md-6">
              <?php $value = (isset($member) ? $member->lastname : ''); ?>
              <?php echo render_input('lastname','staff_add_edit_lastname',$value); ?>
            </div>

            <div class="col-md-6">
              <?php $value = (isset($member) ? $member->phone : ''); ?>
              <?php echo render_input('phone','staff_add_edit_phonenumber',$value); ?>
            </div>
          </div>

          <?php echo render_custom_fields('aff_member', $id); ?>

          <div class="row">
            <div class="col-md-12">    
              <div class="modal-footer">
                <!-- Close Button (Back to previous route) -->
                <button type="button" class="btn btn-default" onclick="window.history.back();">
                  <?php echo _l('close'); ?>
                </button>
                <!-- Submit Button -->
                <button type="submit" class="btn btn-info commission-policy-form-submiter">
                  <?php echo _l('submit'); ?>
                </button>
              </div>
            </div>
          </div>

          <?php echo form_close(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
<?php require 'modules/affiliate/assets/js/members/member_js.php';?>
