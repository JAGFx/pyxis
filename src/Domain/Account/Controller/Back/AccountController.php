<?php

namespace App\Domain\Account\Controller\Back;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Form\AccountCreateOrUpdateType;
use App\Domain\Account\Message\Command\CreateOrUpdateAccount\CreateOrUpdateAccountCommand;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Infrastructure\Cqs\Bus\SymfonyMessageBus;
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

#[Route('/accounts')]
class AccountController extends AbstractController
{
    use FormErrorMappingTrait;

    public function __construct(
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface $objectMapper,
        private readonly SymfonyMessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        name: 'back_account_list',
        methods: Request::METHOD_GET
    )]
    public function index(): Response
    {
        $searchQuery = new FindAccountsQuery()->setOrderBy('name');
        $accounts    = $this->messageBus->dispatch($searchQuery);

        return $this->render('domain/account/index.html.twig', [
            'accounts' => $accounts,
            'config'   => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::ACCOUNT),
        ]);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/create',
        name: 'back_account_new',
        methods: [Request::METHOD_POST, Request::METHOD_GET]
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
        name: 'back_account_edit',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function edit(Request $request, Account $account): Response
    {
        return $this->handleForm($request, $account);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    private function handleForm(Request $request, ?Account $account = null): Response
    {
        $accountCommand = is_null($account)
            ? new CreateOrUpdateAccountCommand()
            : $this->objectMapper->map($account, CreateOrUpdateAccountCommand::class);

        $form = $this
            ->createForm(AccountCreateOrUpdateType::class, $accountCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!is_null($account)) {
                    $accountCommand->setOriginId($account->getId());
                }

                $this->messageBus->dispatch($accountCommand);
            } catch (ValidationFailedException $exception) {
                $this->mapBusinessErrorsToForm($exception->getViolations(), $form);
            }

            return $this->redirectToRoute('back_account_list');
        }

        return $this->render('domain/account/form.html.twig', [
            'form'    => $form,
            'account' => $account,
        ]);
    }
}
