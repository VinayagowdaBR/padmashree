<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin report-title"><?php echo _l('Log Edited Details'); ?></h4>
            <hr class="hr-panel-heading" />

            <!-- Filters -->
            <div class="row mbot15">
              
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
                <div class="btn-group" style="margin-top:25px;">
                  <button class="btn btn-primary" onclick="filterLogEditedReport(); return false;"><?php echo _l('apply'); ?></button>
                  <button class="btn btn-default" onclick="resetLogEditedReport(); return false;"><?php echo _l('reset'); ?></button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-log-edited-report" id="log-edited-table" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <th>Edit Date & Time</th>
                        <th>Bill No</th>
                        <th>MRD No</th>
                        <th>Customer</th>
                        <th>Edited By</th>
                        <th>Description of Edit</th>
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
  #log-edited-table th,
  #log-edited-table td {
    vertical-align: middle;
  }
</style>

<?php init_tail(); ?>

<script>
var logEditedTable;
var isFirstLoad = true;

$(document).ready(function () {
    var today = new Date();
    var day = String(today.getDate()).padStart(2, '0');
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var year = today.getFullYear();
    var formattedDate = day + '-' + month + '-' + year;
    
    $('input[name="report_from"]').val(formattedDate);
    $('input[name="report_to"]').val(formattedDate);

    logEditedTable = initDataTable(
        '.table-log-edited-report',
        admin_url + 'reports/log_edited_table',
        [],
        [],
        {},
        [0, 'desc']
    );

    $('.table-log-edited-report').on('preXhr.dt', function (e, settings, data) {
        data.report_from = $('input[name="report_from"]').val();
        data.report_to = $('input[name="report_to"]').val();
        data.mrd_from = $('input[name="mrd_from"]').val();
        data.mrd_to = $('input[name="mrd_to"]').val();
    });

    $('.table-log-edited-report').on('init.dt', function() {
        if (isFirstLoad) {
            isFirstLoad = false;
            logEditedTable.ajax.reload();
        }
    });

    $('input[name="report_from"], input[name="report_to"], input[name="mrd_from"], input[name="mrd_to"]').on('change', function () {
        filterLogEditedReport();
    });
});

function filterLogEditedReport() {
    logEditedTable.ajax.reload();
}

function resetLogEditedReport() {
    $('input[name="report_from"]').val('');
    $('input[name="report_to"]').val('');
    $('input[name="mrd_from"]').val('');
    $('input[name="mrd_to"]').val('');
    logEditedTable.ajax.reload();
}
</script>
