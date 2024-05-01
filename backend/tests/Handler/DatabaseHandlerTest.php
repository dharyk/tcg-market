<?php

namespace TcgMarket\Tests\Handler;

use Aura\Sql\ExtendedPdo;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use TcgMarket\Exception\DatabaseQueryFailureException;
use TcgMarket\Handler\DatabaseHandler;

class DatabaseHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $pdo;

    public function testGetPdo(): void
    {
        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );

        $this->assertInstanceOf(ExtendedPdo::class, $handler->getPdo());
    }

    public function testPing(): void
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->rowCount()
            ->shouldBeCalled()
            ->willReturn(1);

        $this->pdo
            ->prepareWithValues(
                Argument::exact('SELECT 1'),
                Argument::exact([])
            )
            ->shouldBeCalled()
            ->willReturn(
                $statement->reveal()
            );

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $handler->ping();
    }

    public function testTransaction(): void
    {
        $this->pdo
            ->beginTransaction()
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $handler->transaction();
    }

    public function testCommit(): void
    {
        $this->pdo
            ->commit()
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $handler->commit();
    }

    public function testRollback(): void
    {
        $this->pdo
            ->rollBack()
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $handler->rollback();
    }

    public function testQuote(): void
    {
        $this->pdo
            ->quote(
                Argument::any(),
                Argument::type('int')
            )
            ->shouldBeCalled()
            ->willReturn('test');

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $handler->quote('test', PDO::PARAM_STR);
    }

    public function testPrepare(): void
    {
        $query = 'SELECT `field` FROM `table` WHERE `id` = :id';
        $values = ['id' => 1];

        $statement = $this->prophesize(PDOStatement::class);

        $this->pdo
            ->prepareWithValues(
                Argument::exact($query),
                Argument::exact($values)
            )
            ->shouldBeCalled()
            ->willReturn(
                $statement->reveal()
            );

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $this->assertInstanceOf(
            PDOStatement::class,
            $handler->prepare($query, $values)
        );
    }

    public function testExecuteSuccess(): void
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->rowCount()
            ->shouldBeCalled()
            ->willReturn(1);

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $handler->execute($statement->reveal());
    }

    public function testExecuteFails(): void
    {
        $errorCode = 9;
        $errorMessage = 'test error message';

        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute()
            ->shouldBeCalled()
            ->willReturn(false);
        $statement
            ->errorInfo()
            ->shouldBeCalled()
            ->willReturn(['', $errorCode, $errorMessage]);

        $this->expectException(DatabaseQueryFailureException::class);
        $this->expectExceptionCode($errorCode);
        $this->expectExceptionMessage($errorMessage);

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $handler->execute($statement->reveal());
    }

    public function testRun(): void
    {
        $query = 'SELECT `field` FROM `table` WHERE `id` = :id';
        $values = ['id' => 1];

        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->rowCount()
            ->shouldBeCalled()
            ->willReturn(1);

        $this->pdo
            ->prepareWithValues(
                Argument::exact($query),
                Argument::exact($values)
            )
            ->shouldBeCalled()
            ->willReturn(
                $statement->reveal()
            );

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $handler->run($query, $values);
    }

    public function testQueryOne(): void
    {
        $row = [
            'id' => 1,
            'name' => 'test',
        ];

        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->rowCount()
            ->shouldBeCalled()
            ->willReturn(1);
        $statement
            ->closeCursor()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->fetch(
                Argument::exact(PDO::FETCH_ASSOC)
            )
            ->shouldBeCalled()
            ->willReturn($row);

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $result = $handler->queryOne($statement->reveal());

        $this->assertEquals($row, $result);
    }

    public function testQueryAssoc(): void
    {
        $rows = [
            [
                'id' => 1,
                'name' => 'test1',
            ],
            [
                'id' => 2,
                'name' => 'test2',
            ],
        ];

        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->rowCount()
            ->shouldBeCalled()
            ->willReturn(2);
        $statement
            ->closeCursor()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->fetchAll(
                Argument::exact(PDO::FETCH_ASSOC)
            )
            ->shouldBeCalled()
            ->willReturn($rows);

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $result = $handler->queryAssoc($statement->reveal());

        $this->assertEquals($rows, $result);
    }

    public function testQueryObject(): void
    {
        $row = [
            'id' => 1,
            'name' => 'test1',
        ];

        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->rowCount()
            ->shouldBeCalled()
            ->willReturn(2);
        $statement
            ->closeCursor()
            ->shouldBeCalled()
            ->willReturn(true);
        $statement
            ->fetch(
                Argument::exact(PDO::FETCH_ASSOC)
            )
            ->shouldBeCalled()
            ->willReturn($row, null);

        $handler = new DatabaseHandler(
            $this->pdo->reveal()
        );
        $result = $handler->queryObject($statement->reveal());

        $this->assertIsArray($result);
        $this->assertInstanceOf(stdClass::class, $result[0]);
    }

    protected function setUp(): void
    {
        $this->pdo = $this->prophesize(ExtendedPdo::class);
    }

    protected function tearDown(): void
    {
        unset($this->pdo);
    }
}
