<?php

namespace App\Tests\Integration\Domain\Assignment\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Manager\AssignmentManager;
use App\Domain\Assignment\Message\Command\AssignmentCreateOrUpdateCommand;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class AssignmentManagerTest extends KernelTestCase
{
    private AssignmentManager $assignmentManager;
    private ObjectMapperInterface $objectMapper;

    protected function setUp(): void
    {
        self::bootKernel();
        $container               = static::getContainer();
        $this->assignmentManager = $container->get(AssignmentManager::class);
        $this->objectMapper      = $container->get(ObjectMapperInterface::class);
    }

    public function testCreateDoesNotThrowException(): void
    {
        // Given
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        $command = new AssignmentCreateOrUpdateCommand();
        $command->setName('Test Assignment');
        $command->setAccount($account);
        $command->setAmount(150.0);

        // When & Then - Should not throw any exception
        $this->assignmentManager->create($command);

        $this->expectNotToPerformAssertions();
    }

    public function testUpdateDoesNotThrowException(): void
    {
        // Given
        /** @var Assignment $existingAssignment */
        $existingAssignment = AssignmentFactory::new()->create([
            'name' => 'Original Assignment',
        ])->_real();

        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        $command = new AssignmentCreateOrUpdateCommand();
        $command->setName('Updated Assignment');
        $command->setAccount($account);
        $command->setAmount(250.0);
        $command->setOrigin($existingAssignment);

        // When & Then - Should not throw any exception
        $this->assignmentManager->update($command);

        $this->expectNotToPerformAssertions();
    }

    public function testObjectMapperMappingDoesNotThrowException(): void
    {
        // Given - Création d'une entité Assignment
        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create([
            'name' => 'Test Assignment for Mapping',
        ])->_real();

        // When & Then - ObjectMapper mapping should not throw any exception
        $this->objectMapper->map($assignment, AssignmentCreateOrUpdateCommand::class);

        $this->expectNotToPerformAssertions();
    }
}
