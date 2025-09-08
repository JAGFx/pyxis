<?php

namespace App\Infrastructure\Turbo\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Turbo\TurboBundle;

trait TurboResponseTrait
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function renderTurboStream(Request $request, string $view, array $parameters = [], ?Response $response = null): Response
    {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->render($view, $parameters, $response);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    abstract protected function render(string $view, array $parameters = [], ?Response $response = null): Response;
}
