<?php namespace Engage\QueryTextParser;

use Engage\QueryTextParser\Data\Group;
use Engage\QueryTextParser\Data\GroupComparison;
use Engage\QueryTextParser\Data\Partial;

class Parser
{
    public function parse($query) {
       return $this->parseGroup($query);
    }

    private function parseGroup($query, $parentGroup = null) {
        $group = new Group;

        if ($parentGroup) {
            $parentGroup->children[] = $group;
        }

        $depth = 0;
        $depthStartIndex = -1;
        $depthEndIndex = -1;
        $inQuotedString = false;
        for ($i = 0; $i < strlen($query); $i++) {
            // Handle quotes
            if ($query[$i] == '"') {
                if ($i > 0 && $query[$i - 1] == '\\') {
                    // Ignore this quote
                } else {
                    $inQuotedString = !$inQuotedString;
                }
            }

            // Pause parsing if we're in a quoted string
            if (!$inQuotedString) {
                if ($query[$i] == '(') {
                    if ($depth == 0) {
                        // Parse anything before this group
                        $start = ($depthEndIndex == -1) ? 0 : $depthEndIndex;
                        if ($i - $start > 0) {
                            $this->parsePartials(substr($query, $start, $i - $start), $group);
                        }

                        $depthStartIndex = $i;
                    }

                    $depth++;
                } elseif ($query[$i] == ')') {
                    $depth--;

                    if ($depth == 0) {
                        // Parse group
                        $start = $depthStartIndex + 1;
                        $length = $i - $depthStartIndex - 1;
                        if ($start < 0) {
                            $start = 0;
                        }

                        $this->parseGroup(substr($query, $start, $length), $group);
                        $depthEndIndex = $i;
                    }
                }
            }
        }

        // Parse anything that's remaining
        $start = (($depthEndIndex == -1) ? 0 : $depthEndIndex);

        if ($start < strlen($query) - 1) {
            $this->parsePartials(substr($query, $start, strlen($query) - $start), $group);
        }

        return $group;
    }

    private function parsePartials($query, $group) {
        $query = $this->trim($query);

        $parts = Tokenizer::tokenize($query);

        $alreadyDetectedGroupType = null;
        $children = array();

        foreach ($parts as $part) {
            if (GroupComparison::isValidOperator($part['token'])) {
                $operator = $part['token'];

                // If we already detected a group type, we'll have to
                // create a sub group for these partials
                if ($alreadyDetectedGroupType != null && $alreadyDetectedGroupType != $operator) {
                    $subGroup = new Group;
                    $subGroup->type = $group->type;
                    $subGroup->children = $children;
                    $children = array($subGroup); // Reset children
                }

                $group->type = $operator;
                $alreadyDetectedGroupType = $operator;
            } else {
                $partial = new Partial;
                $partial->text = $part['token'];
                $partial->negate = $part['negated'];
                $children[] = $partial;
            }
        }

        $group->children = array_merge($group->children, $children);
    }

    private function getPartOperator($part)
    {

    }

    private function trim($str) {
        return trim($str, " \t\n\r\0\x0B()");
    }
}
