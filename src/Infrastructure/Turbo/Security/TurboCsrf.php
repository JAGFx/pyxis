<?php

declare(strict_types=1);

namespace App\Infrastructure\Turbo\Security;

/**
 * Turbo sends the `csrf-token` meta content in this header on every non-GET request it handles
 * (`data-turbo-method` links and Turbo forms). Usage on a controller action:
 *
 * #[IsCsrfTokenValid(TurboCsrf::TOKEN_ID, tokenKey: TurboCsrf::HEADER, tokenSource: IsCsrfTokenValid::SOURCE_HEADER)]
 */
final class TurboCsrf
{
    public const string TOKEN_ID = 'turbo-action';

    public const string HEADER = 'X-CSRF-Token';
}
