<?php declare(strict_types=1);

/**
 * This code was auto-generated by {this script}[https://github.com/cucumber/common/blob/main/messages/jsonschema/scripts/codegen.rb]
 */

namespace Cucumber\Messages;

use \JsonSerializable;
use Cucumber\Messages\DecodingException\SchemaViolationException;


/**
 * Represents the Product message in Cucumber's message protocol
 * @see https://github.com/cucumber/common/tree/main/messages#readme
 *
 * Used to describe various properties of Meta */
final class Product implements JsonSerializable
{
    use JsonEncodingTrait;

    private function __construct(

        /**
         * The product name
         */
        public readonly string $name,

        /**
         * The product version
         */
        public readonly ?string $version,

    ){}

    /**
     * @throws SchemaViolationException
     *
     * @internal
     */
    public static function fromArray(array $arr) : self
    {
        self::ensureName($arr);
        self::ensureVersion($arr);

        return new self(
            (string) $arr['name'],
            isset($arr['version']) ? (string) $arr['version'] : null,
        );
    }

    /**
     * @psalm-assert array{name: string|int|bool} $arr
     */
    private static function ensureName(array $arr): void
    {
        if (!array_key_exists('name', $arr)) {
            throw new SchemaViolationException('Property \'name\' is required but was not found');
        }
        if (array_key_exists('name', $arr) && is_array($arr['name'])) {
            throw new SchemaViolationException('Property \'name\' was array');
        }
    }

    /**
     * @psalm-assert array{version: string|int|bool} $arr
     */
    private static function ensureVersion(array $arr): void
    {
        if (array_key_exists('version', $arr) && is_array($arr['version'])) {
            throw new SchemaViolationException('Property \'version\' was array');
        }
    }
}
