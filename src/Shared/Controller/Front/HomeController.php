<?php

namespace App\Shared\Controller\Front;

use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\ValueObject\SearchFormTargetEnum;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    use TurboResponseTrait;

    #[Route('/search-form', name: 'front_home_search_form', methods: Request::METHOD_GET)]
    public function searchForm(
        Request $request,
        #[MapQueryParameter] string $target,
    ): Response {
        $searchTarget = SearchFormTargetEnum::tryFrom($target)
            ?? throw new InvalidArgumentException(sprintf("Target '%s' is not supported", $target));

        $form = $this->createForm($searchTarget->getFormType());

        return $this->renderTurboStream(
            $request,
            'shared/turbo/_stream_search_form.html.twig',
            [
                'form'          => $form,
                'liveComponent' => $searchTarget->getLiveComponent(),
            ]);
    }
}
