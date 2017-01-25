<?

define("IPS_BASE", 10000);
define("IPS_VARIABLEMESSAGE", IPS_BASE + 600);
define("VM_UPDATE", IPS_VARIABLEMESSAGE + 3);

class PresenceDetector extends IPSModule
{
  
  public function Create()
  {
    //Never delete this line!
    parent::Create();
    
    $this->RegisterPropertyInteger("MotionVariable", 0);
    $this->RegisterPropertyInteger("BrightnessVariable", 0);
    $this->RegisterPropertyInteger("ThresholdValue", 0);
    $this->RegisterPropertyInteger("SwitchVariable", 0);
    $this->RegisterPropertyInteger("ScriptVariable", 0);
    $this->RegisterPropertyBoolean("OnlyBool", false);
  }

  public function ApplyChanges()
  {
    if($this->ReadPropertyInteger("MotionVariable") != 0) {
      $this->UnregisterMessage($this->ReadPropertyInteger("MotionVariable"), VM_UPDATE);
    }
    
    //Never delete this line!
    parent::ApplyChanges();
    
    //Create our trigger
    if(IPS_VariableExists($this->ReadPropertyInteger("MotionVariable"))) {
      $this->RegisterMessage($this->ReadPropertyInteger("MotionVariable"), VM_UPDATE);
    }
  }
  
  /**
   * Interne Funktion des SDK.
   * Data[0] = neuer Wert
   * Data[1] = Wert wurde geändert?
   * Data[2] = alter Wert
   *
   * @access public
   */
  public function MessageSink($timeStamp, $senderID, $message, $data)
  {
    $this->SendDebug('MessageSink', 'Time: '. $timeStamp . 'data[0]:' . var_export($data[0]) . ', data[1]:' . var_export($data[1]) . ' ,data[2]:' . var_export($data[2]), 0);

    switch ($message)
    {
      case VM_UPDATE:
        if ($senderID != $this->ReadPropertyInteger("MotionVariable")) {
          $this->SendDebug('MessageSink', $senderID . " unbekannt!", 0);
          break;
        }
        if ($data[0] == true && $data[1] == true) { // OnChange auf TRUE
            SwitchState();
        }
      break;
    }
  }   

  /**
  * This function will be available automatically after the module is imported with the module control.
  * Using the custom prefix this function will be callable from PHP and JSON-RPC through:
  *
  * TPD_SwitchState($id);
  *
  * @access public
  */
  public function SwitchState()
  {
    // Check Brightness
    if ($this->ReadPropertyInteger("BrightnessVariable") <> 0) {
      $bv = GetValue($this->ReadPropertyInteger("BrightnessVariable"));
      $tv = $this->ReadPropertyInteger("ThresholdValue");
      if ($tv != 0 && $bv < $tv) {
        $this->SendDebug('SwitchState', 'Schwellwert nicht erreicht:' . $bv  . '(Soll = ' . $tv . ')', 0);
        return; // nix zu tun
      }
    }
    // Variable schalten          
    if ($this->ReadPropertyInteger("SwitchVariable") <> 0) {
      $sv = $this->ReadPropertyInteger("SwitchVariable");
      if($this->ReadPropertyBoolean("OnlyBool") == true) {
        SetValueBoolean($sv, true);
      }
      else {
        $pid = IPS_GetParent($sv);          
        HM_WriteValueBoolean($pid, "STATE", true); //Gerät einschalten
      }
      $this->SendDebug('SwitchState', "Variable (#" . $sv . ") auf true geschalten!" , 0);
    }
    // Script ausführen
    if ($this->ReadPropertyInteger("ScriptVariable") <> 0) {
      if (IPS_ScriptExists($this->ReadPropertyInteger("ScriptVariable"))) {
        $sr = IPS_RunScript($this->ReadPropertyInteger("ScriptVariable"));
        $this->SendDebug('SwitchState', 'Script Return Value: '. var_export($rs), 0);
      }
    }
  }

  /**
  * This function will be available automatically after the module is imported with the module control.
  * Using the custom prefix this function will be callable from PHP and JSON-RPC through:
  *
  * TPD_SetThreshold($id, $threshold);
  *
  * @access public
  * @param  bool $threshold Helligkeitsschwellwert ab welchem geschalten werden soll.
  * @return bool true if successful, otherwise false.
  */
  public function SetThreshold(integer $threshold)
  {
    if ((($threshold % 5) == 0) && $threshold >= 0 && $threshold <= 50 || $threshold = 75 || $threshold = 100)  {
      IPS_SetProperty($this->InstanceID, "ThresholdValue", $threshold);
      IPS_ApplyChanges($this->InstanceID);
      return true;
    }
    return false;
  }
}

?>