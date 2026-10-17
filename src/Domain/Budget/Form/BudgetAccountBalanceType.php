<?php

namespace App\Domain\Budget\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\ValueObject\BudgetCashFlowByAccountValueObject;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Command\ApplyBudgetAccountBalance\ApplyBudgetAccountBalanceCommand;
use App\Shared\Message\Query\GetBudgetCashFlowsByAccount\GetBudgetCashFlowsByAccountQuery;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BudgetAccountBalanceType extends AbstractType
{
    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $form = $event->getForm();
                /** @var ApplyBudgetAccountBalanceCommand $budgetAccountBalanceCommand */
                $budgetAccountBalanceCommand = $event->getData();

                // Same cash flow definition as the balancing handler: only accounts with something to balance
                /** @var BudgetCashFlowByAccountValueObject[] $cashFlows */
                $cashFlows = $this->messageBus->dispatch(
                    new GetBudgetCashFlowsByAccountQuery($budgetAccountBalanceCommand->getBudget()->getId())
                );

                $form
                    ->add('account', EntityType::class, [
                        'class'        => Account::class,
                        'choice_label' => 'name',
                        'choices'      => array_map(
                            static fn (BudgetCashFlowByAccountValueObject $cashFlow): Account => $cashFlow->getAccount(),
                            $cashFlows
                        ),
                    ])
                ;
            })
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => ApplyBudgetAccountBalanceCommand::class,
            'label_format'       => 'budget.account_balance.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
