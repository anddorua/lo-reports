<?php
/**
 * Created by PhpStorm.
 * User: andriy
 * Date: 08.12.16
 * Time: 18:22
 */

namespace App\Services;


class BufferReader
{
    private $buffer;
    private $tail;

    /**
     * BufferAnalyzer constructor.
     */
    public function __construct()
    {
        $this->buffer = '';
        $this->tail = 0;
    }

    public function add($fragment, callable $callback)
    {
        $this->buffer .= $fragment;
        if ($pos = strpos($this->buffer, "\n", $this->tail)) {
            $sub_line = substr($this->buffer, $this->tail, $pos - $this->tail);
            $this->tail = $pos + 1;
            $callback($sub_line);
        }
    }

    public function getBuffer()
    {
        return $this->buffer;
    }
}