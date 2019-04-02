<?php

namespace Authters\Chronicle\Metadata\Matcher;

use Authters\Chronicle\Exceptions\InvalidArgumentException;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;

trait StatementWhereClauseTrait
{
    /**
     * @var array
     */
    private $wheres = [];

    /**
     * @var array
     */
    private $bindings = [];

    public function createWhereClauseStatement(?MetadataMatcher $metadataMatcher): array
    {
        if (!$metadataMatcher) {
            return [$this->wheres, $this->bindings];
        }

        /** @var ExpresionFactory $factory */
        foreach ($this->data($metadataMatcher) as $key => $factory) {
            $parameters = $this->prepareParameters($key, $factory->value());

            $parameterString = \implode(', ', $parameters);

            $strings = $this->operatorChunkString($factory, $parameterString);

            if ($factory->isMetadataField()) {
                $this->addWhereClauseMetaData($factory, $strings);
            } else {
                $this->addWhereClauseProperty($factory, $strings);
            }

            $value = (array)$factory->value();
            foreach ($value as $k => $v) {
                $this->bindings[$parameters[$k]] = $v;
            }
        }

        return [$this->wheres, $this->bindings];
    }

    private function addWhereClauseMetaData(ExpresionFactory $factory, array $strings): void
    {
        $value = $factory->value();
        $field = $factory->field();

        [$opString, $parameter, $opEnd] = $strings;

        if (\is_bool($value)) {
            $this->wheres[] = "metadata->\"$.$field\" $opString " . \var_export($value, true) . ' ' . $opEnd;
        } else {
            $this->wheres[] = "JSON_UNQUOTE(metadata->\"$.$field\") $opString $parameter $opEnd";
        }
    }

    private function addWhereClauseProperty(ExpresionFactory $factory, array $strings): void
    {
        $value = $factory->value();
        $field = $factory->field();

        [$opString, $parameter, $opEnd] = $strings;

        if (\is_bool($value)) {
            $this->wheres[] = "$field $opString " . \var_export($value, true) . ' ' . $opEnd;
        } else {
            $this->wheres[] = "$field $opString $parameter $opEnd";
        }
    }

    private function operatorChunkString(ExpresionFactory $factory, string $parameterString): array
    {
        $opEnd = '';
        $operator = $factory->operator();

        if ($operator->is(Operator::REGEX())) {
            $opString = 'REGEXP';
        } elseif ($operator->is(Operator::IN())) {
            $opString = 'IN (';
            $opEnd = ')';
        } elseif ($operator->is(Operator::NOT_IN())) {
            $opString = 'NOT IN (';
            $opEnd = ')';
        } else {
            $opString = $operator->getValue();
        }

        return [$opString, $parameterString, $opEnd];
    }

    private function prepareParameters(int $key, $value): array
    {
        $parameters = [];
        if (\is_array($value)) {
            foreach ($value as $k => $v) {
                $parameters[] = ':metadata_' . $key . '_' . $k;
            }
            return $parameters;
        }

        return [':metadata_' . $key];
    }

    private function data(MetadataMatcher $metadataMatcher): iterable
    {
        if (!$metadataMatcher instanceof MetadataMatcherAggregate) {
            throw new InvalidArgumentException(
                "Metadata matcher must be an instance of " . (MetadataMatcherAggregate::class)
            );
        }

        yield from $metadataMatcher->data();
    }
}