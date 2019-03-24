<?php

require_once __DIR__.'/../libs/traits.php';  // Allgemeine Funktionen

// CLASS ShutterActuator
class ShutterActuator extends IPSModule
{
    use ProfileHelper, DebugHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Shutter variables
        $this->RegisterPropertyInteger('ReceiverVariable', 0);
        $this->RegisterPropertyInteger('TransmitterVariable', 0);
        $this->RegisterPropertyInteger('StopVariable', 0);
        // Position(Level) Variables
        $this->RegisterPropertyFloat('Position0', 1.0);
        $this->RegisterPropertyFloat('Position25', 0.85);
        $this->RegisterPropertyFloat('Position50', 0.70);
        $this->RegisterPropertyFloat('Position75', 0.50);
        $this->RegisterPropertyFloat('Position99', 0.25);
        $this->RegisterPropertyFloat('Position100', 0.0);
    }

    public function ApplyChanges()
    {
        // Trigger???
        if ($this->ReadPropertyInteger('ReceiverVariable') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('ReceiverVariable'), VM_UPDATE);
        }
        // Never delete this line!
        parent::ApplyChanges();
        // Variable Profile
        $association = [
            [0, 'Auf', '', 0x00FF00],
            [25, '25 %%', '', 0x00FF00],
            [50, '50 %%', '', 0x00FF00],
            [75, '75 %%', '', 0x00FF00],
            [99, '99 %%', '', 0x00FF00],
            [100, 'Zu', '', 0xFFFFFF],
        ];
        $this->RegisterProfile(vtInteger, 'HM.ShutterActuator', 'Jalousie', '', '', 0, 100, 0, 0, $association);
        // Position
        $this->MaintainVariable('Position', 'Position', vtInteger, 'HM.ShutterActuator', 1, true);
        // Create our trigger
        if (IPS_VariableExists($this->ReadPropertyInteger('ReceiverVariable'))) {
            $this->RegisterMessage($this->ReadPropertyInteger('ReceiverVariable'), VM_UPDATE);
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
        //$this->SendDebug('MessageSink', 'SenderId: '.$senderID.' Data: '.print_r($data, true), 0);
        switch ($message) {
            case VM_UPDATE:
                // ReceiverVariable
                if ($senderID == $this->ReadPropertyInteger('ReceiverVariable')) {
                    $this->SendDebug('MessageSink', 'SenderID: '.$senderID.' unbekannt!');
                    // Aenderungen auslesen
                    if ($data[1] == true) { // OnChange - neuer Wert?
                        $this->SendDebug('MessageSink', 'Level: '.$data[2].' => '.$data[0]);
                        $this->LevelToPosition($data[0]);
                    } else { // OnChange - keine Zustandsaenderung
                        $this->SendDebug('MessageSink', 'Level unveraendert - keine Wertaenderung');
                    }
                }
                // Position Variable
                $id = $this->GetIDForIdent('Position');
                if ($senderID != $id) {
                    $this->SendDebug('MessageSink', 'SenderID: '.$senderID.' unbekannt!');
                } else {
                    // Aenderungen auslesen
                  if ($data[1] == true) { // OnChange - neuer Wert?
                      $this->SendDebug('MessageSink', 'Position: '.$data[2].' => '.$data[0]);
                  //$this->LevelToPosition($data[0]);
                  } else { // OnChange - keine Zustandsaenderung
                      $this->SendDebug('MessageSink', 'Position unveraendert - keine Wertaenderung');
                  }
                }
            break;
          }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSA_Up($id);
     */
    public function Up()
    {
        $vid = $this->ReadPropertyInteger('TransmitterVariable');
        if ($vid != 0) {
            $this->SendDebug('Up', 'Rollladen hochfahren!');
            RequestAction($vid, 1.0);
        } else {
            $this->SendDebug('Up', 'Variable zum steuern des Rollladens nicht gesetzt!');
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSA_Down($id);
     */
    public function Down()
    {
        $vid = $this->ReadPropertyInteger('TransmitterVariable');
        if ($vid != 0) {
            $this->SendDebug('Down', 'Rollladen runterfahren!');
            RequestAction($vid, 0.0);
        } else {
            $this->SendDebug('Down', 'Variable zum steuern des Rollladens nicht gesetzt!');
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSA_Stop($id);
     */
    public function Stop()
    {
        $vid = $this->ReadPropertyInteger('StopVariable');
        if ($vid != 0) {
            $this->SendDebug('Stop', 'Rollladen angehalten!');
            RequestAction($vid, true);
        } else {
            $this->SendDebug('Stop', 'Variable zum stoppen des Rollladens nicht gesetzt!');
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSA_Level($id);
     *
     * @return float The actual internal level (position).
     */
    public function Level()
    {
        $vid = $this->ReadPropertyInteger('ReceiverVariable');
        if ($vid != 0) {
            $level = GetValue($vid);
            $this->SendDebug('Level', 'Aktuelle interne Position ist: '.$level);

            return sprintf('%.2f', $level);
        } else {
            $this->SendDebug('Level', 'Variable zum auslesen der Rollladenposition nicht gesetzt!');

            return '-1';
        }
    }

    /**
     * Map Level to Position.
     *
     * @param float $level Shutter level value
     */
    private function LevelToPosition(float $level)
    {
        // Position Variable
        $id = $this->GetIDForIdent('Position');
        // Mapping values
        $pos000 = $this->ReadPropertyFloat('Position0');
        $pos025 = $this->ReadPropertyFloat('Position25');
        $pos050 = $this->ReadPropertyFloat('Position50');
        $pos075 = $this->ReadPropertyFloat('Position75');
        $pos099 = $this->ReadPropertyFloat('Position99');
        $pos100 = $this->ReadPropertyFloat('Position100');
        // Level Position - Schalt Position zuweisen
        $pos = 0;
        if ($level == $pos000) {
            $pos = 100;
        } elseif ($level <= $pos099) {
            $pos = 99;
        } elseif ($level > $pos099 && $level <= $pos075) {
            $pos = 75;
        } elseif ($level > $pos075 && $level <= $pos050) {
            $pos = 50;
        } elseif ($level > $pos050 && $level <= $pos025) {
            $pos = 25;
        } else {
            $pos = 0;
        }
        // Zuordnen
        SetValue($id, $pos);
    }
}
