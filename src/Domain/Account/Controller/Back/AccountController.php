<?php

namespace App\Domain\Account\Controller\Back;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Form\AccountCreateOrUpdateType;
use App\Domain\Account\Manager\AccountManager;
use App\Domain\Account\Message\Command\CreateOrUpdateAccountCommand;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Shared\Controller\ControllerActionEnum;
use App\Shared\Cqs\Bus\MessageBus;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/accounts')]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface $objectMapper,
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route(name: 'back_account_list', methods: Request::METHOD_GET)]
    public function index(): Response
    {
        $searchQuery = new FindAccountsQuery()->setOrderBy('name');
        $accounts    = $this->messageBus->dispatch($searchQuery);

        return $this->render('domain/account/index.html.twig', [
            'accounts' => $accounts,
            'config'   => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::ACCOUNT),
        ]);
    }

    #[Route('/create', name: 'back_account_new', methods: [Request::METHOD_POST, Request::METHOD_GET])]
    public function create(Request $request): Response
    {
        return $this->handleForm(ControllerActionEnum::CREATE, $request);
    }

    #[Route('/{id}/update', name: 'back_account_edit', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Request $request, Account $account): Response
    {
        return $this->handleForm(ControllerActionEnum::EDIT, $request, $account);
    }

    private function handleForm(ControllerActionEnum $action, Request $request, ?Account $account = null): Response
    {
        $accountCommand = is_null($account)
            ? new CreateOrUpdateAccountCommand()
            : $this->objectMapper->map($account, CreateOrUpdateAccountCommand::class);

        $form = $this
            ->createForm(AccountCreateOrUpdateType::class, $accountCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: Refactor: Use is_null($account) to determinate the action
            if (ControllerActionEnum::CREATE === $action) {
                $this->accountManager->create($accountCommand);
            } else {
                /** @var int $accountId */
                $accountId = $account?->getId();
                $accountCommand->setOriginId($accountId);
                $this->accountManager->update($accountCommand);
            }

            return $this->redirectToRoute('back_account_list');
        }

        return $this->render('domain/account/form.html.twig', [
            'form'    => $form,
            'account' => $account,
        ]);
    }
}
