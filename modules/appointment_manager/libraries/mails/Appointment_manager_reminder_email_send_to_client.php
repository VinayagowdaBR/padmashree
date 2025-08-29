<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Appointment_manager_reminder_email_send_to_client extends App_mail_template
{
    protected $for = 'customer';

    protected $email;

    protected $client_id;

    protected $event;

    public $slug = 'appointment-manager-appointment-reminder-to-client';

    public $rel_type = 'appointment_manager';

    public function __construct($event, $email)
    {
        parent::__construct(); 
        $this->event = $event;
        $this->email      = $email;
        $this->client_id  = $event->client;
    }

    public function build()
    {
        $this->set_merge_fields('appointment_manager_merge_fields', $this->event);
        $this->set_merge_fields('client_merge_fields', $this->client_id);
        $this->to($this->email)
            ->set_rel_id($this->client_id);
    }
}