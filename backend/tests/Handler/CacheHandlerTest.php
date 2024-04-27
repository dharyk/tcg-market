<?php

namespace TcgMarket\Tests\Handler;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Redis;
use TcgMarket\Handler\CacheHandler;

class CacheHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $redis;

    public function testPing(): void
    {
        $this->redis
            ->ping()
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );

        $this->assertTrue($handler->ping());
    }

    public function testGetHits(): void
    {
        $key = 'my_test_key';
        $value = 'my_test_value';

        $this->redis
            ->get(
                Argument::exact($key)
            )
            ->shouldBeCalled()
            ->willReturn($value);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );
        $result = $handler->get($key);

        $this->assertEquals($value, $result);
    }

    public function testGetMisses(): void
    {
        $key = 'my_test_key';
        $default = 'my_default_value';

        $this->redis
            ->get(
                Argument::exact($key)
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );
        $result = $handler->get($key, $default);

        $this->assertEquals($default, $result);
    }

    public function testSetWithTtl(): void
    {
        $key = 'my_test_key';
        $value = 'my_test_value';
        $ttl = new DateInterval('PT45S');

        $this->redis
            ->setex(
                Argument::exact($key),
                Argument::exact(45),
                Argument::exact($value)
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );

        $this->assertTrue($handler->set($key, $value, $ttl));
    }

    public function testSetDefaultTtl(): void
    {
        $key = 'my_test_key';
        $value = 'my_test_value';

        $this->redis
            ->setex(
                Argument::exact($key),
                Argument::exact(60),
                Argument::exact($value)
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );

        $this->assertTrue($handler->set($key, $value));
    }

    public function testDelete(): void
    {
        $key = 'my_test_key';

        $this->redis
            ->del(
                Argument::exact($key)
            )
            ->shouldBeCalled()
            ->willReturn(1);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );

        $this->assertTrue($handler->delete($key));
    }

    public function testClear(): void
    {
        $this->redis
            ->flushDB()
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );

        $this->assertTrue($handler->clear());
    }

    public function testGetMultiple(): void
    {
        $key1 = 'my_test_key_1';
        $key2 = 'my_test_key_2';
        $key3 = 'my_test_key_3';
        $value1 = 'my_test_value_1';
        $value2 = 'my_test_value_2';

        $this->redis
            ->get(
                Argument::exact($key1)
            )
            ->shouldBeCalled()
            ->willReturn($value1);
        $this->redis
            ->get(
                Argument::exact($key2)
            )
            ->shouldBeCalled()
            ->willReturn($value2);
        $this->redis
            ->get(
                Argument::exact($key3)
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );
        $result = $handler->getMultiple([$key1, $key2, $key3]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey($key1, $result);
        $this->assertEquals($value1, $result[$key1]);
        $this->assertArrayHasKey($key2, $result);
        $this->assertEquals($value2, $result[$key2]);
        $this->assertArrayHasKey($key3, $result);
        $this->assertNull($result[$key3]);
    }

    public function testSetMultiple(): void
    {
        $key1 = 'my_test_key_1';
        $key2 = 'my_test_key_2';
        $value1 = 'my_test_value_1';
        $value2 = 'my_test_value_2';

        $this->redis
            ->setex(
                Argument::exact($key1),
                Argument::type('int'),
                Argument::exact($value1)
            )
            ->shouldBeCalled()
            ->willReturn(true);
        $this->redis
            ->setex(
                Argument::exact($key2),
                Argument::type('int'),
                Argument::exact($value2)
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );
        $this->assertTrue(
            $handler->setMultiple([
                $key1 => $value1,
                $key2 => $value2,
            ])
        );
    }

    public function testDeleteMultiple(): void
    {
        $key1 = 'my_test_key_1';
        $key2 = 'my_test_key_2';

        $this->redis
            ->del(
                Argument::exact([$key1, $key2])
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );
        $this->assertTrue(
            $handler->deleteMultiple([$key1, $key2])
        );
    }

    public function testHas(): void
    {
        $key1 = 'my_test_key_1';
        $key2 = 'my_test_key_2';

        $this->redis
            ->exists(
                Argument::exact($key1)
            )
            ->shouldBeCalled()
            ->willReturn(true);
        $this->redis
            ->exists(
                Argument::exact($key2)
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $handler = new CacheHandler(
            $this->redis->reveal()
        );
        $this->assertTrue($handler->has($key1));
        $this->assertFalse($handler->has($key2));
    }

    protected function setUp(): void
    {
        $this->redis = $this->prophesize(Redis::class);
        $this->redis
            ->close()
            ->shouldBeCalled()
            ->wilLReturn(true);
    }

    protected function tearDown(): void
    {
        unset($this->redis);
    }
}
