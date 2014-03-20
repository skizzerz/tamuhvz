Any new code checked into the repository **MUST** conform with the following rules:

## Indenting, alignment, and braces
* Indent using a single tab character, never use spaces for indentation. In the event you need a multi-line statement, the second and further lines are indented one extra level.
* Follow the One True Brace Style, where the open brace is on the same line as the statement which introduces it, and the closing brace is at the same indentation level as the beginning of the block. The block contents are indented by one indent:
```PHP
if ($something == 2) {
	echo 'This line is indented by one tab character';
} else {
	echo 'Closing braces are aligned with the "if", and the else/elseif '
		. 'keywords are on the same line as both the open and close brace. '
		. 'Multi-line statements like this one have extra indentation on further lines.';
}
```
* Do not have any trailing whitespace on a line with code. Empty lines may have trailing whitespace at the same indent level as lines around it.
* `case` labels inside of switch statements should be indented one additional tab from the switch itself
```PHP
switch ($something) {
	case 1:
		echo '1';
		break;
	case 2:
	case 3:
		echo '2 or 3';
		break;
	default:
		echo 'Default';
		break;
}
```
* Avoid using vertical alignment, e.g. do **NOT** do the following:
```PHP
$var1          = 'Value';
$somethingelse = 'Something';
$var2          = 'Hi';
```
* Never use multi-line braceless control structures. Single-line braceless control structures are acceptable only if it is returning from a function, calling exit/die, or throwing an exception.
```PHP
// OK
if ($var) {
	echo 'Hi';
}

// Also OK
if ($var) return;

// Not OK
if ($var)
	return;

// Not OK
if ($var) $foo = 2;
```

## Line Length
* Lines should be broken at between 100 and 120 columns. There are exceptions to this, however functions with lots of parameters are not exceptions.
* The operator seperating two lines that were broken up should be placed at the beginning of the next line, with exception of commas, which should end the previous line.
```PHP
return strtolower( $val ) == 'on'
	|| strtolower( $val ) == 'true'
	|| strtolower( $val ) == 'yes';

$var = 'This is a very long string.'
	. ' This is a very long string.';

return reallyLongFunctionName(
	// exception: commas go at the END of the line
	$param1,
	$param2,
	$param3
);
```
