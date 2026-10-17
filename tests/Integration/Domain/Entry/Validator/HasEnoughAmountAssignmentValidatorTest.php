<?php

namespace App\Tests\Integration\Domain\Entry\Validator;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Domain\Entry\Validator\HasEnoughAmountAssignment;
use App\Domain\Entry\Validator\HasEnoughAmountAssignmentValidator;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Zenstruck\Foundry\Test\Factories;

class HasEnoughAmountAssignmentValidatorTest extends ConstraintValidatorTestCase
{
    use Factories;

    protected function createValidator(): HasEnoughAmountAssignmentValidator
    {
        return new HasEnoughAmountAssignmentValidator($this->createStub(EntityFinder::class));
    }

    #[DataProvider('provideValidCases')]
    public function testValidCases(callable $commandFactory): void
    {
        $command    = $commandFactory();
        $constraint = new HasEnoughAmountAssignment();

        $this->validator->validate($command, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('provideInvalidCases')]
    public function testInvalidCases(callable $commandFactory, string $assignmentAmount, int $entryAmount): void
    {
        $command    = $commandFactory();
        $constraint = new HasEnoughAmountAssignment();

        $this->validator->validate($command, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('%assignmentAmount%', $assignmentAmount)
            ->setInvalidValue($entryAmount)
            ->atPath('property.path.amount')
            ->assertRaised();
    }

    public static function provideValidCases(): Generator
    {
        yield 'assignment has enough amount for negative entry' => [
            fn () => self::createCommandWithAssignment(assignmentAmount: 100000, entryAmount: -50000),
        ];

        yield 'assignment has exactly the same amount as negative entry' => [
            fn () => self::createCommandWithAssignment(assignmentAmount: 50000, entryAmount: -50000),
        ];

        yield 'positive entry amount should pass' => [
            fn () => self::createCommandWithAssignment(assignmentAmount: 10000, entryAmount: 20000),
        ];

        yield 'zero entry amount should pass' => [
            fn () => self::createCommandWithAssignment(assignmentAmount: 10000, entryAmount: 0),
        ];
    }

    public static function provideInvalidCases(): Generator
    {
        yield 'assignment does not have enough amount' => [
            fn () => self::createCommandWithAssignment(assignmentAmount: 10000, entryAmount: -20000),
            '100,00', // assignment balance
            -20000, // entry amount
        ];

        yield 'assignment has zero amount but entry is negative' => [
            fn () => self::createCommandWithAssignment(assignmentAmount: 0, entryAmount: -5000),
            '0,00', // assignment balance
            -5000, // entry amount
        ];

        yield 'small insufficient amount' => [
            fn () => self::createCommandWithAssignment(assignmentAmount: 9999, entryAmount: -10000),
            '99,99', // assignment balance
            -10000, // entry amount
        ];
    }

    private static function createCommandWithAssignment(int $assignmentAmount, int $entryAmount): CreateOrUpdateEntryCommand
    {
        /** @var Account $account */
        $account = AccountFactory::new()
            ->withoutPersisting()
            ->create()
            ->_real();

        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()
            ->withoutPersisting()
            ->create([
                'amount'  => $assignmentAmount,
                'account' => $account,
            ])
            ->_real();

        return new CreateOrUpdateEntryCommand(
            account: $account,
            name: 'Test Entry',
            amount: $entryAmount,
            assignment: $assignment
        );
    }
}
