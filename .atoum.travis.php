<?php

use \mageekguy\atoum;

require_once '.atoum.php';

$script->addDefaultReport();

$cloverWriter = new atoum\writers\file(__DIR__.'/clover.xml');
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);

$runner->addReport($cloverReport);
