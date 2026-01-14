<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div id="vueApp">
			<div class="row">
				<div class="col-md-12 tw-mb-3 md:tw-mb-6">
					<div class="md:tw-flex md:tw-items-center">
						<div class="tw-grow">
							<h4 class="tw-my-0 tw-font-bold tw-text-xl">
								<?= _l('invoices'); ?>
							</h4>
							<?php if (! isset($project)) { ?>
							<a href="<?= admin_url('invoices/recurring'); ?>"
								class="tw-mr-4">
								<?= _l('invoices_list_recurring'); ?>
								&rarr;
							</a>
							<?php } ?>
						</div>

						<div id="invoices_total" data-type="badge"
							class="tw-self-start tw-mt-2 md:tw-mt-0 empty:tw-min-h-[60px]"></div>
					</div>

				</div>
				<div class="col-md-12">
					<?php $this->load->view('admin/invoices/quick_stats'); ?>
				</div>
				<?php include_once APPPATH . 'views/admin/invoices/filter_params.php'; ?>
				<?php $this->load->view('admin/invoices/list_template'); ?>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<div id="modal-wrapper"></div>
<script>
	var hidden_columns = [2, 6, 7, 8];
	
</script>
<?php init_tail(); ?>
<script>
	$(function() {
		init_invoice();

        // Run Action Button Script
        $('#run-action-btn').click(function() {
            $.ajax({
                url: '<?= admin_url('clients/update_option'); ?>',
                method: 'POST',
                success: function(response) {
                    alert(response);
                },
                error: function(xhr, status, error) {
                    alert('Failed to perform action: ' + error);
                }
            });
        });
	});

	// Initialize invoice sidebar functionality
	function init_invoice(id) {
		// Load invoice by ID if provided
		if (typeof id !== 'undefined') {
			load_invoice_sidebar(id);
		} else {
			// Check if there's an invoice ID in the hidden input (from URL)
			var invoiceid = $('input[name="invoiceid"]').val();
			if (invoiceid && invoiceid != '') {
				load_invoice_sidebar(invoiceid);
			}
		}

		// Add click handler to invoice table rows
		$('.table-invoices').on('click', 'tbody tr', function(e) {
			// Don't trigger if clicking on links, buttons, or dropdown menus
			if ($(e.target).closest('a, button, .dropdown, input, .dropdown-menu').length) {
				return;
			}

			// Get the invoice ID from the row's DT_RowId attribute
			var rowData = $('.table-invoices').DataTable().row(this).data();
			if (rowData && rowData.DT_RowId) {
				var invoiceId = rowData.DT_RowId.replace('invoice_', '');
				load_invoice_sidebar(invoiceId);
			}
		});
	}

	// Load invoice data into sidebar via AJAX
	function load_invoice_sidebar(id) {
		console.log('=== load_invoice_sidebar called with ID:', id);
		
		if (!id) {
			console.error('No invoice ID provided');
			return;
		}

		// Show loading state
		console.log('Setting loading spinner...');
		$('#invoice').html('<div class="text-center mtop50"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Loading invoice ' + id + '...</p></div>');

		// If sidebar is hidden, show it
		if ($('#invoice').hasClass('hide')) {
			console.log('Sidebar is hidden, calling toggle_small_view...');
			toggle_small_view('.table-invoices', '#invoice');
		}

		var ajaxUrl = admin_url + 'invoices/get_invoice_data_ajax/' + id;
		console.log('Making AJAX call to:', ajaxUrl);

		// Fetch invoice preview HTML via AJAX (using existing controller method)
		$.get(ajaxUrl, function(response) {
			console.log('AJAX Success! Response length:', response.length, 'characters');
			console.log('Response preview:', response.substring(0, 200));
			
			$('#invoice').html(response);
			console.log('Content loaded into #invoice div');

			// Update URL hash without page reload
			if (typeof history.pushState !== 'undefined') {
				var newUrl = admin_url + 'invoices/list_invoices/' + id;
				history.pushState({invoiceId: id}, '', newUrl);
				console.log('URL updated to:', newUrl);
			}

			// Update hidden input
			$('input[name="invoiceid"]').val(id);

			// Initialize scripts needed for the sidebar content
			console.log('Initializing sidebar scripts...');
			try {
				if (typeof init_tabs_scrollable === 'function') init_tabs_scrollable();
				if (typeof init_datepicker === 'function') init_datepicker();
				if (typeof init_selectpicker === 'function') init_selectpicker();
				if (typeof init_btn_with_tooltips === 'function') init_btn_with_tooltips();
				if (typeof init_lightbox === 'function') init_lightbox();
				
				// Initialize tooltips on new content
				$('[data-toggle="tooltip"]').tooltip();
				
				// Re-initialize invoice specific functions if they exist
				if (typeof init_invoice_view !== 'undefined') {
					init_invoice_view();
				}
				console.log('All scripts initialized successfully');
			} catch(e) {
				console.error('Error initializing scripts:', e);
			}
		}).fail(function(xhr, status, error) {
			console.error('=== AJAX FAILED ===');
			console.error('Status:', status);
			console.error('Error:', error);
			console.error('Response:', xhr.responseText);
			console.error('Status Code:', xhr.status);
			
			$('#invoice').html('<div class="alert alert-danger text-center mtop50"><h4>Failed to load invoice</h4><p>Error: ' + error + '</p><p>Status: ' + xhr.status + '</p></div>');
			alert_float('danger', 'Failed to load invoice details: ' + error);
		});
	}
</script>

</script>
</body>

</html>