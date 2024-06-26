<?php

namespace CoffeeScript;

#[\AllowDynamicProperties]
class yy_If extends yy_Base
{
  public $children = array('condition', 'body', 'else_body');

  function constructor($condition, $body, $options = array())
  {
    $this->condition = (isset($options['type']) && $options['type'] === 'unless') ? $condition->invert() : $condition;
    $this->body = $body;
    $this->else_body = NULL;
    $this->is_chain = FALSE;
    $this->soak = isset($options['soak']) ? $options['soak'] : NULL;

    return $this;
  }

  function add_else($else_body)
  {
    if ($this->is_chain())
    {
      $this->else_body_node()->add_else($else_body);
    }
    else
    {
      $this->is_chain = $else_body instanceof yy_If;
      $this->else_body = $this->ensure_block($else_body);
    }

    return $this;
  }

  function body_node()
  {
    return $this->body ? $this->body->unwrap() : NULL;
  }

  function compile_node($options = array())
  {
    return $this->is_statement($options) ? $this->compile_statement($options) : $this->compile_expression($options);
  }

  function compile_expression($options)
  {
    $cond = $this->condition->compile($options, LEVEL_COND);
    $body = $this->body_node()->compile($options, LEVEL_LIST);

    $alt = ($tmp = $this->else_body_node()) ? $tmp->compile($options, LEVEL_LIST) : 'void 0';
    $code = "{$cond} ? {$body} : {$alt}";

    return (isset($options['level']) && $options['level'] >= LEVEL_COND) ? "({$code})" : $code;
  }

  function compile_statement($options)
  {
    $child = del($options, 'chainChild');
    $exeq = del($options, 'isExistentialEquals');

    if ($exeq)
    {
      return yy('If', $this->condition->invert(), $this->else_body_node(), array('type' => 'if'))->compile($options);
    }

    $cond = $this->condition->compile($options, LEVEL_PAREN);
    $options['indent'] .= TAB;
    $body = $this->ensure_block($this->body);
    $if_part = "if ({$cond}) {\n".$body->compile($options)."\n{$this->tab}}";

    if ( ! $child)
    {
      $if_part = $this->tab.$if_part;
    }

    if ( ! $this->else_body)
    {
      return $if_part;
    }

    $ret = $if_part.' else ';

    if ($this->is_chain())
    {
      $options['indent'] = $this->tab;
      $options['chainChild'] = TRUE;

      $ret .= $this->else_body->unwrap()->compile($options, LEVEL_TOP);
    }
    else
    {
      $ret .= "{\n".$this->else_body->compile($options, LEVEL_TOP)."\n{$this->tab}}";
    }

    return $ret;
  }

  function else_body_node()
  {
    return (isset($this->else_body) && $this->else_body) ? $this->else_body->unwrap() : NULL;
  }

  function ensure_block($node)
  {
    return $node instanceof yy_Block ? $node : yy('Block', array($node));
  }

  function is_chain()
  {
    return $this->is_chain;
  }

  function is_statement($options = array())
  {
    return (isset($options['level']) && $options['level'] === LEVEL_TOP) ||
      $this->body_node()->is_statement($options) ||
      (($tmp = $this->else_body_node()) && $tmp->is_statement($options));
  }

  function jumps($options = array())
  {
    $tmp = $this->body->jumps($options);

    if ( ! $tmp && isset($this->else_body))
    {
      $tmp = $this->else_body->jumps($options);
    }

    return $tmp;
  }

  function make_return($res = NULL)
  {
    if ( ! (isset($this->else_body) && $this->else_body))
    {
      if ($res)
      {
        $this->else_body = yy('Block', array(yy('Literal', 'void 0')));
      }
    }

    if ($this->body)
    {
      $this->body = yy('Block', array($this->body->make_return($res)));
    }

    if ($this->else_body)
    {
      $this->else_body = yy('Block', array($this->else_body->make_return($res)));
    }

    return $this;
  }

  function unfold_soak($options = NULL)
  {
    return $this->soak ? $this : FALSE;
  }
}

?>
