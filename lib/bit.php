<?php
  function setbit($val, $bit) {
     if (readbit($val, $bit)) return $val;
     return $val += '0x'.dechex(1<<($bit-1));
  }

  function clearbit($val, $bit) {
     if (!readbit($val, $bit)) return $val;
     return $val^(0+('0x'.dechex(1<<($bit-1))));
  }

  function readbit($val, $bit) {
     return ($val&(0+('0x'.dechex(1<<($bit-1)))))?'1':'0';
  }

  function debug($var, $bitlength=32) {
     for ($j=$bitlength;$j>0;$j--) {
        echo readbit($var, $j);
        if ($j%4 == 1) echo ' ';
     }
  }
?>