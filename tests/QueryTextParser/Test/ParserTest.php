<?php namespace Engage\QueryTextParser\Test;

use Engage\QueryTextParser\Parser;
use Engage\QueryTextParser\Exceptions\ParserException;
use Engage\QueryTextParser\Data\Group;
use Engage\QueryTextParser\Data\GroupComparison;
use Engage\QueryTextParser\Data\Partial;

class ParserTest extends \PHPUnit_Framework_TestCase
{
	private $parser;

	public function setUp() {
		$this->parser = new Parser();
	}

    public function testSimpleQuery() {
		try {
			$result = $this->parser->parse('Chicago');

			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $result);

			$this->assertEquals($result->type, GroupComparison::OPERATOR_AND);
			$this->assertCount(1, $result->children);

			$this->assertEquals($result->children[0]->text, 'Chicago');
			$this->assertEquals($result->children[0]->negate, false);
		} catch (ParserException $e) {
			echo 'Parse Error: ' . $e->getMessage();
		}
    }

    public function testSimpleAnd() {
		try {
			$result = $this->parser->parse('Chicago AND Houston');

			// Verify consistency of group
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $result);
			$this->assertEquals($result->type, GroupComparison::OPERATOR_AND);

			$this->assertCount(2, $result->children);

			// Verify consistency of children
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[0]);
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[1]);

			$this->assertEquals($result->children[0]->text, 'Chicago');
			$this->assertEquals($result->children[0]->negate, false);

			$this->assertEquals($result->children[1]->text, 'Houston');
			$this->assertEquals($result->children[1]->negate, false);
		} catch (ParserException $e) {
			echo 'Parse Error: ' . $e->getMessage();
		}
    }

    public function testSimpleOr() {
		try {
			$result = $this->parser->parse('Chicago OR Houston');

			// Verify consistency of group
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $result);
			$this->assertEquals($result->type, GroupComparison::OPERATOR_OR);

			$this->assertCount(2, $result->children);

			// Verify consistency of children
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[0]);
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[1]);

			$this->assertEquals($result->children[0]->text, 'Chicago');
			$this->assertEquals($result->children[0]->negate, false);

			$this->assertEquals($result->children[1]->text, 'Houston');
			$this->assertEquals($result->children[1]->negate, false);
		} catch (ParserException $e) {
			echo 'Parse Error: ' . $e->getMessage();
		}
    }

    public function testGroups() {
		try {
			$result = $this->parser->parse('(Chicago AND Houston) OR Phoenix');

			// Verify consistency of outer group
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $result);
			$this->assertEquals($result->type, GroupComparison::OPERATOR_OR);
			$this->assertCount(2, $result->children);

			// Verify consistency of left group (foo AND bar)
			$leftSide = $result->children[0];
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $leftSide);
			$this->assertEquals($leftSide->type, GroupComparison::OPERATOR_AND);
			$this->assertCount(2, $leftSide->children);
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $leftSide->children[0]);
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $leftSide->children[1]);

			$this->assertEquals($leftSide->children[0]->text, 'Chicago');
			$this->assertEquals($leftSide->children[0]->negate, false);

			$this->assertEquals($leftSide->children[1]->text, 'Houston');
			$this->assertEquals($leftSide->children[1]->negate, false);

			// Verify consistency of right group (abc)
			$rightSide = $result->children[1];
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $rightSide);

			$this->assertEquals($rightSide->text, 'Phoenix');
			$this->assertEquals($rightSide->negate, false);

		} catch (ParserException $e) {
			echo 'Parse Error: ' . $e->getMessage();
		}
    }

    public function testComplex() {
		try {
			$result = $this->parser->parse('(Chicago AND Houston OR (Dallas AND Austin AND Columbus)) OR ((Phoenix OR Detroit) AND Charlotte)');
			/*
			(
				Chicago AND Houston
				OR
				(
					Dallas AND Austin AND Columbus
				)
			)
			OR
			(
				(
					Phoenix OR Detroit
				)
				AND
				Charlotte
			)
			*/

			// Verify consistency of outer group
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $result);
			$this->assertEquals($result->type, GroupComparison::OPERATOR_OR);
			$this->assertCount(2, $result->children);

			// Left side -- "Chicago AND Houston OR (Dallas AND Austin AND Columbus)"
			$leftSide = $result->children[0];
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $leftSide);
			$this->assertEquals($leftSide->type, GroupComparison::OPERATOR_OR);

			// Chicago AND Houston
			$leftLeftSide = $leftSide->children[0];
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $leftLeftSide);
			$this->assertEquals($leftLeftSide->type, GroupComparison::OPERATOR_AND);
			$this->assertEquals($leftLeftSide->children[0]->text, 'Chicago');
			$this->assertEquals($leftLeftSide->children[1]->text, 'Houston');

			// Dallas AND Austin AND Columbus
			$rightLeftSide = $leftSide->children[1];
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $rightLeftSide);
			$this->assertEquals($rightLeftSide->type, GroupComparison::OPERATOR_AND);
			$this->assertEquals($rightLeftSide->children[0]->text, 'Dallas');
			$this->assertEquals($rightLeftSide->children[1]->text, 'Austin');
			$this->assertEquals($rightLeftSide->children[2]->text, 'Columbus');

			// Right side -- "(Phoenix OR Detroit) AND Charlotte"
			$rightSide = $result->children[1];
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $rightSide);
			$this->assertEquals($rightSide->type, GroupComparison::OPERATOR_AND);

			// Phoeneix OR Detroit
			$leftRightSide = $rightSide->children[0];
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $leftRightSide);
			$this->assertEquals($leftRightSide->type, GroupComparison::OPERATOR_OR);
			$this->assertEquals($leftRightSide->children[0]->text, 'Phoenix');
			$this->assertEquals($leftRightSide->children[1]->text, 'Detroit');

			// Charlotte
			$rightRightSide = $rightSide->children[1];
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $rightRightSide);
			$this->assertEquals($rightRightSide->text, 'Charlotte');


		} catch (ParserException $e) {
			echo 'Parse Error: ' . $e->getMessage();
		}
    }

    public function testQuotes() {
		try {
			$result = $this->parser->parse('"New York" AND "San Francisco"');

			// Verify consistency of group
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $result);
			$this->assertEquals($result->type, GroupComparison::OPERATOR_AND);

			$this->assertCount(2, $result->children);

			// Verify consistency of children
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[0]);
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[1]);

			$this->assertEquals($result->children[0]->text, 'New York');
			$this->assertEquals($result->children[0]->negate, false);

			$this->assertEquals($result->children[1]->text, 'San Francisco');
			$this->assertEquals($result->children[1]->negate, false);
		} catch (ParserException $e) {
			echo 'Parse Error: ' . $e->getMessage();
		}
    }

	public function testSimpleNear() {
		try {
			$result = $this->parser->parse('Chicago NEAR Houston');

			// Verify consistency of group
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $result);
			$this->assertEquals($result->type, GroupComparison::OPERATOR_NEAR);

			$this->assertCount(2, $result->children);

			// Verify consistency of children
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[0]);
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[1]);

			$this->assertEquals($result->children[0]->text, 'Chicago');
			$this->assertEquals($result->children[0]->negate, false);

			$this->assertEquals($result->children[1]->text, 'Houston');
			$this->assertEquals($result->children[1]->negate, false);
		} catch (ParserException $e) {
			echo 'Parse Error: ' . $e->getMessage();
		}
    }


	public function testSimpleAdj() {
		try {
			$result = $this->parser->parse('Chicago ADJ Houston');

			// Verify consistency of group
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Group', $result);
			$this->assertEquals($result->type, GroupComparison::OPERATOR_ADJ);

			$this->assertCount(2, $result->children);

			// Verify consistency of children
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[0]);
			$this->assertInstanceOf('Engage\QueryTextParser\Data\Partial', $result->children[1]);

			$this->assertEquals($result->children[0]->text, 'Chicago');
			$this->assertEquals($result->children[0]->negate, false);

			$this->assertEquals($result->children[1]->text, 'Houston');
			$this->assertEquals($result->children[1]->negate, false);
		} catch (ParserException $e) {
			echo 'Parse Error: ' . $e->getMessage();
		}
    }

}
