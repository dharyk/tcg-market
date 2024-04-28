<?php

namespace TcgMarket\Handler;

use DateInterval;
use DateTimeImmutable;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Redis;

class CacheHandler implements CacheInterface
{
    private const DEFAULT_TTL = 60; // seconds

    public function __construct(private readonly Redis $redis)
    {
    }

    public function ping(): bool
    {
        return (bool) $this->redis->ping();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($key);

        if (false === $value) {
            return $default;
        }

        return $value;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        if (null === $ttl) {
            $ttl = self::DEFAULT_TTL;
        } elseif ($ttl instanceof DateInterval) {
            $ttl = $this->parseInterval($ttl);
        }

        return $this->redis->setex($key, $ttl, $value);
    }

    public function delete(string $key): bool
    {
        return 0 < (int) $this->redis->del($key);
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    /**
     * /**
     * @param array<string, mixed>  $keys
     * @param mixed|null            $default
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->get($key, $default);
        }

        return $data;
    }

    /**
     * @param array<string, mixed>  $values
     * @param DateInterval|int|null $ttl
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            $success = $success && $this->set($key, $value, $ttl);
        }

        return $success;
    }

    /**
     * @param array<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $data = [];
        foreach ($keys as $key) {
            $data[] = $key;
        }

        return 0 < (int) $this->redis->del($data);
    }

    public function has(string $key): bool
    {
        return (bool) $this->redis->exists($key);
    }

    private function parseInterval(DateInterval $interval): int
    {
        $ref = new DateTimeImmutable();
        $end = $ref->add($interval);

        return (int) ($end->getTimestamp() - $ref->getTimestamp());
    }
}
