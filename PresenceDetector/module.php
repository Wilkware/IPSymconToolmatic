<?php

// --- BASE MESSAGE
if (!defined('IPS_BASE')) {
    define('IPS_BASE', 10000);
}
// --- VARIABLE MANAGER
if (!defined('IPS_VARIABLEMESSAGE')) {
    define('IPS_VARIABLEMESSAGE', IPS_BASE + 600);
    define('VM_UPDATE', IPS_VARIABLEMESSAGE + 3);
}

// CLASS PresenceDetector
class PresenceDetector extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyInteger('MotionVariable', 0);
        $this->RegisterPropertyInteger('BrightnessVariable', 0);
        $this->RegisterPropertyInteger('ThresholdValue', 0);
        $this->RegisterPropertyInteger('SwitchVariable', 0);
        $this->RegisterPropertyInteger('ScriptVariable', 0);
        $this->RegisterPropertyBoolean('OnlyBool', false);
    }

    public function ApplyChanges()
    {
        if ($this->ReadPropertyInteger('MotionVariable') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('MotionVariable'), VM_UPDATE);
        }

        //Never delete this line!
        parent::ApplyChanges();

        //Create our trigger
        if (IPS_VariableExists($this->ReadPropertyInteger('MotionVariable'))) {
            $this->RegisterMessage($this->ReadPropertyInteger('MotionVariable'), VM_UPDATE);
        }
    }

    /**
     * Interne Funktion des SDK.
     * data[0] = neuer Wert
     * data[1] = wurde Wert geändert?
     * data[2] = alter Wert
     * data[3] = Timestamp.
     */
    public function MessageSink($timeStamp, $senderID, $message, $data)
    {
        // $this->SendDebug('MessageSink', 'SenderId: '. $senderID . 'Data: ' . print_r($data, true), 0);
        switch ($message) {
            case VM_UPDATE:
                if ($senderID != $this->ReadPropertyInteger('MotionVariable')) {
                    // Safety Check
                    $this->SendDebug('MessageSink', $senderID.' unbekannt!', 0);
                    break;
                }
                if ($data[0] == true && $data[1] == true) { // OnChange auf TRUE, d.h. Bewegung erkannt
                    $this->SendDebug('MessageSink', 'OnChange auf TRUE - Bewegung erkannt', 0);
                    $this->SwitchState();
                } elseif ($data[0] == false && $data[1] == true) { // OnChange auf FALSE, d.h. keine Bewegung
                    $this->SendDebug('MessageSink', 'OnChange auf FALSE - keine Bewegung', 0);
                } else { // OnChange auf FALSE, d.h. keine Bewegung
                    $this->SendDebug('MessageSink', 'OnChange unveraendert - keine Zustandsaenderung', 0);
                }
            break;
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TPD_SwitchState($id);
     */
    public function SwitchState()
    {
        // Check Brightness
        if ($this->ReadPropertyInteger('BrightnessVariable') != 0) {
            $bv = GetValue($this->ReadPropertyInteger('BrightnessVariable'));
            $tv = $this->ReadPropertyInteger('ThresholdValue');
            if ($tv != 0 && $bv > $tv) {
                $this->SendDebug('SwitchState', 'Oberhalb Schwellwert: '.$bv.'(Schwellwert: '.$tv.')', 0);

                return; // nix zu tun
            }
            $this->SendDebug('SwitchState', 'Immer oder unterhalb Schwellwert: '.$bv.' (Schwellwert: '.$tv.')', 0);
        }
        // Variable schalten
        if ($this->ReadPropertyInteger('SwitchVariable') != 0) {
            $sv = $this->ReadPropertyInteger('SwitchVariable');
            if ($this->ReadPropertyBoolean('OnlyBool') == true) {
                SetValueBoolean($sv, true);
            } else {
                $pid = IPS_GetParent($sv);
                $ret = @HM_WriteValueBoolean($pid, 'STATE', true); //Gerät einschalten
                if ($ret === false) {
                    $this->SendDebug('SwitchState', 'Gerät konnte nicht eingeschalten werden (UNREACH)!', 0);
                }
            }
            $this->SendDebug('SwitchState', 'Variable (#'.$sv.') auf true geschalten!', 0);
        }
        // Script ausführen
        if ($this->ReadPropertyInteger('ScriptVariable') != 0) {
            if (IPS_ScriptExists($this->ReadPropertyInteger('ScriptVariable'))) {
                $sr = IPS_RunScript($this->ReadPropertyInteger('ScriptVariable'));
                $this->SendDebug('SwitchState', 'Script Return Value: '.$rs, 0);
            }
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TPD_SetThreshold($id, $threshold);
     *
     * @param bool $threshold Helligkeitsschwellwert ab welchem geschalten werden soll.
     *
     * @return bool true if successful, otherwise false.
     */
    public function SetThreshold(int $threshold)
    {
        if ((($threshold % 5) == 0) && $threshold >= 0 && $threshold <= 50 || $threshold = 75 || $threshold = 100) {
            IPS_SetProperty($this->InstanceID, 'ThresholdValue', $threshold);
            IPS_ApplyChanges($this->InstanceID);

            return true;
        }

        return false;
    }
}
