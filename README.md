# Usage example

~~~
use alcamo\time\Duration;

$duration = new Duration('P0M1DT02.50S');

echo "$duration\n";

echo $duration->getTotalMinutes() . "\n";

echo $duration->getTotalSeconds() . "\n";
~~~

This will output:

~~~
P1DT2.5S
1440
86402.5
~~~

Unlike PHP's built-in `DateInterval::__construct()`,
 `Duration::__construct()` can handle fractions of seconds.

Furthermore, methods are provided to get the total number of days,
hours, minutes and seconds.

# Provided classes

* `Duration` Duration adding features to `DateInterval`
* `PosixFormat` Posix date/time format

See the doxygen documentation for details.
