<?php

include 'Core/CodeGenerator/CodeGenerator.php';

use App\Core\CodeGenerator\CodeGenerator;

$generator = new CodeGenerator(__DIR__ . '/example.xml');
$generator->generateCode();
