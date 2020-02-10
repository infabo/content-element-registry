<?php
namespace Digitalwerk\ContentElementRegistry\Utility;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\Field;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Fields
 * @package Digitalwerk\ContentElementRegistry\Utility
 */
class FieldsUtility
{
    /**
     * @param array $array
     * @param string $key
     * @param array $new
     *
     * @return array
     */
    public static function arrayInsertAfter( array $array, $key, array $new ) {
        $keys = array_keys( $array );
        $index = array_search( $key, $keys );
        $pos = false === $index ? count( $array ) : $index + 1;
        return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
    }

    /**
     * @param string $filename
     * @param array $newLine
     * @param array $afterLines
     */
    public static function importStringInToFileAfterString(string $filename, array $newLine, array $afterLines)
    {
        $lines = file($filename);
        $index = 0;
        $editedAfterLines = [];

        if (count($afterLines) === count(array_intersect($afterLines, array_map('trim', $lines)))) {
            foreach ($lines as $line) {
                if (trim($line) === $afterLines[0]) {
                    break;
                }
                $index++;
            }

            for ($oldKey = 0; $oldKey <= count($afterLines)-1; $oldKey++) {
                $editedAfterLines[$index] = $afterLines[$oldKey];
                $index++;
            }

            if (count($editedAfterLines) === count(array_intersect_assoc($editedAfterLines, array_map('trim', $lines)))) {
                $lines = self::arrayInsertAfter($lines, array_search(end($editedAfterLines), array_map('trim', $lines)), $newLine);
                file_put_contents($filename, $lines);
            }
        }
    }

