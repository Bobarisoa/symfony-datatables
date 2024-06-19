<?php
namespace Prolyfix\SymfonyDatatablesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ProlyfixSymfonyDatatablesBundle extends Bundle
{
      public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}