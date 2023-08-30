<?php

namespace App\Http\Email;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\Factory as Queue;

class NullMail implements Mailable
{
    public function build()
    {
        return $this;
    }

    public function send($mailer)
    {
        return true;
    }

    public function to($address, $name = null, $override = false)
    {
        return $this;
    }

    public function cc($address, $name = null, $override = false)
    {
        return $this;
    }

    public function bcc($address, $name = null, $override = false)
    {
        return $this;
    }

    public function replyTo($address, $name = null)
    {
        return $this;
    }

    public function subject($subject)
    {
        return $this;
    }

    public function priority($level)
    {
        return $this;
    }

    public function attach($file, array $options = [])
    {
        return $this;
    }

    public function attachData($data, $name, array $options = [])
    {
        return $this;
    }

    public function with($key, $value = null)
    {
        return $this;
    }

    public function queue(Queue $queue)
    {
        // TODO: Implement queue() method.
    }

    public function later($delay, Queue $queue)
    {
        // TODO: Implement later() method.
    }

    public function locale($locale)
    {
        // TODO: Implement locale() method.
    }

    public function mailer($mailer)
    {
        // TODO: Implement mailer() method.
    }
}
