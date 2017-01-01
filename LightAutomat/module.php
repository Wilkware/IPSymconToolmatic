<?

class LightAutomat extends IPSModule
{
  
  public function Create()
  {
    //Never delete this line!
    parent::Create();
    
    $this->RegisterPropertyInteger("StateVariable", 0);
    $this->RegisterPropertyInteger("Duration", 10);
    $this->RegisterPropertyBoolean("ExecScript", false);
    $this->RegisterPropertyInteger("ScriptVariable", 0);
    $this->RegisterPropertyBoolean("OnlyScript", false);
    $this->RegisterTimer("TriggerTimer",0,"TLA_Trigger(\$_IPS['TARGET']);");
  }

  public function ApplyChanges()
  {
    if($this->ReadPropertyInteger("StateVariable") != 0) {
      $this->UnregisterMessage($this->ReadPropertyInteger("StateVariable"), 10603 /*VM_UPDATE*/);
    }

    //Never delete this line!
    parent::ApplyChanges();
    
    //Create our trigger
    if(IPS_VariableExists($this->ReadPropertyInteger("StateVariable"))) {
      $this->RegisterMessage($this->ReadPropertyInteger("StateVariable"), 10603 /*VM_UPDATE*/);
    }
  }
  
  /**
   * Interne Funktion des SDK.
   * Data[0] = neuer Wert
   * Data[1] = Wert wurde geaändert?
   *
   * @access public
   */
  public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
  {
    //$this->SendDebug('Message:SenderID', $SenderID, 0);
    //$this->SendDebug('Message:Message', $Message, 0);
    $this->SendDebug('Message:Data', $Data[0] . " : " . $Data[1], 0);

    switch ($Message)
    {
      case 10603 /*VM_UPDATE*/:
        if ($SenderID != $this->ReadPropertyInteger("StateVariable")) {
          $this->SendDebug('Message:SenderID', $SenderID . " unbekannt!", 0);
          break;
        }
        
        if ($Data[0] == true) {
          // Minutenberechnung = 1000ms * 1min(60s) * Duration
          $this->SetTimerInterval("TriggerTimer", 1000 * 60 * $this->ReadPropertyInteger("Duration"));
        }
        else {
          // Licht(Aktor) wurde schon manuell (aus)geschaltet
          $this->SetTimerInterval("TriggerTimer", 0);
        }
      break;
    }
  }   

  /**
  * This function will be available automatically after the module is imported with the module control.
  * Using the custom prefix this function will be callable from PHP and JSON-RPC through:
  *
  * TLA_Trigger($id);
  *
  */
  public function Trigger()
  {
    if (GetValue($this->ReadPropertyInteger("StateVariable")) == true) {

      if($this->ReadPropertyBoolean("OnlyScript") == false ) {
        $pid = IPS_GetParent($this->ReadPropertyInteger("StateVariable"));
      
        HM_WriteValueBoolean($pid, "STATE", false); //Gerät ausschalten
        $this->SendDebug('TLA_Trigger', "STATE von #" . $pid . "auf false geschalten!" , 0);
        // WFC_PushNotification(xxxxx , 'Licht', '...wurde ausgeschalten!', '', 0);
      }    
      
      if($this->ReadPropertyBoolean("ExecScript") == true) {     
        if ($this->ReadPropertyInteger("ScriptVariable") <> 0) {
          if (IPS_ScriptExists($this->ReadPropertyInteger("ScriptVariable"))) {
              $sr = IPS_RunScriptEx($this->ReadPropertyInteger("ScriptVariable"));
              $this->SendDebug('Script Execute: Return Value', $rs, 0);
          }
        }
        else {
          $this->SendDebug("TLA_Trigger", "Script #" . $this->ReadPropertyInteger('ScriptVariable') . " existiert nicht!",0);
        }
      }
    }
    else {
      $this->SendDebug('TLA_Trigger', "STATE schon FALSE - Timer löschen!" , 0);
    }        
    $this->SetTimerInterval("TriggerTimer", 0);
  }
}

?>