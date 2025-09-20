<?php

namespace Contactum;

interface Contactum_Mailer_Contract {
    public function send( $to, $subject, $body, $headers );
}
