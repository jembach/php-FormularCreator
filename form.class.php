<?php

/**
 * Class too output a bottstrap formular and prove the user input
 * @category  Bootstrap Formular
 * @package   php-FormularCreator
 * @author    Jonas Embach
 * @copyright Copyright (c) 2017
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link      https://github.com/jembach/php-FormularCreator
 * @version   1.0-master
 */

class form {


  public static $supported=array("input","file","password","radio","checkbox","select","textarea","hidden");
  protected static $instance=0;
	protected var $elements=array();
  protected var $caption;
  protected var $emptyObjects=array();

  const FORM_DATE=1;
  const FORM_IMAGE=2;
  const FORM_PASSWORT=3;
  const FORM_EMAIL=4;
  const FORM_PLZ="/[0-9]{5}/";
  const FORM_NAME=6;


  /**
   * initializer - optional could set the formular caption
   *
   * @param      string  $caption  The caption of the formular
   */
  public function __constuct($caption=""){
    self::$instance++;
    $this->caption=$caption;
  }


  /**
   * destructor - deletes the form elements
   */
  public function __destruct(){
    $this->elements=array();
  }


  /**
   * adds a html formular input 
   *
   * @param      string   $type      The input type - only supported allowed
   * @param      string   $name      The name for input element
   * @param      <type>   $caption   The caption
   * @param      string   $value     The value
   * @param      boolean  $required  if set to false element is optional in the formular
   *                                 - set to true for required or set an regex which is required
   *                                 - or use a defined regex from the class constant
   * @param      array    $list      The list for the select, radio or checkbox elements
   */
  public function add($type,$name,$caption,$value="",$required=false,$list=array()){
    if(in_array($type,self::$supported) && !isset($this->elements[$name])){
      $regex="";
      if($required===true){
        $regex="/[^\ņ]+/";
      } else if($required!==false){
        $regex=$required;
        $required=true;
      }
      $this->elements[$name]=array("caption"=>$caption,"type"=>$type,"required"=>$required,"regex"=>$regex,"value"=>$value);
      if(count($list)!=0) $this->elements[$name]["list"]=$list;
    }
  }


  /**
   * checks if all required elements were successfull send
   *
   * @return     boolean  if true all required elements where send
   */
  public function check(){
    foreach ($this->elements as $name => $value) {
      if($value['required']===true && (!isset($_POST[$name]) || empty($_POST[$name]) || !preg_match($value['regex'], $_POST[$name]))){
        $this->emptyObjects[]=$name;
      }
    }
    return (count($this->emptyObjects)==0);
  }


  /**
   * returns a list of input elements that are required but not 
   * successfully returned
   *
   * @return     array  an array list of the empty input elements by their name
   */
  public function getEmptyObjects(){
    if (count($this->emptyObjects)==0)
      self::check();
    return $this->emptyObjects;
  }


  /**
   * Determines if formular was send
   *
   * @param      string   $checkVar  The GET/POST variable that must be send
   * @param      boolean  $get       decision var to decide if $checkVar was send as GET or POST
   *                                 - true if is GET var - false if is POST var
   * @param      string   $value     if $checkvar must contain a special string
   *
   * @return     boolean  True if available, False otherwise.
   */
  public function isAvailable($checkVar="submit",$get=true,$value=""){
    if     ($get==true  && isset($_GET[$checkVar])  && $_GET[$checkVar]==$value)  return true;
    else if($get==false && isset($_POST[$checkVar]) && $_POST[$checkVar]==$value) return true;
    else                                                                          return false;
  }


