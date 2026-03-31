<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="text-center">
                <h1 class="report-company-name" style="margin: 0; font-weight: bold; text-transform: uppercase;">PADMASHREE ADVANCED IMAGING SERVICES</h1>
                <h2 class="no-margin report-title"><?php echo _l('SUMMARY DETAILS'); ?></h2>
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
                <div class="btn-group" style="margin-top:25px;">
                  <button class="btn btn-primary" onclick="filterSummaryDetails(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetSummaryDetails(); return false;"><?php echo _l('reset'); ?></button>
                  <button class="btn btn-info" onclick="window.print(); return false;">
                    <i class="fa fa-print"></i> <?php echo _l('Print'); ?>
                  </button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="table-responsive" id="summary-table-wrapper" style="display: none;">
              <table class="table table-bordered table-striped table-summary-details" id="summary-details-table" cellspacing="0" width="100%">
                <thead>
                  <tr>
                    <th><?php echo _l('Sl.NO'); ?></th>
                    <th><?php echo _l('DATE'); ?></th>
                    <th><?php echo _l('MR'); ?></th>
                    <th><?php echo _l('DISC'); ?></th>
                    <th><?php echo _l('NET'); ?></th>
                    <th><?php echo _l('CONTRAST'); ?></th>
                    <th><?php echo _l('DISC'); ?></th>
                    <th><?php echo _l('NET'); ?></th>
                    <th><?php echo _l('MR-CONTRAST'); ?></th>
                    <th><?php echo _l('DISC'); ?></th>
                    <th><?php echo _l('NET'); ?></th>
                    <th><?php echo _l('CT'); ?></th>
                    <th><?php echo _l('DISC'); ?></th>
                    <th><?php echo _l('NET'); ?></th>
                    <th><?php echo _l('CONTRAST'); ?></th>
                    <th><?php echo _l('DISC'); ?></th>
                    <th><?php echo _l('NET'); ?></th>
                    <th><?php echo _l('CT-CONTRAST'); ?></th>
                    <th><?php echo _l('DISC'); ?></th>
                    <th><?php echo _l('NET'); ?></th>
                    <th><?php echo _l('TOTAL GROSS'); ?></th>
                    <th><?php echo _l('TOTAL DISC'); ?></th>
                    <th><?php echo _l('TOTAL NET'); ?></th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th colspan="2" class="text-right"><b><?php echo _l('total'); ?>:</b></th>
                    <?php for ($i = 0; $i < 21; $i++) {
                      echo '<th></th>';
                    } ?>
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

<!-- Screen Styles -->
<style>
  #summary-details-table th,
  #summary-details-table td {
    border: 1px solid #ccc !important;
    vertical-align: middle;
    padding: 8px 4px;
    font-size: 11px;
  }
</style>

<!-- Print Styles -->
<style media="print">
  @page {
    size: A4 landscape;
    margin: 6mm 4mm;
  }
  #header, #top-header, aside, .sidebar, .sidebar-wrapper, nav, .navbar, .setup-menu, footer, .footer, .btn, .btn-group, button, .form-group, label, input, select, .hr-panel-heading, .mbot15, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate, .dataTables_processing, div.dataTables_wrapper > div:first-child, div.dataTables_wrapper > div:last-child {
    display: none !important;
  }
  html, body { width: 100% !important; height: auto !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; background: #fff !important; overflow: visible !important; }
  #wrapper, .content, .row, .col-md-12 { width: 100% !important; height: auto !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; float: none !important; display: block !important; overflow: visible !important; }
  .panel_s { border: none !important; box-shadow: none !important; margin: 0 !important; }
  .panel-body { padding: 2mm !important; }
  .report-company-name { font-size: 16pt !important; font-weight: bold !important; text-align: center !important; margin: 0 0 2mm 0 !important; padding: 0 !important; color: #000 !important; text-transform: uppercase !important; }
  .report-title { font-size: 14pt !important; font-weight: bold !important; text-align: center !important; margin: 0 0 4mm 0 !important; padding: 0 !important; color: #000 !important; }
  
  #summary-details-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 6pt !important;
  }
  #summary-details-table th,
  #summary-details-table td {
    border: 1px solid #000 !important;
    padding: 2px !important;
  }
</style>

<?php init_tail(); ?>
<script>
var summaryDetailsTable;
var isFirstLoad = true;

$(function () {
  // Set BOTH report_from and report_to to today's date BEFORE DataTable init
  var today = new Date();
  var day = String(today.getDate()).padStart(2, '0');
  var month = String(today.getMonth() + 1).padStart(2, '0');
  var year = today.getFullYear();
  var formattedDate = day + '-' + month + '-' + year; // DD-MM-YYYY format
  
  // Set both dates immediately
  $('input[name="report_from"]').val(formattedDate);
  $('input[name="report_to"]').val(formattedDate);

  // Show the table immediately since we're auto-loading
  $('#summary-table-wrapper').show();

  var reportServerParams = {
    report_from: '[name="report_from"]',
    report_to: '[name="report_to"]'
  };

  summaryDetailsTable = initDataTable(
    '.table-summary-details',
    admin_url + 'reports/summary_details_table',
    [],
    [],
    reportServerParams,
    [0, 'asc']
  );

  // Use init.dt event to reload after table is fully initialized
  $('.table-summary-details').on('init.dt', function() {
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
      summaryDetailsTable.ajax.reload(null, false);
    }
  });

  $('.table-summary-details').on('draw.dt', function () {
    var api = summaryDetailsTable;
    var response = api.ajax.json();
    if (!response || !response.footer) return;

    var footer = response.footer;

    var footerMap = {
      mr_total: 2, mr_discount: 3, mr_net: 4,
      mr_contrast_total: 5, mr_contrast_discount: 6, mr_contrast_net: 7,
      mr_diff_total: 8, mr_diff_discount: 9, mr_diff_net: 10,
      ct_total: 11, ct_discount: 12, ct_net: 13,
      ct_contrast_total: 14, ct_contrast_discount: 15, ct_contrast_net: 16,
      ct_diff_total: 17, ct_diff_discount: 18, ct_diff_net: 19,
      total_gross: 20, total_discount: 21, total_net: 22
    };

    for (var key in footerMap) {
      if (footerMap.hasOwnProperty(key) && footer[key] !== undefined) {
        $(api.column(footerMap[key]).footer()).html(footer[key]);
      }
    }
  });

  // Automatically filter when date inputs change
  $('input[name="report_from"], input[name="report_to"]').on('change', function () {
    filterSummaryDetails();
  });
});

function filterSummaryDetails() {
  var from = $('input[name="report_from"]').val();
  var to = $('input[name="report_to"]').val();

  if (from && to) {
    $('#summary-table-wrapper').show();
    summaryDetailsTable.ajax.reload(null, false);
  }
}

function resetSummaryDetails() {
  $('input[name="report_from"]').val('');
  $('input[name="report_to"]').val('');
  $('#summary-table-wrapper').hide();
  summaryDetailsTable.ajax.reload(null, false);
}
</script>
