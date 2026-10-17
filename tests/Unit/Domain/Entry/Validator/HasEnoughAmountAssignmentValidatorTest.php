<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entry\Validator;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Domain\Entry\Validator\HasEnoughAmountAssignment;
use App\Domain\Entry\Validator\HasEnoughAmountAssignmentValidator;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class HasEnoughAmountAssignmentValidatorTest extends TestCase
{
    private HasEnoughAmountAssignmentValidator $validator;

    private ExecutionContextInterface $context;

    private EntityFinder $entityFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityFinder = $this->createStub(EntityFinder::class);
        $this->validator    = new HasEnoughAmountAssignmentValidator($this->entityFinder);
        $this->context      = $this->createMock(ExecutionContextInterface::class);
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

    #[DataProvider('provideNoViolationCases')]
    public function testNoViolationCases(CreateOrUpdateEntryCommand $command): void
    {
        $constraint = new HasEnoughAmountAssignment();

        $this->context
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($command, $constraint);
    }

    public static function provideNoViolationCases(): Generator
    {
        yield 'Entry without assignment' => [
            self::createCommand(assignment: null, withAssignment: false),
        ];

        yield 'Entry within the assignment balance' => [
            self::createCommand(),
        ];

        yield 'Entry update within the assignment balance' => [
            self::createCommand(123),
        ];
    }

    public function testUpdateReplacesTheStoredAmountInTheBalance(): void
    {
        $account    = self::createAccount();
        $assignment = self::createAssignment($account, 500.0);
        $stored     = new Entry()->setName('Stored')->setAmount(-400.0)->setAccount($account)->setAssignment($assignment);
        $assignment->getEntries()->add($stored);

        $this->entityFinder->method('findByIntIdentifier')->willReturn($stored);

        // Balance 100 once the -400 is spent: -450 fits, as the -400 is replaced and not added
        $this->context->expects(self::never())->method('buildViolation');

        $this->validator->validate(self::createCommand(42, $assignment, -450.0, $account), new HasEnoughAmountAssignment());
    }

    public function testUpdateExceedingTheAssignmentBalanceRaisesAViolation(): void
    {
        $account    = self::createAccount();
        $assignment = self::createAssignment($account, 500.0);
        $stored     = new Entry()->setName('Stored')->setAmount(-400.0)->setAccount($account)->setAssignment($assignment);
        $assignment->getEntries()->add($stored);

        $this->entityFinder->method('findByIntIdentifier')->willReturn($stored);

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->method('setParameter')->willReturnSelf();
        $builder->method('setInvalidValue')->willReturnSelf();
        $builder->method('atPath')->willReturnSelf();
        $builder->expects(self::once())->method('addViolation');

        $this->context->expects(self::once())->method('buildViolation')->willReturn($builder);

        $this->validator->validate(self::createCommand(42, $assignment, -600.0, $account), new HasEnoughAmountAssignment());
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
        bool $withAssignment = true,
    ): CreateOrUpdateEntryCommand {
        $account    = $account ?? self::createAccount();
        $assignment = $withAssignment ? ($assignment ?? self::createAssignment($account)) : null;

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
