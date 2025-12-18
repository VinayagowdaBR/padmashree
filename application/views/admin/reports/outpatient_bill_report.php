<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin report-title"><?php echo _l('Out Patient Details'); ?></h4>
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
                  <button class="btn btn-info" onclick="window.print(); return false;">
                    <i class="fa fa-print"></i> <?php echo _l('Print'); ?>
                  </button>
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
                        <th>PaidBy</th>
                        <th>Pay Details</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
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
  #outpatient-bill-table th,
  #outpatient-bill-table td {
    border: 1px solid #ccc !important;
    vertical-align: middle;
    padding: 8px 4px;
  }
  #outpatient-bill-table {
    border-collapse: collapse !important;
  }
</style>

<!-- PRINT STYLES FOR OUTPATIENT REPORT -->
<style media="print">
  @page {
    size: A4 landscape;
    margin: 6mm 4mm;
  }
  
  /* Hide URLs */
  a[href]:after {
    content: none !important;
  }
  
  a {
    text-decoration: none !important;
    color: #000 !important;
  }
  
  /* Hide UI elements */
  #header,
  #top-header,
  aside,
  .sidebar,
  .sidebar-wrapper,
  nav,
  .navbar,
  .setup-menu,
  footer,
  .footer,
  .btn,
  .btn-group,
  button,
  .form-group,
  label,
  input[type="text"],
  input[type="date"],
  select,
  .hr-panel-heading,
  .mbot15,
  .dataTables_filter,
  .dataTables_length,
  .dataTables_info,
  .dataTables_paginate,
  .dataTables_processing,
  div.dataTables_wrapper > div:first-child,
  div.dataTables_wrapper > div:last-child {
    display: none !important;
  }
  
  /* Prevent blank pages */
  html, body {
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
    overflow: visible !important;
  }
  
  #wrapper,
  .content,
  .row,
  .col-md-12 {
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
    display: block !important;
    overflow: visible !important;
  }
  
  .panel_s {
    border: none !important;
    box-shadow: none !important;
    margin: 0 !important;
  }
  
  .panel-body {
    padding: 2mm !important;
  }
  
  /* Title */
  .report-title {
    font-size: 14pt !important;
    font-weight: bold !important;
    text-align: center !important;
    margin: 0 0 4mm 0 !important;
    padding: 0 !important;
    color: #000 !important;
  }
  
  .table-responsive {
    overflow: visible !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    height: auto !important;
  }
  
  .dataTables_wrapper {
    margin: 0 !important;
    padding: 0 !important;
    height: auto !important;
  }
  
  /* TABLE STYLES */
  #outpatient-bill-table {
    width: 100% !important;
    max-width: 100% !important;
    border-collapse: collapse !important;
    font-size: 5.5pt !important;
    margin: 0 !important;
    page-break-inside: auto;
    font-family: Arial, sans-serif !important;
  }
  
  #outpatient-bill-table thead {
    display: table-header-group;
  }
  
  #outpatient-bill-table tfoot {
    display: table-footer-group;
  }
  
  #outpatient-bill-table th {
    background-color: #e0e0e0 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    font-size: 6pt !important;
    font-weight: bold !important;
    padding: 1.5mm 0.3mm !important;
    border: 0.3pt solid #000 !important;
    text-align: center !important;
    white-space: nowrap !important;
    color: #000 !important;
    line-height: 1.1 !important;
  }
  
  #outpatient-bill-table td {
    font-size: 5.5pt !important;
    padding: 0.8mm 0.3mm !important;
    border: 0.3pt solid #666 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    color: #000 !important;
    line-height: 1.1 !important;
  }

  #outpatient-bill-table td strong {
    font-weight: bold !important;
    color: #000 !important; 
  }
  
  #outpatient-bill-table a {
    text-decoration: none !important;
    color: inherit !important;
    font-weight: normal !important;
  }
  
  #outpatient-bill-table tbody tr {
    page-break-inside: avoid;
  }
  
  #outpatient-bill-table tbody tr:nth-child(even) {
    background-color: #f5f5f5 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  #outpatient-bill-table thead tr {
    page-break-after: avoid;
  }
  
  /* FOOTER */
  #outpatient-bill-table tfoot {
    border-top: 2pt solid #000 !important;
  }
  
  #outpatient-bill-table tfoot th {
    background-color: #ffffff !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    font-weight: bold !important;
    font-size: 6pt !important;
    padding: 2mm 0.3mm !important;
    border: 0.5pt solid #000 !important;
    color: #000 !important;
  }
  
  #outpatient-bill-table tfoot th:first-child {
    text-align: right !important;
    padding-right: 2mm !important;
  }
  
  #outpatient-bill-table tfoot th:not(:first-child) {
    text-align: right !important;
    padding-right: 2mm !important;
  }
  
  /* Column widths - 22 columns */
  #outpatient-bill-table th:nth-child(1), #outpatient-bill-table td:nth-child(1) { width: 5% !important; text-align: left !important; }
  #outpatient-bill-table th:nth-child(2), #outpatient-bill-table td:nth-child(2) { width: 5% !important; text-align: center !important; }
  #outpatient-bill-table th:nth-child(3), #outpatient-bill-table td:nth-child(3) { width: 4% !important; text-align: center !important; }
  #outpatient-bill-table th:nth-child(4), #outpatient-bill-table td:nth-child(4) { width: 8% !important; text-align: left !important; }
  #outpatient-bill-table th:nth-child(5), #outpatient-bill-table td:nth-child(5) { width: 8% !important; text-align: left !important; }
  #outpatient-bill-table th:nth-child(6), #outpatient-bill-table td:nth-child(6) { width: 3% !important; text-align: center !important; }
  #outpatient-bill-table th:nth-child(7), #outpatient-bill-table td:nth-child(7) { width: 4% !important; text-align: center !important; }
  /* Sex 8 (was 9) */
  #outpatient-bill-table th:nth-child(8), #outpatient-bill-table td:nth-child(8) { width: 3% !important; text-align: center !important; }
  /* Mobile 9 (was 10) */
  #outpatient-bill-table th:nth-child(9), #outpatient-bill-table td:nth-child(9) { width: 6% !important; text-align: left !important; }
  /* Total Amt 10 (was 11) */
  #outpatient-bill-table th:nth-child(10), #outpatient-bill-table td:nth-child(10) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  /* Disc 11 (was 12) */
  #outpatient-bill-table th:nth-child(11), #outpatient-bill-table td:nth-child(11) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  /* Bill Amt 12 (was 13) */
  #outpatient-bill-table th:nth-child(12), #outpatient-bill-table td:nth-child(12) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  /* Serv.Charge 13 (was 14) */
  #outpatient-bill-table th:nth-child(13), #outpatient-bill-table td:nth-child(13) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  /* Paid Amt 14 (was 15) */
  #outpatient-bill-table th:nth-child(14), #outpatient-bill-table td:nth-child(14) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  /* Bal 15 (was 16) */
  #outpatient-bill-table th:nth-child(15), #outpatient-bill-table td:nth-child(15) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  /* Cash 16 (was 17) */
  #outpatient-bill-table th:nth-child(16), #outpatient-bill-table td:nth-child(16) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  /* Cheq 17 (was 18) */
  #outpatient-bill-table th:nth-child(17), #outpatient-bill-table td:nth-child(17) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  /* CC 18 (was 19) */
  #outpatient-bill-table th:nth-child(18), #outpatient-bill-table td:nth-child(18) { width: 4% !important; text-align: right !important; padding-right: 1mm !important; }
  /* UPI 19 (was 20) */
  #outpatient-bill-table th:nth-child(19), #outpatient-bill-table td:nth-child(19) { width: 4% !important; text-align: right !important; padding-right: 1mm !important; }
  
  /* PaidBy 20 (moved from 8, width was 6% left) */
  #outpatient-bill-table th:nth-child(20), #outpatient-bill-table td:nth-child(20) { width: 6% !important; text-align: left !important; }
  
  /* Pay Details 21 (was 21, width was 6% left) */
  #outpatient-bill-table th:nth-child(21), #outpatient-bill-table td:nth-child(21) { width: 6% !important; text-align: left !important; }
