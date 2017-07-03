<?php

class form {

  protected static $instance=0;
	protected static $elements=array();
  protected static $caption;
  protected static $emptyObjects=array();
  protected static $supported=array("input","file","password","radio","checkbox","select","textarea","hidden");

  const FORM_DATE=1;
  const FORM_IMAGE=2;
  const FORM_PASSWORT=3;
  const FORM_EMAIL=4;
  const FORM_PLZ="/[0-9]{5}/";
  const FORM_NAME=6;

  /**
   * setzt den Namen des Formulars
   * @param  string $caption Name des Formulars
   */
  public function __constuct($caption=""){
    self::$instance++;
    self::$caption=$caption;
  }

  /**
   * löscht die gespeicherten Felder
   */
  public function __destruct(){
    self::$elements=array();
  }

  /**
   * Funktion zum hinzufühgen neuer Formularfelder
   * @param string  $type     Typ des Feldes
   * @param string  $name     Feldname
   * @param string  $caption  Anzeigename des Feldes
   * @param string  $value    Wert des Feldes
   * @param boolean $required Prüfvariable, ob eine Eingabe erfolgen muss
   * @param array   $list     Eingabeliste für bestimmte Felder
   */
  public function add($type,$name,$caption,$value="",$required=false,$list=array()){
    if(in_array($type,self::$supported) && !isset(self::$elements[$name])){
      $regex="";
      if($required===true){
        $regex="/[^\ņ]+/";
      } else if($required!==false){
        $regex=$required;
        $required=true;
      }
      self::$elements[$name]=array("caption"=>$caption,"type"=>$type,"required"=>$required,"regex"=>$regex,"value"=>$value);
      if(count($list)!=0) self::$elements[$name]["list"]=$list;
    }
  }

  /**
   * Parserfunktion zum parsen der Formulardaten 
   * @param  string  $file   Dateipfad
   * @param  array   $Blocks liste der Werte die ersetzt werden sollen
   * @param  boolean $return Prüfvariable, ob Daten ausgegeben oder zurückgegeben werden sollen
   * @return [type]          die geparste Datei
   */
  public function parse($file,$Blocks=array(),$return=false){
    if(!file_exists($file)){
      return false;
    } else {
      $data=file_get_contents($file);
      foreach($Blocks as $block => $replace){
        $data = str_replace("{__".$block."__}", $replace, $data);
      }
      if($return) return $data;
      else        echo $data;
    }
  }

