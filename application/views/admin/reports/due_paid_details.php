<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h2 class="no-margin"><?php echo _l('DUE PAID DETAILS'); ?></h2>
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
                  <button class="btn btn-primary" onclick="filterDuePaidReport(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetDuePaidReport(); return false;"><?php echo _l('reset'); ?></button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-due-paid-details-report" id="due-paid-details-table" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <th><?php echo _l('sl_no'); ?></th>
                        <th><?php echo _l('Bill No'); ?></th>
                        <th><?php echo _l('date'); ?></th>
                        <th><?php echo _l('MRD No'); ?></th>
                        <th><?php echo _l('Patient Name'); ?></th>
                        <th><?php echo _l('Age'); ?></th>
                        <th><?php echo _l('Sex'); ?></th>
                        <th><?php echo _l('mobile'); ?></th>
                        <th><?php echo _l('ref_by'); ?></th>
                        <th><?php echo _l('Total Amt'); ?></th>
                        <th><?php echo _l('Disc'); ?></th>
                        <th><?php echo _l('Bill Amt'); ?></th>
                        <th><?php echo _l('Serv.charge'); ?></th>
                        <th><?php echo _l('LastPaid Amt'); ?></th>
                        <th><?php echo _l('DuePaid Amt'); ?></th>
                        <th><?php echo _l('TotalPaid Amt'); ?></th>
                        <th><?php echo _l('Bal Amt'); ?></th>
                        <th><?php echo _l('Paid by'); ?></th>
                        <th><?php echo _l('Pay Details'); ?></th>
                        <th><?php echo _l('Gen by'); ?></th>
                        <th><?php echo _l('DuePaid On'); ?></th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                      <tr>
                        <th></th> <!-- SL No -->
                        <th></th> <!-- Bill No -->
                        <th></th> <!-- Date -->
                        <th></th> <!-- MRD No -->
                        <th></th> <!-- Patient Name -->
                        <th></th> <!-- Age -->
                        <th></th> <!-- Sex -->
                        <th></th> <!-- Mobile -->
                        <th class="text-right"><?php echo _l('Total'); ?>:</th> <!-- Label -->
                        <th></th> <!-- Total Amount -->
                        <th></th> <!-- Discount -->
                        <th></th> <!-- Bill Amount -->
                        <th></th> <!-- Service Charge -->
                        <th></th> <!-- Last Paid -->
                        <th></th> <!-- Due Paid -->
                        <th></th> <!-- Total Paid -->
                        <th></th> <!-- Balance -->
                        <th></th> <!-- Paid By -->
                        <th></th> <!-- Payment Details -->
                        <th></th> <!-- Gen By -->
                        <th></th> <!-- Due Paid On -->
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Print-Friendly Styling -->
<style media="print">
  table, th, td {
    border: 1px solid #000 !important;
    border-collapse: collapse !important;
  }
  th, td {
    padding: 6px !important;
    vertical-align: middle !important;
    text-align: left !important;
  }
  tfoot th {
    font-weight: bold !important;
    background-color: #f9f9f9 !important;
  }
  tfoot th:nth-child(n+10):nth-child(-n+17) {
    text-align: right !important;
  }
</style>

<?php init_tail(); ?>

<script>
var duePaidTable;

$(function() {
    // Set default report_to date to current date
    var today = new Date();
    var day = String(today.getDate()).padStart(2, '0');
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var year = today.getFullYear();
    var formattedDate = day + '-' + month + '-' + year; // DD-MM-YYYY format
    $('input[name="report_to"]').val(formattedDate);

    var DuePaidDetailsServerParams = {
        report_from: '[name="report_from"]',
        report_to: '[name="report_to"]',
        mrd_from: '[name="mrd_from"]',
        mrd_to: '[name="mrd_to"]',
        referral_name: '[name="referral_name"]'
    };

    duePaidTable = initDataTable(
        '.table-due-paid-details-report',
        admin_url + 'reports/due_paid_details_table',
        [],
        [],
        DuePaidDetailsServerParams,
        [0, 'desc']
    );

    $('.table-due-paid-details-report').on('draw.dt', function () {
        var api = duePaidTable;
        var response = api.ajax.json();
        if (!response || !response.sums) return;
        var sums = response.sums;

        $(api.column(9).footer()).html(sums.bill_amount);
        $(api.column(10).footer()).html(sums.discount);
        $(api.column(11).footer()).html(sums.total_amount);
        $(api.column(12).footer()).html(sums.service_charge);
        $(api.column(13).footer()).html(sums.last_paid);
        $(api.column(14).footer()).html(sums.due_paid);
        $(api.column(15).footer()).html(sums.total_paid);
        $(api.column(16).footer()).html(sums.balance);
    });

    $('input[name="report_from"], input[name="report_to"], input[name="mrd_from"], input[name="mrd_to"], input[name="referral_name"]').on('change', function() {
        filterDuePaidReport();
    });
});

function filterDuePaidReport() {
    duePaidTable.ajax.reload();
}

function resetDuePaidReport() {
    $('input[name="report_from"]').val('');
    $('input[name="report_to"]').val('');
    $('input[name="mrd_from"]').val('');
    $('input[name="mrd_to"]').val('');
    $('input[name="referral_name"]').val('');
    duePaidTable.ajax.reload();
}
</script>
