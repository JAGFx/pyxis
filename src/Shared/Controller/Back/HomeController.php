<?php

namespace App\Shared\Controller\Back;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Form\TransferType;
use App\Shared\Message\Command\Transfer\TransferCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    #[Route('/', name: 'home', methods: Request::METHOD_GET)]
    public function home(): Response
    {
        return $this->render('shared/home.html.twig');
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route('/transfer', name: 'home_transfer', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function transfer(Request $request): Response
    {
        $form = $this
            ->createForm(TransferType::class, $transferCommand = new TransferCommand())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->messageBus->dispatch($transferCommand);

            return $this->redirectToRoute('home');
        }

        return $this->render('shared/transfer/_form.html.twig', [
            'form' => $form,
        ]);
    }
}