  /**
   * outputs the formular
   *
   * @param      string  $action  The GET var to prove if formular was send
   */
  public function toHtml($action = "?submit"){
    $inputs=""; $checkObject="";
    foreach ($this->elements as $name => $value) {
      switch ($value['type']) {
        case 'input':    $inputs.=self::input($name);
                         if($value['required']===true)
                            $checkObject.="checker".self::$instance.".addField(document.getElementsByName(\"".$name."\")[0],".$value['regex'].");\n";
                         break;
        case 'file':     $inputs.=self::file($name);
                         //if($value['required']===true)   
                            //$checkObject.="checker".self::$instance.".addField(document.getElementsByName(\"".$name."\")[0],".$value['regex'].");\n";
                         break;
        case 'password': $inputs.=self::password($name);  
                         if($value['required']===true)
                            $checkObject.="checker".self::$instance.".addField(document.getElementsByName(\"".$name."\")[0],".$value['regex'].");\n";
                         break;
        case 'radio':    $inputs.=self::radio($name);     
                         //if($value['required']===true) 
                            //$checkObject.="checker".self::$instance.".addField(document.getElementsByName(\"".$name."\")[0],".$value['regex'].");\n";
                         break;
        case 'checkbox': $inputs.=self::checkbox($name); 
                         //if($value['required']===true) 
                            //$checkObject.="checker".self::$instance.".addField(document.getElementsByName(\"".$name."\")[0],".$value['regex'].");\n";
                        break;
        case 'select':   $inputs.=self::select($name); 
                         //if($value['required']===true) 
                            //$checkObject.="checker".self::$instance.".addField(document.getElementsByName(\"".$name."\")[0],".$value['regex'].");\n";
                         break;
        case 'textarea': $inputs.=self::textarea($name);  
                         if($value['required']===true)
                            $checkObject.="checker".self::$instance.".addField(document.getElementsByName(\"".$name."\")[0],".$value['regex'].");\n";
                         break;
        case 'hidden':   $inputs.=self::hidden($name);  
                         if($value['required']===true)
                            $checkObject.="checker".self::$instance.".addField(document.getElementsByName(\"".$name."\")[0],".$value['regex'].");\n";
                         break;
      }
    }
    $html ='<div class="col-md-6">';
    $html.='  <form class="form-horizontal" role="form" name="form'.self::$instance.'" id="id-form'.self::$instance.'" enctype="multipart/form-data" onsubmit="return checkForm();" action="'.$action.'" method="POST">';
    $html.=     $inputs;
    $html.='    <div class="form-group">';
    $html.='      <div class="col-sm-push-3 col-sm-9">';
    $html.='        <button type="submit" id="id-submit'.self::$instance.'" class="btn col-xs-12">Submit</button>';
    $html.='      </div>';
    $html.='    </div>';
    $html.='  </form>';
    $html.='</div>';
    $html.=self::generateJS();
    $html.='<script type="text/javascript">';
    $html.='  $(\'[data-toggle="popover"]\').popover({placement: "bottom", trigger:"focus"});';
    $html.='  var checker'.self::$instance.' = new fieldCheck();';
    $html.='  checker'.self::$instance.'.varName = "checker'.self::$instance.'";';
    $html.='  checker'.self::$instance.'.formObject = document.getElementById("id-form'.self::$instance.'");';
    $html.='  checker'.self::$instance.'.setSubmitButton(document.getElementById("id-submit'.self::$instance.'"));';
    $html.=   $checkObject;
    $html.='</script>';
    echo $html;
  }


  /**
   * generate the bootstrap toggle by the regex
   *
   * @param      name  $name   The name of the form element
   *
   * @return     string  the html code for the toggle
   */
  protected function generateToggle($name){
    if($this->elements[$name]['required']==true){
      $string='data-toggle="popover" title="Information" data-content="';
      switch ($this->elements[$name]['regex']) {
        case self::FORM_DATE:     return $string.='Bitte geben Sie das Datum im folgenden Format an: dd.mm.yyyy"';
        case self::FORM_PASSWORT: return $string.='Bitte geben Sie ein 8 stelliges Password an welches Buchstaben, Zahlen und Zeichen enthält"';
        case self::FORM_EMAIL:    return $string.='Beispiel: mmustermann@max.de"';
        case self::FORM_PLZ:      return $string.='Beispiel: 33578"';
        case self::FORM_NAME:     return $string.='Bitte geben Sie nur Buchstaben ein"';
        default: return ""; break;
      }
    } else 
      return "";
  }


