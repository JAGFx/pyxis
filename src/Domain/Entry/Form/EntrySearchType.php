<?php

declare(strict_types=1);

namespace App\Domain\Entry\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Account\Request\AccountSearchRequest;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Budget\Request\BudgetSearchRequest;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\Entry\Request\EntrySearchRequest;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntrySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
            ->add('account', EntityType::class, [
                'class'         => Account::class,
                'choice_label'  => 'name',
                'query_builder' => function (AccountRepository $repository): QueryBuilder {
                    $searchRequest = new AccountSearchRequest(true)->setOrderBy('name');

                    return $repository->getAccountsQueryBuilder($searchRequest);
                },
                'required'    => false,
                'placeholder' => 'shared.default.placeholders.all',
            ])
            ->add('budget', EntityType::class, [
                'class'         => Budget::class,
                'choice_label'  => 'name',
                'query_builder' => function (BudgetRepository $repository): QueryBuilder {
                    $searchRequest = new BudgetSearchRequest(enabled: true)->setOrderBy('name');

                    return $repository->getBudgetsQueryBuilder($searchRequest);
                },
                'required'    => false,
                'placeholder' => 'shared.default.placeholders.all',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => EntrySearchRequest::class,
            'label_format'       => 'entry.search.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
