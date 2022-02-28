<?php

namespace NickDeKruijk\Shopwire\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('shopwire::mail.ordercreated')
            ->to(config('shopwire.owner_email.address'), config('shopwire.owner_email.name'))
            ->replyTo($this->order->customer['email'], $this->order->customer['firstname'] . ' ' . $this->order->customer['lastname'])
            ->from(config('shopwire.email_from.address'), config('shopwire.email_from.name'));
    }
}
