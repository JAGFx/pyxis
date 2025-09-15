<?php

declare(strict_types=1);

namespace App\Domain\Assignment\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Account\Request\AccountSearchRequest;
use App\Domain\Assignment\Request\AssignmentSearchRequest;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('name', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => AssignmentSearchRequest::class,
            'label_format'       => 'assignment.search.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
