<?php
class StatefulGridField extends GridField {
    /**
     * Creates a new GridField field
     * @param {string} $name Name of the GridField
     * @param {string} $title Title of the GridField
     * @param {SS_List} $dataList Data List to use in the GridField
     * @param {GridFieldConfig} $config GridField Configuration to use
     */
    public function __construct($name, $title = null, SS_List $dataList = null, GridFieldConfig $config = null) {
        parent::__construct($name, $title, $dataList, $config);
        
        //Replace the state with a StatefulGridField_State instance
        $this->state=new StatefullGridField_State($this);
    }
    
    /**
     * Return a Link to this field, if the list is an instance of GridFieldStatefulList the session key for the state is appended to the url
     * @param {string} $action Action to append to the url
     * @return {string} Relative link to this form field
     */
    public function Link($action=null) {
        if($this->list instanceof GridFieldStatefulList) {
            return Controller::join_links(parent::Link($action), '?'.strtolower($this->name).'_skey='.$this->state->getSessionKey());
        }
        
        return parent::Link($action);
    }
    
    /**
	 * Set the datasource. If the list is an instance of UnsavedRelationList the list is converted to a GridFieldStatefulList. If the state is empty then it tries to restore the state from the session.
	 * @param {SS_List} $list List to use in the Grid
	 */
	public function setList(SS_List $list) {
        if($list instanceof UnsavedRelationList) {
            $list=new GridFieldStatefulList($this, $list->getField('baseClass'), $list->getField('relationName'), $list->dataClass());
            
            $stateValue=$this->state->getData()->toArray();
            if(empty($stateValue) && !empty($this->form)) {
                $this->state->restoreFromSession();
            }
        }
        
		return parent::setList($list);
    }
    
    /**
     * Set the container form. If the list is an instance of GridFieldStatefulList and the state is empty it tries to restore the state from the session.
     * @param {Form} $form Form to be used
     */
    public function setForm($form) {
        $return=parent::setForm($form);
        
        if($this->list instanceof GridFieldStatefulList) {
            $stateValue=$this->state->getData()->toArray();
            if(empty($stateValue) && !empty($this->form)) {
                $this->state->restoreFromSession();
            }
        }
        
        return $return;
    }
}

class StatefullGridField_State extends GridState {
    private $_stateSessionKey;
    
    /**
     * Sets the value of the state, if the list is an instance of GridFieldStatefulList than the list is refreshed from the state
     * @param {string} $state Value to be set
     */
    public function setValue($value) {
        parent::setValue($value);
        
        if($this->grid && $this->grid->getList() instanceof GridFieldStatefulList) {
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
		
		//Cache in the session
		$sessionKey=$this->getSessionKey();
		Session::set('FormInfo.'.$this->grid->getForm()->FormName().'.'.$this->grid->getName().'.state.key', $sessionKey);
		Session::set('FormInfo.'.$this->grid->getForm()->FormName().'.'.$this->grid->getName().'.state.value', $value);
		
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
        	            var_dump($value);exit;
        	            $this->setValue($value);
        	        }
        	    }
    	    }
	    }
	}
}
?>