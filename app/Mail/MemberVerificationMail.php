<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $code;

    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Kode Verifikasi Pendaftaran Member')
            ->view('emails.member_verification')
            ->with(['user' => $this->user, 'code' => $this->code]);
    }
}
