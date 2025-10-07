<?php
/** Basic .env parser */
function load_dotenv($file) {
    $env = @fopen($file, 'r');
    if (!$env) {
        return false;
    }
    $state = $name = $value = $repeat = $variable = $quote = $whitespace = null;
    while (true) {
        $char = $repeat ?? fgetc($env);
        $repeat = null;
        if ($char === false || $char == "\n" || $char == "\r") {
            if ($state == 2) {
                $value = "";
            }
            if ($state == 4) {
                $value .= '${' . $variable;
            }
            if (isset($name, $value)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
            $state = 0;
            $quote = '';
            $whitespace = '';
            $name = null;
            $value = null;
            $variable = null;
            if ($char === false) {
                break;
            }
            continue;
        }
        if ($state == 0) {
            if (ctype_space($char)) {
                continue;
            }
            if ($char != '_' && !ctype_alpha($char)) {
                $state = -1;
                continue;
            }
            $whitespace = '';
            $name = $char;
            $state++;
        } elseif ($state == 1) {
            if ($char == '_' || ctype_alnum($char)) {
                if ($whitespace !== '') {
                    $state = -1;
                    continue;
                }
                $name .= $char;
                continue;
            }
            if (ctype_space($char) || $char == '=') {
                $whitespace .= $char;
                if ($char == '=') {
                    $whitespace = '';
                    $state++;
                }
                continue;
            }
            $state = -1;
            continue;
        } elseif ($state == 2) {
            if (ctype_space($char)) {
                continue;
            }
            if ($char == '#') {
                $state = -1;
                continue;
            }
            if ($char == '"' || $char == "'") {
                $quote = $char;
                $value = '';
                $state++;
                continue;
            }
            $quote = '';
            $value = $char;
            $state++;
        } elseif ($state == 3) {
            if ($char == $quote) {
                $state = -1;
                continue;
            }
            if ($quote == '"' && $char == '\\') {
                $char = fgetc($env);
                if ($char === false || $char == "\n" || $char == "\r") {
                    $value .= '\\';
                    $repeat = $char;
                    continue;
                }
                if ($char == 'n') {
                    $value .= "\n";
                } elseif ($char == 'r') {
                    $value .= "\r";
                } elseif ($char == 't') {
                    $value .= "\t";
                } elseif ($char == '"') {
                    $value .= '"';
                } elseif ($char == '\\') {
                    $value .= '\\';
                } elseif ($char == '$') {
                    $value .= '$';
                } else {
                    $value .= "\\$char";
                }
                continue;
            }
            if ($quote != "'" && $char == '$') {
                $value .= $whitespace;
                $whitespace = '';
                $char = fgetc($env);
                if ($char != '{') {
                    $value .= '$';
                    if ($char !== false && $char != "\n" && $char != "\r" && $char != $quote) {
                        $value .= $char;
                    } else {
                        $repeat = $char;
                    }
                    continue;
                }
                $variable = '';
                $state++;
                continue;
            }
            if ($quote == '') {
                if ($char == '#') {
                    $state = -1;
                    continue;
                }
                if (ctype_space($char)) {
                    $whitespace .= $char;
                    continue;
                }
            }
            if ($whitespace !== '') {
                $value .= $whitespace;
                $whitespace = '';
            }
            $value .= $char;
        } elseif ($state == 4) {
            if ($char == '}' && $variable != '') {
                $value .= (string)($_ENV[$variable] ?? getenv($variable));
                $variable = null;
                $state--;
                continue;
            }
            if ($char == '_' || ($variable == '' ? ctype_alpha($char) : ctype_alnum($char))) {
                $variable .= $char;
                continue;
            }
            $value .= '${' . $variable;
            $repeat = $char;
            $variable = null;
            $state--;
        }
    }
    return true;
}