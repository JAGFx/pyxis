<?php

namespace App\Module\Exporter\Domain\Artifact\Mailer;

use App\Infrastructure\Mailer\AsyncableMailerTrait;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\RequestExportCommandInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArtifactMailerDispatcher
{
    use AsyncableMailerTrait;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        #[Autowire(env: 'MAILER_AUTHOR')]
        private readonly string $author,
    ) {
    }

    /**
     * @param array<string, string> $artifacts
     *
     * @throws TransportExceptionInterface
     */
    public function requestExportFinished(RequestExportCommandInterface $command, array $artifacts): void
    {
        $email = $this->newAsyncMail()
            ->to('email@me.com')
            ->subject(
                $this->translator->trans('exporters.shared.request_export_finished.subject', domain: 'mailer')
            )
            ->context([
                'exportName' => $command->getTranslationKey(),
                'artifacts'  => $artifacts,
            ])
            ->htmlTemplate('module/exporter/domain/artifact/email/artifact_attached_to_export_request.html.twig');

        $this->mailer->send($email);
    }
}
