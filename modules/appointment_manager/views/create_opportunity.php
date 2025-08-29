<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade modal-opportunity opportunity-modal-<?php echo $id; ?>" tabindex="-1" role="dialog"
    aria-labelledby="myModalLabelOpportunity">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/projects/project', ['id' => 'form-opportunity']); ?>
            <div class="modal-header">
                <button type="button" class="close close-modal-opportunity" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabelOpportunity"><i class="fa-regular fa-circle-question" data-toggle="tooltip"
                        title="<?php echo _l('test'); ?>" data-placement="bottom"></i>
                    <?php echo 'opportunity'; ?></h4>
            </div>
            <div class="modal-body">
                <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                    <div class="horizontal-tabs">
                        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#tab_project" aria-controls="tab_project" role="tab" data-toggle="tab">
                                    <?php echo _l('project'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#tab_settings" aria-controls="tab_settings" role="tab"
                                    data-toggle="tab">
                                    <?php echo _l('project_settings'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content tw-mt-3">
                    <div role="tabpanel" class="tab-pane active" id="tab_project">


                        <?php
                        $disable_type_edit = '';
                        if (isset($project)) {
                            if ($project->billing_type != 1) {
                                if (total_rows(db_prefix() . 'tasks', ['rel_id' => $project->id, 'rel_type' => 'project', 'billable' => 1, 'billed' => 1]) > 0) {
                                    $disable_type_edit = 'disabled';
                                }
                            }
                        }
                        ?>
                        <?php $value = (isset($project) ? $project->name : ''); ?>
                        <?php echo render_input('name', 'project_name', $value); ?>
                        <div class="form-group select-placeholder">
                            <label for="clientid"
                                class="control-label"><?php echo _l('project_customer'); ?></label>
                            <select id="clientid" name="clientid" data-live-search="true" data-width="100%"
                                class="ajax-search"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <?php $selected = (isset($appointment) ? $appointment->client : '');
                                if ($selected == '') {
                                    $selected = (isset($customer_id) ? $customer_id : '');
                                }
                                if ($selected != '') {
                                    $rel_data = get_relation_data('customer', $selected);
                                    $rel_val  = get_relation_values($rel_data, 'customer');
                                    echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox" <?php if ((isset($project) && $project->progress_from_tasks == 1) || !isset($project)) {
                                                            echo 'checked';
                                                        } ?> name="progress_from_tasks" id="progress_from_tasks">
                                <label
                                    for="progress_from_tasks"><?php echo _l('calculate_progress_through_tasks'); ?></label>
                            </div>
                        </div>
                        <?php
                        if (isset($project) && $project->progress_from_tasks == 1) {
                            $value = $this->projects_model->calc_progress_by_tasks($project->id);
                        } elseif (isset($project) && $project->progress_from_tasks == 0) {
                            $value = $project->progress;
                        } else {
                            $value = 0;
                        }
                        ?>
                        <label for=""><?php echo _l('project_progress'); ?> <span
                                class="label_progress"><?php echo e($value); ?>%</span></label>
                        <?php echo form_hidden('progress', $value); ?>
                        <div class="project_progress_slider project_progress_slider_horizontal mbot15"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group select-placeholder">
                                    <label for="billing_type"><?php echo _l('project_billing_type'); ?></label>
                                    <div class="clearfix"></div>
                                    <select name="billing_type" class="selectpicker" id="billing_type"
                                        data-width="100%" <?php echo $disable_type_edit; ?>
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <option value="1" <?php if (isset($project) && $project->billing_type == 1 || !isset($project) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 1) {
                                                                echo 'selected';
                                                            } ?>><?php echo _l('project_billing_type_fixed_cost'); ?></option>
                                        <option value="2" <?php if (isset($project) && $project->billing_type == 2 || !isset($project) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 2) {
                                                                echo 'selected';
                                                            } ?>><?php echo _l('project_billing_type_project_hours'); ?></option>
                                        <option value="3"
                                            data-subtext="<?php echo _l('project_billing_type_project_task_hours_hourly_rate'); ?>" <?php if (isset($project) && $project->billing_type == 3 || !isset($project) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 3) {
                                                                                                                                        echo 'selected';
                                                                                                                                    } ?>><?php echo _l('project_billing_type_project_task_hours'); ?></option>
                                    </select>
                                    <?php if ($disable_type_edit != '') {
                                        echo '<p class="text-danger">' . _l('cant_change_billing_type_billed_tasks_found') . '</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group select-placeholder">
                                    <label for="status"><?php echo _l('project_status'); ?></label>
                                    <div class="clearfix"></div>
                                    <select name="status" id="status" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <?php foreach ($statuses as $status) { ?>
                                            <option value="<?php echo e($status['id']); ?>" <?php if (!isset($project) && $status['id'] == 2 || (isset($project) && $project->status == $status['id'])) {
                                                                                                echo 'selected';
                                                                                            } ?>><?php echo e($status['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php if (isset($project) && project_has_recurring_tasks($project->id)) { ?>
                            <div class="alert alert-warning recurring-tasks-notice hide"></div>
                        <?php } ?>
                        <?php if (is_email_template_active('project-finished-to-customer')) { ?>
                            <div class="form-group project_marked_as_finished hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="project_marked_as_finished_email_to_contacts"
                                        id="project_marked_as_finished_email_to_contacts">
                                    <label
                                        for="project_marked_as_finished_email_to_contacts"><?php echo _l('project_marked_as_finished_to_contacts'); ?></label>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (isset($project)) { ?>
                            <div class="form-group mark_all_tasks_as_completed hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="mark_all_tasks_as_completed"
                                        id="mark_all_tasks_as_completed">
                                    <label
                                        for="mark_all_tasks_as_completed"><?php echo _l('project_mark_all_tasks_as_completed'); ?></label>
                                </div>
                            </div>
                            <div class="notify_project_members_status_change hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="notify_project_members_status_change"
                                        id="notify_project_members_status_change">
                                    <label
                                        for="notify_project_members_status_change"><?php echo _l('notify_project_members_status_change'); ?></label>
                                </div>
                                <hr />
                            </div>
                        <?php } ?>
                        <?php
                        $input_field_hide_class_total_cost = '';
                        if (!isset($project)) {
                            if ($auto_select_billing_type && $auto_select_billing_type->billing_type != 1 || !$auto_select_billing_type) {
                                $input_field_hide_class_total_cost = 'hide';
                            }
                        } elseif (isset($project) && $project->billing_type != 1) {
                            $input_field_hide_class_total_cost = 'hide';
                        }
                        ?>
                        <div id="project_cost" class="<?php echo e($input_field_hide_class_total_cost); ?>">
                            <?php $value = (isset($project) ? $project->project_cost : ''); ?>
                            <?php echo render_input('project_cost', 'project_total_cost', $value, 'number'); ?>
                        </div>
                        <?php
                        $input_field_hide_class_rate_per_hour = '';
                        if (!isset($project)) {
                            if ($auto_select_billing_type && $auto_select_billing_type->billing_type != 2 || !$auto_select_billing_type) {
                                $input_field_hide_class_rate_per_hour = 'hide';
                            }
                        } elseif (isset($project) && $project->billing_type != 2) {
                            $input_field_hide_class_rate_per_hour = 'hide';
                        }
                        ?>
                        <div id="project_rate_per_hour"
                            class="<?php echo e($input_field_hide_class_rate_per_hour); ?>">
                            <?php $value = (isset($project) ? $project->project_rate_per_hour : ''); ?>
                            <?php
                            $input_disable = [];
                            if ($disable_type_edit != '') {
                                $input_disable['disabled'] = true;
                            }
                            ?>
                            <?php echo render_input('project_rate_per_hour', 'project_rate_per_hour', $value, 'number', $input_disable); ?>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_input('estimated_hours', 'estimated_hours', isset($project) ? $project->estimated_hours : '', 'number'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                $selected = [];
                                if (isset($project_members)) {
                                    foreach ($project_members as $member) {
                                        array_push($selected, $member['staff_id']);
                                    }
                                } else {
                                    array_push($selected, get_staff_user_id());
                                }
                                echo render_select('project_members[]', $staff, ['staffid', ['firstname', 'lastname']], 'project_members', $selected, ['multiple' => true, 'data-actions-box' => true], [], '', '', false);
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($project) ? _d($project->start_date) : _d(date('Y-m-d'))); ?>
                                <?php echo render_date_input('start_date', 'project_start_date', $value); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($project) ? _d($project->deadline) : ''); ?>
                                <?php echo render_date_input('deadline', 'project_deadline', $value); ?>
                            </div>
                        </div>
                        <?php if (isset($project) && $project->date_finished != null && $project->status == 4) { ?>
                            <?php echo render_datetime_input('date_finished', 'project_completed_date', _dt($project->date_finished)); ?>
                        <?php } ?>
                        <div class="form-group">
                            <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                                <?php echo _l('tags'); ?></label>
                            <input type="text" class="tagsinput" id="tags" name="tags"
                                value="<?php echo (isset($project) ? prep_tags_input(get_tags_in($project->id, 'project')) : ''); ?>"
                                data-role="tagsinput">
                        </div>
                        <?php $rel_id_custom_field = (isset($project) ? $project->id : false); ?>
                        <?php echo render_custom_fields('projects', $rel_id_custom_field); ?>
                        <p class="bold"><?php echo _l('project_description'); ?></p>
                        <?php $contents = '';
                        if (isset($project)) {
                            $contents           = $project->description;
                        } ?>
                        <?php echo render_textarea('description', '', $contents, [], [], '', 'tinymce'); ?>

                        <?php if (isset($estimate)) { ?>
                            <hr class="hr-panel-separator" />
                            <h5 class="font-medium"><?php echo _l('estimate_items_convert_to_tasks') ?></h5>
                            <input type="hidden" name="estimate_id" value="<?php echo $estimate->id ?>">
                            <div class="row">
                                <?php foreach ($estimate->items as $item) { ?>
                                    <div class="col-md-8 border-right">
                                        <div class="checkbox mbot15">
                                            <input type="checkbox" name="items[]" value="<?php echo $item['id'] ?>"
                                                checked id="item-<?php echo $item['id'] ?>">
                                            <label for="item-<?php echo $item['id'] ?>">
                                                <h5 class="no-mbot no-mtop text-uppercase">
                                                    <?php echo $item['description'] ?>
                                                </h5>
                                                <span class="text-muted"><?php echo $item['long_description'] ?></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div data-toggle="tooltip"
                                            title="<?php echo _l('task_single_assignees_select_title'); ?>">
                                            <?php echo render_select('items_assignee[]', $staff, ['staffid', ['firstname', 'lastname']], '', get_staff_user_id(), ['data-actions-box' => true], [], '', '', false); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <hr class="hr-panel-separator" />

                        <?php if (is_email_template_active('assigned-to-project')) { ?>
                            <div class="checkbox checkbox-primary tw-mb-0">
                                <input type="checkbox" name="send_created_email" id="send_created_email">
                                <label
                                    for="send_created_email"><?php echo _l('project_send_created_email'); ?></label>
                            </div>
                        <?php } ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="tab_settings">
                        <div id="project-settings-area">
                            <div class="form-group select-placeholder">
                                <label for="contact_notification" class="control-label">
                                    <span class="text-danger">*</span>
                                    <?php echo _l('projects_send_contact_notification'); ?>
                                </label>
                                <select name="contact_notification" id="contact_notification"
                                    class="form-control selectpicker"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                    required>
                                    <?php
                                    $options = [
                                        ['id' => 1, 'name' => _l('project_send_all_contacts_with_notifications_enabled')],
                                        ['id' => 2, 'name' => _l('project_send_specific_contacts_with_notification')],
                                        ['id' => 0, 'name' => _l('project_do_not_send_contacts_notifications')],
                                    ];
                                    foreach ($options as $option) { ?>
                                        <option value="<?php echo e($option['id']); ?>" <?php if ((isset($project) && $project->contact_notification == $option['id'])) {
                                                                                            echo ' selected';
                                                                                        } ?>><?php echo e($option['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group select-placeholder <?php echo (isset($project) && $project->contact_notification == 2) ? '' : 'hide' ?>"
                                id="notify_contacts_wrapper">
                                <label for="notify_contacts" class="control-label"><span
                                        class="text-danger">*</span>
                                    <?php echo _l('project_contacts_to_notify') ?></label>
                                <select name="notify_contacts[]" data-id="notify_contacts" id="notify_contacts"
                                    class="ajax-search" data-width="100%" data-live-search="true"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                    multiple>
                                    <?php
                                    $notify_contact_ids = isset($project) ? unserialize($project->notify_contacts) : [];
                                    foreach ($notify_contact_ids as $contact_id) {
                                        $rel_data = get_relation_data('contact', $contact_id);
                                        $rel_val  = get_relation_values($rel_data, 'contact');
                                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php foreach ($settings as $setting) {
                                $checked = ' checked';
                                if (isset($project)) {
                                    if ($project->settings->{$setting} == 0) {
                                        $checked = '';
                                    }
                                } else {
                                    foreach ($last_project_settings as $last_setting) {
                                        if ($setting == $last_setting['name']) {
                                            if ($last_setting['value'] == 0 || $last_setting['name'] == 'hide_tasks_on_main_tasks_table') {
                                                $checked = '';
                                            }
                                        }
                                    }
                                    if (count($last_project_settings) == 0 && $setting == 'hide_tasks_on_main_tasks_table') {
                                        $checked = '';
                                    }
                                } ?>
                                <?php if ($setting != 'available_features') { ?>
                                    <div class="checkbox">
                                        <input type="checkbox" name="settings[<?php echo e($setting); ?>]"
                                            <?php echo e($checked); ?> id="<?php echo e($setting); ?>">
                                        <label for="<?php echo e($setting); ?>">
                                            <?php if ($setting == 'hide_tasks_on_main_tasks_table') { ?>
                                                <?php echo _l('hide_tasks_on_main_tasks_table'); ?>
                                            <?php } else { ?>
                                                <?php echo e(_l('project_allow_client_to', _l('project_setting_' . $setting))); ?>
                                            <?php } ?>
                                        </label>
                                    </div>
                                <?php } else { ?>
                                    <div class="form-group mtop15 select-placeholder project-available-features">
                                        <label for="available_features"><?php echo _l('visible_tabs'); ?></label>
                                        <select name="settings[<?php echo e($setting); ?>][]" id="<?php echo e($setting); ?>"
                                            multiple="true" class="selectpicker" id="available_features"
                                            data-width="100%" data-actions-box="true" data-hide-disabled="true">
                                            <?php foreach (get_project_tabs_admin() as $tab) {
                                                $selected = '';
                                                if (isset($tab['collapse'])) { ?>
                                                    <optgroup label="<?php echo e($tab['name']); ?>">
                                                        <?php foreach ($tab['children'] as $tab_dropdown) {
                                                            $selected = '';
                                                            if (isset($project) && (
                                                                (isset($project->settings->available_features[$tab_dropdown['slug']])
                                                                    && $project->settings->available_features[$tab_dropdown['slug']] == 1)
                                                                || !isset($project->settings->available_features[$tab_dropdown['slug']])
                                                            )) {
                                                                $selected = ' selected';
                                                            } elseif (!isset($project) && count($last_project_settings) > 0) {
                                                                foreach ($last_project_settings as $last_project_setting) {
                                                                    if ($last_project_setting['name'] == $setting) {
                                                                        if (
                                                                            isset($last_project_setting['value'][$tab_dropdown['slug']])
                                                                            && $last_project_setting['value'][$tab_dropdown['slug']] == 1
                                                                        ) {
                                                                            $selected = ' selected';
                                                                        }
                                                                    }
                                                                }
                                                            } elseif (!isset($project)) {
                                                                $selected = ' selected';
                                                            } ?>
                                                            <option value="<?php echo e($tab_dropdown['slug']); ?>"
                                                                <?php echo e($selected); ?><?php if (isset($tab_dropdown['linked_to_customer_option']) && is_array($tab_dropdown['linked_to_customer_option']) && count($tab_dropdown['linked_to_customer_option']) > 0) { ?>
                                                                data-linked-customer-option="<?php echo implode(',', $tab_dropdown['linked_to_customer_option']); ?>"
                                                                <?php } ?>><?php echo e($tab_dropdown['name']); ?></option>
                                                        <?php
                                                        } ?>
                                                    </optgroup>
                                                <?php } else {
                                                    if (isset($project) && (
                                                        (isset($project->settings->available_features[$tab['slug']])
                                                            && $project->settings->available_features[$tab['slug']] == 1)
                                                        || !isset($project->settings->available_features[$tab['slug']])
                                                    )) {
                                                        $selected = ' selected';
                                                    } elseif (!isset($project) && count($last_project_settings) > 0) {
                                                        foreach ($last_project_settings as $last_project_setting) {
                                                            if ($last_project_setting['name'] == $setting) {
                                                                if (
                                                                    isset($last_project_setting['value'][$tab['slug']])
                                                                    && $last_project_setting['value'][$tab['slug']] == 1
                                                                ) {
                                                                    $selected = ' selected';
                                                                }
                                                            }
                                                        }
                                                    } elseif (!isset($project)) {
                                                        $selected = ' selected';
                                                    } ?>
                                                    <option value="<?php echo e($tab['slug']); ?>" <?php if ($tab['slug'] == 'project_overview') {
                                                                                                        echo ' disabled selected';
                                                                                                    } ?> <?php echo e($selected); ?>
                                                        <?php if (isset($tab['linked_to_customer_option']) && is_array($tab['linked_to_customer_option']) && count($tab['linked_to_customer_option']) > 0) { ?>
                                                        data-linked-customer-option="<?php echo implode(',', $tab['linked_to_customer_option']); ?>"
                                                        <?php } ?>>
                                                        <?php echo e($tab['name']); ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                <?php } ?>
                                <hr class="tw-my-3 -tw-mx-8" />
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default close-modal-opportunity"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>