<?php

namespace CoffeeScript;

#[\AllowDynamicProperties]
class yy_In extends yy_Base
{
  public $children = array('object', 'array');

  public $negated = NULL;

  function constructor($object = NULL, $array = NULL)
  {
    $this->array = $array;
    $this->object = $object;

    return $this;
  }

  function compile_node($options = array())
  {
    if ($this->array instanceof yy_Value && $this->array->is_array())
    {
      $has_splat = FALSE;

      foreach ($this->array->base->objects as $obj)
      {
        if ($obj instanceof yy_Splat)
        {
          $has_splat = TRUE;
          break;
        }
      }

      if ( ! $has_splat)
      {
        return $this->compile_or_test($options);
      }
    }

    return $this->compile_loop_test($options);
  }

  function compile_or_test($options)
  {
    if (count($this->array->base->objects) === 0)
    {
      return $this->negated ? 'true' : 'false';
    }

    list($sub, $ref) = $this->object->cache($options, LEVEL_OP);
    list($cmp, $cnj) = $this->negated ? array(' !== ', ' && ') : array(' === ', ' || ');

    $tests = array();

    foreach ($this->array->base->objects as $i => $item)
    {
      $tests[] = ($i ? $ref : $sub).$cmp.$item->compile($options, LEVEL_ACCESS);
    }

    if ( ! $tests)
    {
      // In JavaScript '' + false gives 'false', not so in PHP
      return 'false';
    }

    $tests = implode($cnj, $tests);

    return (isset($options['level']) && $options['level'] < LEVEL_OP) ? $tests : "({$tests})";
  }

  function compile_loop_test($options)
  {
    list($sub, $ref) = $this->object->cache($options, LEVEL_LIST);

    $code = utility('indexOf').".call(".$this->array->compile($options, LEVEL_LIST).", {$ref}) "
      .($this->negated ? '< 0' : '>= 0');

    if ($sub === $ref)
    {
      return $code;
    }

    $code = $sub.', '.$code;
    return (isset($options['level']) && $options['level'] < LEVEL_LIST) ? $code : "({$code})";
  }

  function invert()
  {
    $this->negated = ! $this->negated;
    return $this;
  }

  function to_string($idt = '', $name = __CLASS__)
  {
    return parent::to_string($idt, $name.($this->negated ? '!' : ''));
  }
}

?>
