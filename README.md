# Query Text Parser

[![Build Status](https://travis-ci.org/engage/query-text-parser.png?branch=master)](https://travis-ci.org/engage/query-text-parser)

The Query Text Parser library performs search query text parsing

## Example usage

```php
$parser = new Engage\QueryTextParser\Parser;
$result = $parser->parse('(Chicago AND Houston) OR Phoenix');
print_r($result);
```

### Output
```
Engage\QueryTextParser\Data\Group Object
(
    [type] => OR
    [children] => Array
        (
            [0] => Engage\QueryTextParser\Data\Group Object
                (
                    [type] => AND
                    [children] => Array
                        (
                            [0] => Engage\QueryTextParser\Data\Partial Object
                                (
                                    [text] => Chicago
                                    [negate] =>
                                )

                            [1] => Engage\QueryTextParser\Data\Partial Object
                                (
                                    [text] => Houston
                                    [negate] =>
                                )

                        )

                )

            [1] => Engage\QueryTextParser\Data\Partial Object
                (
                    [text] => Phoenix
                    [negate] =>
                )

        )

)
```