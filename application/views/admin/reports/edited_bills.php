<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h2 class="no-margin report-title">Edited Bills Report</h2>
            <hr class="hr-panel-heading" />

            <!-- Filters -->
            <div class="row mbot15">
              <div class="col-md-3">
                <?php echo render_date_input('report_from', 'report_from'); ?>
              </div>
              <div class="col-md-3">
                <?php echo render_date_input('report_to', 'report_to'); ?>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="staff_name"><?php echo _l('Staff Name'); ?></label>
                  <input type="text" name="staff_name" id="staff_name" class="form-control" placeholder="Search by Staff Name" />
                </div>
              </div>

              <div class="col-md-3">
                <div class="btn-group" style="margin-top:25px;">
                  <button class="btn btn-primary" onclick="filterReport(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetReport(); return false;"><?php echo _l('reset'); ?></button>
                  <button class="btn btn-info" onclick="window.print(); return false;">
                    <i class="fa fa-print"></i> <?php echo _l('Print'); ?>
                  </button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="row">
              <div class="col-md-12">
                 <p class="text-info"><i class="fa fa-info-circle"></i> Showing history of modifications to bills.</p>
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-edited-bills-report" id="edited-bills-table" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Bill No</th>
                        <th>Patient Name</th>
                        <th>Edited By</th>
                        <th>Description of Change</th>
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
  #edited-bills-table th,
  #edited-bills-table td {
    vertical-align: middle;
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
  
  #header, #top-header, aside, .sidebar, .sidebar-wrapper, nav, .navbar, .setup-menu, footer, .footer,
  .btn, .btn-group, button, .form-group, label, input, select, .hr-panel-heading, .mbot15,
  .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate, .dataTables_processing {
    display: none !important;
  }
  
  html, body {
    width: 100% !important; height: auto !important; margin: 0 !important; padding: 0 !important;
    background: #fff !important;
  }
  
  #wrapper, .content, .row, .col-md-12, .panel_s, .panel-body {
    width: 100% !important; margin: 0 !important; padding: 0 !important;
    border: none !important; box-shadow: none !important; float: none !important;
  }
  
  .report-title {
    text-align: center !important; margin-bottom: 5mm !important;
  }
  
  .table-responsive { overflow: visible !important; }
  
  #edited-bills-table {
    width: 100% !important; border-collapse: collapse !important; font-size: 9pt !important;
    font-family: Arial, sans-serif !important;
  }
  
  #edited-bills-table th {
    background-color: #e0e0e0 !important; font-weight: bold !important;
    border: 1px solid #000 !important; padding: 5px !important; text-align: center !important;
  }
  
  #edited-bills-table td {
    border: 1px solid #666 !important; padding: 5px !important;
  }

  /* Column Widths */
  #edited-bills-table th:nth-child(1) { width: 15% !important; } /* Date */
  #edited-bills-table th:nth-child(2) { width: 10% !important; } /* Bill No */
  #edited-bills-table th:nth-child(3) { width: 20% !important; } /* Patient */
  #edited-bills-table th:nth-child(4) { width: 15% !important; } /* Author */
  #edited-bills-table th:nth-child(5) { width: 40% !important; } /* Description */
</style>

<?php init_tail(); ?>

<script>
var reportTable;

$(function() {
    // Default to today/recent if needed, or leave empty to show all
    // $('input[name="report_from"]').val(moment().format('YYYY-MM-DD'));

    reportTable = initDataTable(
        '.table-edited-bills-report',
        admin_url + 'reports/edited_bills_table',
        [],
        [],
        'undefined',
        [0, 'desc']
    );

    // Filter injection
    $('.table-edited-bills-report').on('preXhr.dt', function(e, settings, data) {
        data.report_from = $('input[name="report_from"]').val();
        data.report_to = $('input[name="report_to"]').val();
        data.staff_name = $('input[name="staff_name"]').val();
    });

    $('input[name="report_from"], input[name="report_to"], input[name="staff_name"]').on('change keyup', function() {
        // Debounce if keyup
        filterReport();
    });
});

function filterReport() {
    reportTable.ajax.reload();
}

function resetReport() {
    $('input[name="report_from"]').val('');
    $('input[name="report_to"]').val('');
    $('input[name="staff_name"]').val('');
    reportTable.ajax.reload();
}
</script>
