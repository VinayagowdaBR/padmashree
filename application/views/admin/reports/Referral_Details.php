<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h2 class="no-margin"><?php echo _l('REFERRAL DETAILS'); ?></h2>
            <hr class="hr-panel-heading" />

            <!-- Filters -->
            <div class="row mbot15">
              <input type="hidden" name="report_months" value="custom">
              <div class="col-md-2">
                <?php echo render_date_input('report_from', 'report_from'); ?>
              </div>
              <div class="col-md-2">
                <?php echo render_date_input('report_to', 'report_to'); ?>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="mrd_from"><?php echo _l('From MRD No'); ?></label>
                  <input type="text" name="mrd_from" id="mrd_from" class="form-control" placeholder="Enter From MRD No" />
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="mrd_to"><?php echo _l('To MRD No'); ?></label>
                  <input type="text" name="mrd_to" id="mrd_to" class="form-control" placeholder="Enter To MRD No" />
                </div>
              </div>

              <!-- Referral filter as text input -->
              <div class="col-md-2">
                <div class="form-group">
                  <label for="referral_filter"><?php echo _l('Referral'); ?></label>
                  <input type="text" name="referral_filter" id="referral_filter" class="form-control" placeholder="<?php echo _l('Enter referral name or code'); ?>" />
                </div>
              </div>

              <div class="col-md-2">
                <div class="btn-group" style="margin-top:25px;">
                  <button class="btn btn-primary" onclick="filterReferralReport(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetReferralReport(); return false;"><?php echo _l('reset'); ?></button>
                </div>
              </div>
            </div>

            <!-- Referral Table -->
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-referral-details-report" id="referral-details-table" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <th><?php echo _l('sl_no'); ?></th>
                        <th><?php echo _l('Bill No'); ?></th>
                        <th><?php echo _l('Date'); ?></th>
                        <th><?php echo _l('Mrd No'); ?></th>
                        <th><?php echo _l('Name'); ?></th>
                        <th><?php echo _l('Refer'); ?></th>
                        <th><?php echo _l('Modality'); ?></th>
                        <th><?php echo _l('Study'); ?></th>
                        <th><?php echo _l('Amount'); ?></th> 
                        <th><?php echo _l('discount'); ?></th>
                        <th><?php echo _l('Total'); ?></th>
                        <th><?php echo _l('Balance'); ?></th> 
                        <th><?php echo _l('RefAmt'); ?></th> 
                      </tr>
                    </thead>
                    <tbody></tbody>
                   
                  </table>
                </div>
              </div>
            </div>

          </div> <!-- panel-body -->
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

<script>
var referralTable;

$(function() {
    // Set default report_to date to current date
    var today = new Date();
    var day = String(today.getDate()).padStart(2, '0');
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var year = today.getFullYear();
    var formattedDate = day + '-' + month + '-' + year; // DD-MM-YYYY format
    $('input[name="report_to"]').val(formattedDate);

    referralTable = initDataTable(
        '.table-referral-details-report',
        admin_url + 'reports/referral_details_table',
        [],
        [],
        {},
        [0, 'desc'],
        function (settings, json) {
            // Update footer totals
            if (json.footerTotals) {
                $('#footer_amount').html(json.footerTotals.amount);
                $('#footer_discount').html(json.footerTotals.discount);
                $('#footer_total').html(json.footerTotals.total);
                $('#footer_balance').html(json.footerTotals.balance);
                $('#footer_commission').html(json.footerTotals.commission);
            }
        }
    );

    // Set filter values before AJAX request
    $('.table-referral-details-report').on('preXhr.dt', function(e, settings, data) {
        data.report_from     = $('input[name="report_from"]').val();
        data.report_to       = $('input[name="report_to"]').val();
        data.mrd_from        = $('input[name="mrd_from"]').val();
        data.mrd_to          = $('input[name="mrd_to"]').val();
        data.referral_name   = $('input[name="referral_filter"]').val(); // fixed here
    });

    // Auto filter on input change
    $('input[name="report_from"], input[name="report_to"], input[name="mrd_from"], input[name="mrd_to"], input[name="referral_filter"]').on('change', function() {
        filterReferralReport();
    });
});

function filterReferralReport() {
    referralTable.ajax.reload();
}

function resetReferralReport() {
    $('input[name="report_from"]').val('');
    $('input[name="report_to"]').val('');
    $('input[name="mrd_from"]').val('');
    $('input[name="mrd_to"]').val('');
    $('input[name="referral_filter"]').val('');
    referralTable.ajax.reload();
}
</script>
