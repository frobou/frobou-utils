<?php

namespace Frobou\Utils;

class TimeCount {

    private $start;
    private $end;

    public function start()
    {
        $this->start = (float) array_sum(explode(' ', microtime()));
    }

    public function end()
    {
        $this->end = (float) array_sum(explode(' ', microtime()));
    }

    public function getTime($style = 'seconds')
    {
        return sprintf("%.4f", ($this->end - $this->start)) . " seconds";
    }

}
