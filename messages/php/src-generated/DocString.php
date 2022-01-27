<?php declare(strict_types=1);

/**
 * This code was auto-generated by {this script}[https://github.com/cucumber/common/blob/main/messages/jsonschema/scripts/codegen.rb]
 */

namespace Cucumber\Messages;

use \JsonSerializable;
use Cucumber\Messages\DecodingException\SchemaViolationException;


/**
 * Represents the DocString message in Cucumber's message protocol
 * @see https://github.com/cucumber/common/tree/main/messages#readme
 *
 */
final class DocString implements JsonSerializable
{
    use JsonEncodingTrait;

    private function __construct(

        public readonly Location $location,

        public readonly ?string $mediaType,

        public readonly string $content,

        public readonly string $delimiter,

    ){}

    /**
     * @throws SchemaViolationException
     *
     * @internal
     */
    public static function fromArray(array $arr) : self
    {
        self::ensureLocation($arr);
        self::ensureMediaType($arr);
        self::ensureContent($arr);
        self::ensureDelimiter($arr);

        return new self(
            Location::fromArray($arr['location']),
            isset($arr['mediaType']) ? (string) $arr['mediaType'] : null,
            (string) $arr['content'],
            (string) $arr['delimiter'],
        );
    }

    /**
     * @psalm-assert array{location: array} $arr
     */
    private static function ensureLocation(array $arr): void
    {
        if (!array_key_exists('location', $arr)) {
            throw new SchemaViolationException('Property \'location\' is required but was not found');
        }
        if (array_key_exists('location', $arr) && !is_array($arr['location'])) {
            throw new SchemaViolationException('Property \'location\' was not array');
        }
    }

    /**
     * @psalm-assert array{mediaType: string|int|bool} $arr
     */
    private static function ensureMediaType(array $arr): void
    {
        if (array_key_exists('mediaType', $arr) && is_array($arr['mediaType'])) {
            throw new SchemaViolationException('Property \'mediaType\' was array');
        }
    }

    /**
     * @psalm-assert array{content: string|int|bool} $arr
     */
    private static function ensureContent(array $arr): void
    {
        if (!array_key_exists('content', $arr)) {
            throw new SchemaViolationException('Property \'content\' is required but was not found');
        }
        if (array_key_exists('content', $arr) && is_array($arr['content'])) {
            throw new SchemaViolationException('Property \'content\' was array');
        }
    }

    /**
     * @psalm-assert array{delimiter: string|int|bool} $arr
     */
    private static function ensureDelimiter(array $arr): void
    {
        if (!array_key_exists('delimiter', $arr)) {
            throw new SchemaViolationException('Property \'delimiter\' is required but was not found');
        }
        if (array_key_exists('delimiter', $arr) && is_array($arr['delimiter'])) {
            throw new SchemaViolationException('Property \'delimiter\' was array');
        }
    }
}
