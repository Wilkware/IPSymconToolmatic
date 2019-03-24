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
        //Never delete this line!
        parent::ApplyChanges();
        // Position
        $this->MaintainVariable('Position', 'Position', vtFloat, '', 1, true);
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
     * TSA_GetPosition($id);
     *
     * @return float The actual internal level (position).
     */
    public function GetPosition()
    {
        $vid = $this->ReadPropertyInteger('ReceiverVariable');
        if ($vid != 0) {
            $level = GetValue($vid);
            $this->SendDebug('GetPosition', 'Aktuelle interne Position ist: '.$level);

            return $level;
        } else {
            $this->SendDebug('GetPosition', 'Variable zum auslesen der Rollladenposition nicht gesetzt!');

            return 'Unknown';
        }
    }
}
