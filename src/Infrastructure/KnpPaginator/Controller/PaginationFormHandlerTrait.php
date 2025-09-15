<?php

namespace App\Infrastructure\KnpPaginator\Controller;

use App\Infrastructure\KnpPaginator\DTO\PaginationInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

trait PaginationFormHandlerTrait
{
    public function handlePaginationForm(Request $request, string $formType, PaginationInterface $searchRequest): void
    {
        $this
            ->createForm($formType, $searchRequest, [
                'method'          => Request::METHOD_GET,
                'csrf_protection' => false,
            ])
            ->submit($request->query->all(), false);
    }

    abstract protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface;
}