  /**
   * returns the html code for a file input
   *
   * @param      string  $name   The name of the form element
   *
   * @return     string  html code
   */
  protected function file($name){
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.$this->elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <input class="form-control" name="'.$name.'" type="file">';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }

  
  /**
   * returns the html code for a text input
   *
   * @param      string  $name   The name of the form element
   *
   * @return     string  html code
   */
  protected function input($name){
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.$this->elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <input class="form-control" name="'.$name.'" type="text" 
                       value="'.$this->elements[$name]["value"].'" autocomplete="off" '.self::generateToggle($name).'>';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }


  /**
   * returns the html code for a hidden input
   *
   * @param      string  $name   The name of the form element
   *
   * @return     string  html code
   */
  protected function hidden($name){
    return '<input name="'.$name.'" type="hidden" value="'.$this->elements[$name]["value"].'" autocomplete="off">';
  }

  
  /**
   * returns the html code for a password input
   *
   * @param      string  $name   The name of the form element
   *
   * @return     string  html code
   */
  protected function password($name){
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label for="inputPassword" class="col-sm-3 control-label">'.$this->elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <input class="form-control" name="'.$name.'" type="password" value="'.$this->elements[$name]["value"].'" autocomplete="off">';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }


  /**
   * returns the html code for a textarea input
   *
   * @param      string  $name   The name of the form element
   *
   * @return     string  html code
   */
  protected function textarea($name){
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.$this->elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <textarea class="form-control" rows="5" name="'.$name.'" '.self::generateToggle($name).'>'.$this->elements[$name]["value"].'</textarea>';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }


  /**
   * returns the html code for a list of checkboxes
   *
   * @param      string  $name   The name of the form element
   *
   * @return     string  html code
   */
  protected function checkbox($name){
    if(!is_array($this->elements[$name]["value"])){
      $this->elements[$name]["value"]=array($this->elements[$name]["value"]);
    }
    $boxes="";
    foreach ($this->elements[$name]["list"] as $value => $caption) {
      $checked="";
      if(in_array($value, $this->elements[$name]["value"])) $checked="CHECKED";
      $boxes.='<div class="checkbox">';
      $boxes.=' <label>';
      $boxes.='   <input type="checkbox" name="'.$name.'[]" value="'.$value.'" '.$checked.'>'.$caption;
      $boxes.=' </label>';
      $boxes.='</div>';
    }
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.$this->elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.=     $boxes;
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }


  /**
   * returns the html code for a list of radio elements
   *
   * @param      string  $name   The name of the form element
   *
   * @return     string  html code
   */
  protected function radio($name){
    $boxes="";
    foreach ($this->elements[$name]["list"] as $value => $caption) {
      $checked="";
      if($value==$this->elements[$name]["value"]) $checked="CHECKED";
      $boxes.='<div class="radio">';
      $boxes.='  <label>';
      $boxes.='    <input type="radio" name="'.$name.'" value="'.$value.'" '.$checked.'>'.$caption;
      $boxes.='  </label>';
      $boxes.='</div>';
    }
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.$this->elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.=     $boxes;
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }


  /**
   * returns the html code for a slect input
   *
   * @param      string  $name   The name of the form element
   *
   * @return     string  html code
   */
  protected function select($name){
    if(isset($this->elements[$name]["list"][$this->elements[$name]["value"]])){
      $boxes.='<option value="'.$this->elements[$name]["value"].'">'.
                $this->elements[$name]["list"][$this->elements[$name]["value"]].'</option>';
      unset($this->elements[$name]["list"][$this->elements[$name]["value"]]);
    } else {
      $boxes=""; 
    }
    foreach ($this->elements[$name]["list"] as $value => $caption) {
      $boxes.='<option value="'.$value.'">'.$caption.'</option>';
    }

    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.$this->elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <select class="form-control" name="'.$name.'">';
    $html.=       $boxes;
    $html.='    </select>';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }


  /**
   * returns the js code that is necessary for the required prove
   *
   * @return     string  js code
   */
  protected function generateJS(){
    $js ='<script type="text/javascript">';
    $js.='  function fieldCheck() {';
    $js.='    this.fields = new Array();';
    $js.='    this.expressions = new Array();';
    $js.='    this.submitButton = null;';
    $js.='    this.varName = "";';
    $js.='    this.formObject = null;';

    $js.='    this.init = function(vn){this.varName = vn;};';

    $js.='    this.addField = function(fieldObj, expression){';
    $js.='      this.fields.push(fieldObj);';
    $js.='      var expressionsID = this.expressions.push(expression);';
    $js.='      fieldObj.setAttribute("onchange",this.varName+".checkAll()");';
    $js.='      //fieldObj.setAttribute("onkeypress","return "+this.varName+".checkSingleObj("+expressionsID+",event);");';
    $js.='    };';
        
    $js.='    this.checkSingleObj = function(fieldID, e) {';
    $js.='      if (!e) { var e = window.event }';
    $js.='      if (e.keyCode) { code = e.keyCode; } else if (e.which) { code = e.which; }';
    $js.='      var character = String.fromCharCode(code);';
                // if they pressed esc... remove focus from field...
    $js.='      if (code == 27) { this.blur(); return false; }';
                // ignore if they are press other keys
                // strange because code: 39 is the down key AND ' key...';
                // and DEL also equals .
    $js.='      if (!e.ctrlKey && code != 9 && code != 8 && code != 36 && code != 37 && code != 38 && (code != 39 || (code == 39 && character == "\'")) && code != 40) {';
    $js.='        if (this.fields[fieldID].value.match(this.expressions[fieldID])) {';
    $js.='          return true;';
    $js.='        } else {';
    $js.='          return false;';
    $js.='        }';
    $js.='      }';
    $js.='    };';
            
    $js.='    this.checkAll = function(){';
    $js.='      var ok = true;';
    $js.='      for(var i = 0; i < this.fields.length;++i){';
    $js.='        var res = this.fields[i].value.trim();';
    $js.='        res = res.match(this.expressions[i]);';
    $js.='        if(res == null || res.length == 0 || res[0] != this.fields[i].value.trim()){';
    $js.='          ok = false;';
    $js.='          document.getElementById("id-"+this.fields[i].name).className+=" has-error";';
    $js.='        } else {';
    $js.='          document.getElementById("id-"+this.fields[i].name).className="form-group";';
    $js.='        }';
    $js.='      }';
    $js.='      return ok;';
    $js.='    };';

    $js.='    this.setSubmitButton = function(button){';
    $js.='      this.submitButton = button;';
    $js.='      this.formObject.setAttribute("onsubmit","return "+this.varName+".checkAll()");';
    $js.='    };';
    $js.='  };';
    $js.='</script>';
    return $js;
  }

}

?>
