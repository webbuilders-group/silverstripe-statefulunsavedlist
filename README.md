GridField Stateful Unsaved List
=================
GridField component that allows unsaved relation lists for many_many relationships to be stored in the GridField's state and session.

## Maintainer Contact
* Ed Chipman ([UndefinedOffset](https://github.com/UndefinedOffset))

## Requirements
* SilverStripe Framework 3.1.x


## Installation
```
composer require webbuilders-group/silverstripe-statefulunsavedlist
```

If you prefer you may also manually install:
* Download the module from here https://github.com/webbuilders-group/silverstripe-statefulunsavedlist/archive/master.zip
* Extract the downloaded archive into your site root so that the destination folder is called statefulunsavedlist, opening the extracted folder should contain _config.php in the root along with other files/folders
* Run dev/build?flush=all to regenerate the manifest



## Usage
To use this module you need to use ``StatefulGridField`` instead of ``GridField`` when initializing your GridField. Note that at this time this module **does not** support has_many relationships it only supports many_many relationships. You may get undesired effects with a has_many relationship.

#####Before:
```php
$fields->push(new GridField('ExampleRelation', 'Example Relation', $this->ExampleRelation(), GridFieldConfig_RelationEditor::create(10)));
```

#####After:
```php
$fields->push(new StatefulGridField('ExampleRelation', 'Example Relation', $this->ExampleRelation(), GridFieldConfig_RelationEditor::create(10)));
```

##Note about 3rd party components
Some 3rd party components may run into issues with this module since it modifies the address of the field to include a hash in the url. This has is used to remover the GridState from the session should it not be passed as part of the request. 3rd party components that utilize the link of the GridField may not be accounting for the case where the url has parameters already appended to the url. It's recommended that you file an issue on that components module, however you may also want to raise one here first for investigation.