    /**
     * @param $fields
     * @param $name
     * @param $table
     * @param $extraSpace
     * @return string
     * Return field's name with --linebreak-- (format string)
     */
    public static function addFieldsToPalette($fields, $name, $table, $extraSpace)
    {
        if (!empty($fields)) {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(FieldsUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class);
            $createdFields = [];


            foreach ($fieldsToArray as $field) {
                $fieldName = $generalCreateCommandUtility->getFieldName($field);
                $fieldType = $generalCreateCommandUtility->getFieldType($field);

                if ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['isFieldDefault']) {
                    $createdFields[] = '--linebreak--, ' . $fieldType;
                } elseif ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['isFieldDefault'] === false) {
                    $createdFields[] = '--linebreak--, ' . strtolower($name).'_'.$fieldName;
                } else {
//                    Fieldtype does not exist
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.1');
                }
            }
            return preg_replace('/--linebreak--, /', '', implode(",\n" . $extraSpace, $createdFields),1);
        } else {
            return '';
        }
    }

    /**
     * @param $fields
     * @param $table
     * @return bool
     */
    public static function areAllFieldsDefault($fields, $table)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(FieldsUtility::class)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class);

            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['isFieldDefault'] === true) {
                } elseif ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['isFieldDefault'] === false) {

                    return false;
                    break;
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldName($field)
    {
        return explode(',', $field)[0];
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldType($field)
    {
        return explode(',', $field)[1];
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldTitle($field)
    {
        return explode(',', $field)[2];
    }

    /**
     * @param $field
     * @return array
     */
    public function getFieldItems($field)
    {
        $fieldItems = explode('*', explode(',', $field)[3]);
        array_pop($fieldItems);
        return $fieldItems;
    }

    /**
     * @param $field
     * @return string
     */
    public function getFirstFieldItem($field)
    {
        return explode('*', explode(',', $field)[3])[0];
    }

    /**
     * @param $field
     * @return bool
     */
    public function hasItems($field)
    {
        return !empty(self::getFieldItems($field));
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemName($item)
    {
        return explode(';', $item)[0];
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemType($item)
    {
        return explode(';', $item)[1];
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemValue($item)
    {
        return explode(';', $item)[1];
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemTitle($item)
    {
        return explode(';', $item)[2];
    }

    /**
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param $fieldName
     * @param $field
     * @param $relativePath
     * @param $fieldType
     * @param $firstItem
     * @param $relativePathToClass
     * @return string
     */
    public function getFieldConfig($table, $contentElementName, $secondDesignation, $fieldName, $field, $relativePath, $fieldType, $firstItem, $relativePathToClass)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class);
        return $TCAFieldTypes->getTCAFieldTypes($table, $contentElementName, $secondDesignation, $fieldName, $field, $relativePath, $fieldType, $firstItem, $relativePathToClass)[$table][$fieldType]['config'];
    }

    /**
     * @param $table
     * @param $fieldType
     * @return bool
     */
    public function isFieldTypeDefault($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['isFieldDefault'] === true;
    }

    /**
     * @param $table
     * @param $fieldType
     * @return bool
     */
    public function needFieldImportClass($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['needImportClass'] === true;
    }

    /**
     * @param $table
     * @param $fieldType
     * @return bool
     */
    public function needFieldImportClassDefaultFieldName($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['importClassConditional']['needDefaulFieldName'] === true;
    }

    /**
     * @param $table
     * @param $fieldType
     * @return mixed
     */
    public function getFieldDefaultTitle($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['defaultFieldTitle'];
    }

    /**
     * @param $table
     * @param $fieldType
     * @return bool
     */
    public function isFieldTCAItemsAllowed($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['TCAItemsAllowed'] === true;
    }

    /**
     * @param $table
     * @param $fieldType
     * @return bool
     */
    public function isFlexFormTCAItemsAllowed($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['FlexFormItemsAllowed'] === true;
    }

    /**
     * @param $table
     * @param $fieldType
     * @return bool
     */
    public function isFieldInlineItemsAllowed($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['inlineFieldsAllowed'] === true;
    }

    /**
     * @param $table
     * @param $fieldType
     * @return mixed
     */
    public function getFieldDefaultName($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['defaultFieldName'];
    }

    /**
     * @param $table
     * @param $fieldType
     * @return string
     */
    public function getFieldModelDataTypeProperty($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['propertyDataType'];
    }

    /**
     * @param $table
     * @param $fieldType
     * @param $field
     * @param string $inlineRelativePath
     * @return mixed
     */
    public function getFieldModelDataTypePropertyDescribe($table, $fieldType, $field, $inlineRelativePath)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);

        if (self::isFieldInlineItemsAllowed($table, $fieldType)) {
            $fieldItem = self::getFirstFieldItem($field);
            $emptyFieldObject = new Field();
            return GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table,'', '', '',$emptyFieldObject,'','',$fieldItem,'',$inlineRelativePath)[$table][$fieldType]['modelDataTypes']['propertyDataTypeDescribe'];
        } else {
            return $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['propertyDataTypeDescribe'];
        }
    }

    /**
     * @param $table
     * @param $fieldType
     * @return string
     */
    public function getFieldModelDataTypeGetter($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['getterDataType'];
    }

    /**
     * @param $table
     * @param $fieldType
     * @return string
     */
    public function getFieldModelDataTypeGetterDescribe($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['getterDataTypeDescribe'];
    }

    /**
     * @param $table
     * @param $fieldType
     * @return mixed
     */
    public function getFieldTrait($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['trait'];
    }

    /**
     * @param $table
     * @param $fieldType
     * @return mixed
     */
    public function getFieldImportClasses($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return $TCAFieldTypes[$table][$fieldType]['importClass'];
    }

    /**
     * @param $table
     * @param $fieldType
     * @return bool
     */
    public function isFieldTypeExist($table, $fieldType)
    {
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        return !empty($TCAFieldTypes[$table][$fieldType]);
    }

    /**
     * @param $fields
     * @return array
     * Return converted fields from string to array
     */
    public static function fieldsToArray($fields)
    {
        $fieldsToArray = explode('/',$fields);
        array_pop($fieldsToArray);

        if (count($fieldsToArray) === 0 && $fields !== '-') {
            throw new InvalidArgumentException('Field syntax error.');
        }

        foreach ($fieldsToArray as $field) {
            if (count(explode(',', $field)) !== 3) {
                if (count(explode(',', $field)) === 4 && count(explode(';', (new FieldsUtility)->getFirstFieldItem($field))) !== 3) {
                    throw new InvalidArgumentException('Field syntax error.');
                }
                if (count(explode(',', $field)) > 4) {
                    throw new InvalidArgumentException('Field syntax error.');
                }
            }
        }

        return $fieldsToArray;
    }

    /**
     * @param $fields
     * @param $table
     * @param $relativePathToModel [example: 'Domain\Model\ContentElement\']
     * @param $name
     * @param $secondDesignation
     * @param $relativePathToClass
     * @param $relativePathToModel2
     * @return Fields
     */
    public function generateObject($fields, $table, $relativePathToModel, $name, $secondDesignation, $relativePathToClass, $relativePathToModel2)
    {
        $fields = self::fieldsToArray($fields);
        $fieldObjectStorage = new ObjectStorage();

        foreach ($fields as $field) {
            $fieldToObject = new Field();
            $fieldModelDataTypesToObject = new Field\ModelDataTypes();
            $itemObjectStorage = new ObjectStorage();
            $fieldToObject->setName(self::getFieldName($field));
            $fieldToObject->setType(self::getFieldType($field));
            $fieldToObject->setTitle(self::getFieldTitle($field));
            $fieldToObject->setDefault(self::isFieldTypeDefault($table, self::getFieldType($field)));
            $fieldToObject->setExist(self::isFieldTypeExist($table, self::getFieldType($field)));
            $fieldToObject->setDefaultTitle(self::getFieldDefaultTitle($table, self::getFieldType($field)));
            $fieldToObject->setNeedImportClass(self::needFieldImportClass($table, self::getFieldType($field)));
            $fieldToObject->setNeedImportedClassDefaultName(self::needFieldImportClassDefaultFieldName($table, self::getFieldType($field)));
            $fieldToObject->setDefaultName(self::getFieldDefaultName($table, self::getFieldType($field)));
            $fieldToObject->setImportClasses(self::getFieldImportClasses($table, self::getFieldType($field)));
            $fieldToObject->setTCAItemsAllowed(self::isFieldTCAItemsAllowed($table, self::getFieldType($field)));
            $fieldToObject->setFlexFormItemsAllowed(self::isFlexFormTCAItemsAllowed($table, self::getFieldType($field)));
            $fieldToObject->setInlineItemsAllowed(self::isFieldInlineItemsAllowed($table, self::getFieldType($field)));
            $fieldToObject->setTrait(self::getFieldTrait($table, self::getFieldType($field)));
            $fieldToObject->setConfig(self::getFieldConfig($table, $name, $secondDesignation, self::getFieldName($field), $field, $relativePathToModel2, self::getFieldType($field), $this->getFieldItems($field)[0], $relativePathToClass));

            if ($this->hasItems($field)) {
                foreach ($this->getFieldItems($field) as $item) {
                    $itemToObject = new Field\Item();
                    $itemToObject->setName($this->getItemName($item));
                    $itemToObject->setValue($this->getItemValue($item));
                    $itemToObject->setTitle($this->getItemTitle($item));

                    $itemObjectStorage->attach($itemToObject);
                }
                $fieldToObject->setItems($itemObjectStorage);
            }

            $fieldModelDataTypesToObject->setPropertyDataType(self::getFieldModelDataTypeProperty($table, self::getFieldType($field)));
            $fieldModelDataTypesToObject->setGetterDataType(self::getFieldModelDataTypeGetter($table, self::getFieldType($field)));
            $fieldModelDataTypesToObject->setGetterDataTypeDescribe(self::getFieldModelDataTypeGetterDescribe($table, self::getFieldType($field)));
            $fieldModelDataTypesToObject->setPropertyDataTypeDescribe(self::getFieldModelDataTypePropertyDescribe($table, self::getFieldType($field), $field, $relativePathToModel));
            $fieldToObject->setModelDataTypes($fieldModelDataTypesToObject);

            $fieldObjectStorage->attach($fieldToObject);
        }

        $fields = new Fields();
        $fields->setFields($fieldObjectStorage);

        return $fields;
    }
}