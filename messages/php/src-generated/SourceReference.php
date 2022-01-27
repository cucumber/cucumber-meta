<?php declare(strict_types=1);

/**
 * This code was auto-generated by {this script}[https://github.com/cucumber/common/blob/main/messages/jsonschema/scripts/codegen.rb]
 */

namespace Cucumber\Messages;

use \JsonSerializable;
use Cucumber\Messages\DecodingException\SchemaViolationException;


/**
 * Represents the SourceReference message in Cucumber's message protocol
 * @see https://github.com/cucumber/common/tree/main/messages#readme
 *
 * Points to a [Source](#io.cucumber.messages.Source) identified by `uri` and a
 * [Location](#io.cucumber.messages.Location) within that file. */
final class SourceReference implements JsonSerializable
{
    use JsonEncodingTrait;

    private function __construct(

        public readonly ?string $uri,

        public readonly ?JavaMethod $javaMethod,

        public readonly ?JavaStackTraceElement $javaStackTraceElement,

        public readonly ?Location $location,

    ){}

    /**
     * @throws SchemaViolationException
     *
     * @internal
     */
    public static function fromArray(array $arr) : self
    {
        self::ensureUri($arr);
        self::ensureJavaMethod($arr);
        self::ensureJavaStackTraceElement($arr);
        self::ensureLocation($arr);

        return new self(
            isset($arr['uri']) ? (string) $arr['uri'] : null,
            isset($arr['javaMethod']) ? JavaMethod::fromArray($arr['javaMethod']) : null,
            isset($arr['javaStackTraceElement']) ? JavaStackTraceElement::fromArray($arr['javaStackTraceElement']) : null,
            isset($arr['location']) ? Location::fromArray($arr['location']) : null,
        );
    }

    /**
     * @psalm-assert array{uri: string|int|bool} $arr
     */
    private static function ensureUri(array $arr): void
    {
        if (array_key_exists('uri', $arr) && is_array($arr['uri'])) {
            throw new SchemaViolationException('Property \'uri\' was array');
        }
    }

    /**
     * @psalm-assert array{javaMethod?: array} $arr
     */
    private static function ensureJavaMethod(array $arr): void
    {
        if (array_key_exists('javaMethod', $arr) && !is_array($arr['javaMethod'])) {
            throw new SchemaViolationException('Property \'javaMethod\' was not array');
        }
    }

    /**
     * @psalm-assert array{javaStackTraceElement?: array} $arr
     */
    private static function ensureJavaStackTraceElement(array $arr): void
    {
        if (array_key_exists('javaStackTraceElement', $arr) && !is_array($arr['javaStackTraceElement'])) {
            throw new SchemaViolationException('Property \'javaStackTraceElement\' was not array');
        }
    }

    /**
     * @psalm-assert array{location?: array} $arr
     */
    private static function ensureLocation(array $arr): void
    {
        if (array_key_exists('location', $arr) && !is_array($arr['location'])) {
            throw new SchemaViolationException('Property \'location\' was not array');
        }
    }
}
