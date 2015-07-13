<?php
class StatefulGridField extends GridField {
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
?>