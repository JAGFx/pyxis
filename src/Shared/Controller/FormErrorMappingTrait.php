<?php

namespace App\Shared\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait FormErrorMappingTrait
{
    private function mapBusinessErrorsToForm(ConstraintViolationListInterface $violations, FormInterface $form): void
    {
        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();

            if ($form->has($propertyPath)) {
                $form->get($propertyPath)->addError(new FormError((string) $violation->getMessage()));
            } else {
                $form->addError(new FormError((string) $violation->getMessage()));
            }
        }
    }
}
