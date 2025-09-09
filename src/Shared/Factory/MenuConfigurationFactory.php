<?php

namespace App\Shared\Factory;

use App\Shared\ValueObject\MenuConfiguration;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class MenuConfigurationFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createFor(MenuConfigurationEntityEnum $target): MenuConfiguration
    {
        return new MenuConfiguration(
            createUrl: $this->generateCreateUrl($target),
            searchFormUrl: $this->generateSearchFormUrl($target)
        );
    }

    private function generateCreateUrl(MenuConfigurationEntityEnum $target): ?string
    {
        return match ($target) {
            MenuConfigurationEntityEnum::ACCOUNT    => $this->urlGenerator->generate('back_account_new'),
            MenuConfigurationEntityEnum::ASSIGNMENT => $this->urlGenerator->generate('back_assignment_create'),
            default                                 => null,
        };
    }

    private function generateSearchFormUrl(MenuConfigurationEntityEnum $target): ?string
    {
        return match ($target) {
            MenuConfigurationEntityEnum::ACCOUNT,
            MenuConfigurationEntityEnum::ASSIGNMENT => $this->urlGenerator->generate('front_home_search_form', [
                'target' => $target->value,
            ]),
            default => null,
        };
    }
}
