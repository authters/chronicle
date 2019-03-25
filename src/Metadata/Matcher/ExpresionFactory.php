<?php

namespace Authters\Chronicle\Metadata\Matcher;

use Authters\Chronicle\Exceptions\InvalidArgumentException;

final class ExpresionFactory
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var Operator
     */
    private $operator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var FieldType
     */
    private $fieldType;

    protected function __construct(string $field, Operator $operator, $value, FieldType $fieldType)
    {
        $this->assertValidationValue($operator, $value);

        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->fieldType = $fieldType;
    }

    public static function fromMetadata(string $field, Operator $operator, $value): self
    {
        return new self($field, $operator, $value, FieldType::METADATA());
    }

    public static function fromProperty(string $field, Operator $operator, $value): self
    {
        return new self($field, $operator, $value, FieldType::MESSAGE_PROPERTY());
    }

    public function field(): string
    {
        return $this->field;
    }

    public function operator(): Operator
    {
        return $this->operator;
    }

    public function fieldType(): FieldType
    {
        return $this->fieldType;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    public function isMetadataField(): bool
    {
        return $this->fieldType->is(FieldType::METADATA());
    }

    public function isPropertyField(): bool
    {
        return $this->fieldType->is(FieldType::MESSAGE_PROPERTY());
    }

    protected function assertValidationValue(Operator $operator, $value): void
    {
        if ($operator->is(Operator::IN() || $operator->is(Operator::NOT_IN()))) {
            if (!\is_array($value)) {
                throw new InvalidArgumentException(
                    "Value must be an array for Operator {$operator->getName()}"
                );
            }

            return;
        }

        if ($operator->is(Operator::REGEX()) && !\is_string($value)) {
            throw new InvalidArgumentException('Value must be a string for the regex Operator.');
        }

        if (!\is_scalar($value)) {
            throw new InvalidArgumentException(
                "Value must be a scalar type for operator {$operator->getName()}"
            );
        }
    }
}