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

    self::parse(LAYOUTDIR."/general/form/form.form",
                  array("formDir"=>LAYOUTDIR."/general/form",
                        "formID"=>self::$instance,
                        "action"=>$action,
                        "checkObjects"=>$checkObject,
                        "inputs"=>$inputs));
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
    return self::parse(LAYOUTDIR."/general/form/file.form",
                  array("caption"=>self::$elements[$name]["caption"],
                        "name"=>$name),true);
  }

  /**
  * Funktion zum Parsen des Input Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function input($name){
    return self::parse(LAYOUTDIR."/general/form/input.form",
                  array("caption"=>self::$elements[$name]["caption"],
                        "name"=>$name,
                        "value"=>self::$elements[$name]["value"],
                        "dataToggle"=>self::generateToggle($name)),true);
  }

  /**
  * Funktion zum Parsen des Hidden Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function hidden($name){
    return self::parse(LAYOUTDIR."/general/form/hidden.form",
                  array("name"=>$name,
                        "value"=>self::$elements[$name]["value"]),true);
  }

  /**
  * Funktion zum Parsen des Passwortes Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function password($name){
    return self::parse(LAYOUTDIR."/general/form/password.form",
                  array("caption"=>self::$elements[$name]["caption"],
                        "name"=>$name,
                        "value"=>self::$elements[$name]["value"],
                        "dataToggle"=>self::generateToggle($name)),true);
  }

  /**
  * Funktion zum Parsen des Textarea Eingabefeldes
  * @param  string $name Index des Formulararrays
  */
  public function textarea($name){
    return self::parse(LAYOUTDIR."/general/form/textarea.form",
                    array("caption"=>self::$elements[$name]["caption"],
                          "name"=>$name,
                          "value"=>self::$elements[$name]["value"],
                          "dataToggle"=>self::generateToggle($name)),true);
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

      $boxes.=self::parse(LAYOUTDIR."/general/form/checkbox.form",
                              array("name"=>$name,
                                    "value"=>$value,
                                    "caption"=>$caption,
                                    "checked"=>$checked),true);
    }
    return self::parse(LAYOUTDIR."/general/form/list.form",
                    array("caption"=>self::$elements[$name]["caption"],
                          "name"=>$name,
                          "input"=>$boxes),true);
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
      $boxes.=self::parse(LAYOUTDIR."/general/form/radio.form",
                              array("name"=>$name,
                                    "value"=>$value,
                                    "caption"=>$caption,
                                    "checked"=>$checked),true);
    }
    return self::parse(LAYOUTDIR."/general/form/list.form",
                    array("caption"=>self::$elements[$name]["caption"],
                          "name"=>$name,
                          "input"=>$boxes),true);
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
    return self::parse(LAYOUTDIR."/general/form/select.form",
                    array("caption"=>self::$elements[$name]["caption"],
                          "name"=>$name,
                          "options"=>$boxes),true);
  }
}

?>
