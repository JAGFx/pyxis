<?php

declare(strict_types=1);

namespace App\Domain\Assignment\Controller\Back;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Form\AssignmentCreateOrUpdateType;
use App\Domain\Assignment\Message\Command\CreateOrUpdateAssignment\CreateOrUpdateAssignmentCommand;
use App\Domain\Assignment\Message\Query\FindAssignments\FindAssignmentsQuery;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Controller\FormErrorMappingTrait;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Throwable;

#[Route('/assignments')]
class AssignmentController extends AbstractController
{
    use FormErrorMappingTrait;

    public function __construct(
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface    $objectMapper,
        private readonly MessageBus               $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        name: 'back_assignment_list',
        methods: [Request::METHOD_GET]
    )]
    public function index(): Response
    {
        $searchQuery = new FindAssignmentsQuery()->setOrderBy('name');
        $assignments = $this->messageBus->dispatch($searchQuery);

        return $this->render('domain/assigment/index.html.twig', [
            'assignments' => $assignments,
            'config'      => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::ASSIGNMENT),
        ]);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/create',
        name: 'back_assignment_create',
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function create(Request $request): Response
    {
        return $this->handleForm($request);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/{id}/update',
        name: 'back_assignment_edit',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function edit(Assignment $assignment, Request $request): Response
    {
        return $this->handleForm($request, $assignment);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    private function handleForm(Request $request, ?Assignment $assignment = null): Response
    {
        $assigmentCommand = is_null($assignment)
            ? new CreateOrUpdateAssignmentCommand()
            : $this->objectMapper->map($assignment, CreateOrUpdateAssignmentCommand::class);

        $form = $this
            ->createForm(AssignmentCreateOrUpdateType::class, $assigmentCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!is_null($assignment)) {
                    $assigmentCommand->setOriginId($assignment->getId());
                }

                $this->messageBus->dispatch($assigmentCommand);

                return $this->redirectToRoute('back_assignment_list');
            } catch (ValidationFailedException $exception) {
                $this->mapBusinessErrorsToForm($exception->getViolations(), $form);
            }
        }

        return $this->render('domain/assigment/form.html.twig', [
            'form'       => $form,
            'assignment' => $assignment,
        ]);
    }
}
