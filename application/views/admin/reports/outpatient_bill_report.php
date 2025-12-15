<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('Out Patient Details'); ?></h4>
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
                  <button class="btn btn-primary" onclick="filterOutpatientReport(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetOutpatientReport(); return false;"><?php echo _l('reset'); ?></button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-outpatient-bill-report" id="outpatient-bill-table" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <th>Bill No</th>
                        <th>Date</th>
                        <th>MRD No</th>
                        <th>Customer</th>
                        <th>Ref.By</th>
                        <th>Age</th>
                        <th>Modality</th>
                        <th>PaidBy</th>
                        <th>Sex</th>
                        <th>Mobile No</th>
                        <th>Total Amt</th>
                        <th>Disc</th>
                        <th>Bill Amt</th>
                        <th>Serv.Charge</th>
                        <th>Paid Amt</th>
                        <th>Bal</th>
                        <th>Cash Amt</th>
                        <th>Cheq Amt</th>
                        <th>CC</th>
                        <th>UPI</th>
                        <th>Pay Details</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                     
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
  #outpatient-bill-table th,
  #outpatient-bill-table td {
    border: 1px solid #ccc !important;
    vertical-align: middle;
  }
  #outpatient-bill-table {
    border-collapse: collapse !important;
  }
</style>

<?php init_tail(); ?>

<script>
var outpatientTable;
var isFirstLoad = true;

$(document).ready(function () {
    // Set BOTH report_from and report_to to today's date BEFORE DataTable init
    var today = new Date();
    var day = String(today.getDate()).padStart(2, '0');
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var year = today.getFullYear();
    var formattedDate = day + '-' + month + '-' + year; // DD-MM-YYYY format
    
    // Set both dates immediately
    $('input[name="report_from"]').val(formattedDate);
    $('input[name="report_to"]').val(formattedDate);

    outpatientTable = initDataTable(
        '.table-outpatient-bill-report',
        admin_url + 'reports/outpatient_bill_table',
        [],
        [],
        {},
        [0, 'desc'],
        function (settings, json) {
            if (json.footerTotals) {
                $('#footer_total_amount').html(json.footerTotals.total_amount);
                $('#footer_discount').html(json.footerTotals.discount);
                $('#footer_bill_amount').html(json.footerTotals.bill_amount);
                $('#footer_service_charge').html(json.footerTotals.service_charge);
                $('#footer_paid_amount').html(json.footerTotals.paid_amount);
                $('#footer_balance').html(json.footerTotals.balance);
            }
        }
    );

    $('.table-outpatient-bill-report').on('preXhr.dt', function (e, settings, data) {
        data.report_from = $('input[name="report_from"]').val();
        data.report_to = $('input[name="report_to"]').val();
        data.mrd_from = $('input[name="mrd_from"]').val();
        data.mrd_to = $('input[name="mrd_to"]').val();
        data.referral_name = $('input[name="referral_name"]').val();
    });

    // Use init.dt event to reload after table is fully initialized
    $('.table-outpatient-bill-report').on('init.dt', function() {
        // Re-set dates to ensure they're correct after datepicker initialization
        var today = new Date();
        var day = String(today.getDate()).padStart(2, '0');
        var month = String(today.getMonth() + 1).padStart(2, '0');
        var year = today.getFullYear();
        var formattedDate = day + '-' + month + '-' + year;
        $('input[name="report_from"]').val(formattedDate);
        $('input[name="report_to"]').val(formattedDate);
        
        // Trigger reload with correct dates
        if (isFirstLoad) {
            isFirstLoad = false;
            outpatientTable.ajax.reload();
        }
    });

    $('input[name="report_from"], input[name="report_to"], input[name="mrd_from"], input[name="mrd_to"], input[name="referral_name"]').on('change', function () {
        filterOutpatientReport();
    });

    window.filterOutpatientReport = function () {
        outpatientTable.ajax.reload();
    };

    window.resetOutpatientReport = function () {
        $('input[name="report_from"]').val('');
        $('input[name="report_to"]').val('');
        $('input[name="mrd_from"]').val('');
        $('input[name="mrd_to"]').val('');
        $('input[name="referral_name"]').val('');
        outpatientTable.ajax.reload();
    };
});
</script>
