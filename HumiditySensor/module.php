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
class HumitidySensor extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Outdoor variables
        $this->RegisterPropertyInteger('TempOutdoor', 0);
        $this->RegisterPropertyInteger('HumyOutdoor', 0);
        // Indoor variables
        $this->RegisterPropertyInteger('TempIndoor', 0);
        $this->RegisterPropertyInteger('HumyIndoor', 0);
        // Dashboard
        $this->RegisterPropertyInteger('ScriptMessage', 0);
        $this->RegisterPropertyString('RoomName', 'Unknown');
        $this->RegisterPropertyInteger('LifeTime', 0);
        // Settings
        $this->RegisterPropertyInteger('UpdateTimer', 15);
        $this->RegisterPropertyBoolean('CreateDewPoint', true);
        $this->RegisterPropertyBoolean('CreateWaterContent', true);

        // Update trigger
        $this->RegisterTimer('UpdateTrigger', 0, "THS_Update(\$_IPS['TARGET']);");
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        // Update Trigger Timer
        $this->SetTimerInterval('UpdateTrigger', 1000 * 60 * $this->ReadPropertyInteger('UpdateTimer'));

        // Profile "THS.AirOrNot"
        $association = [
            [0, 'Nicht Lüften!', 'Window-100', 0xFF0000],
            [1, 'Lüften!', 'Window-0', 0x00FF00],
        ];
        $this->RegisterProfile(IPSVarType::vtBoolean, 'THS.AirOrNot', 'Window', '', '', 0, 0, 0, 0, $association);

        // Profile "THS.WaterContent"
        $association = [
            [0, '%0.2f', '', 0x808080],
        ];
        $this->RegisterProfile(IPSVarType::vtFloat, 'THS.WaterContent', 'Drops', '', ' g/m³', 0, 0, 0, 0, $association);

        // Update Timer
        $minutes = $this->ReadPropertyInteger('UpdateTimer');
        $this->RegisterCyclicTimer('UpdateTimer', 0, $minutes, 0, 'THS_Update('.$this->InstanceID.');', true);

        // Ergebnis & Hinweis
        $this->RegisterVariable(IPSVarType::vtBoolean, 'Hinweis', 'Hint', 'THS.AirOrNot', 1, true);
        $this->RegisterVariable(IPSVarType::vtString, 'Ergebnis', 'Result', '', 2, true);

        // Taupunkt
        $create = $this->ReadPropertyBoolean('CreateDewPoint');
        $this->RegisterVariable(IPSVarType::vtFloat, 'Taupunkt Aussen', 'DewPointOutdoor', '~Temperature', 3, $create);
        $this->RegisterVariable(IPSVarType::vtFloat, 'Taupunkt Innen', 'DewPointIndoor', '~Temperature', 4, $create);

        // Wassergehalt (WaterContent)
        $create = $this->ReadPropertyBoolean('CreateWaterContent');
        $this->RegisterVariable(IPSVarType::vtFloat, 'Wassergehalt Aussen', 'WaterContentOutdoor', 'THS.WaterContent', 5, $create);
        $this->RegisterVariable(IPSVarType::vtFloat, 'Wassergehalt Innen', 'WaterContentIndoor', 'THS.WaterContent', 6, $create);
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * THS_Update($id);
     */
    public function Update()
    {
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSH_Duration($id, $duration);
     *
     * @param int $duration Wartezeit einstellen.
     */
    public function Duration(int $duration)
    {
        IPS_SetProperty($this->InstanceID, 'UpdateTimer', $duration);
        IPS_ApplyChanges($this->InstanceID);
    }

    /**
     * Create the profile for the given associations.
     */
    protected function RegisterProfile($vartype, $name, $icon, $prefix = '', $suffix = '', $minvalue = 0, $maxvalue = 0, $stepsize = 0, $digits = 0, $associations = null)
    {
        if (!IPS_VariableProfileExists($name)) {
            switch ($vartype) {
                case IPSVarType::vtBoolean:
                    $this->RegisterProfileBoolean($name, $icon, $prefix, $suffix, $associations);
                    break;
                case IPSVarType::vtInteger:
                    $this->RegisterProfileInteger($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $associations);
                    break;
                case IPSVarType::vtFloat:
                    $this->RegisterProfileFloat($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $associations);
                    break;
                case IPSVarType::vtString:
                    $this->RegisterProfileString($name, $icon);
                    break;
            }
        }

        return $name;
    }

    protected function RegisterProfileType($name, $type)
    {
        if (!IPS_VariableProfileExists($name)) {
            IPS_CreateVariableProfile($name, $type);
        } else {
            $profile = IPS_GetVariableProfile($name);
            if ($profile['ProfileType'] != $type) {
                throw new Exception('Variable profile type does not match for profile '.$name);
            }
        }
    }

    protected function RegisterProfileBoolean($name, $icon, $prefix, $suffix, $asso)
    {
        $this->RegisterProfileType($name, IPSVarType::vtBoolean);

        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);

        if (count($asso) !== 0) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }

    protected function RegisterProfileInteger($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $step, $digits, $asso)
    {
        $this->RegisterProfileType($name, IPSVarType::vtInteger);

        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileDigits($name, $digits);

        if (count($asso) === 0) {
            $minvalue = 0;
            $maxvalue = 0;
        }
        IPS_SetVariableProfileValues($name, $minvalue, $maxvalue, $step);

        if (count($asso) !== 0) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }

    protected function RegisterProfileFloat($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $step, $digits, $asso)
    {
        $this->RegisterProfileType($name, IPSVarType::vtFloat);

        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileDigits($name, $digits);

        if (count($asso) === 0) {
            $minvalue = 0;
            $maxvalue = 0;
        }
        IPS_SetVariableProfileValues($name, $minvalue, $maxvalue, $step);

        if (count($asso) !== 0) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }

    protected function RegisterProfileString($name, $icon, $prefix, $suffix)
    {
        $this->RegisterProfileType($name, IPSVarType::vtString);

        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileIcon($name, $icon);
    }

    /**
     * Create or delete variable.
     */
    protected function RegisterVariable($vartype, $name, $ident, $profile, $position, $register)
    {
        if ($register == true) {
            switch ($vartype) {
                case IPSVarType::vtBoolean:
                    $objId = $this->RegisterVariableBoolean($ident, $name, $profile, $position);
                    break;
                case IPSVarType::vtInteger:
                    $objId = $this->RegisterVariableInteger($ident, $name, $profile, $position);
                    break;
                case IPSVarType::vtFloat:
                    $objId = $this->RegisterVariableFloat($ident, $name, $profile, $position);
                    break;
                case IPSVarType::vtString:
                    $objId = $this->RegisterVariableString($ident, $name, $profile, $position);
                    break;
            }
        } else {
            $objId = @$this->GetIDForIdent($ident);
            if ($objId > 0) {
                $this->UnregisterVariable($ident);
            }
        }

        return $objId;
    }

    /**
     * Create the cyclic Update Timer.
     *
     * @param string $ident Name and Ident of the Timer.
     * @param string $cId   Client ID .
     */
    protected function RegisterCyclicTimer($ident, $hour, $minute, $second, $script, $active)
    {
        $id = @$this->GetIDForIdent($ident);
        $name = $ident;
        if ($id && IPS_GetEvent($id)['EventType'] != 1) {
            IPS_DeleteEvent($id);
            $id = 0;
        }
        if (!$id) {
            $id = IPS_CreateEvent(1);
            IPS_SetParent($id, $this->InstanceID);
            IPS_SetIdent($id, $ident);
        }
        IPS_SetName($id, $name);
        // IPS_SetInfo($id, "Update Timer");
        // IPS_SetHidden($id, true);
        IPS_SetEventScript($id, $script);
        if (!IPS_EventExists($id)) {
            throw new Exception("Ident with name $ident is used for wrong object type");
        }
        //IPS_SetEventCyclic($id, 0, 0, 0, 0, 0, 0);
        IPS_SetEventCyclicTimeFrom($id, $hour, $minute, $second);
        IPS_SetEventActive($id, $active);
    }
}

/**
 * Helper class for IPS variable types.
 */
class IPSVarType extends stdClass
{
    const vtNone = -1;
    const vtBoolean = 0;
    const vtInteger = 1;
    const vtFloat = 2;
    const vtString = 3;
}
