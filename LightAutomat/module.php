<?
// --- BASE MESSAGE
if(!defined("IPS_BASE")) {
  define("IPS_BASE", 10000);
}
// --- VARIABLE MANAGER
if (!defined("IPS_VARIABLEMESSAGE")) {
  define("IPS_VARIABLEMESSAGE", IPS_BASE + 600);
  define("VM_UPDATE", IPS_VARIABLEMESSAGE + 3);
}

// CLASS LightAutomat
class LightAutomat extends IPSModule
{
  
  public function Create()
  {
    //Never delete this line!
    parent::Create();
    
    $this->RegisterPropertyInteger("StateVariable", 0);
    $this->RegisterPropertyInteger("Duration", 10);
    $this->RegisterPropertyInteger("MotionVariable", 0);
    $this->RegisterPropertyInteger("PermanentVariable", 0);
    $this->RegisterPropertyBoolean("ExecScript", false);
    $this->RegisterPropertyInteger("ScriptVariable", 0);
    $this->RegisterPropertyBoolean("OnlyBool", false);
    $this->RegisterPropertyBoolean("OnlyScript", false);
    $this->RegisterTimer("TriggerTimer",0,"TLA_Trigger(\$_IPS['TARGET']);");
  }

  public function ApplyChanges()
  {
    if($this->ReadPropertyInteger("StateVariable") != 0) {
      $this->UnregisterMessage($this->ReadPropertyInteger("StateVariable"), VM_UPDATE);
    }
    
    //Never delete this line!
    parent::ApplyChanges();
    
    //Create our trigger
    if(IPS_VariableExists($this->ReadPropertyInteger("StateVariable"))) {
      $this->RegisterMessage($this->ReadPropertyInteger("StateVariable"), VM_UPDATE);
    }
  }
  
  /**
   * Interne Funktion des SDK.
   * data[0] = neuer Wert
   * data[1] = wurde Wert geändert?
   * data[2] = alter Wert
   * data[3] = Timestamp
   *
   * @access public
   */
  public function MessageSink($timeStamp, $senderID, $message, $data)
  {
    // $this->SendDebug('MessageSink', 'SenderId: '. $senderID . ' Data: ' . print_r($data, true), 0);

    switch ($message)
    {
      case VM_UPDATE:
        // Safty Check
        if ($senderID != $this->ReadPropertyInteger("StateVariable")) {
          $this->SendDebug('MessageSink', 'SenderID: ' . $senderID . " unbekannt!", 0);
          break;
        }
        // Dauerbetrieb, tue nix!
        $pid = $this->ReadPropertyInteger("PermanentVariable");
        if ($pid != 0 && GetValue($pid)) {
          $this->SendDebug('MessageSink', "Dauerbetrieb ist angeschalten!", 0);
          break;
        }
        
        if ($data[0] == true && $data[1] == true) { // OnChange auf TRUE, d.h. Angeschalten
          $this->SendDebug('MessageSink', 'OnChange auf TRUE - Angeschalten', 0);
          $this->SetTimerInterval("TriggerTimer", 1000 * 60 * $this->ReadPropertyInteger("Duration"));
        }
        else if ($data[0] == false && $data[1] == true) { // OnChange auf FALSE, d.h. Ausgeschalten
          $this->SendDebug('MessageSink', 'OnChange auf FALSE - Ausgeschalten', 0);
          $this->SetTimerInterval("TriggerTimer", 0);
        }
        else { // OnChange - keine Zustandsaenderung
          $this->SendDebug('MessageSink', 'OnChange unveraendert - keine Zustandsaenderung', 0);
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
  * @access public
  */
  public function Trigger()
  {
    $sv = $this->ReadPropertyInteger("StateVariable");
    if (GetValueBoolean($sv) == true) {
      if($this->ReadPropertyBoolean("OnlyScript") == false ) {
        $mid = $this->ReadPropertyInteger("MotionVariable");
        if($mid != 0 && GetValue($mid)) {
          $this->SendDebug('TLA_Trigger', "Bewegungsmelder aktiv, also nochmal!" , 0);
          return;
        }
        else {
          if($this->ReadPropertyBoolean("OnlyBool") == true) {
            SetValueBoolean($sv, false);
          }
          else {
            $pid = IPS_GetParent($sv);          
            HM_WriteValueBoolean($pid, "STATE", false); //Gerät ausschalten
          }
          $this->SendDebug('TLA_Trigger', "StateVariable (#" . $sv . ") auf false geschalten!" , 0);
          // WFC_PushNotification(xxxxx , 'Licht', '...wurde ausgeschalten!', '', 0);
        }
      }    
      // Script ausführen
      if($this->ReadPropertyBoolean("ExecScript") == true) {     
        if ($this->ReadPropertyInteger("ScriptVariable") <> 0) {
          if (IPS_ScriptExists($this->ReadPropertyInteger("ScriptVariable"))) {
              $sr = IPS_RunScript($this->ReadPropertyInteger("ScriptVariable"));
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

  /**
  * This function will be available automatically after the module is imported with the module control.
  * Using the custom prefix this function will be callable from PHP and JSON-RPC through:
  *
  * TLA_Duration($id, $duration);
  *
  * @access public
  * @param  integer $duration Wartezeit einstellen.
  */
  public function Duration(int $duration)
  {
      IPS_SetProperty($this->InstanceID, "Duration", $duration);
      IPS_ApplyChanges($this->InstanceID);
  }
}

?>