</style>

<?php init_tail(); ?>

<script>
var outpatientTable;
var isFirstLoad = true;

$(document).ready(function () {
    var today = new Date();
    var day = String(today.getDate()).padStart(2, '0');
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var year = today.getFullYear();
    var formattedDate = day + '-' + month + '-' + year;
    
    $('input[name="report_from"]').val(formattedDate);
    $('input[name="report_to"]').val(formattedDate);

    outpatientTable = initDataTable(
        '.table-outpatient-bill-report',
        admin_url + 'reports/outpatient_bill_table',
        [],
        [],
        {},
        [0, 'desc']
    );

    $('.table-outpatient-bill-report').on('preXhr.dt', function (e, settings, data) {
        data.report_from = $('input[name="report_from"]').val();
        data.report_to = $('input[name="report_to"]').val();
        data.mrd_from = $('input[name="mrd_from"]').val();
        data.mrd_to = $('input[name="mrd_to"]').val();
        data.referral_name = $('input[name="referral_name"]').val();
    });

    $('.table-outpatient-bill-report').on('init.dt', function() {
        var today = new Date();
        var day = String(today.getDate()).padStart(2, '0');
        var month = String(today.getMonth() + 1).padStart(2, '0');
        var year = today.getFullYear();
        var formattedDate = day + '-' + month + '-' + year;
        $('input[name="report_from"]').val(formattedDate);
        $('input[name="report_to"]').val(formattedDate);
        
        if (isFirstLoad) {
            isFirstLoad = false;
            outpatientTable.ajax.reload();
        }
    });

    $('input[name="report_from"], input[name="report_to"], input[name="mrd_from"], input[name="mrd_to"], input[name="referral_name"]').on('change', function () {
        filterOutpatientReport();
    });
});

function filterOutpatientReport() {
    outpatientTable.ajax.reload();
}

function resetOutpatientReport() {
    $('input[name="report_from"]').val('');
    $('input[name="report_to"]').val('');
    $('input[name="mrd_from"]').val('');
    $('input[name="mrd_to"]').val('');
    $('input[name="referral_name"]').val('');
    outpatientTable.ajax.reload();
}
</script>
