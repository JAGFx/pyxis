<?php

namespace App\Tests\Integration\Shared;

use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    use Factories;
    use ResetDatabase;
}
