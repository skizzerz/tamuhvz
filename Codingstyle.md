Any new code checked into the repository **MUST** conform with the following rules:

### Indenting, alignment, and braces
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

### Line Length
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

### Spaces
* Always put a space before and after binary operators
```PHP
// No
$a=$b+$c;

// Yes
$a = $b + $c;
```
* Never put a space between a function name and its opening parenthesis
```PHP
// No
function myFunction ($param1) {

// Yes
function myFunction($param1) {

// No
return myFunction ($a);

// Yes
return myFunction($a);
```
* Always put a space after a control flow keyword (`if`, `elseif`, `while`, `for`, `foreach`, `switch`, `catch`, etc.) and its opening parenthesis
```PHP
// No
if($something) {

// Yes
if ($something) {
```
* Putting a space inside of parenthesis is optional, but if you do it, always only add exactly one space and always make sure to do it both after the open parenthesis and before the close parenthesis. Never put spaces inside of empty parenthesis
```PHP
// OK
if ($something) {

// Also OK
if ( $something ) {

// Not OK
if ( $something) {

// Not OK
if (  $something  ) {

// OK
return myFunction();

// Not OK
return myFunction( );
```
* When type casting, do not put spaces inside of the parenthesis or after the cast operator
```PHP
// Yes
return (int)$var;

// No
return (int) $var;
return ( int )$var;
return ( int ) $var;
```

### Comments
* In comments, there should always be exactly one space between the comment character(s) and the start of the comment. For multiline comments, there should always be an additional asterisk on each line indented by one space
* Do not put multiline-style comments on a single line
* Never use #-style comments
* Doxygen-style comments should leave the first line blank, other multiline comments should not
* Never "box" in a comment with additional characters
* Prefer using multiple single-line comments to normal (`/* */`) multiline comments
```PHP
// Yes: proper inline comment

//No: missing space
# No: do not use #-style comments, always use // for single-line comments
/* No: do not use multiline comments on a single line */

/**
 * An example of a doxygen-style comment (defined by two asterisks in the first line)
 * Note: asterisk for additional lines is indented one additional space to align with the original asterisk.
 * There is still a space between the * and the comment on each line.
 */

/* An example of a normal multiline comment
 * Notice that the top line can be filled out in this case
 */

// Note however that this style of comment
// is preferred instead of the above

/* No: missing asterisk in the middle lines
   Multiline comments like this are invalid
 */
 
// ===========================
// No: do not box in comments
// ===========================

/********************************
 ** No: do not box in comments **
 ********************************/
```
### Naming
* Name functions and class methods using lowerCamelCase
* Name classes and interfaces using CamelCase. Prefix interface names with an I, e.g. ISomethingOrOther
* Use all-uppercase with underscores for naming constants
* Prefer lowerCamelCase for variable names, avoid using underscores
* Do not use prefixes or hungarian notation when naming variables, simply name them descriptive enough so you can come back in five weeks and still know what they are
* Single-letter variable names are fine as long as the place where it is defined and every place where it is used fits onto one screen, and the purpose of the variable is obvious enough just by looking at it
* Class names that define objects should be singular, e.g. class Apple instead of class Apples.
* Method names should be verbs that describe what the method does, e.g. getValue() instead of value().

### Other
* On pure-code files, never include the closing ?> tag
* Never nest ternary operators
* Have error_reporting set to E_ALL. Any form of notice/warning will be a cause for code rejection
* Existing code may not follow all of the above rules. If you fix it, do coding convention fixes in seperate commits/pull requests from actual features
* Use the alternative syntax for control flow when it makes sense to do so (e.g. if you are jumping between PHP and HTML mode in a template file)
* Use all-lowercase for PHP keywords `true`, `false`, and `null`
* Use `elseif` instead of `else if`
