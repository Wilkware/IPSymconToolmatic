<?

	class LightAutomat extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("StateVariable", 0);
			$this->RegisterPropertyInteger("Duration", 10);
			
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
					IPS_SetName($eid, "Trigger for #".$this->ReadPropertyInteger("StateVariable"));
				}
				IPS_SetEventTrigger($eid, 0, $this->ReadPropertyInteger("DurationVariable"));
				IPS_SetEventScript($eid, "TLA_Trigger(\$_IPS['TARGET'], \$_IPS['VALUE']));");
        IPS_SetEventActive($eid, false);
			}
			
		}
	
		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* TLA_Trigger($id, $value);
		*
		*/
		public function Trigger(int $id, $value)
		{
			$this->SendDebug("TLA_Trigger", "Id -".$id." mit Wert: ".$value, 0);

		}
	
	}

?>
