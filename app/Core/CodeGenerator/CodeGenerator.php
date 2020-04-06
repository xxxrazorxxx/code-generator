<?php

namespace App\Core\CodeGenerator;

include 'Entity.php';

use App\Core\CodeGenerator\Entity;

class CodeGenerator
{
    private $entities = [];

    private $file;

    function __construct($file)
    {
        $this->file = $file;
    }

    function generateCode()
    {
        $this->parseXml();
        foreach ($this->entities as $key => $entity) {
            $entity->createModel();
        }
    }

    function parseXml()
    {
        if (!file_exists($this->file)) {
            return;
        }

        $entities = simplexml_load_file($this->file);

        foreach ($entities as $key => $entity) {
            $this->entities[] = new Entity($entity);
        }
    }
}
