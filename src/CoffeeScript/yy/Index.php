<?php

namespace CoffeeScript;

#[\AllowDynamicProperties]
class yy_Index extends yy_Base
{
  public $children = array('index');

  function constructor($index)
  {
    $this->index = $index;

    return $this;
  }

  function compile($options, $level = NULL)
  {
    return '['.$this->index->compile($options, LEVEL_PAREN).']';
  }

  function is_complex()
  {
    return $this->index->is_complex();
  }
}

?>
