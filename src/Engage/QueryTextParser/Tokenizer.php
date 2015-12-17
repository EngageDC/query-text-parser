<?php namespace Engage\QueryTextParser;

class Tokenizer
{
    public static function tokenize($string)
    {
        $tokens = preg_split('/\s*(-?"[^"]*")\s*|\s+/', $string, -1 , PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $finalTokens = array();
        foreach ($tokens as $token) {
            if (substr($token, 0, 1) === '-') {
                $negated = true;
                $token = mb_substr($token, 1, mb_strlen($token));
            } else {
                $negated = false;
            }
            $finalTokens[] = array('token' => str_replace('"', '', $token), 'negated' => $negated);
        }
        return $finalTokens;
    }
}
