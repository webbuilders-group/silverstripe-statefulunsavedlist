<?php
class StatefulGridField extends GridField
{
    /**
     * Creates a new GridField field
     * @param {string} $name Name of the GridField
     * @param {string} $title Title of the GridField
     * @param {SS_List} $dataList Data List to use in the GridField
     * @param {GridFieldConfig} $config GridField Configuration to use
     */
    public function __construct($name, $title = null, SS_List $dataList = null, GridFieldConfig $config = null)
    {
        $this->name=$name;
        
        $state=new StatefulGridFieldState($this);
        //Replace the state with a StatefulGridField_State instance
        $this->state=$state;
        
        parent::__construct($name, $title, $dataList, $config);
        
        //Replace the state with a StatefulGridField_State instance
        $this->state=$state;
        
        $this->getConfig()
                        ->removeComponentsByType('GridState_Component')
                        ->addComponent(new StatefulGridFieldState_Component());
    }
    
    /**
     * Sets the gridfield config ensuring that the save handler is in place
     * @param {GridFieldConfig} $config
     * @return {GridField}
     */
    public function setConfig(GridFieldConfig $config)
    {
        parent::setConfig($config);
        
        //Force the StatefulGridFieldListSaveHandler into the config
        $config->addComponent(new StatefulGridFieldListSaveHandler());
        
        return $this;
    }
    
    /**
     * Return a Link to this field, if the list is an instance of StatefulGridFieldList the session key for the state is appended to the url
     * @param {string} $action Action to append to the url
     * @return {string} Relative link to this form field
     */
    public function Link($action=null)
    {
        if ($this->list instanceof StatefulGridFieldList) {
            return Controller::join_links(parent::Link($action), '?'.strtolower($this->name).'_skey='.$this->state->getSessionKey());
        }
        
        return parent::Link($action);
    }
    
    /**
     * Set the datasource. If the list is an instance of UnsavedRelationList the list is converted to a StatefulGridFieldList. If the state is empty then it tries to restore the state from the session.
     * @param {SS_List} $list List to use in the Grid
     */
    public function setList(SS_List $list)
    {
        if ($list instanceof UnsavedRelationList && !($list instanceof StatefulGridFieldList)) {
            $list=new StatefulGridFieldList($this, $list->getField('baseClass'), $list->getField('relationName'), $list->dataClass());
            
            $stateValue=$this->state->getData()->toArray();
            if (empty($stateValue) && !empty($this->form)) {
                $this->state->restoreFromSession();
            }
        }
        
        return parent::setList($list);
    }
    
    /**
     * Set the container form. If the list is an instance of StatefulGridFieldList and the state is empty it tries to restore the state from the session.
     * @param {Form} $form Form to be used
     */
    public function setForm($form)
    {
        $return=parent::setForm($form);
        
        if ($this->list instanceof StatefulGridFieldList) {
            $stateValue=$this->state->getData()->toArray();
            if (empty($stateValue) && !empty($this->form)) {
                $this->state->restoreFromSession();
            }
        }
        
        return $return;
    }
}