  /**
   * Ausgabe des Formulars
   * @param  string $action Formularwert action
   */
  public function toHtml($action = "?submit"){
    $inputs=""; $checkObject="";
    foreach (self::$elements as $name => $value) {
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


  protected function generateToggle($name){
    if(self::$elements[$name]['required']==true){
      $string='data-toggle="popover" title="Information" data-content="';
      switch (self::$elements[$name]['regex']) {
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
   * prüft, ob alle benötigten Werte übermittelt wurden
   * @return boolean true, wenn alle Werte richitg übertragen wurden
   */
  public function check(){
    foreach (self::$elements as $name => $value) {
      if($value['required']===true && (!isset($_POST[$name]) || empty($_POST[$name]) || !preg_match($value['regex'], $_POST[$name]))){
        self::$emptyObjects[]=$name;
      }
    }
    return (count(self::$emptyObjects)==0);
  }

  /**
   * gibt die Fehlenden Objecke des Formulars zurück, welche nicht ausgefühlt wurden
   * @return array 
   */
  public function getEmptyObjects(){
    return self::$emptyObjects;
  }
  /**
   * prüft, ob ein Formular bereits abgeschickt wurde
   * @param  string  $checkVar Variablenname der GET oder POST Variable
   * @param  boolean $get      Übermittlungsmethode: GET oder POST
   * @param  string  $value    prüf hash, der mit dem Wert der Variable übereinstimmen soll
   * @return boolean           ist übermittelt: ja oder nein
   */
  public function isAvailable($checkVar="submit",$get=true,$value=""){
    if     ($get==true  && isset($_GET[$checkVar])  && $_GET[$checkVar]==$value)  return true;
    else if($get==false && isset($_POST[$checkVar]) && $_POST[$checkVar]==$value) return true;
    else                                                                          return false;
  }

  /**
   * Funktion zum Parsen des Datei Eingabefeldes
   * @param  string $name Index des Formulararrays
   */
  public function file($name){
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.self::$elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <input class="form-control" name="'.$name.'" type="file">';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }

  /**
  * Funktion zum Parsen des Input Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function input($name){
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.self::$elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <input class="form-control" name="'.$name.'" type="text" 
                       value="'.self::$elements[$name]["value"].'" autocomplete="off" '.self::generateToggle($name).'>';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }

  /**
  * Funktion zum Parsen des Hidden Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function hidden($name){
    return '<input name="'.$name.'" type="hidden" value="'.self::$elements[$name]["value"].'" autocomplete="off">';
  }

  /**
  * Funktion zum Parsen des Passwortes Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function password($name){
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label for="inputPassword" class="col-sm-3 control-label">'.self::$elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <input class="form-control" name="'.$name.'" type="password" value="'.self::$elements[$name]["value"].'" autocomplete="off">';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }

  /**
  * Funktion zum Parsen des Textarea Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function textarea($name){
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.self::$elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <textarea class="form-control" rows="5" name="'.$name.'" '.self::generateToggle($name).'>'.self::$elements[$name]["value"].'</textarea>';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }

  /**
  * Funktion zum Parsen einer Liste von Checkboxen Eingabefelder
  * @param  string $name Index des Formulararrays
  */
  public function checkbox($name){
    if(!is_array(self::$elements[$name]["value"])){
      self::$elements[$name]["value"]=array(self::$elements[$name]["value"]);
    }
    $boxes="";
    foreach (self::$elements[$name]["list"] as $value => $caption) {
      $checked="";
      if(in_array($value, self::$elements[$name]["value"])) $checked="CHECKED";
      $boxes.='<div class="checkbox">';
      $boxes.=' <label>';
      $boxes.='   <input type="checkbox" name="'.$name.'[]" value="'.$value.'" '.$checked.'>'.$caption;
      $boxes.=' </label>';
      $boxes.='</div>';
    }
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.self::$elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.=     $boxes;
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }

  /**
  * Funktion zum Parsen einer Liste von Radio Eingabefelder
  * @param  string $name Index des Formulararrays
  */
  public function radio($name){
    $boxes="";
    foreach (self::$elements[$name]["list"] as $value => $caption) {
      $checked="";
      if($value==self::$elements[$name]["value"]) $checked="CHECKED";
      $boxes.='<div class="radio">';
      $boxes.='  <label>';
      $boxes.='    <input type="radio" name="'.$name.'" value="'.$value.'" '.$checked.'>'.$caption;
      $boxes.='  </label>';
      $boxes.='</div>';
    }
    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.self::$elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.=     $boxes;
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }

  /**
  * Funktion zum Parsen des Select Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function select($name){
    if(isset(self::$elements[$name]["list"][self::$elements[$name]["value"]])){
      $boxes.='<option value="'.self::$elements[$name]["value"].'">'.
                self::$elements[$name]["list"][self::$elements[$name]["value"]].'</option>';
      unset(self::$elements[$name]["list"][self::$elements[$name]["value"]]);
    } else {
      $boxes=""; 
    }
    foreach (self::$elements[$name]["list"] as $value => $caption) {
      $boxes.='<option value="'.$value.'">'.$caption.'</option>';
    }

    $html ='<div class="form-group" id="id-'.$name.'">';
    $html.='  <label class="col-sm-3 control-label">'.self::$elements[$name]["caption"].'</label>';
    $html.='  <div class="col-sm-9">';
    $html.='    <select class="form-control" name="'.$name.'">';
    $html.=       $boxes;
    $html.='    </select>';
    $html.='  </div>';
    $html.='</div>';
    return $html;
  }


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
