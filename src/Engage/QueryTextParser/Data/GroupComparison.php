<?php namespace Engage\QueryTextParser\Data;

abstract class GroupComparison
{
    const OPERATOR_AND = 'AND';
    const OPERATOR_OR = 'OR';
    const OPERATOR_NEAR = 'NEAR';
    const OPERATOR_ADJ = 'ADJ';

    private static function getValidOperators() {
        return [
            self::OPERATOR_AND,
            self::OPERATOR_OR,
            self::OPERATOR_NEAR,
            self::OPERATOR_ADJ,
        ];
    }

    public static function isValidOperator($operator)
    {
        return in_array($operator, self::getValidOperators());
    }
}
