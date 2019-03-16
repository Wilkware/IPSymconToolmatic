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

        $this->RegisterPropertyString(
            'Mapping', '[{"Position":1,"Level"},
                {"Position": "Auf (0%)","Level": "100"},
                {"Position": "25 %","Level": "85"},
                {"Position": "50 %","Level": "70"},
                {"Position": "75 %","Level": "50"},
                {"Position": "99 %","Level": "25"},
                {"Position": "Zu (100%)","Level": "0"}]'
        );
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        // Position
        $this->MaintainVariable('Position', 'Position', vtFloat, '', 1, true);
    }
}
