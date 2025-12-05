<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Fetch affiliates for dropdown
$this->db->select("affiliate_code, CONCAT(firstname, ' ', lastname) AS full_name");
$this->db->from('tblaffiliate_users');
$this->db->where('status', 1);
$this->db->where('approval', 1);
$affiliates = $this->db->get()->result_array();
?>

<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading">
    <?= _l('client_add_edit_profile'); ?>
</h4>
<?php } ?>

<div class="row">
    <?= form_open($this->uri->uri_string(), ['class' => 'client-form', 'autocomplete' => 'off']); ?>
    <div class="additional"></div>
    <div class="col-md-12">
        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
                <ul class="nav nav-tabs customer-profile-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="<?= !$this->input->get('tab') ? 'active' : ''; ?>">
                        <a href="#contact_info" aria-controls="contact_info" role="tab" data-toggle="tab">
                            <?= _l('customer_profile_details'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="tab-content mtop15">
            <div role="tabpanel" class="tab-pane<?= !$this->input->get('tab') ? ' active' : ''; ?>" id="contact_info">
                <div class="row">
                    <div class="col-md-12<?= isset($client) && (!is_empty_customer_company($client->userid) && total_rows(db_prefix() . 'contacts', ['userid' => $client->userid, 'is_primary' => 1]) > 0) ? '' : ' hide'; ?>" id="client-show-primary-contact-wrapper">
                        <div class="checkbox checkbox-info mbot20 no-mtop">
                            <input type="checkbox" name="show_primary_contact" <?= isset($client) && $client->show_primary_contact == 1 ? 'checked' : ''; ?> value="1" id="show_primary_contact">
                            <label for="show_primary_contact"><?= _l('show_primary_contact', _l('invoices') . ', ' . _l('estimates') . ', ' . _l('payments') . ', ' . _l('credit_notes')); ?></label>
                        </div>
                    </div>
                    <div class="col-md-<?= !isset($client) ? 12 : 8; ?>">
                        <?php $value = (isset($client) ? $client->company : ''); ?>
                        <?php $attrs = (isset($client) ? [] : ['autofocus' => true]); ?>
                        <?= render_input('company', 'Patient Name', $value, 'text', $attrs); ?>

                        <!-- Affiliate Dropdown -->
                        <div class="form-group select-placeholder">
                            <label for="affiliate_code"><?= _l('Ref.By'); ?></label>
                            <select id="affiliate_code" name="affiliate_code" class="form-control selectpicker" data-live-search="true" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                <option value=""><?= _l('select_an_affiliate'); ?></option>
                                <?php foreach ($affiliates as $affiliate): ?>
                                    <option value="<?= htmlspecialchars($affiliate['affiliate_code']); ?>" <?= (isset($client) && $client->affiliate_code == $affiliate['affiliate_code']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($affiliate['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php /* Hidden Fields
                        <?= render_input('phonenumber', 'client_phonenumber', $client->phonenumber ?? ''); ?>
                        <?= render_input('vat', 'client_vat_number', $client->vat ?? ''); ?>
                        <?= render_input('website', 'client_website', $client->website ?? ''); ?>
                        <?= render_select('groups_in[]', $groups, ['id', 'name'], 'customer_groups', $selected ?? [], ['multiple' => true]); ?>
                        <?= render_select('default_currency', $currencies, ['id', 'name'], 'invoice_add_edit_currency', $client->default_currency ?? ''); ?>
                        <?= render_select('default_language', $this->app->get_available_languages(), [], 'localization_default_language', $client->default_language ?? ''); ?>
                        <?= render_textarea('address', 'client_address', $client->address ?? ''); ?>
                        <?= render_input('city', 'client_city', $client->city ?? ''); ?>
                        <?= render_input('state', 'client_state', $client->state ?? ''); ?>
                        <?= render_input('zip', 'client_postal_code', $client->zip ?? ''); ?>
                        <?= render_select('country', $countries, ['country_id', ['short_name']], 'clients_country', $client->country ?? ''); ?>
                        */ ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= form_close(); ?>
</div>

<script>
$(function() {
    $('#affiliate_code').selectpicker();

    appValidateForm($('.client-form'), {
        company: { required: false },
        affiliate_code: {
            required: true,
            messages: {
                required: 'Please select an affiliate'
            }
        }
    });

    // Optional: Hide extra tab headers if still present in template
    $('[href="#billing_and_shipping"]').closest('li').hide();
    $('[href="#customer_admins"]').closest('li').hide();
    $('[href="#custom_fields"]').closest('li').hide();
});
</script>
