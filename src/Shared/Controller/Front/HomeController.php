<?php

namespace App\Shared\Controller\Front;

use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
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
        $searchTarget = MenuConfigurationEntityEnum::tryFrom($target)
            ?? throw new InvalidArgumentException(sprintf("Target '%s' is not supported", $target));

        $form = $this->createForm($searchTarget->getSearchFormType());

        return $this->renderTurboStream(
            $request,
            'shared/turbo/search_form.turbo.stream.html.twig',
            [
                'form'          => $form,
                'liveComponent' => $searchTarget->getSearchLiveComponent(),
            ]);
    }
}
