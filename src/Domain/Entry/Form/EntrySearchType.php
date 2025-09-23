<?php

declare(strict_types=1);

namespace App\Domain\Entry\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Message\Query\FindBudgetsQuery;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\Entry\Message\Query\FindEntriesQuery;
use App\Infrastructure\KnpPaginator\Form\PaginationBuilder;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntrySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        PaginationBuilder::buildForm($builder);

        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('type', EnumType::class, [
                'class'        => EntryTypeEnum::class,
                'required'     => false,
                'placeholder'  => 'shared.default.placeholders.all',
                'choice_label' => 'label',
                'expanded'     => true,
            ])
            ->add('flags', ChoiceType::class, [
                'choices' => array_merge(
                    ['entry_flag_enum.form.options.UNFLAGGED' => FindEntriesQuery::WITHOUT_FLAG_VALUE],
                    array_combine(
                        array_map(fn (EntryFlagEnum $flag): string => $flag->label(), EntryFlagEnum::cases()),
                        EntryFlagEnum::cases()
                    )
                ),
                'required'    => false,
                'placeholder' => 'shared.default.placeholders.all',
                'multiple'    => true,
            ])
            ->add('account', EntityType::class, [
                'class'         => Account::class,
                'choice_label'  => 'name',
                'query_builder' => function (AccountRepository $repository): QueryBuilder {
                    $searchQuery = new FindAccountsQuery(true)->setOrderBy('name');

                    return $repository->getAccountsQueryBuilder($searchQuery);
                },
                'required'    => false,
                'placeholder' => 'shared.default.placeholders.all',
            ])
            ->add('budget', EntityType::class, [
                'class'         => Budget::class,
                'choice_label'  => 'name',
                'query_builder' => function (BudgetRepository $repository): QueryBuilder {
                    $searchQuery = new FindBudgetsQuery(enabled: true)->setOrderBy('name');

                    return $repository->getBudgetsQueryBuilder($searchQuery);
                },
                'required'    => false,
                'placeholder' => 'shared.default.placeholders.all',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => FindEntriesQuery::class,
            'label_format'       => 'entry.search.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
