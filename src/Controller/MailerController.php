<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerController extends AbstractController
{
    private $mailer;
    private $to = 'lilosti@aarhus.dk';
    private $prefix = 'dev';

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send email to mail-adress defined in env, hvis subject prefix defined in env.
     */
    public function sendEmail(string $subject, string $message): void
    {
        $email = (new Email())
            ->from('asanayesplanintegration@musikhusaarhus.dk')
            ->to($this->to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($this->prefix.' '.$subject)
            ->text($message)
            ->html($message);

        $this->mailer->send($email);
    }
}
