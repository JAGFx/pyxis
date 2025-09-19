<?php

declare(strict_types=1);

namespace App\Domain\Assignment\Controller\Back;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Form\AssignmentCreateOrUpdateType;
use App\Domain\Assignment\Manager\AssignmentManager;
use App\Domain\Assignment\Message\Command\AssignmentCreateOrUpdateCommand;
use App\Domain\Assignment\Message\Query\AssignmentSearchQuery;
use App\Shared\Controller\ControllerActionEnum;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assignments')]
class AssignmentController extends AbstractController
{
    public function __construct(
        private readonly AssignmentManager $assignmentManager,
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface $objectMapper,
    ) {
    }

    #[Route(name: 'back_assignment_list', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $searchQuery = new AssignmentSearchQuery()->setOrderBy('name');

        return $this->render('domain/assigment/index.html.twig', [
            'assignments' => $this->assignmentManager->getAssignments($searchQuery),
            'config'      => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::ASSIGNMENT),
        ]);
    }

    #[Route('/create', name: 'back_assignment_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleForm(ControllerActionEnum::CREATE, $request);
    }

    #[Route('/{id}/update', name: 'back_assignment_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Assignment $assignment, Request $request): Response
    {
        return $this->handleForm(ControllerActionEnum::EDIT, $request, $assignment);
    }

    private function handleForm(ControllerActionEnum $type, Request $request, ?Assignment $assignment = null): Response
    {
        $assigmentCommand = is_null($assignment)
            ? new AssignmentCreateOrUpdateCommand()
            : $this->objectMapper->map($assignment, AssignmentCreateOrUpdateCommand::class);

        $form = $this
            ->createForm(AssignmentCreateOrUpdateType::class, $assigmentCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (ControllerActionEnum::CREATE === $type) {
                $this->assignmentManager->create($assigmentCommand);
            } else {
                $assigmentCommand->setOrigin($assignment);
                $this->assignmentManager->update($assigmentCommand);
            }

            return $this->redirectToRoute('back_assignment_list');
        }

        return $this->render('domain/assigment/form.html.twig', [
            'form'       => $form,
            'assignment' => $assignment,
        ]);
    }
}
