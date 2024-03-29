<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailerController extends AbstractController
{
    private $mailer;
    private $options;

    public function __construct(array $mailerOptions, MailerInterface $mailer)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($mailerOptions);
        $this->mailer = $mailer;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'mail_to',
            'mail_prefix',
            'mail_from',
            ]);
    }

     /**
      * Send email to mail-adress defined in env, hvis subject prefix defined in env.
      */
     public function sendEmail(string $subject, string $message): void
     {
         $email = (new Email())
         ->from($this->options['mail_from'])
         ->to($this->options['mail_to'])
         ->subject($this->options['mail_prefix'].' '.$subject)
         ->text($message)
         ->html($message);

         $this->mailer->send($email);
     }
}
