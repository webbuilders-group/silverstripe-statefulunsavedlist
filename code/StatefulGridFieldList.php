<?php
class StatefulGridFieldList extends UnsavedRelationList {
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
        if(isset($this->gridField->State->StatefulListData)) {
            $stateList=$this->gridField->State->StatefulListData;
            if(!empty($stateList)) {
                $stateList=$stateList->toArray();
                
                foreach($stateList as $item) {
                    $this->push($item['ID'], $item['extraFields']);
                }
            }else if($sourceList->count()>0) {
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
            
            $stateList[]=array(
                            'ID'=>$item,
                            'extraFields'=>$extraFields
                        );
            
            $this->gridField->State->StatefulListData=$stateList;
        }
        
        parent::push($item, $extraFields);
    }
    
    /**
     * Add a number of items to the relation.
     * @param {array} $items Items to add, as either DataObjects or IDs.
     * @return {StatefulGridFieldList}
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
        if(isset($this->gridField->State->StatefullListData)) {
            $this->gridField->State->StatefulListData=array();
        }
        
        //Remove all from the source list
        parent::removeAll();
    }
    
    /**
     * Remove the items from this list with the given IDs
     * @param {array} $idList
     */
    public function removeMany($items) {
        //Remove from the state
        $stateItems=$this->gridField->State->StatefulListData->toArray();
        if($stateItems && count($stateItems)>0) {
            foreach($items as $item) {
                foreach($stateItems as $key=>$value) {
                    $id=$item;
                    if(is_object($item)) {
                        $id=$item->ID;
                    }
                    
                    if($value['ID']==$id) {
                        unset($stateItems[$key]);
                        break;
                    }
                }
            }
             
            $this->gridField->State->StatefulListData=$stateItems;
        }
        
        //Remove from the source list
        parent::removeMany($items);
        
        return $this;
    }

	/**
	 * Remove this item from this list
	 * @param {mixed} $item 
	 */
	public function remove($item) {
	    $id=$item;
	    if(is_object($item)) {
	        $id=$item->ID;
	    }
	    
        $stateItems=$this->gridField->State->StatefulListData->toArray();
        if($stateItems && count($stateItems)>0) {
	        foreach($stateItems as $key=>$value) {
	            if($value['ID']==$id) {
	                unset($stateItems[$key]);
	                break;
	            }
	        }
	        
	        $this->gridField->State->StatefulListData=$stateItems;
	    }
	    
		parent::remove($id);
	}
    
    /**
     * Removes items from this list which are equal.
     * @param {string} $field unused
     */
    public function removeDuplicates($field='ID') {
        //Remove Duplicates from the state
        if(isset($this->gridField->State->StatefulListData)) {
            $this->gridField->State->StatefulListData=array_unique($this->gridField->State->StatefulListData->toArray(), SORT_REGULAR);
        }
        
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
     * Return the first DataObject with the given ID
     * @param {int} $id ID of the object to retrieve from the list
     * @return {DataObject} Object fetched
     */
    public function byID($id) {
        if(array_search($id, $this->items)!==false) {
            return DataList::create($this->dataClass())->byId($id);
        }
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