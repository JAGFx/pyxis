<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entry\Validator;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Domain\Entry\Validator\HasEnoughAmountAssignment;
use App\Domain\Entry\Validator\HasEnoughAmountAssignmentValidator;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasEnoughAmountAssignmentValidatorTest extends TestCase
{
    private HasEnoughAmountAssignmentValidator $validator;

    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new HasEnoughAmountAssignmentValidator();
        $this->context   = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testThrowsExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $invalidConstraint = $this->createMock(Constraint::class);
        $command           = self::createCommand();

        $this->validator->validate($command, $invalidConstraint);
    }

    public function testThrowsExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint   = new HasEnoughAmountAssignment();
        $invalidValue = new stdClass();

        $this->validator->validate($invalidValue, $constraint);
    }

    #[DataProvider('provideEarlyReturnCases')]
    public function testEarlyReturnCasesDoNotTriggerValidation(CreateOrUpdateEntryCommand $command): void
    {
        $constraint = new HasEnoughAmountAssignment();

        $this->context
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($command, $constraint);
    }

    public static function provideEarlyReturnCases(): Generator
    {
        yield 'Entry update (non-null originId) should skip validation' => [
            self::createCommand(123),
        ];

        yield 'Entry without assignment should skip validation' => [
            self::createCommand(assignment: null),
        ];

        yield 'Entry update with null assignment should skip validation' => [
            self::createCommand(456),
        ];

        yield 'Entry update with null amount should skip validation' => [
            self::createCommand(789),
        ];
    }

    private static function createAccount(): Account
    {
        return new Account()
            ->setName('Test Account');
    }

    private static function createAssignment(Account $account, float $amount = 500.0): Assignment
    {
        return new Assignment()
            ->setName('Test Assignment')
            ->setAmount($amount)
            ->setAccount($account);
    }

    private static function createCommand(
        ?int $originId = null,
        ?Assignment $assignment = null,
        ?float $amount = -100.0,
        ?Account $account = null,
    ): CreateOrUpdateEntryCommand {
        $account    = $account ?? self::createAccount();
        $assignment = $assignment ?? self::createAssignment($account);

        $command = new CreateOrUpdateEntryCommand(
            account: $account,
            name: 'Test Entry',
            amount: $amount,
            assignment: $assignment
        );

        if (null !== $originId) {
            $command->setOriginId($originId);
        }

        return $command;
    }
}
