<?php

namespace Carrito\Framework\Library;

class Log
{
    private $filename;

    public function write($message)
    {
        file_put_contents($this->filename, date('Y-m-d G:i:s').' - '.print_r($message, true), FILE_APPEND);
    }

}
