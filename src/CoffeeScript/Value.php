<?php

namespace CoffeeScript;

Init::init();

#[\AllowDynamicProperties]
class Value
{
  function __construct($v)
  {
    $this->v = $v;
  }

  function __toString()
  {
    return $this->v;
  }
}

?>
