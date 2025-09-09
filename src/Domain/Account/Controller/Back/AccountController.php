<?php

namespace App\Domain\Account\Controller\Back;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Entity\Account;
use App\Domain\Account\Form\AccountType;
use App\Domain\Account\Manager\AccountManager;
use App\Shared\Controller\ControllerActionEnum;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/accounts')]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
    ) {
    }

    #[Route(name: 'back_account_account_list', methods: Request::METHOD_GET)]
    public function index(): Response
    {
        $accountSearchCommand = new AccountSearchCommand()->setOrderBy('name');
        $accounts             = $this->accountManager->getAccounts($accountSearchCommand);

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

    #[Route('/{id}', name: 'back_account_edit', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Request $request, Account $account): Response
    {
        return $this->handleForm(ControllerActionEnum::EDIT, $request, $account);
    }

    private function handleForm(ControllerActionEnum $action, Request $request, ?Account $account = null): Response
    {
        $account ??= new Account()->setName('');

        $form = $this
            ->createForm(AccountType::class, $account)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (ControllerActionEnum::CREATE === $action) {
                $this->accountManager->create($account);
            } else {
                $this->accountManager->update();
            }

            return $this->redirectToRoute('back_account_account_list');
        }

        return $this->render('domain/account/form.html.twig', [
            'form'    => $form,
            'account' => $account,
        ]);
    }
}
