<?php

namespace App\Domain\PeriodicEntry\Controller\Back;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Form\PeriodicEntryCreateOrUpdateType;
use App\Domain\PeriodicEntry\Manager\PeriodicEntryManager;
use App\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntryCommand;
use App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntriesQuery;
use App\Shared\Controller\ControllerActionEnum;
use App\Shared\Controller\FormErrorMappingTrait;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\Validation\ValidationGroupEnum;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/periodic_entries')]
class PeriodicEntryController extends AbstractController
{
    use FormErrorMappingTrait;

    public function __construct(
        private readonly PeriodicEntryManager $periodicEntryManager,
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface $objectMapper,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route(name: 'back_periodic_entry_list', methods: Request::METHOD_GET)]
    public function list(): Response
    {
        $searchQuery = new FindPeriodicEntriesQuery()->setOrderBy('name');

        return $this->render('domain/periodic_entry/index.html.twig', [
            'periodicEntries' => $this->periodicEntryManager->getPeriodicEntries($searchQuery),
            'config'          => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::PERIODIC_ENTRY),
        ]);
    }

    #[Route('/create', 'back_periodic_entry_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleRequest(ControllerActionEnum::CREATE, $request);
    }

    #[Route('/{id}/update', 'back_periodic_entry_edit', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(PeriodicEntry $periodicEntry, Request $request): Response
    {
        return $this->handleRequest(ControllerActionEnum::EDIT, $request, $periodicEntry);
    }

    private function handleRequest(ControllerActionEnum $type, Request $request, ?PeriodicEntry $periodicEntry = null): Response
    {
        $periodicEntryCommand = is_null($periodicEntry)
            ? new CreateOrUpdatePeriodicEntryCommand()
            : $this->objectMapper->map($periodicEntry, CreateOrUpdatePeriodicEntryCommand::class);

        $form = $this
            ->createForm(PeriodicEntryCreateOrUpdateType::class, $periodicEntryCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $violations = $this->validator->validate($periodicEntryCommand, groups: [ValidationGroupEnum::Business->value]);

            if (0 === $violations->count()) {
                if (ControllerActionEnum::CREATE === $type) {
                    $this->periodicEntryManager->create($periodicEntryCommand);
                } else {
                    $periodicEntryCommand->setOrigin($periodicEntry);
                    $this->periodicEntryManager->update($periodicEntryCommand);
                }

                return $this->redirectToRoute('back_periodic_entry_list');
            }

            $this->mapBusinessErrorsToForm($violations, $form);
        }

        return $this->render('domain/periodic_entry/form.html.twig', [
            'form'          => $form,
            'periodicEntry' => $periodicEntry,
        ]);
    }
}
