<?php

declare(strict_types=1);

namespace App\Domain\Assignment\Controller\Front;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Form\AssignmentSearchType;
use App\Domain\Assignment\Message\Command\RemoveAssignment\RemoveAssignmentCommand;
use App\Domain\Assignment\Message\Query\FindAssignments\FindAssignmentsQuery;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\Cqs\Bus\MessageBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/assignments')]
class AssignmentController extends AbstractController
{
    use TurboResponseTrait;

    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/{id}/remove', name: 'front_assignment_remove', requirements: ['id' => Requirement::DIGITS], methods: Request::METHOD_GET)]
    public function remove(Assignment $assignment, Request $request): Response
    {
        /** @var int $assignmentId */
        $assignmentId = $assignment->getId();

        $this->messageBus->dispatch(new RemoveAssignmentCommand($assignmentId));

        return $this->renderTurboStream($request, 'domain/assigment/turbo/remove.turbo.stream.html.twig', [
            'assignmentId' => $assignmentId,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/search', name: 'front_assignment_search', methods: [Request::METHOD_POST])]
    public function search(Request $request): Response
    {
        $searchQuery = new FindAssignmentsQuery()->setOrderBy('name');

        $this->createForm(AssignmentSearchType::class, $searchQuery)
            ->handleRequest($request);

        $assignments = $this->messageBus->dispatch($searchQuery);

        return $this->renderTurboStream(
            $request,
            'domain/assigment/turbo/search.turbo.stream.html.twig',
            [
                'assignments' => $assignments,
            ]);
    }
}
