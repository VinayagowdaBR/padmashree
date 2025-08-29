<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
             <h2 class="no-margin"><?php echo _l('BALANCE DETAILS'); ?></h2>
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
              <div class="col-md-2">
                <div class="form-group">
                  <label for="referral_name"><?php echo _l('Referral Name'); ?></label>
                  <input type="text" name="referral_name" id="referral_name" class="form-control" placeholder="Enter Referral Name" />
                </div>
              </div>
              <div class="col-md-2">
                <div class="btn-group" style="margin-top:25px;">
                  <button class="btn btn-primary" onclick="filterBalanceReport(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetBalanceReport(); return false;"><?php echo _l('reset'); ?></button>
                </div>
              </div>
            </div>

              <!-- Report Header -->
             
            <!-- Table -->
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-balance-details-report table-bordered table-striped" id="balance-details-table">
                    <thead>
                      <tr>
                        <th><?php echo _l('sl_no'); ?></th>
                        <th><?php echo _l('Bill No'); ?></th>
                        <th><?php echo _l('Date'); ?></th>
                        <th><?php echo _l('MRD No'); ?></th>
                        <th><?php echo _l('Name'); ?></th>
                        <th><?php echo _l('Mobile'); ?></th>
                        <th><?php echo _l('Refer'); ?></th>
                        <th><?php echo _l('Modality'); ?></th>
                        <th><?php echo _l('Study'); ?></th>
                        <th><?php echo _l('Amount'); ?></th>
                        <th><?php echo _l('discount'); ?></th>
                        <th><?php echo _l('Total'); ?></th>
                        <th><?php echo _l('Paid'); ?></th>
                        <th><?php echo _l('Balance'); ?></th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                      <!-- <tr>
                        <th colspan="9" class="text-right"><?php echo _l('total'); ?>:</th>
                        <th id="footer_subtotal"></th>
                        <th id="footer_discount"></th>
                        <th id="footer_total"></th>
                        <th id="footer_paid"></th>
                        <th id="footer_balance"></th>
                      </tr> -->
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

          </div> <!-- panel-body -->
        </div> <!-- panel_s -->
      </div>
    </div>
  </div>
</div>

<!-- Optional CSS for enhanced table clarity -->
<style>
  #balance-details-table th,
  #balance-details-table td {
    border: 1px solid #000 !important;
    vertical-align: middle;
  }
  #balance-details-table {
    border-collapse: collapse !important;
  }
</style>

<?php init_tail(); ?>

<script>
function updateDateRangeText() {
  const from = $('input[name="report_from"]').val();
  const to = $('input[name="report_to"]').val();
  $('#fromDateText').text(from || '--');
  $('#toDateText').text(to || '--');
}

$(document).ready(function () {
    updateDateRangeText();

    var balanceTable = initDataTable(
        '.table-balance-details-report',
        admin_url + 'reports/balance_details_table',
        [],
        [],
        {},
        [0, 'desc'],
        function (settings, json) {
            if (json.footerTotals) {
                $('#footer_subtotal').html(json.footerTotals.subtotal);
                $('#footer_discount').html(json.footerTotals.discount);
                $('#footer_total').html(json.footerTotals.total);
                $('#footer_paid').html(json.footerTotals.paid);
                $('#footer_balance').html(json.footerTotals.balance);
            }
        }
    );

    $('.table-balance-details-report').on('preXhr.dt', function(e, settings, data) {
        data.report_from = $('input[name="report_from"]').val();
        data.report_to = $('input[name="report_to"]').val();
        data.mrd_from = $('input[name="mrd_from"]').val();
        data.mrd_to = $('input[name="mrd_to"]').val();
        data.referral_name = $('input[name="referral_name"]').val();
    });

    $('input[name="report_from"], input[name="report_to"], input[name="mrd_from"], input[name="mrd_to"], input[name="referral_name"]').on('change', function() {
        updateDateRangeText();
        filterBalanceReport();
    });

    window.filterBalanceReport = function () {
        balanceTable.ajax.reload();
    };

    window.resetBalanceReport = function () {
        $('input[name="report_from"]').val('');
        $('input[name="report_to"]').val('');
        $('input[name="mrd_from"]').val('');
        $('input[name="mrd_to"]').val('');
        $('input[name="referral_name"]').val('');
        updateDateRangeText();
        balanceTable.ajax.reload();
    };
});
</script>
