<?php

class WhatiTemplate
{
  public $template_name;
  public $broadcast_name;
  public $parameters;
}

class TemplateParameters
{
  public $name;
  public $value;
}

class Parser{
    function get_string_between($string, $start, $end)
    {

        $inc = 0;
        $result = null;
        $ini = 0;
        $string = ' ' . $string;


        while (true) {

        $ini = strpos($string, $start, $ini);
        if ($ini == 0) break;
        
        $ini += strlen($start);

        $len = strpos($string, $end, $ini) - $ini;
        $res = substr($string, $ini, $len);

        $result[$inc] = $res;
        $ini = strpos($string, $end, $len + $ini);

        $inc++;
        if ($inc > 100) break;
        }

        return $result;
    }
}
