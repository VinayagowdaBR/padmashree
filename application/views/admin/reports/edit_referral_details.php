<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h2 class="no-margin report-title">Edit Referral Details Report</h2>
            <div class="print-only-dates text-center" style="display: none; margin-bottom: 10px;">
                <p><strong>From:</strong> <span id="print_from_date"></span> &nbsp;&nbsp; <strong>To:</strong> <span id="print_to_date"></span></p>
            </div>
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
                  <label for="referral_filter"><?php echo _l('Referral'); ?></label>
                  <input type="text" name="referral_filter" id="referral_filter" class="form-control" placeholder="<?php echo _l('Enter referral name'); ?>" />
                </div>
              </div>

              <div class="col-md-2">
                <div class="btn-group" style="margin-top:25px;">
                  <button class="btn btn-primary" onclick="filterReferralReport(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetReferralReport(); return false;"><?php echo _l('reset'); ?></button>
                  <button class="btn btn-info" onclick="window.print(); return false;">
                    <i class="fa fa-print"></i> <?php echo _l('Print'); ?>
                  </button>
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
                        <th>SL No</th>
                        <th>Bill No</th>
                        <th>Date</th>
                        <th>Mrd No</th>
                        <th>Age</th>
                        <th>Name</th>
                        <th>Refer</th>
                        <th>Modality</th>
                        <th>Study</th>
                        <th>Amount</th> 
                        <th>discount</th>
                        <th>Total</th>
                        <th>Balance</th> 
                        <th>RefAmt</th> 
                      </tr>
                    </thead>
                    <tbody></tbody>
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

<!-- Screen Styles -->
<style>
  #referral-details-table th,
  #referral-details-table td {
    border: 1px solid #ccc !important;
    vertical-align: middle;
    padding: 8px 4px;
  }
  #referral-details-table {
    border-collapse: collapse !important;
  }
  #referral-details-table tfoot th {
    font-weight: bold !important;
  }
</style>

<!-- COMPLETE PRINT STYLES -->
<style media="print">
  @page {
    size: A4 landscape;
    margin: 6mm 4mm;
  }
  a[href]:after { content: none !important; }
  a { text-decoration: none !important; color: #000 !important; }
  #header, #top-header, aside, .sidebar, .sidebar-wrapper, nav, .navbar, .setup-menu, footer, .footer, .btn, .btn-group, button, .form-group, label, input, select, .hr-panel-heading, .mbot15, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate, .dataTables_processing, div.dataTables_wrapper > div:first-child, div.dataTables_wrapper > div:last-child {
    display: none !important;
  }
  html, body { width: 100% !important; height: auto !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; background: #fff !important; overflow: visible !important; }
  #wrapper, .content, .row, .col-md-12 { width: 100% !important; height: auto !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; float: none !important; display: block !important; overflow: visible !important; }
  .panel_s { border: none !important; box-shadow: none !important; margin: 0 !important; }
  .panel-body { padding: 2mm !important; }
  .report-title { font-size: 14pt !important; font-weight: bold !important; text-align: center !important; margin: 0 0 2mm 0 !important; padding: 0 !important; color: #000 !important; }
  .print-only-dates { display: block !important; text-align: center !important; font-size: 10pt !important; margin-bottom: 4mm !important; color: #000 !important; }
  .table-responsive { overflow: visible !important; margin: 0 !important; padding: 0 !important; border: none !important; height: auto !important; }
  .dataTables_wrapper { margin: 0 !important; padding: 0 !important; height: auto !important; }
  #referral-details-table { width: 100% !important; max-width: 100% !important; border-collapse: collapse !important; font-size: 6.5pt !important; margin: 0 !important; page-break-inside: auto; font-family: Arial, sans-serif !important; }
  #referral-details-table thead { display: table-header-group; }
  #referral-details-table th { background-color: #e0e0e0 !important; font-size: 7pt !important; font-weight: bold !important; padding: 1.5mm 0.5mm !important; border: 0.3pt solid #000 !important; text-align: center !important; white-space: nowrap !important; color: #000 !important; }
  #referral-details-table td { font-size: 6pt !important; padding: 1mm 0.5mm !important; border: 0.3pt solid #666 !important; vertical-align: middle !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; color: #000 !important; }
  #referral-details-table tbody tr { page-break-inside: avoid; height: auto !important; }
  #referral-details-table tbody tr:nth-child(even) { background-color: #f5f5f5 !important; }
  #referral-details-table td strong { font-weight: bold !important; font-size: 12px !important; color: #000 !important; }
</style>

<?php init_tail(); ?>

<script>
var referralTable;

function updatePrintDates() {
    var from = $('input[name="report_from"]').val();
    var to = $('input[name="report_to"]').val();
    $('#print_from_date').text(from || '---');
    $('#print_to_date').text(to || '---');
}

$(function() {
    var today = new Date();
    var day = String(today.getDate()).padStart(2, '0');
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var year = today.getFullYear();
    var formattedDate = day + '-' + month + '-' + year;
    $('input[name="report_to"]').val(formattedDate);
    updatePrintDates();

    referralTable = initDataTable(
        '.table-referral-details-report',
        admin_url + 'reports/edit_referral_details_table',
        [],
        [],
        {}, 
        [0, 'desc']
    );

    $('.table-referral-details-report').on('preXhr.dt', function(e, settings, data) {
        data.report_from = $('input[name="report_from"]').val();
        data.report_to = $('input[name="report_to"]').val();
        data.mrd_from = $('input[name="mrd_from"]').val();
        data.mrd_to = $('input[name="mrd_to"]').val();
        data.referral_name = $('input[name="referral_filter"]').val();
        updatePrintDates();
    });

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
    updatePrintDates();
    referralTable.ajax.reload();
}
</script>
