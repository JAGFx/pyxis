<?php

namespace App\Shared\Controller\Back;

use App\Shared\DTO\Transfer;
use App\Shared\Form\TransferType;
use App\Shared\Operator\HomeOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly HomeOperator $homeOperator,
    ) {
    }

    #[Route('/', name: 'home', methods: Request::METHOD_GET)]
    public function home(): Response
    {
        return $this->render('shared/home.html.twig');
    }

    #[Route('/transfer', name: 'home_transfer', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function transfer(Request $request): Response
    {
        $form = $this
            ->createForm(TransferType::class, $transfer = new Transfer())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->homeOperator->transfer($transfer);

            return $this->redirectToRoute('home');
        }

        return $this->render('shared/transfer/_form.html.twig', [
            'form' => $form,
        ]);
    }
}
