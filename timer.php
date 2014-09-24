<?php

// Timer class // License: None, do whatever you want with it.
// Allows for benchmarking page performance.
//-------------------------------------------------------------------------
class Timer {
var $startTime, $endTime, $timeDifference;

        function start()  { $this->startTime = $this->currentTime(); }
        function finish() { $this->endTime = $this->currentTime();   }

        function getTime() {
        $this->timeDifference = $this->endTime - $this->startTime;
        return round($this->timeDifference, 5);
        }


        function currentTime() { list($usec, $sec) = explode(' ',microtime()); return ((float)$usec + (float)$sec); }


}// End Timer class

?>
