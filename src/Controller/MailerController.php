<?php

namespace App\Controller;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

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

   public function configureOptions(OptionsResolver $resolver):void
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
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($this->options['mail_prefix'].' '.$subject)
            ->text($message)
            ->html($message);

        $this->mailer->send($email);

    }
}