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

        // Profile "THS.Difference"
        $association = [
            [-500, '%0.2f %%', 'Window-100', 32768],
            [0, '%0.2f %%', 'Window-100', 32768],
            [0.01, '+%0.2f %%', 'Window-100', 16744448],
            [10, '+%0.2f %%', 'Window-0', 16711680],
        ];
        $this->RegisterProfile(IPSVarType::vtFloat, 'THS.Difference', 'Window', '', '', 0, 0, 0, 2, $association);

        // Ergebnis & Hinweis & Differenz
        $this->RegisterVariable(IPSVarType::vtBoolean, 'Hinweis', 'Hint', 'THS.AirOrNot', 1, true);
        $this->RegisterVariable(IPSVarType::vtString, 'Ergebnis', 'Result', '', 2, true);
//        $this->RegisterVariable(IPSVarType::vtFloat, 'Differenz', 'Difference', 'THS.Difference', 3, true);
        $this->MaintainVariable('Difference', 'Differenz', IPSVarType::vtFloat, 'THS.Difference', 3, true);
        // Taupunkt
        $create = $this->ReadPropertyBoolean('CreateDewPoint');
        $this->RegisterVariable(IPSVarType::vtFloat, 'Taupunkt Aussen', 'DewPointOutdoor', '~Temperature', 4, $create);
        $this->RegisterVariable(IPSVarType::vtFloat, 'Taupunkt Innen', 'DewPointIndoor', '~Temperature', 5, $create);

        // Wassergehalt (WaterContent)
        $create = $this->ReadPropertyBoolean('CreateWaterContent');
        $this->RegisterVariable(IPSVarType::vtFloat, 'Wassergehalt Aussen', 'WaterContentOutdoor', 'THS.WaterContent', 6, $create);
        $this->RegisterVariable(IPSVarType::vtFloat, 'Wassergehalt Innen', 'WaterContentIndoor', 'THS.WaterContent', 7, $create);
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * THS_Update($id);
     */
    public function Update()
    {
        $result = 'Ergebnis konnte nicht ermittelt werden!';
        // Daten lesen
        $state = true;
        // Temp Outdoor
        $to = $this->ReadPropertyInteger('TempOutdoor');
        if ($to != 0) {
            $to = GetValue($to);
        } else {
            $this->SendDebug('UPDATE', 'Temperature Outdoor not set!', 0);
            $state = false;
        }
        // Humidity Outddoor
        $ho = $this->ReadPropertyInteger('HumyOutdoor');
        if ($ho != 0) {
            $ho = GetValue($ho);
        } else {
            $this->SendDebug('UPDATE', 'Humidity Outdoor not set!', 0);
            $state = false;
        }
        // Temp indoor
        $ti = $this->ReadPropertyInteger('TempIndoor');
        if ($ti != 0) {
            $ti = GetValue($ti);
        } else {
            $this->SendDebug('UPDATE', 'Temperature Indoor not set!', 0);
            $state = false;
        }
        // Humidity Outddoor
        $hi = $this->ReadPropertyInteger('HumyIndoor');
        if ($hi != 0) {
            $hi = GetValue($hi);
        } else {
            $this->SendDebug('UPDATE', 'Humidity Indoor not set!', 0);
            $state = false;
        }
        // All okay
        if ($state == false) {
            $this->SetValueString('Result', $result);

            return;
        }

        // Minus oder Plus ;-)
        if ($ti >= 0) {
            // Plustemperaturen
            $ao = 7.5;
            $bo = 237.7;
            $ai = $ao;
            $bi = $bo;
        } else {
            // Minustemperaturen
            $ao = 7.6;
            $bo = 240.7;
            $ai = $ao;
            $bi = $bo;
        }

        $rg = 8314.3;
        $m = 18.016;
        $ko = $to + 273.15;
        $ki = $ti + 273.15;

        $so = 6.1078 * pow(10, (($ao * $to) / ($bo + $to)));
        $si = 6.1078 * pow(10, (($ai * $ti) / ($bi + $ti)));

        // DewPoint
        $do = ($ho / 100) * $so;
        $di = ($hi / 100) * $si;

        $vo = log10($do / 6.1078);
        $dpo = $bo * $vo / ($ao - $vo);

        $vi = log10($di / 6.1078);
        $dpi = $bi * $vi / ($ai - $vi);

        $update = $this->ReadPropertyBoolean('CreateDewPoint');
        if ($update == true) {
            $this->SetValue('DewPointOutdoor', $dpo);
            $this->SetValue('DewPointIndoor', $dpi);
        }

        // WaterContent
        $wco = pow(10, 5) * $m / $rg * $do / $ko;
        $wci = pow(10, 5) * $m / $rg * $di / $ki;

        $update = $this->ReadPropertyBoolean('CreateWaterContent');
        if ($update == true) {
            $this->SetValue('WaterContentOutdoor', $wco);
            $this->SetValue('WaterContentIndoor', $wci);
        }

        // Result (diff out / in)
        $wc = $wco - $wci;
        $wcy = ($wci / $wco) * 100;
        $difference = round(($wcy - 100) * 100) / 100;
        if ($wc >= 0) {
            $result = round((100 - $wcy) * 100) / 100 .'% trockener! Draussen ist es feuchter!';
            $hint = false;
        } elseif ($wcy <= 110) {
            $result = 'Zwar ist es innen '.$difference.'% feuchter, aber es lohnt nicht zu lüften!';
            $hint = false;
        } else {
            $result = 'Innen ist es '.$difference.'% feuchter!';
            $hint = true;
        }
        $this->SetValue('Result', $result);
        $this->SetValue('Hint', $hint);
        $this->SetValue('Difference', $difference);

        $scriptId = $this->ReadPropertyInteger('ScriptMessage');
        if ($scriptId != 0 && $hint == true) {
            $room = $this->ReadPropertyString('RoomName');
            $time = $this->ReadPropertyInteger('LifeTime');
            $time = $time * 60;
            if (IPS_ScriptExists($scriptId)) {
                if ($time > 0) {
                    IPS_RunScriptWaitEx($scriptId,
                        ['action'       => 'add', 'text' => $room.' bitte lüften!', 'expires' => time() + $time,
                            'removable' => true, 'type' => 3, 'image' => 'Ventilation', ]);
                } else {
                    IPS_RunScriptWaitEx($scriptId,
                        ['action'       => 'add', 'text' => $room.' bitte lüften!',
                            'removable' => true, 'type' => 3, 'image' => 'Ventilation', ]);
                }
            }
        }
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
