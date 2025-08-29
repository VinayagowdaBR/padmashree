<?php init_head();?>
<div id="wrapper" class="affiliate">
  <div class="content">
    <div class="row">
      <div class="panel_s">
        <div class="panel-body">
          <div class="horizontal-tabs mb-5">
              <ul class="nav nav-tabs nav-tabs-horizontal mb-10">
            <?php
            // ✅ Show only member_list tab
            if(in_array('member_list', $tab)) { ?> 
              <li class="active">
                <a href="<?php echo admin_url('affiliate/members?group=member_list'); ?>" data-group="member_list">
                  <?php echo _l('member_list'); ?>
                </a>
              </li>
            <?php } ?>
            </ul>
          </div>
          <?php $this->load->view($tabs['view']); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
</body>
</html>
<?php 
  if($group == 'member_list'){
    require 'modules/affiliate/assets/js/members/members_list_js.php';
  }
?>
