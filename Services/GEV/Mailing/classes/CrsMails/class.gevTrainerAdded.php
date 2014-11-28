<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevTrainerAdded extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Trainer";
	}
	
	public function _getDescription() {
		return "Trainer wird auf Training hinzugefügt";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B07";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseTrainers();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
	
	public function getMail($a_recipient) {
		if ($this->getAdditionalMailSettings()->getSuppressMails()) {
			return null;
		}
		
		return parent::getMail($a_recipient);
	}
}

?>