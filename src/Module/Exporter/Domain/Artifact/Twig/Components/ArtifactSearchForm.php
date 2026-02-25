<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Artifact\Twig\Components;

use App\Module\Exporter\Domain\Artifact\Form\ArtifactSearchType;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'module/exporter/domain/artifact/components/ArtifactSearchForm.html.twig')]
class ArtifactSearchForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ArtifactSearchType::class, new FindArtifactsQuery(), [
            'action' => $this->generateUrl('front_exporter_search_artifacts'),
        ]);
    }
}
