<?php

namespace App\Shared\MenuConfiguration\Factory;

use App\Shared\MenuConfiguration\Enum\MenuConfigurationEntityEnum;
use App\Shared\MenuConfiguration\ValueObject\MenuConfiguration;
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

    private function generateCreateUrl(MenuConfigurationEntityEnum $target): string
    {
        return match ($target) {
            MenuConfigurationEntityEnum::ACCOUNT        => $this->urlGenerator->generate('back_account_new'),
            MenuConfigurationEntityEnum::ASSIGNMENT     => $this->urlGenerator->generate('back_assignment_create'),
            MenuConfigurationEntityEnum::BUDGET         => $this->urlGenerator->generate('back_budget_create'),
            MenuConfigurationEntityEnum::PERIODIC_ENTRY => $this->urlGenerator->generate('back_periodic_entry_create'),
            MenuConfigurationEntityEnum::ENTRY          => $this->urlGenerator->generate('back_entry_create'),
        };
    }

    private function generateSearchFormUrl(MenuConfigurationEntityEnum $target): string
    {
        return match ($target) {
            MenuConfigurationEntityEnum::ACCOUNT,
            MenuConfigurationEntityEnum::ASSIGNMENT,
            MenuConfigurationEntityEnum::BUDGET,
            MenuConfigurationEntityEnum::PERIODIC_ENTRY,
            MenuConfigurationEntityEnum::ENTRY => $this->urlGenerator->generate('front_home_search_form', [
                'target' => $target->value,
            ]),
        };
    }
}
