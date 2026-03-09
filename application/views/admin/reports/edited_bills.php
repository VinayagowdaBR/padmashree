<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin report-title"><?php echo _l('Edited Outpatient Bills'); ?></h4>
            <hr class="hr-panel-heading" />

            <!-- Filters -->
            <div class="row mbot15">
              <input type="hidden" name="report_months" value="custom">
              
              <!-- Row 1 -->
              <div class="col-md-3">
                <?php echo render_date_input('report_from', 'report_from'); ?>
              </div>
              <div class="col-md-3">
                <?php echo render_date_input('report_to', 'report_to'); ?>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="mrd_from"><?php echo _l('From MRD No'); ?></label>
                  <input type="text" name="mrd_from" id="mrd_from" class="form-control" placeholder="Enter From MRD No" />
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="mrd_to"><?php echo _l('To MRD No'); ?></label>
                  <input type="text" name="mrd_to" id="mrd_to" class="form-control" placeholder="Enter To MRD No" />
                </div>
              </div>

              <div class="clearfix"></div>

              <!-- Row 2 -->
              <div class="col-md-3">
                <div class="form-group">
                  <label for="referral_name"><?php echo _l('Referral Name'); ?></label>
                  <input type="text" name="referral_name" id="referral_name" class="form-control" placeholder="Enter Referral Name" />
                </div>  
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="paid_by"><?php echo _l('Paid By'); ?></label>
                  <select name="paid_by" id="paid_by" class="form-control selectpicker" data-live-search="true" data-size="5">
                    <option value=""><?php echo _l('All'); ?></option>
                    <?php
                    $CI = &get_instance();
                    $CI->load->model('payment_modes_model');
                    $payment_modes = $CI->payment_modes_model->get('', [], true);
                    foreach ($payment_modes as $mode) {
                        if ($mode['active'] == 1) {
                            echo '<option value="' . $mode['id'] . '">' . $mode['name'] . '</option>';
                        }
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="pay_details"><?php echo _l('Pay Details'); ?></label>
                  <input type="text" name="pay_details" id="pay_details" class="form-control" placeholder="Enter Pay Details (e.g. Transaction ID)" />
                </div>
              </div>
              <div class="col-md-3">
                <div class="btn-group" style="margin-top:25px;">
                  <button class="btn btn-primary" onclick="filterEditedBillsReport(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetEditedBillsReport(); return false;"><?php echo _l('reset'); ?></button>
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
                  <table class="table table-bordered table-striped table-edited-bills-report" id="edited-bills-table" cellspacing="0" width="100%">
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
  #edited-bills-table th,
  #edited-bills-table td {
    border: 1px solid #ccc !important;
    vertical-align: middle;
    padding: 8px 4px;
  }
  #edited-bills-table {
    border-collapse: collapse !important;
  }
</style>

