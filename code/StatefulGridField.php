<?php
class StatefulGridField extends GridField {
    /**
     * Creates a new GridField field
     * @param string $name
     * @param string $title
     * @param SS_List $dataList
     * @param GridFieldConfig $config
     */
    public function __construct($name, $title = null, SS_List $dataList = null, GridFieldConfig $config = null) {
        parent::__construct($name, $title, $dataList, $config);
        
        
        $this->state=new StatefullGridField_State($this);
    }
    
    /**
	 * Set the datasource
	 * @param SS_List $list
	 */
	public function setList(SS_List $list) {
        if($list instanceof UnsavedRelationList) {
            $list=new GridFieldStatefulList($this, $list->getField('baseClass'), $list->getField('relationName'), $list->dataClass());
        }
        
		return parent::setList($list);
    }
}

class StatefullGridField_State extends GridState {
    public function setValue($value) {
        parent::setValue($value);
        
        if($this->grid && $this->grid->getList() instanceof GridFieldStatefulList) {
            $this->grid->getList()->refreshFromState();
        }
    }
}
?>