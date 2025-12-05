<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel_s invoice accounting-template desktop-ui">
    <div class="panel-body">
        <!-- Header Section -->
        <div class="invoice-header tw-bg-gray-50 tw-p-4 tw-rounded-lg tw-mb-6">
            <div class="row">
  
                <div class="col-md-8 text-right">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group tw-mb-2">
                                <label class="control-label tw-text-xs tw-font-semibold tw-text-gray-600">INVOICE NO.</label>
                                <input type="text" name="number" class="form-control input-sm" 
                                       value="<?= isset($invoice) ? $invoice->number : 'INV-' . date('Ymd') . sprintf('%03d', $next_invoice_number ?? 1) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group tw-mb-2">
                                <label class="control-label tw-text-xs tw-font-semibold tw-text-gray-600">DATE</label>
                                <input type="date" name="date" class="form-control input-sm" 
                                       value="<?= isset($invoice) ? $invoice->date : date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group tw-mb-2">
                                <label class="control-label tw-text-xs tw-font-semibold tw-text-gray-600">DUE DATE</label>
                                <input type="date" name="duedate" class="form-control input-sm" 
                                       value="<?= isset($invoice) ? $invoice->duedate : date('Y-m-d', strtotime('+1 day')) ?>" 
                                       min="<?= isset($invoice) ? $invoice->date : date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Affiliate Information -->
        <div class="row tw-mb-6">
            <!-- Customer Information Card -->
            <div class="col-md-8">
                <div class="panel panel-default tw-shadow-sm">
                    <div class="panel-heading tw-bg-blue-50 tw-border-b tw-border-blue-200">
                        <h5 class="panel-title tw-text-blue-800 tw-font-semibold">
                            <i class="fa fa-user tw-mr-2"></i>Customer Information
                        </h5>
                    </div>
                    <div class="panel-body tw-p-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label tw-text-sm tw-font-medium tw-text-gray-700">Full Name *</label>
                                    <input type="text" id="customer_name" name="customer_name" class="form-control input-sm" 
                                           value="<?= isset($invoice) ? $invoice->client->company : '' ?>" 
                                           placeholder="Enter customer name">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label tw-text-sm tw-font-medium tw-text-gray-700">Phone *</label>
                                    <input type="tel" id="customer_phone" name="customer_phone" class="form-control input-sm" 
                                           value="<?= isset($invoice) ? $invoice->client->phonenumber : '' ?>" 
                                           placeholder="Phone number">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label tw-text-sm tw-font-medium tw-text-gray-700">Gender</label>
                                    <select id="customer_gender" name="customer_gender" class="form-control input-sm">
                                        <option value="Male" <?= (isset($invoice) && isset($invoice->custom_fields->gender) && $invoice->custom_fields->gender == 'Male') ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= (isset($invoice) && isset($invoice->custom_fields->gender) && $invoice->custom_fields->gender == 'Female') ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= (isset($invoice) && isset($invoice->custom_fields->gender) && $invoice->custom_fields->gender == 'Other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label tw-text-sm tw-font-medium tw-text-gray-700">Age (Y)</label>
                                            <input type="number" name="customer_age_years" class="form-control input-sm" 
                                                   placeholder="YY" min="0" max="120"
                                                   value="<?= isset($invoice) ? ($invoice->custom_fields->age_years ?? '') : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label tw-text-sm tw-font-medium tw-text-gray-700">Age (M)</label>
                                            <input type="number" name="customer_age_months" class="form-control input-sm" 
                                                   placeholder="MM" min="0" max="11"
                                                   value="<?= isset($invoice) ? ($invoice->custom_fields->age_months ?? '') : '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Affiliate Information Card -->
            <div class="col-md-4">
                <div class="panel panel-default tw-shadow-sm">
                    <div class="panel-heading tw-bg-green-50 tw-border-b tw-border-green-200">
                        <h5 class="panel-title tw-text-green-800 tw-font-semibold">
                            <i class="fa fa-hospital tw-mr-2"></i>Affiliate Information
                        </h5>
                    </div>
                    <div class="panel-body tw-p-4">
                        <div class="form-group tw-mb-3">
                            <div class="input-group input-group-sm">
                                <input type="text" id="affiliate_search" class="form-control" 
                                       placeholder="Search affiliate...">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#affiliateModal">
                                        <i class="fa fa-plus"></i> New
                                    </button>
                                </span>
                            </div>
                            <div id="affiliate_search_results" class="search-results-dropdown" style="display:none;"></div>
                        </div>

                        <!-- Selected Affiliate Display -->
                        <div id="selected_affiliate" class="<?= isset($invoice) && !empty($invoice->affiliate_id) ? '' : 'hide' ?>">
                            <div class="tw-bg-green-50 tw-border tw-border-green-200 tw-rounded tw-p-3">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div class="tw-flex-1">
                                        <h6 class="tw-mt-0 tw-mb-1 tw-font-semibold tw-text-green-800" id="affiliate_name">
                                            <?= isset($invoice->affiliate) ? $invoice->affiliate->name : '' ?>
                                        </h6>
                                        <p class="tw-mb-1 tw-text-xs tw-text-gray-600" id="affiliate_phone">
                                            <i class="fa fa-phone tw-mr-1"></i><?= isset($invoice->affiliate) ? $invoice->affiliate->phone : '' ?>
                                        </p>
                                        <p class="tw-mb-0 tw-text-xs tw-text-gray-600" id="affiliate_location">
                                            <i class="fa fa-map-marker tw-mr-1"></i><?= isset($invoice->affiliate) ? $invoice->affiliate->location : '' ?>
                                        </p>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-xs tw-ml-2" onclick="clearAffiliate()">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Section -->
        <div class="panel panel-default tw-shadow-sm">
            <div class="panel-heading tw-bg-purple-50 tw-border-b tw-border-purple-200">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="panel-title tw-text-purple-800 tw-font-semibold">
                            <i class="fa fa-list-alt tw-mr-2"></i>Invoice Items
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <input type="text" id="item_search" class="form-control" 
                                   placeholder="Search items by name or description...">
                            <span class="input-group-addon tw-bg-purple-50 tw-border-purple-200">
                                <i class="fa fa-search tw-text-purple-600"></i>
                            </span>
                        </div>
                        <div id="item_search_results" class="search-results-dropdown" style="display:none;"></div>
                    </div>
                </div>
            </div>
            <div class="panel-body tw-p-0">
                <div class="table-responsive">
                    <table class="table invoice-items-table items table-main-invoice-edit has-calculations tw-mb-0">
                        <thead class="tw-bg-gray-100">
                            <tr>
                                <th width="30%" class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">Item Name</th>
                                <th width="35%" class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">Description</th>
                                <th width="8%" class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">Qty</th>
                                <th width="12%" class="tw-px-4 tw-py-3 tw-text-right tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">Service (₹)</th>
                                <th width="10%" class="tw-px-4 tw-py-3 tw-text-right tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">Price (₹)</th>
                                <th width="10%" class="tw-px-4 tw-py-3 tw-text-right tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">Total (₹)</th>
                                <th width="5%" class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="invoice_items_tbody" class="tw-divide-y tw-divide-gray-200">
                            <?php if (isset($invoice) && !empty($invoice->items)): ?>
                                <?php foreach ($invoice->items as $item): ?>
                                    <tr class="item tw-hover:bg-gray-50" data-item-id="<?= $item->id ?>">
                                        <td class="tw-px-4 tw-py-3 tw-font-semibold tw-text-gray-900"><?= htmlspecialchars($item->description) ?></td>
                                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-600"><?= htmlspecialchars($item->long_description) ?></td>
                                        <td class="tw-px-4 tw-py-3 tw-text-center">
                                            <input type="number" name="items[<?= $item->id ?>][qty]" 
                                                   value="1" min="1" class="form-control input-sm item-qty tw-text-center">
                                        </td>
                                        <td class="tw-px-4 tw-py-3 tw-text-right">
                                            <input type="number" name="items[<?= $item->id ?>][service_charge]" 
                                                   value="<?= $item->service_charge ?>" min="0" step="0.01" 
                                                   class="form-control input-sm item-service-charge tw-text-right">
                                        </td>
                                        <td class="tw-px-4 tw-py-3 tw-text-right tw-font-semibold tw-text-gray-900 rate"><?= number_format($item->rate, 2) ?></td>
                                        <td class="tw-px-4 tw-py-3 tw-text-right tw-font-bold tw-text-blue-600 amount">
                                            <?= number_format(($item->rate * $item->qty) + $item->service_charge, 2) ?>
                                        </td>
                                        <td class="tw-px-4 tw-py-3 tw-text-center">
                                            <button type="button" class="btn btn-danger btn-xs" onclick="removeInvoiceItem(this, <?= $item->id ?>)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="no_items_message">
                                    <td colspan="7" class="tw-px-4 tw-py-8 tw-text-center tw-text-gray-500 tw-italic">
                                        <i class="fa fa-search tw-mr-2"></i>No items added yet. Use the search bar above to add items.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Totals & Discount Section -->
        <div class="row tw-mt-6">
            <div class="col-md-6">
                <!-- Notes Section (Optional) -->
            </div>
            <div class="col-md-6">
                <div class="panel panel-default tw-shadow-sm">
                    <div class="panel-heading tw-bg-orange-50 tw-border-b tw-border-orange-200">
                        <h5 class="panel-title tw-text-orange-800 tw-font-semibold">
                            <i class="fa fa-calculator tw-mr-2"></i>Payment Summary
                        </h5>
                    </div>
                    <div class="panel-body">
                        <table class="table tw-mb-0">
                            <tbody>
                                <tr>
                                    <td class="tw-border-0 tw-py-2">
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-700">Subtotal:</span>
                                    </td>
                                    <td class="tw-border-0 tw-py-2 tw-text-right">
                                        <span class="tw-text-sm tw-font-semibold subtotal">₹0.00</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tw-border-0 tw-py-2" colspan="2">
                                        <div class="tw-bg-orange-50 tw-border tw-border-orange-200 tw-rounded tw-p-3">
                                            <label class="tw-block tw-text-xs tw-font-semibold tw-text-orange-800 tw-mb-2">DISCOUNT</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <select name="discount_type" class="form-control input-sm discount-type">
                                                        <option value="percent">Percentage (%)</option>
                                                        <option value="amount">Fixed Amount (₹)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="number" name="discount_value" class="form-control input-sm discount-value" 
                                                           value="0" min="0" step="0.01" placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tw-border-0 tw-py-2">
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-700">Discount Amount:</span>
                                    </td>
                                    <td class="tw-border-0 tw-py-2 tw-text-right">
                                        <span class="tw-text-sm tw-font-semibold tw-text-red-600 discount-total">-₹0.00</span>
                                    </td>
                                </tr>
                                <tr class="tw-border-t tw-border-gray-200">
                                    <td class="tw-py-3">
                                        <span class="tw-text-lg tw-font-bold tw-text-gray-900">Grand Total:</span>
                                    </td>
                                    <td class="tw-py-3 tw-text-right">
                                        <span class="tw-text-xl tw-font-bold tw-text-blue-600 total">₹0.00</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Action Bar -->
    <div class="panel-footer tw-bg-gray-50 tw-border-t tw-border-gray-200 tw-p-4">
        <div class="row">
            <div class="col-md-6">
                <div class="tw-text-sm tw-text-gray-600">
                    <i class="fa fa-info-circle tw-mr-1"></i> All fields marked with * are required
                </div>
            </div>
            <div class="col-md-6 text-right">
                <?php if (!isset($invoice)): ?>
                    <button class="btn btn-default btn-lg mright5 invoice-form-submit save-as-draft transaction-submit">
                        <i class="fa fa-save tw-mr-2"></i>Save as Draft
                    </button>
                <?php endif; ?>
                
                <div class="btn-group dropup">
                    <button type="button" class="btn btn-primary btn-lg invoice-form-submit transaction-submit">
                        <i class="fa fa-check tw-mr-2"></i><?= isset($invoice) ? 'Update Invoice' : 'Create Invoice' ?>
                    </button>
                    <button type="button" class="btn btn-primary btn-lg dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li>
                            <a href="#" class="invoice-form-submit save-and-send transaction-submit">
                                <i class="fa fa-paper-plane tw-mr-2"></i>Save & Send
                            </a>
                        </li>
                        <?php if (!isset($invoice)): ?>
                            <li>
                                <a href="#" class="invoice-form-submit save-and-record-payment transaction-submit">
                                    <i class="fa fa-credit-card tw-mr-2"></i>Save & Record Payment
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Affiliate Modal -->
<div class="modal fade" id="affiliateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header tw-bg-green-50">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title tw-text-green-800">
                    <i class="fa fa-plus-circle tw-mr-2"></i>Add New Affiliate
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Name <span class="text-danger">*</span></label>
                    <input type="text" id="modal_affiliate_name" class="form-control" placeholder="Enter affiliate name">
                </div>
                <div class="form-group">
                    <label class="control-label">Phone <span class="text-danger">*</span></label>
                    <input type="tel" id="modal_affiliate_phone" class="form-control" placeholder="Enter phone number">
                </div>
                <div class="form-group">
                    <label class="control-label">Location</label>
                    <input type="text" id="modal_affiliate_location" class="form-control" placeholder="Enter location">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times tw-mr-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="addNewAffiliate()">
                    <i class="fa fa-save tw-mr-2"></i>Save Affiliate
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.desktop-ui {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.search-results-dropdown {
    position: absolute; 
    background: white; 
    border: 1px solid #e2e8f0; 
    border-top: none;
    max-height: 250px; 
    overflow-y: auto; 
    z-index: 1050; 
    width: 100%;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border-radius: 0 0 0.375rem 0.375rem;
}

.search-result-item { 
    padding: 12px; 
    cursor: pointer; 
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.15s ease;
}

.search-result-item:hover { 
    background-color: #f8fafc; 
}

.search-result-item:last-child { 
    border-bottom: none; 
}

.hide { 
    display: none !important; 
}

.table th {
    border-top: none;
    font-weight: 600;
}

.form-control.input-sm {
    height: 32px;
    padding: 4px 8px;
    font-size: 13px;
}

.btn-sm {
    padding: 4px 12px;
    font-size: 12px;
}

.tw-shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Custom scrollbar for dropdown */
.search-results-dropdown::-webkit-scrollbar {
    width: 6px;
}

.search-results-dropdown::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.search-results-dropdown::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.search-results-dropdown::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<script>
(function() {
    'use strict';
    
    // Static data
    window.invoiceStaticData = {
        affiliates: [
            {id: 1, name: 'City Hospital', phone: '9876543220', location: 'Downtown'},
            {id: 2, name: 'Medicare Center', phone: '9876543221', location: 'Uptown'},
            {id: 3, name: 'Health Plus Clinic', phone: '9876543222', location: 'Suburb'},
        ],
        items: [
            {id: 1, name: 'Blood Test', description: 'Complete Blood Count (CBC)', price: 500},
            {id: 2, name: 'X-Ray', description: 'Chest X-Ray PA View', price: 800},
            {id: 3, name: 'MRI Scan', description: 'Brain MRI with Contrast', price: 5000},
            {id: 4, name: 'Ultrasound', description: 'Abdominal Ultrasound', price: 1200},
            {id: 5, name: 'ECG', description: '12 Lead Electrocardiogram', price: 300},
        ]
    };
    
    let invoiceItems = [];
    
    // Wait for jQuery to be available
    function initWhenReady() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initWhenReady, 100);
            return;
        }
        
        jQuery(document).ready(function($) {
            initializeInvoiceForm($);
            calculateTotals($);
        });
    }
    
    initWhenReady();
    
    function initializeInvoiceForm($) {
        // Affiliate search
        $('#affiliate_search').on('input', function() {
            handleAffiliateSearch($(this).val(), $);
        });
        
        // Item search
        $('#item_search').on('input', function() {
            handleItemSearch($(this).val(), $);
        });
        
        // Close dropdowns on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#affiliate_search, #affiliate_search_results').length) {
                $('#affiliate_search_results').hide();
            }
            if (!$(e.target).closest('#item_search, #item_search_results').length) {
                $('#item_search_results').hide();
            }
        });
        
        // Recalculate on changes
        $(document).on('input change', '.item-qty, .item-service-charge, .discount-type, .discount-value', function() {
            calculateTotals($);
        });
    }
    
    window.handleAffiliateSearch = function(searchTerm, $) {
        const resultsContainer = $('#affiliate_search_results');
        if (searchTerm.length < 2) {
            resultsContainer.hide();
            return;
        }
        
        const filtered = window.invoiceStaticData.affiliates.filter(a =>
            a.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            a.phone.includes(searchTerm)
        );
        
        let html = filtered.length ? 
            filtered.map(a => 
                `<div class="search-result-item" onclick="selectAffiliate(${a.id})">
                    <div class="bold">${a.name}</div>
                    <small>${a.location} • ${a.phone}</small>
                </div>`
            ).join('') :
            '<div class="search-result-item text-muted">No affiliates found</div>';
            
        resultsContainer.html(html).show();
    };
    
    window.handleItemSearch = function(searchTerm, $) {
        const resultsContainer = $('#item_search_results');
        if (searchTerm.length < 2) {
            resultsContainer.hide();
            return;
        }
        
        const filtered = window.invoiceStaticData.items.filter(item =>
            item.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            item.description.toLowerCase().includes(searchTerm.toLowerCase())
        );
        
        let html = filtered.length ? 
            filtered.map(item => 
                `<div class="search-result-item" onclick="addItemToInvoice(${item.id})">
                    <div class="bold">${item.name}</div>
                    <small>${item.description}</small>
                    <div class="text-success bold">₹${item.price}</div>
                </div>`
            ).join('') :
            '<div class="search-result-item text-muted">No items found</div>';
            
        resultsContainer.html(html).show();
    };
    
    window.addItemToInvoice = function(itemId) {
        const item = window.invoiceStaticData.items.find(i => i.id === itemId);
        if (!item) return;
        
        const existingIndex = invoiceItems.findIndex(i => i.id === itemId);
        if (existingIndex > -1) {
            invoiceItems[existingIndex].quantity += 1;
        } else {
            invoiceItems.push({
                id: item.id, name: item.name, description: item.description,
                price: item.price, quantity: 1, serviceCharge: 0
            });
            addInvoiceItemRow(item);
        }
        
        $('#item_search').val('').trigger('input');
        calculateTotals(jQuery);
    };
    
    window.addInvoiceItemRow = function(item) {
        const $tbody = $('#invoice_items_tbody');
        const $noItems = $('#no_items_message');
        
        if ($noItems.length) $noItems.remove();
        
        const rowId = `item_${item.id}`;
        const total = (item.price * item.quantity + item.serviceCharge).toFixed(2);
        
        $tbody.append(`
            <tr id="${rowId}" class="item" data-item-id="${item.id}">
                <td class="bold">${item.name}</td>
                <td>${item.description}</td>
                <td class="text-center">
                    <input type="number" name="items[${item.id}][qty]" value="${item.quantity}" 
                           min="1" class="form-control input-sm item-qty">
                </td>
                <td class="text-right rate">${item.price.toFixed(2)}</td>
                <td class="text-right">
                    <input type="number" name="items[${item.id}][service_charge]" value="${item.serviceCharge}" 
                           min="0" step="0.01" class="form-control input-sm item-service-charge">
                </td>
                <td class="text-right amount bold">${total}</td>
                <td class="text-center">
                    <a href="#" class="btn btn-danger btn-xs" onclick="removeInvoiceItem(this, ${item.id})">
                        <i class="fa fa-times"></i>
                    </a>
                </td>
            </tr>
        `);
    };
    
    window.removeInvoiceItem = function(btn, itemId) {
        $(btn).closest('tr').remove();
        invoiceItems = invoiceItems.filter(item => item.id !== itemId);
        
        if (invoiceItems.length === 0) {
            $('#invoice_items_tbody').html(`
                <tr id="no_items_message">
                    <td colspan="7" class="text-center text-muted">No items added yet. Search above to add items.</td>
                </tr>
            `);
        }
        calculateTotals(jQuery);
    };
    
    window.calculateTotals = function($) {
        let subtotal = 0;
        
        $('.item').each(function() {
            const $row = $(this);
            const qty = parseFloat($row.find('.item-qty').val()) || 0;
            const rate = parseFloat($row.find('.rate').text()) || 0;
            const serviceCharge = parseFloat($row.find('.item-service-charge').val()) || 0;
            const itemTotal = (qty * rate) + serviceCharge;
            
            subtotal += itemTotal;
            $row.find('.amount').text(itemTotal.toFixed(2));
        });
        
        const discountType = $('select[name="discount_type"]').val();
        const discountValue = parseFloat($('input[name="discount_value"]').val()) || 0;
        const discountAmount = discountType === 'percent' ? (subtotal * discountValue) / 100 : discountValue;
        const total = Math.max(0, subtotal - discountAmount);
        
        $('.subtotal').text('₹' + subtotal.toFixed(2));
        $('.discount-total').text('-₹' + discountAmount.toFixed(2));
        $('.total').text('₹' + total.toFixed(2));
    };
    
    window.selectAffiliate = function(id) {
        const affiliate = window.invoiceStaticData.affiliates.find(a => a.id === id);
        if (affiliate) {
            $('#affiliate_name').text(affiliate.name);
            $('#affiliate_phone').text(affiliate.phone);
            $('#affiliate_location').text(affiliate.location);
            $('#selected_affiliate').removeClass('hide');
            $('#affiliate_search').val('');
            $('#affiliate_search_results').hide();
        }
    };
    
    window.clearAffiliate = function() {
        $('#selected_affiliate').addClass('hide');
        $('#affiliate_name, #affiliate_phone, #affiliate_location').empty();
    };
    
    window.addNewAffiliate = function() {
        const name = $('#modal_affiliate_name').val();
        const phone = $('#modal_affiliate_phone').val();
        const location = $('#modal_affiliate_location').val();
        
        if (name && phone) {
            const newId = window.invoiceStaticData.affiliates.length + 1;
            window.invoiceStaticData.affiliates.push({id: newId, name, phone, location});
            $('#affiliateModal').modal('hide');
            
            // Clear modal
            $('#modal_affiliate_name, #modal_affiliate_phone, #modal_affiliate_location').val('');
            alert('Affiliate added successfully!');
        } else {
            alert('Name and Phone are required');
        }
    };
})();
</script>
