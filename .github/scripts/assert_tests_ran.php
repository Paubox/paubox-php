<?php
/**
 * Fail CI when a passing PHPUnit run does not mean the suite ran.
 *
 * This suite is the reason the check exists. A Windows path in a data provider
 * left it at 8 tests, 8 errors and 0 assertions, and PHPUnit reported only
 * "the data provider is invalid" -- so the failure was invisible. Group
 * exclusions can hide coverage the same way, silently and with exit code 0.
 *
 * Counts what actually executed: skipped tests do not count, and a test that
 * asserted nothing is not coverage.
 */
declare(strict_types=1);

// Floors, not targets. They only trip if coverage regresses.
const MIN_TESTS = 90;
const MIN_ASSERTIONS = 110;

$path = $argv[1] ?? null;
if ($path === null || !is_file($path)) {
    fwrite(STDERR, "usage: {$argv[0]} <junit.xml>\n");
    exit(2);
}

$xml = simplexml_load_file($path);
if ($xml === false) {
    fwrite(STDERR, "FAIL: could not parse {$path}\n");
    exit(1);
}

$total = $executed = $skipped = $errors = $failures = $assertions = 0;

foreach ($xml->xpath('//testcase') as $case) {
    $total++;
    if (count($case->skipped) > 0) {
        $skipped++;
        continue;
    }
    $executed++;
    $assertions += (int) ($case['assertions'] ?? 0);
    $errors     += count($case->error);
    $failures   += count($case->failure);
}

printf(
    "tests=%d executed=%d skipped=%d assertions=%d failures=%d errors=%d\n",
    $total, $executed, $skipped, $assertions, $failures, $errors
);

$problems = [];
if ($errors > 0)   { $problems[] = "{$errors} error(s)"; }
if ($failures > 0) { $problems[] = "{$failures} failure(s)"; }
if ($executed < MIN_TESTS) {
    $problems[] = sprintf('only %d test(s) executed, expected at least %d', $executed, MIN_TESTS);
}
if ($assertions < MIN_ASSERTIONS) {
    $problems[] = sprintf('only %d assertion(s) made, expected at least %d', $assertions, MIN_ASSERTIONS);
}

if ($problems !== []) {
    fwrite(STDERR, 'FAIL: ' . implode('; ', $problems) . "\n");
    exit(1);
}

printf("OK: %d tests executed making %d assertions (%d skipped)\n", $executed, $assertions, $skipped);
