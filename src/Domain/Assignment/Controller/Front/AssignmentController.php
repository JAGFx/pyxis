<?php

declare(strict_types=1);

namespace App\Domain\Assignment\Controller\Front;

use App\Domain\Assignment\DTO\AssignmentSearchCommand;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Form\AssignmentSearchType;
use App\Domain\Assignment\Manager\AssignmentManager;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assignments')]
class AssignmentController extends AbstractController
{
    use TurboResponseTrait;

    public function __construct(
        private readonly AssignmentManager $assignmentManager,
    ) {
    }

    #[Route('/{id}/remove', name: 'front_assignment_remove', methods: Request::METHOD_GET)]
    public function remove(Assignment $assignment, Request $request): Response
    {
        $assignmentId = $assignment->getId();
        $this->assignmentManager->remove($assignment);

        return $this->renderTurboStream($request, 'domain/assigment/turbo/remove.turbo.stream.html.twig', [
            'assignmentId' => $assignmentId,
        ]);
    }

    #[Route('/search', name: 'front_assigment_search', methods: [Request::METHOD_POST])]
    public function search(Request $request): Response
    {
        $assignmentSearchCommand = new AssignmentSearchCommand()->setOrderBy('name');

        $this->createForm(AssignmentSearchType::class, $assignmentSearchCommand)
            ->handleRequest($request);

        $assignments = $this->assignmentManager->getAssignments($assignmentSearchCommand);

        return $this->renderTurboStream(
            $request,
            'domain/assigment/turbo/search.turbo.stream.html.twig',
            [
                'assignments' => $assignments,
            ]);
    }
}
