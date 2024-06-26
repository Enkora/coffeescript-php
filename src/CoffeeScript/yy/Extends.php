<?php

namespace CoffeeScript;

#[\AllowDynamicProperties]
class yy_Extends extends yy_Base
{
  public $children = array('child', 'parent');

  function constructor($child, $parent)
  {
    $this->child = $child;
    $this->parent = $parent;

    return $this;
  }

  function compile($options, $level = NULL)
  {
    $tmp = yy('Call', yy('Value', yy('Literal', utility('extends'))),
      array($this->child, $this->parent));

    return $tmp->compile($options);
  }
}

?>
