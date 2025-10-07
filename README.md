# Basic .env parser
This is a small PHP script that can parse and load .env files into environment variables. It is intended to be simpler and more efficient than existing library solutions.

## Basic usage
Available via Composer (`composer require rhlt/env`), or by manually including `env.php`. It contains a single function `load_dontenv($path)`:

```php
include 'env.php'; // "include" is needed only when not using Composer
load_dotenv('.env');

// Now you can access them wherever, for example:
$credentials = [
    'user' => getenv('API_USER'),
    'token' => getenv('API_TOKEN'), 
];
```

## Example `.env` files
Basic syntax:
```
API_USER=username
API_TOKEN=p@sswørd
```
Additional examples:
```
VAR1=value # this is a comment
VAR2='single-quoted value'
VAR3="double quoted value"

# Another comment
VAR4='Use quotes to add the # symbol in a value'
VAR5="Double-quoted values (only) allow \\backslash \"escaping\"\nand newlines"

# Whitespace
VAR6 = Whitespace is allowed
   VAR7     = excessive whitespace is trimmed
VAR8        = '  Whitespace is kept when the value is quoted.   '  
VAR9=
# VAR9 is an empty value (alternatively: VAR9="", or VAR9='')

# Variable interpolation: ${NAME}
VAR10=example.com
VAR11=email@${VAR10} # email@example.com
VAR12="You can reach us at ${VAR11}"
VAR13="Backslash escape to get a literal \${VALUE}"
VAR14='The content of single ${QUOTES} is always untouched'

Lines with invalid data will be ignored
VAR15="Quoted value" this will be ignored
VAR16='Value is stored even if the final quote is missing # this is not a comment
```

Syntax errors are handled as gracefully as possible. Note that all variables need to be a single line. Newlines must be entered as `\n` (or `\r\n` if desired) between "double quotes". The file encoding must be ASCII-compatible (such as UTF-8).