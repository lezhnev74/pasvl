[![Latest Stable Version](https://poser.pugx.org/lezhnev74/pasvl/v/stable)](https://packagist.org/packages/lezhnev74/pasvl)
[![Build Status](https://travis-ci.org/lezhnev74/pasvl.svg?branch=master)](https://travis-ci.org/lezhnev74/pasvl)
[![Total Downloads](https://poser.pugx.org/lezhnev74/pasvl/downloads)](https://packagist.org/packages/lezhnev74/pasvl)
[![License](https://poser.pugx.org/lezhnev74/pasvl/license)](https://packagist.org/packages/lezhnev74/pasvl)

# PASVL - PHP Array Structure Validation Library

Think of a regular expression `[ab]+` which matches a string `abab`. Now imaging the same for arrays.

The purpose of this library is to validate an existing (nested) array against a template and report a mismatch. 
It has the object-oriented extendable architecture to write and add custom validators.

**Note to current users**: this version is not backwards compatible with the previous 0.5.6. 

## Installation
```
composer require lezhnev74/pasvl
```

## Example

Refer to files in `Example` folder.

## Usage

### Array Validation
```php
// Define the pattern of the data, define keys and values separately
$pattern = [
    '*' => [
        'type' => 'book',
        'title' => ':string :contains("book")',
        'chapters' => [
            ':string :len(2) {1,3}' => [
                'title' => ':string',
                ':exact("interesting") ?' => ':bool',
            ],
        ],
    ],
];

// Provide the data to match against the above pattern.
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

$builder = \PASVL\Validation\ValidatorBuilder::forArray($pattern);
$validator = $builder->build();
try {
    $validator->validate($data);
} catch (ArrayFailedValidation $e) {
    // If data cannot be matched against the pattern, then exception is thrown.
    // It is not always easy to detect why the data failed matching, the exception MAY sometimes give you extra hints.
    echo "failed: " . $e->getMessage() . "\n";
}
```

### Optional String Validation
```php
$pattern = ":string :regexp('#^[ab]+$#')";
$builder = \PASVL\Validation\ValidatorBuilder::forString($pattern);
$validator = $builder->build();
$validator->validate("abab"); // the string is valid
$validator->validate("abc"); // throws RuleFailed exception with the message: "string does not match regular expression ^[ab]+$"
```

## Validation Language
This package supports a special dialect for validation specification.
It looks like this:

![](pasvl.jpg)

#### Short language reference:
- **Rule Name**
  Specify zero or one Rule Name to apply to the data. Optinal postfix `?` allows data to be `null`.
  Refer to the set of built-in rules in `src/Validation/Rules/Library`. For custom rules read below under `Custom Rules`.
  For example, `:string?` describes strings and `null`.  
- **Sub-Rule Name**
  Specify zero or more Sub-Rule Names to apply to the data AFTER the Rule is applied. Sub Rules are extra methods of the main Rule.
  For example, `:number :float` describes floats.
- **Quantifier**
  Specify quantity expectations for data keys. If none is set then default is assumed - `!`.
  Available quantifiers:                       
  - `!` - one key required (default)
  - `?` - optional key
  - `*` - any count of keys
  - `{2}` - strict keys count
  - `{2,4}` - range of keys count
  
  For example:
  ```php
    $pattern = [":string *" => ":number"];
    // the above pattern matches data:
    $data = ["june"=>10, "aug" => "11"];
  ```

#### Pattern Definitions
- as exact value
  ```php
  $pattern = ["name" => ":any"]; // here the key is the exact value
  $pattern = ["name?" => ":any"]; // here the key is the exact value, can be absent as well
  $pattern = [":exact('name')" => ":any"]; // this is the same
  ```
- as nullable rule
  ```php
  $pattern = ["name" => ":string?"]; // the value must be a string or null
  ```
- as rule with subrules
  ```php
  $pattern = ["name" => ":string :regexp('#\d*#')"]; // the value must be a string which contains only digits
  ```
- as rule with quantifiers
  ```php
  $pattern = [":string {2}" => ":any"]; // data must have exactly two string keys
  ```

#### Compound Definitions
This package supports combinations of rules, expressed in a natural language.
Examples:
- `:string or :number`
- `:string and :number`
- `(:string and :number) or :array`

There are two combination operators: `and`, `or`. 
`and` operator has precedence. 
Both are left-associative. 

## Custom Rules
By default, the system uses only the built-in rules. However you can extend them with your own implementations.
To add new custom rules, follow these steps:
- implement your new rule as a class and extend it from `\PASVL\Validation\Rules\Rule`
- implement a new rule locator by extending a class `\PASVL\Validation\Rules\RuleLocator`
- configure your validator like this:
  ```php
  $builder = ValidatorBuilder::forArray($pattern)->withLocator(new MyLocator()); // set your new locator
  $validator = $builder->build();
  ```

## Built-in Rules
This package comes with a few built-in rules and their corresponding sub-rules (see in folder `src/Validation/Rules/Library`): 
- `:string` - the value must be string
  - `:regexp(<string>)` - provide a regular expression(the same as for `preg_match()`)
  - `:url`
  - `:email`
  - `:uuid`
  - `:contains(<string>)`
  - `:starts(<string>)`
  - `:ends(<string>)`
  - `:in(<string>,<string>,...)`
  - `:len(<int>)`
  - `:max(<int>)`
  - `:min(<int>)`
  - `:between(<int>,<int>)`
- `:number`
  - `:max(<int>)`
  - `:min(<int>)`
  - `:between(<int>, <int>)`
  - `:int` - the number must be an integer
  - `:float` - the number must be a float
  - `:positive`
  - `:negative`
  - `:in(<a>,<b>,<c>)` - the number must be within values (type coercion possible)
  - `:inStrict(<a>,<b>,<c>)` - the number must be within values (type coercion disabled)
- `:exact(<value>)`  
- `:bool(<?value>)` - the value must be boolean, if optional argument is given the value must be exactly it  
- `:object`
  - `:instance(<fqcn>)`
  - `:propertyExists(<string>)`
  - `:methodExists(<string>)`
- `:array`
  - `:count(<int>)`
  - `:keys(<string>,<string>,...)`
  - `:min(<int>)` - min count
  - `:max(<int>)` - max count
  - `:between(<int>, <int>)` - count must be within
- `:any` - a placeholder, any value will match

## Hints
- PHP casts "1" to 1 for array keys:
  ```php
  $data = ["12" => ""];
  $pattern_invalid = [":string" => ""];
  $pattern_valid = [":number :int" => ""];
  ```
 - Technically speaking PASVL is a non-deterministic backtracking parser, and thus it can't always show you what exact key did not match the pattern. That is because, say, a key can match different patterns and there is no way of knowing which one was meant to be correct. In such cases it returns a message like "no matches found at X level".

## 🏆 Contributors
- **[Greg Corrigan](https://github.com/corrigang)**. Greg spotted a problem with nullable values reported as invalid.
- **Henry Combrinck**. Henry tested the library extensively on real data and found tricky bugs and edge cases. Awesome contribution to make the package valuable to the community.
- **[@Averor](https://github.com/Averor)**. Found a bug in parentheses parsing.
- **[Julien Gidel](https://github.com/JuGid)**. Improved `regexp` sub-rule.

## License
This project is licensed under the terms of the MIT license.

