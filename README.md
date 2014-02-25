[![Build Status](https://travis-ci.org/EngageDC/query-text-parser.png?branch=master)](https://travis-ci.org/EngageDC/query-text-parser)

# Query Text Parser

The Query Text Parser library performs search query text parsing.

This allows you to write a search query in free form text and parse it into a machine-readable parsing tree.

The library is fully unit-tested.

## Features

* AND/OR operators
* Grouped queries using paranthesis -- i.e. `(Denver AND Boston) OR Miami`
* Multi-word search queries using quotes -- i.e. `"San Francisco" AND Chicago`

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

## TODO

* Support negating operator (i.e. *NOT*)