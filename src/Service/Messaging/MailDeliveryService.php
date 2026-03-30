<?php

namespace App\Service\Messaging;

use App\Service\Presentation\TemplatePathResolver;
use App\Service\Support\ParameterBagService;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class MailDeliveryService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly ParameterBagService $parameterBagService,
    ) {
    }

    public function sendTemplate(
        string $template,
        string $kid,
        array $options,
        Address|string $to,
        string $subject,
        Address|string|null $reply = null,
        Address|string|null $from = null,
    ): bool {
        $tpl = $this->templatePathResolver->resolveEmail($template, $kid);
        $html = $this->twig->render($tpl, $options);
        $msgTxt = strip_tags($html);

        $from ??= (string) $this->parameterBagService->get('dfx_mail');
        $reply ??= $from;

        $message = (new Email())
            ->subject($subject)
            ->from($from)
            ->to($to)
            ->replyTo($reply)
            ->html($html)
            ->text($msgTxt);

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface) {
            return false;
        }

        return true;
    }
}