<!-- PRINT STYLES FOR EDITED OUTPATIENT REPORT -->
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
  #edited-bills-table {
    width: 100% !important;
    max-width: 100% !important;
    border-collapse: collapse !important;
    font-size: 5.5pt !important;
    margin: 0 !important;
    page-break-inside: auto;
    font-family: Arial, sans-serif !important;
  }
  
  #edited-bills-table thead {
    display: table-header-group;
  }
  
  #edited-bills-table tfoot {
    display: table-footer-group;
  }
  
  #edited-bills-table th {
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
  
  #edited-bills-table td {
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

  #edited-bills-table td strong {
    font-weight: bold !important;
    color: #000 !important; 
  }
  
  #edited-bills-table a {
    text-decoration: none !important;
    color: inherit !important;
    font-weight: normal !important;
  }
  
  #edited-bills-table tbody tr {
    page-break-inside: avoid;
  }
  
  #edited-bills-table tbody tr:nth-child(even) {
    background-color: #f5f5f5 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  #edited-bills-table thead tr {
    page-break-after: avoid;
  }
  
  /* FOOTER */
  #edited-bills-table tfoot {
    border-top: 2pt solid #000 !important;
  }
  
  #edited-bills-table tfoot th {
    background-color: #ffffff !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    font-weight: bold !important;
    font-size: 6pt !important;
    padding: 2mm 0.3mm !important;
    border: 0.5pt solid #000 !important;
    color: #000 !important;
  }
  
  #edited-bills-table tfoot th:first-child {
    text-align: right !important;
    padding-right: 2mm !important;
  }
  
  #edited-bills-table tfoot th:not(:first-child) {
    text-align: right !important;
    padding-right: 2mm !important;
  }
  
  /* Column widths */
  #edited-bills-table th:nth-child(1), #edited-bills-table td:nth-child(1) { width: 5% !important; text-align: left !important; }
  #edited-bills-table th:nth-child(2), #edited-bills-table td:nth-child(2) { width: 5% !important; text-align: center !important; }
  #edited-bills-table th:nth-child(3), #edited-bills-table td:nth-child(3) { width: 4% !important; text-align: center !important; }
  #edited-bills-table th:nth-child(4), #edited-bills-table td:nth-child(4) { width: 8% !important; text-align: left !important; }
  #edited-bills-table th:nth-child(5), #edited-bills-table td:nth-child(5) { width: 8% !important; text-align: left !important; }
  #edited-bills-table th:nth-child(6), #edited-bills-table td:nth-child(6) { width: 3% !important; text-align: center !important; }
  #edited-bills-table th:nth-child(7), #edited-bills-table td:nth-child(7) { width: 4% !important; text-align: center !important; }
  #edited-bills-table th:nth-child(8), #edited-bills-table td:nth-child(8) { width: 3% !important; text-align: center !important; }
  #edited-bills-table th:nth-child(9), #edited-bills-table td:nth-child(9) { width: 6% !important; text-align: left !important; }
  #edited-bills-table th:nth-child(10), #edited-bills-table td:nth-child(10) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(11), #edited-bills-table td:nth-child(11) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(12), #edited-bills-table td:nth-child(12) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(13), #edited-bills-table td:nth-child(13) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(14), #edited-bills-table td:nth-child(14) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(15), #edited-bills-table td:nth-child(15) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(16), #edited-bills-table td:nth-child(16) { width: 5% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(17), #edited-bills-table td:nth-child(17) { width: 4% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(18), #edited-bills-table td:nth-child(18) { width: 4% !important; text-align: right !important; padding-right: 1mm !important; }
  #edited-bills-table th:nth-child(19), #edited-bills-table td:nth-child(19) { width: 6% !important; text-align: left !important; }
  #edited-bills-table th:nth-child(20), #edited-bills-table td:nth-child(20) { width: 6% !important; text-align: left !important; }
</style>

<?php init_tail(); ?>

<script>
var editedBillsTable;
var isFirstLoad = true;

$(document).ready(function () {
    var today = new Date();
    var day = String(today.getDate()).padStart(2, '0');
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var year = today.getFullYear();
    var formattedDate = day + '-' + month + '-' + year;
    
    $('input[name="report_from"]').val(formattedDate);
    $('input[name="report_to"]').val(formattedDate);

    editedBillsTable = initDataTable(
        '.table-edited-bills-report',
        admin_url + 'reports/edited_bills_table',
        [],
        [],
        {},
        [0, 'desc']
    );


    $('.table-edited-bills-report').on('preXhr.dt', function (e, settings, data) {
        data.report_from = $('input[name="report_from"]').val();
        data.report_to = $('input[name="report_to"]').val();
        data.mrd_from = $('input[name="mrd_from"]').val();
        data.mrd_to = $('input[name="mrd_to"]').val();
        data.referral_name = $('input[name="referral_name"]').val();
        data.paid_by = $('select[name="paid_by"]').val();
        data.pay_details = $('input[name="pay_details"]').val();
    });

    $('.table-edited-bills-report').on('init.dt', function() {
        var today = new Date();
        var day = String(today.getDate()).padStart(2, '0');
        var month = String(today.getMonth() + 1).padStart(2, '0');
        var year = today.getFullYear();
        var formattedDate = day + '-' + month + '-' + year;
        $('input[name="report_from"]').val(formattedDate);
        $('input[name="report_to"]').val(formattedDate);
        
        if (isFirstLoad) {
            isFirstLoad = false;
            editedBillsTable.ajax.reload();
        }
    });

    $('input[name="report_from"], input[name="report_to"], input[name="mrd_from"], input[name="mrd_to"], input[name="referral_name"], select[name="paid_by"], input[name="pay_details"]').on('change', function () {
        filterEditedBillsReport();
    });
});

function filterEditedBillsReport() {
    editedBillsTable.ajax.reload();
}

function resetEditedBillsReport() {
    $('input[name="report_from"]').val('');
    $('input[name="report_to"]').val('');
    $('input[name="mrd_from"]').val('');
    $('input[name="mrd_to"]').val('');
    $('input[name="referral_name"]').val('');
    $('select[name="paid_by"]').selectpicker('val', '');
    $('input[name="pay_details"]').val('');
    editedBillsTable.ajax.reload();
}
</script>
