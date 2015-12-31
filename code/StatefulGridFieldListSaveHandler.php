<?php
class StatefulGridFieldListSaveHandler implements GridField_SaveHandler
{
    /**
     * Called when a grid field is saved, converts the StatefulGridFieldList to a RelationList
     * @param {GridField} $field
     * @param {DataObjectInterface} $record
     */
    public function handleSave(GridField $grid, DataObjectInterface $record)
    {
        $list=$grid->getList();
        if ($list instanceof StatefulGridFieldList) {
            $relationName=$list->getRelationName();
            
            if ($record->has_many($relationName)) {
                $list->changeToList($record->getComponents($list->getRelationName()));
            } elseif ($record->many_many($relationName)) {
                $list->changeToList($record->getManyManyComponents($list->getRelationName()));
            } else {
                throw new InvalidArgumentException('Record does not have a has_many or many_many relationship called "'.$relationName.'"', null, null);
            }
        }
    }
}
