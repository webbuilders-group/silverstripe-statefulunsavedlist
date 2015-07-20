<?php
class GridFieldStatefulList extends UnsavedRelationList {
    protected $gridField;
    
    private $_isSetup=false;
    
    /**
     * Constructor
     * @param {GridField} $gridField GridField Reference
     */
    public function __construct(GridField $gridField, $baseClass, $relationName, $dataClass) {
        $this->gridField=$gridField;
        
        $this->refreshFromState();
        
        parent::__construct($baseClass, $relationName, $dataClass);
    }
    
    /**
     * Refreshes the list from the state
     */
    public function refreshFromState() {
        //Empty the local cache
        parent::removeAll();
        
        $this->_isSetup=true;
        
        //Populate from state
        $stateList=$this->gridField->State->StatefulListData;
        if(!empty($stateList)) {
            $stateList=$stateList->toArray();
            
            foreach($stateList as $item) {
                $this->push($item['ID'], $item['extraFields']);
            }
        }else {
            $this->gridField->State->StatefulListData=array();
        
            //If there are items in the unsaved list ensure we add them to this list
            if($sourceList->count()>0) {
                $items=$sourceList->getField('items');
                $extraFields=$sourceList->getField('extraFields');
        
                foreach($items as $key=>$value) {
                    if(is_object($value)) {
                        $value=$value->ID;
                    }
        
                    $this->push($value, $extraFields[$key]);
                }
            }
        }
        
        $this->_isSetup=false;
    }
    
    /**
     * Pushes an item onto the end of this list.
     * @param {array|object} $item
     */
    public function push($item, $extraFields=null) {
        if(!$this->_isSetup) {
            if(is_object($item)) {
                $item=$item->ID;
            }
            
            
            $stateList=$this->gridField->State->StatefulListData->toArray();
            
            //Verify we're not adding a duplicate
            if($stateList && is_array($stateList) && count($stateList)>0) {
                foreach($stateList as $stateItem) {
                    if($stateItem['ID']==$item) {
                        return;
                    }
                }
            }
            
            $tmp=new stdClass();
            $tmp->ID=$item;
            $tmp->extraFields=$extraFields;
            $stateList[]=$tmp;
            
            $this->gridField->State->StatefulListData=$stateList;
        }
        
        parent::push($item, $extraFields);
    }
    
    /**
     * Add a number of items to the relation.
     * @param {array} $items Items to add, as either DataObjects or IDs.
     * @return {GridFieldStatefulList}
     */
    public function addMany($items) {
        foreach($items as $item) {
			$this->add($item);
		}
        
        return $this;
    }
    
    /**
     * Remove all items from this relation.
     */
    public function removeAll() {
        //Clear the state
        $this->gridField->State->StatefulListData=array();
        
        //Remove all from the source list
        parent::removeAll();
    }
    
    /**
     * Remove the items from this list with the given IDs
     * @param {array} $idList
     */
    public function removeMany($items) {
        //Remove from the state
        $this->gridField->State->StatefulListData=array_diff($this->gridField->State->StatefulListData, $items);
        
        //Remove from the source list
        parent::removeMany($items);
        
        return $this;
    }
    
    /**
     * Removes items from this list which are equal.
     * @param {string} $field unused
     */
    public function removeDuplicates($field='ID') {
        //Remove Duplicates from the state
        $this->gridField->State->StatefulListData=array_unique($this->gridField->State->StatefulListData);
        
        //Remove duplicates from the source list
        parent::removeDuplicates($field);
    }
    
    /**
     * Sets the Relation to be the given ID list. Records will be added and deleted as appropriate.
     * @param {array} $idList List of IDs.
     */
    public function setByIDList($idList) {
        $this->removeAll();
        $this->addMany($idList);
    }
    
    /**
     * Save all the items in this list into the RelationList
     * @param {RelationList} $list
     */
    public function changeToList(RelationList $list) {
        parent::changeToList($list);
    }
    
    /**
     * Get the dataClass name for this relation, ie the DataObject ClassName
     * @return {string}
     */
    public function dataClass() {
        return parent::dataClass();
    }
    
    /**
     * Returns an Iterator for this relation.
     * @return {ArrayIterator}
     */
    public function getIterator() {
        return parent::getIterator();
    }
    
    /**
     * Return an array of the actual items that this relation contains at this stage. This is when the query is actually executed.
     * @return {array}
     */
    public function toArray() {
        return parent::toArray();
    }
    
    /**
     * Returns an array with both the keys and values set to the IDs of the records in this list. Does not return the IDs for unsaved DataObjects
     * @return {array}
     */
    public function getIDList() {
        return parent::getIDList();
    }
    
    /**
     * Returns the first item in the list
     * @return {mixed}
     */
    public function first() {
        return parent::first();
    }
    
    /**
     * Returns the last item in the list
     * @return {mixed}
     */
    public function last() {
        return parent::last();
    }
    
    /**
     * Returns an array of a single field value for all items in the list.
     * @param {string} $colName
     * @return {array}
     */
    public function column($colName='ID') {
        return parent::column($colName);
    }
    
    /**
     * Returns a copy of this list with the relationship linked to the given foreign ID.
     * @param {int|array} $id An ID or an array of IDs.
     */
    public function forForeignID($id) {
        return parent::forForeignID($id);
    }
    
    /**
     * Return the DBField object that represents the given field on the related class.
     * @param {string} $fieldName Name of the field
     * @return {DBField} The field as a DBField object
     */
    public function dbObject($fieldName) {
        return parent::dbObject($fieldName);
    }
    
    /**
     * Return the first DataObject with the given ID
     * @param {int} $id ID of the object to retrieve from the list
     * @return {DataObject} Object fetched
     */
    public function byID($id) {
        return DataList::create($this->dataClass())->byId($id);
    }
    
    /**
     * Wrapper to generate the data query based on the current source list
     * @return {DataQuery}
     */
    public function dataQuery() {
        return DataList::create($this->dataClass())
                        ->filter('ID', $this->getIDList())
                        ->dataQuery();
    }
}
?>