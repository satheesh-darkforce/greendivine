<?php
/**
 * Browser key for tools/asset-check.php.
 *
 * Copy this file to tools/check-key.php ON THE SERVER (that file is kept
 * out of git) and replace the value with a long random string, e.g. the
 * output of:  php -r "echo bin2hex(random_bytes(16));"
 *
 * Then open: https://greendivine.in/tools/asset-check.php?key=<that value>
 * Leaving tools/check-key.php absent keeps the browser check switched off.
 */
return 'CHANGE-ME-to-a-long-random-value';
