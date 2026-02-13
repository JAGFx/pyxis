<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Doctrine\Service;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityFinderTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    private function generateEntityFinder(array $onlyMethod = []): EntityFinder|MockObject
    {
        return $this->getMockBuilder(EntityFinder::class)
            ->onlyMethods($onlyMethod)
            ->setConstructorArgs([
                $this->entityManager,
            ])
            ->getMock();
    }

    /**
     * @throws ReflectionException
     */
    public function testFindByIntIdentifierOrFailThrowsLogicExceptionWhenIdIsNull(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('An identifier is required. Null given.');

        $this->generateEntityFinder()->findByIntIdentifierOrFail(Account::class, null);
    }

    /**
     * @throws ReflectionException
     */
    public function testFindByIntIdentifierOrFailThrowsNotFoundHttpExceptionWhenEntityNotFound(): void
    {
        $id = 999;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage("Account not found with id $id");

        $entityFinder = $this->generateEntityFinder(['findByIntIdentifier']);
        $entityFinder->expects(self::once())
            ->method('findByIntIdentifier')
            ->willReturn(null);

        $entityFinder->findByIntIdentifierOrFail(Account::class, $id);
    }

    /**
     * @throws ReflectionException
     */
    public function testFindByUuidIdentifierOrFailThrowsLogicExceptionWhenUuidIsNull(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('An identifier is required. Null given.');

        $this->generateEntityFinder()->findByUuidIdentifierOrFail(Account::class, null);
    }

    /**
     * @throws ReflectionException
     */
    public function testFindByUuidIdentifierOrFailThrowsNotFoundHttpExceptionWhenEntityNotFound(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(sprintf('Account not found with uuid %s', $uuid));

        $entityFinder = $this->generateEntityFinder(['findByUuidIdentifier']);
        $entityFinder->expects(self::once())
            ->method('findByUuidIdentifier')
            ->willReturn(null);

        $entityFinder->findByUuidIdentifierOrFail(Account::class, $uuid);
    }
}
