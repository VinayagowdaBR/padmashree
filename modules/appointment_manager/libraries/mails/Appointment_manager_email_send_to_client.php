<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Appointment_manager_email_send_to_client extends App_mail_template
{
    protected $for = 'contact';

    protected $contact;
    protected $event;

    public $slug = 'appointment-manager-appointment-status-change-to-client';

    public $rel_type = 'appointment_manager';

    public function __construct($event, $contact)
    {
        parent::__construct();
        $this->event = $event;
        $this->contact = $contact;
    }

    public function build()
    {
        $this->set_merge_fields('appointment_manager_merge_fields', $this->event);
        $this->set_merge_fields('client_merge_fields', $this->event->client);
        $this->to($this->contact->email);
    }
}
