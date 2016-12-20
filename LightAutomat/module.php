<?

	class LightAutomat extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("StateVariable", 0);
			$this->RegisterPropertyInteger("Duration", 10);
      $this->RegisterTimer("TriggerTimer",0,"TLA_Trigger(\$_IPS['TARGET']);");
		}
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			//Create our trigger
			if(IPS_VariableExists($this->ReadPropertyInteger("StateVariable"))) {
				$eid = @IPS_GetObjectIDByIdent("DurationTrigger", $this->InstanceID);
				if($eid === false) {
					$eid = IPS_CreateEvent(0 /* Trigger */);
					IPS_SetParent($eid, $this->InstanceID);
					IPS_SetIdent($eid, "DurationTrigger");
					IPS_SetName($eid, "TriggerEvent");
				}
				IPS_SetEventTrigger($eid, 0, $this->ReadPropertyInteger("StateVariable"));
				IPS_SetEventScript($eid, "TLA_Activate(\$_IPS['TARGET'], \$_IPS['VALUE']);");
        IPS_SetEventActive($eid, true);
			}
		}

		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* TLA_Activate($id, &value);
		*
		*/
		public function Activate(boolean $value)
		{
      if ($value  == true) {
        // Minutenberechnung = 1000ms * 1min(60s) * Duration
        $this->SetTimerInterval("TriggerTimer", 1000 * 60 * $this->ReadPropertyInteger("Duration"));
      }
      else {
        // Licht wurde schon manuell ausgeschaltet
        $this->SetTimerInterval("TriggerTimer", 0);
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
      if (GetValue($this->ReadPropertyInteger("StateVariable"))==true) {
        $pid = IPS_GetParent($this->ReadPropertyInteger("StateVariable"));
        HM_WriteValueBoolean($pid, "STATE", false); //Gerät ausschalten
        // WFC_PushNotification(xxxxx , 'Licht', '...wurde ausgeschalten!', '', 0);
      }
      $this->SetTimerInterval("TriggerTimer", 0);
    }
	}

?>