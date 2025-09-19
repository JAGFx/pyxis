<?php

namespace App\Domain\Account\Controller\Back;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Form\AccountCreateOrUpdateType;
use App\Domain\Account\Manager\AccountManager;
use App\Domain\Account\Message\Command\AccountCreateOrUpdateCommand;
use App\Domain\Account\Message\Query\AccountSearchQuery;
use App\Shared\Controller\ControllerActionEnum;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/accounts')]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface $objectMapper,
    ) {
    }

    #[Route(name: 'back_account_list', methods: Request::METHOD_GET)]
    public function index(): Response
    {
        $searchQuery = new AccountSearchQuery()->setOrderBy('name');
        $accounts    = $this->accountManager->getAccounts($searchQuery);

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
            ? new AccountCreateOrUpdateCommand()
            : $this->objectMapper->map($account, AccountCreateOrUpdateCommand::class);

        $form = $this
            ->createForm(AccountCreateOrUpdateType::class, $accountCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (ControllerActionEnum::CREATE === $action) {
                $this->accountManager->create($accountCommand);
            } else {
                $accountCommand->setOrigin($account);
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
