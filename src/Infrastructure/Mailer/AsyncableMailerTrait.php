<?php

namespace App\Infrastructure\Mailer;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

trait AsyncableMailerTrait
{
    private function newAsyncMail(): TemplatedEmail
    {
        // @see https://symfony.com/doc/current/mailer.html#sending-messages-async
        return new TemplatedEmail()->from($this->author);
    }
}
