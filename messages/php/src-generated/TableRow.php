<?php declare(strict_types=1);

/**
 * This code was auto-generated by {this script}[https://github.com/cucumber/common/blob/main/messages/jsonschema/scripts/codegen.rb]
 */

namespace Cucumber\Messages;

use \JsonSerializable;
use Cucumber\Messages\DecodingException\SchemaViolationException;


/**
 * Represents the TableRow message in Cucumber's message protocol
 * @see https://github.com/cucumber/common/tree/main/messages#readme
 *
 * A row in a table */
final class TableRow implements JsonSerializable
{
    use JsonEncodingTrait;

    private function __construct(

        /**
         * The location of the first cell in the row
         */
        public readonly Location $location,

        /**
         * Cells in the row
         * @param list<TableCell> $cells
         */
        public readonly array $cells,

        public readonly string $id,

    ){}

    /**
     * @throws SchemaViolationException
     *
     * @internal
     */
    public static function fromArray(array $arr) : self
    {
        self::ensureLocation($arr);
        self::ensureCells($arr);
        self::ensureId($arr);

        return new self(
            Location::fromArray($arr['location']),
            array_map(fn(array $member) => TableCell::fromArray($member) , $arr['cells']),
            (string) $arr['id'],
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
     * @psalm-assert array{cells: array} $arr
     */
    private static function ensureCells(array $arr): void
    {
        if (!array_key_exists('cells', $arr)) {
            throw new SchemaViolationException('Property \'cells\' is required but was not found');
        }
        if (array_key_exists('cells', $arr) && !is_array($arr['cells'])) {
            throw new SchemaViolationException('Property \'cells\' was not array');
        }
    }

    /**
     * @psalm-assert array{id: string|int|bool} $arr
     */
    private static function ensureId(array $arr): void
    {
        if (!array_key_exists('id', $arr)) {
            throw new SchemaViolationException('Property \'id\' is required but was not found');
        }
        if (array_key_exists('id', $arr) && is_array($arr['id'])) {
            throw new SchemaViolationException('Property \'id\' was array');
        }
    }
}
