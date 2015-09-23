<?php
class StatefulGridFieldState extends GridState {
    private $_stateSessionKey;
    
    /**
     * Sets the value of the state, if the list is an instance of StatefulGridFieldList than the list is refreshed from the state
     * @param {string} $state Value to be set
     */
    public function setValue($value) {
        parent::setValue($value);
        
        if($this->grid && $this->grid->getList() instanceof StatefulGridFieldList) {
            $this->grid->getList()->refreshFromState();
        }
    }
    
    /**
     * Retrieves the session key from the url or generates a new one if it is not already cached
     * @return {string} State's session key
     */
    public function getSessionKey() {
        if(empty($this->_stateSessionKey)) {
            //If the session key is in the url try loading the state from the session
            if(Controller::has_curr()) {
                $urlSessionKey=Controller::curr()->getRequest()->getVar(strtolower($this->grid->getName()).'_skey');
                if(!empty($urlSessionKey)) {
                    $this->_stateSessionKey=$urlSessionKey;
                }else {
                    //No key so generate a new one
                    $this->_stateSessionKey=sha1(uniqid($this->name));
                }
            }else {
                //No key so generate a new one
                $this->_stateSessionKey=sha1(uniqid($this->name));
            }
        }
        
        return $this->_stateSessionKey;
    }
    
	/**
	 * Returns a json encoded string representation of this state. Also stores the grid state in the session
	 * @return {string}
	 */
	public function Value() {
		$value=parent::Value();
		
		if($this->grid->getForm()) {
    		//Cache in the session
    		$sessionKey=$this->getSessionKey();
    		Session::set('FormInfo.'.$this->grid->getForm()->FormName().'.'.$this->grid->getName().'.state.key', $sessionKey);
    		Session::set('FormInfo.'.$this->grid->getForm()->FormName().'.'.$this->grid->getName().'.state.value', $value);
		}
		
		return $value;
	}
	
	/**
	 * Restores the grid state from the session
	 */
	public function restoreFromSession() {
	    $sessionKey=$this->getSessionKey();
	    
	    if(!empty($this->grid)) {
    	    $form=$this->grid->getForm();
    	    if(!empty($form)) {
        	    //Check that the session's key matches the key we determined if it has then set the value from the session
        	    if(Session::get('FormInfo.'.$this->grid->getForm()->FormName().'.'.$this->grid->getName().'.state.key')==$sessionKey) {
        	        $value=Session::get('FormInfo.'.$this->grid->getForm()->FormName().'.'.$this->grid->getName().'.state.value');
        	        if(!empty($value)) {
        	            $this->setValue($value);
        	        }
        	    }
    	    }
	    }
	}
}
?>