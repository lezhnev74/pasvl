[![Latest Stable Version](https://poser.pugx.org/lezhnev74/pasvl/v/stable)](https://packagist.org/packages/lezhnev74/pasvl)
[![Build Status](https://travis-ci.org/lezhnev74/pasvl.svg?branch=master)](https://travis-ci.org/lezhnev74/pasvl)
[![Packagist](https://img.shields.io/packagist/dt/lezhnev74/pasvl.svg)](https://packagist.org/packages/lezhnev74/pasvl)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/lezhnev74/pasvl/master/LICENSE)

# PASVL - PHP Array Structure Validation Library 

The purpose of this library is to validate an existing (nested) array against a template and report a mismatch. 
It has object-oriented extendable architecture to write and add custom validators.


Highly inspired by abandoned package [ptrofimov/matchmaker](https://github.com/ptrofimov/matchmaker). While the mentioned package was written in a functional way, current one embraces OO architecture in a sake of readability, maintainability, and extendability.  

## Installation
```
composer require lezhnev74/pasvl
```

## Example: Valid array

```php

// import fully qualified class names to your namespace
use PASVL\Traverser\TraversingMatcher;
use PASVL\ValidatorLocator\ValidatorLocator;


$data = [
               [
                   'type' => 'book',
                   'title' => 'Geography book',
                   'chapters' => [
                       'eu' => ['title' => 'Europe', 'interesting' => true],
                       'as' => ['title' => 'America', 'interesting' => false],
                   ],
               ],
               [
                   'type' => 'book',
                   'title' => 'Foreign languages book',
                   'chapters' => [
                       'de' => ['title' => 'Deutsch'],
                   ],
               ],
           ];

$pattern = [
            '*' => [
                'type' => 'book',
                'title' => ':string :contains(book)',
                'chapters' => [
                    ':string :length(2) {1,3}' => [
                        'title' => ':string',
                        'interesting?' => ':bool',
                    ],
                ],
            ],
        ];

$traverser = new TraversingMatcher(new ValidatorLocator());
$traverser->match($pattern, $data); // returns void, throws Report on Fail
```

## Example: Invalid array

```php
// import fully qualified class names to your namespace
use PASVL\Traverser\FailReport;
use PASVL\Traverser\TraversingMatcher;
use PASVL\ValidatorLocator\ValidatorLocator;


$data = [
    "password"=>"weak"
];

$pattern = [
    "password" => ":string :min(6)"
];


$traverser = new TraversingMatcher(new ValidatorLocator());
try {
    $traverser->match($pattern, $data); // returns void, throws Report on Fail   
} catch (FailReport $report) {
    echo "\n--- Array does not match a pattern ---\n";

    echo "Reason: ";
    echo $report->getReason()->isValueType() ? "Invalid value found" : "";
    echo $report->getReason()->isKeyType() ? "Invalid key found" : "";
    echo $report->getReason()->isKeyQuantityType() ? "Invalid key quantity found" : "";
    echo "\n";

    echo "Data keys chain to invalid data: ";
    if ($report->getFailedPatternLevel()) {
        echo implode(" => ", $report->getDataKeyChain());
        echo " => ";
    }
    echo $report->getMismatchDataKey() . "\n";
    if ($report->getReason()->isValueType()) {
        echo "Invalid value: ";
        echo json_encode($report->getMismatchDataValue(), JSON_PRETTY_PRINT) . "\n";
    }
    echo "Mismatched pattern: " . json_encode($report->getMismatchPattern(), JSON_PRETTY_PRINT) . "\n";
}
```

The output will be:
```
--- Array does not match a pattern ---
Reason: Invalid value found
Data keys chain to invalid data: password
Invalid value: "weak"
Mismatched pattern: {
    "password": ":string :min(6)"
}
```

The report allows you to locate the problem location. It has following information:
- exact key or value that failed validation (including a special case when keys failed quantity validation)
- the pattern that was compared to
- the level of mismatched data in case it is located deep inside the array
- chain of data and pattern keys to show breadcrumbs down to mismatched data and pattern

Notice: while it allows you to locate the level of mismatched data, it will not tell you the exact rule that failed. This is because each value is compared to multiple patterns and it is hard to say which one was supposed to be valid.

## Pattern 

Any array consists of keys and values. A pattern can set expectations for both.

Usually pattern consists of a 3 parts:
- main validator: `:string`
- optional sub-validators: `:min(2) :max(4)`
- quantifier (for keys only): `{1,2}`

Example: `:string :min(1) :max(4) {1,2}`:


### Pattern definition

A pattern can be set in a few ways:
- **as an explicit key name:**
    ```php
    $pattern = ["name"=>"Nico"]
    ```
- **as an explicit optional key name:**
    ```php
    // array can have optional key "name"
    $pattern = ["name?"=>"Nico"]
    ```
- **as validators list:**
    ```php
    //array can have any number of string keys of exactly 4 bytes long
    // here "string" - main validator, "len" - sub-validator
    $pattern = [":string :len(4)"=>"Nico"]
    ```
    In this case no other symbols are allowed except validator names and arguments. Invalid pattern: `:string name`.
- **as validators list with quantifier:**
    ```php
    //array can have any count (zero or more) string keys matching given regexp
    $pattern = [":string :regexp(/Nic(o|ky)/) *"=>"Nico"]
    ```

#### Quantifier definition
A quantifier is always optional in key's pattern, if none is set then default one is used - `!`. 

Available quantifiers:
- `!` - one key required (default)
- `?` - optional key
- `*` - any count of keys
- `{2}` - strict keys count
- `{2,4}` - range of keys count

    
### Validator definition
A pattern can have single main validator name and any number of sub-validators. Validator's definition must start with `:` and then the name is followed. Validators and sub-validators can have arguments: `:between(1,10)`, but empty argument list is not allowed. 

First validator in pattern is so-called "main validator", the rest are "sub-validators". Validator and sub-validator names follow the same rules as any [PHP label](http://www.php.net/manual/en/language.variables.basics.php):
```
/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/
```

If given pattern does not match the above description, then it is used as an implicit value:
```php
// This pattern matches only if data has key ": string"
$pattern = [
    ": string" => ":any"
];
``` 

## Hints

- PHP [casts](http://www.php.net/manual/en/language.types.array.php) "1" to 1 for array keys:
```php
$data = ["12"=>""];
$pattern_invalid = [":string"=>""];
$pattern_valid = [":int"=>""];
```

## 🏆 Contributors
- **Henry Combrinck**. Henry tested the library extensively on real data and found tricky bugs and edge cases. Awesome contribution to make the package valuable to the community.  

## License

This project is licensed under the terms of the MIT license.

## TODO
- Add validator classes
- Injecting custom validators
- What about supporting IoC?
