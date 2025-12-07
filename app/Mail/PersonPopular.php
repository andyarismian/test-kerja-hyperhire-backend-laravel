<?php

namespace App\Mail;

use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PersonPopular extends Mailable
{
    use Queueable, SerializesModels;

    public $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    public function build()
    {
        return $this->subject('Person popular: ' . $this->person->name)
            ->view('emails.person_popular')
            ->with(['person' => $this->person]);
    }
}
