<?php

declare(strict_types=1);

namespace App\Domain\Assignment\Controller\Back;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Form\AssignmentType;
use App\Domain\Assignment\Manager\AssignmentManager;
use App\Domain\Assignment\Model\AssignmentSearchCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assignments')]
class AssignmentController extends AbstractController
{
    public const string HANDLE_FORM_CREATE = 'create';
    public const string HANDLE_FORM_UPDATE = 'update';

    public function __construct(
        private readonly AssignmentManager $assignmentManager,
    ) {
    }

    #[Route(name: 'back_assignment_list', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $assignmentSearchCommand = new AssignmentSearchCommand()->setOrderBy('name');

        return $this->render('domain/assigment/index.html.twig', [
            'assignments' => $this->assignmentManager->getAssignments($assignmentSearchCommand),
        ]);
    }

    #[Route('/create', name: 'back_assignment_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleForm(self::HANDLE_FORM_CREATE, $request, new Assignment());
    }

    #[Route('/{id}/update', name: 'back_assignment_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Assignment $assignment, Request $request): Response
    {
        return $this->handleForm(self::HANDLE_FORM_UPDATE, $request, $assignment);
    }

    private function handleForm(string $type, Request $request, Assignment $assignment): Response
    {
        $form = $this
            ->createForm(AssignmentType::class, $assignment)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (self::HANDLE_FORM_CREATE === $type) {
                $this->assignmentManager->create($assignment);
            } else {
                $this->assignmentManager->update();
            }

            return $this->redirectToRoute('back_assignment_list');
        }

        return $this->render('domain/assigment/form.html.twig', [
            'form' => $form,
        ]);
    }
}
