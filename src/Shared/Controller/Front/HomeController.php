<?php

namespace App\Shared\Controller\Front;

use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\MenuConfiguration\Enum\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    use TurboResponseTrait;

    /**
     * A missing or unsupported target is answered with a 404 by the query parameter resolver.
     */
    #[Route('/search-form', name: 'front_home_search_form', methods: Request::METHOD_GET)]
    public function searchForm(
        Request $request,
        #[MapQueryParameter('target')] MenuConfigurationEntityEnum $searchTarget,
    ): Response {
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
