<?php namespace Engage\QueryTextParser\Data;

use Engage\QueryTextParser\Data\GroupComparison;

class Group
{
	/**
	 * Comparison type
	 * @var GroupComparison
	 */
	public $type = GroupComparison::OPERATOR_AND;

	/**
	 * The children for the group, this can
	 * either be Partial or Group instances
	 * @var array
	 */
	public $children = array();
}