<?php
namespace Acme\Lib;

class EpiTemplate
{
  public $path;
  
  public function __construct($path)
  {
	$this->path = $path;
  }

  /**
   * EpiRoute::display('/path/to/template.php', $array);
   * @name  display
   * @author  Jaisen Mathai <jaisen@jmathai.com>
   * @param string $template
   * @param array $vars
   * @method display
   * @static method
   */
  public function display($template = null, $vars = null)
  {
    $templateInclude = $this->path . '/' . $template;

    if(is_file($templateInclude))
    {
      if(is_array($vars))
      {
        extract($vars);
      }
      
      include $templateInclude;
    }
    else
    {
	  throw new \Exception("Could not load template",404);
	}
  }
  
  /**
   * EpiRoute::get('/path/to/template.php', $array);
   * @name  get
   * @author  Jaisen Mathai <jaisen@jmathai.com>
   * @param string $template
   * @param array $vars
   * @method get
   * @static method
   */
  public function get($template = null, $vars = null)
  {
    $templateInclude = $this->path . '/' . $template;
    if(is_file($templateInclude))
    {
      if(is_array($vars))
      {
        extract($vars);
      }
      ob_start();
      include $templateInclude;
      $contents = ob_get_contents();
      ob_end_clean();
      return $contents;
    }
    else
    {
      throw new \Exception("Could not load template",404);
	}
  }
  
  /**
   * EpiRoute::json($variable); 
   * @name  json
   * @author  Jaisen Mathai <jaisen@jmathai.com>
   * @param mixed $data
   * @return string
   * @method json
   * @static method
   */
  public function json($data)
  {
    if($retval = json_encode($data))
    {
      return $retval;
    }
    else
    {
      $dataDump = var_export($dataDump, 1);
      throw new Exception("json_encode failed for {$dataDump}",404);
	}
  }
  
  /**
   * EpiRoute::jsonResponse($variable); 
   * This method echo's JSON data in the header and to the screen and returns.
   * @name  jsonResponse
   * @author  Jaisen Mathai <jaisen@jmathai.com>
   * @param mixed $data
   * @method jsonResponse
   * @static method
   */
  public function jsonResponse($data)
  {
    $json = self::json($data);
    header('X-JSON: (' . $json . ')');
    header('Content-type: application/x-json');
    echo $json;
  }
}

