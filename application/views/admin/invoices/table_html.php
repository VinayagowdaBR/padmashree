  <?php defined('BASEPATH') or exit('No direct script access allowed');

  $table_id = $table_id ?? 'invoices';

  $table_data = array(
      _l('Bill No'),
      _l('invoice_dt_table_heading_date'),
      _l('MRD No'),
      array(
          'name' => _l('invoice_dt_table_heading_client'),
          'th_attrs' => array('class' => (isset($client) ? 'not_visible' : ''))
      ),
      _l('Ref.By'),
      _l('Modality'),
      _l('Paid by'),
      _l('Total Amount'),   // index 7
      _l('discount'),       // index 8
      _l('Bill Amount'),    // index 9
      _l('Serv.charge'),    // index 10
      _l('Paid Amount'),    // index 11
      _l('Balance '),       // index 12
      _l('Refunds')         // index 13 - NEW: Refund amount column
  );

  // Add custom fields
    // $custom_fields = get_custom_fields('invoice', array('show_on_table' => 1));
    // foreach ($custom_fields as $field) {
    //     array_push($table_data, [
    //         'name'     => $field['name'],
    //         'th_attrs' => array('data-type' => $field['type'], 'data-custom-field' => 1)
    //     ]);
    // }

  // Apply hooks
  $table_data = hooks()->apply_filters('invoices_table_columns', $table_data);

  // Render datatable
  render_datatable($table_data, (isset($class) ? $class : 'invoices'), [], [
      'id' => $table_id,
      'render_footer' => true
  ]);
  ?>

  <style>
  tfoot {
      display: table-footer-group !important;
  }
  </style>

<script>
(function ($) {
  const tableId = '#<?php echo $table_id; ?>';
  const $table = $(tableId);

  jQuery(document).on('preInit.dt', function (e, settings) {
    const table = new jQuery.fn.dataTable.Api(settings);
    const $t = jQuery(table.table().node());
    if (!$t.find('tfoot').length) {
      let foot = '<tfoot><tr>';
      $t.find('thead th').each(() => {
        foot += '<th></th>';
      });
      foot += '</tr></tfoot>';
      $t.append(foot);
    }
  });

  const dt = $table.DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?php echo admin_url('invoices/get_invoices_ajax'); ?>',
      type: 'POST',
      data: function (d) {
        d.mrd_no = $('input[name="mrd_no"]').val();
        d.client_id = $('select[name="client_id"]').val();
        d.ref_by = $('select[name="ref_by"]').val();
        d.date_from = $('input[name="from_date"]').val();
        d.date_to = $('input[name="to_date"]').val();
      },
      dataSrc: function (json) {
        dt.totals = json.totals || {};
        return json.data || [];
      }
    },
    footerCallback: function () {
      const api = this.api();
      const footer = $(api.table().footer()).find('th');
      const totals = dt.totals;

      footer.eq(6).html('Total:');
      footer.eq(7).html(formatMoney(totals.total_amount || 0));
      footer.eq(8).html(formatMoney(totals.discount || 0));
      footer.eq(9).html(formatMoney(totals.bill_amount || 0));
      footer.eq(10).html(formatMoney(totals.service_charge || 0));
      footer.eq(11).html(formatMoney(totals.paid_amount || 0));
      footer.eq(12).html(formatMoney(totals.balance || 0));

      for (let i = 13; i < footer.length; i++) {
        footer.eq(i).html('');
      }
    }
  });

  function formatMoney(number) {
    if (typeof app_format_money === 'function') {
      return app_format_money(number, app.options.currency);
    }
    return parseFloat(number || 0).toFixed(2);
  }
});
</script>

