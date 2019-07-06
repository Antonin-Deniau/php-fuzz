PHP-fuzz
========

Consider this project unstable, I've not yet tweaked the mutation parametters, 
so it will not perform verry well for now. 

This is a general purpose fuzzer for PHP projects.
It's use some sort of genetic algorithm, and tracing via XDEBUG.

Usage example:
--------------

```PHP
namespace MyNamespace;

require __DIR__ . '/../vendor/autoload.php';

use Exception;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

use AntoninDeniau\Fuz\Fuzzer;
use AntoninDeniau\Fuz\Genome;
use AntoninDeniau\Fuz\Mutator;

$logger = new Logger('fuzzer');
$logger->pushHandler(new ErrorLogHandler());

$fuzzer = new Fuzzer($logger, new Mutator());

function test($input) {
    if (strlen($input) !== 7) return false;
    if ($input[0] !== "P") return false;
    if ($input[1] !== "A") return false;
    if ($input[2] !== "S") return false;
    if ($input[3] !== "S") return false;
    if ($input[4] !== "W") return false;
    if ($input[5] !== "O") return false;
    if ($input[6] !== "R") return false;
    if ($input[6] !== "D") return false;
    return True;
}

$t = function ($data) {
    if (test($data)) {
        $this->running = false;
    }
};

$pop = [new Genome("test", 0)];

$fuzzer->fuzz($pop, $t);
```
