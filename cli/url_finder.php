<?php

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/clilib.php');

list($options, $unrecognized) = cli_get_params(array('help' => false), array('h' => 'help'));
if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}
if ($options['help']) {
    $help = "List of http (not https) urls on a site in the DB
Options:
-h, --help            Print out this help
Example:
\$sudo -u www-data /usr/bin/php admin/tool/httpsreplace/cli/url_finder.php  \n";
    echo $help;
    exit(0);
}

$urlfinder = new \local_mrooms\url_finder();
$results = $urlfinder->http_link_stats();
$fp = fopen('php://stdout', 'w');
fputcsv($fp, ['clientsite', 'httpdomain', 'urlcount']);
foreach ($results as $domain => $count) {
    fputcsv($fp, [$SITE->shortname, $domain, $count]);
}
fclose($fp);
