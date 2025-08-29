<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Appointment_manager_reminder_email_send_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $staffid;

    protected $staff;

    protected $event;

    public $slug = 'appointment-manager-appointment-reminder-to-staff';

    public $rel_type = 'appointment_manager';

    public function __construct($event, $staff)
    {
        parent::__construct();

        $this->staff = $staff;
        $this->event = $event;
    }

    public function build()
    {
        $this->set_merge_fields('appointment_manager_merge_fields', $this->event);
        $this->set_merge_fields('staff_merge_fields', $this->staff->staffid);
        $this->to($this->staff->email)
            ->set_rel_id($this->staff->staffid);
    }
}