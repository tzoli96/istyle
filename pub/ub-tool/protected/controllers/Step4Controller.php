<?php

include_once('BaseController.php');

/**
 * @todo: Catalog categories migration for LV
 *
 * Class Step4Controller
 */
class Step4Controller extends BaseController
{
    protected $stepIndex = 4;

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
//get step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $result = UBMigrate::checkStep($step->sorder);
        if ($result['allowed']) {
            //get total categories from Magento 1
            $totalCategories = Mage1CatalogCategoryEntity::model()->count("level > 0");
            //get all root categories from Magento1
            $rootCategories = Mage1CatalogCategoryEntity::model()->findAll("level = 1");

            //check over max_input_vars setting
            $maxVars = (int)ini_get('max_input_vars');
            //calculate total vars in form
            $totalVars = $totalCategories;
            $continue = true;
            if ($totalVars > $maxVars) {
                $continue = false;
                $msgNotice = Yii::t('frontend', 'The migration tool detects that you have %s1 variables. Please increase the max_input_vars param in your PHP settings (New value must be bigger than %s1) before continuing this step.', array("%s1" => $totalVars));
                Yii::app()->user->setFlash('note', $msgNotice);
            }

            if (Yii::app()->request->isPostRequest) {
                //$selectAll = Yii::app()->request->getParam('select_all_categories', false);
                //get selected data ids
                $selectedCategoryIds = Yii::app()->request->getParam('category_ids', array());
                if ($selectedCategoryIds) {
                    //make setting data to save
                    $settingData = [
                        'category_ids' => $selectedCategoryIds,
                        'select_all_category' => (sizeof($selectedCategoryIds) == $totalCategories) ? 1 : 0
                    ];
                    $step->setting_data = base64_encode(serialize($settingData));
                    $step->status = UBMigrate::STATUS_SETTING;

                    //save settings data
                    if ($step->update()) {
                        //alert message
                        Yii::app()->user->setFlash('success', "Your settings have been saved successfully");
                        //get next step index
                        $stepIndex = ($this->stepIndex < UBMigrate::MAX_STEP_INDEX) ? ++$this->stepIndex : 1;
                        //go to next step
                        $this->redirect(UBMigrate::getSettingUrl($stepIndex));
                    }
                } else {
                    Yii::app()->user->setFlash('note', Yii::t('frontend', 'You must select at least one Product category to migrate'));
                }
            }

            $assignData = array(
                'step' => $step,
                'totalCategories' => $totalCategories,
                'rootCategories' => $rootCategories,
                'continue' => $continue
            );
            $this->render("setting", $assignData);
        } else {
            Yii::app()->user->setFlash('note', Yii::t('frontend', "Reminder! You need to finish settings in the step #%s", array("%s" => ($result['back_step_index']))));
            $this->redirect($result['back_step_url']);
        }
    }

    /**
     * @todo: Run Migrate data
     */
    public function actionRun()
    {
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $rs = [
            'step_status_text' => $step->getStepStatusText(),
            'step_index' => $this->stepIndex,
            'status' => 'done',
            'message' => 'Finished as expected',
            'percent_up' => 100,
            'errors' => '',
            'offset' => 0
        ];
        Yii::app()->cache->flush();
        $storemapping = array();
        //add store LV->LV
        $storemapping[3] = 14;
        //add store LV->RU
        $storemapping[4] = 15;
        //add store admin
        $storemapping[0] = 0;

        $lvrootcategoryidinmk = 2100;//2097;
        $lvrootcategoryidinlv = 19;
        $lvcategoryentitytypeidinmk = 3;
        $lvcategoryentitytypeidinlv = 3;
        //find lv categories in MK and LV
        $mklvcategories = UBMigrate::getListObjects('Mage2CatalogCategoryEntity', "path like '1/{$lvrootcategoryidinmk}/%' or path = '1/{$lvrootcategoryidinmk}'", -1, -1, "level ASC");
        $lvlvcategories = UBMigrate::getListObjects('Mage3CatalogCategoryEntity', "path like '1/{$lvrootcategoryidinlv}/%' or path = '1/{$lvrootcategoryidinlv}'", -1, -1, "level ASC");

        //find category attributes in MK and LV
        $lvmkattributemapping = array();
        $mklvattributes = UBMigrate::getListObjects('Mage2Attribute', "entity_type_id = '{$lvcategoryentitytypeidinmk}'", -1, -1, "attribute_id ASC");
        $lvlvattributes = UBMigrate::getListObjects('Mage3Attribute', "entity_type_id = '{$lvcategoryentitytypeidinlv}'", -1, -1, "attribute_id ASC");

        //init name attributeid
        $mknameattributeid = null;
        $lvnameattributeid = null;

        /** @var Mage2Attribute $mklvattribute */
        foreach($mklvattributes as $mklvattribute)
        {
            foreach ($lvlvattributes as $lvlvattribute)
            {
                if($lvlvattribute->attribute_code==$mklvattribute->attribute_code)
                {
                    $lvmkattributemapping[$lvlvattribute->attribute_id]['id'] = $mklvattribute->attribute_id;
                }
                if($lvlvattribute->attribute_code=='name')
                {
                    $lvnameattributeid = $lvlvattribute->attribute_id;
                }
            }
            if($mklvattribute->attribute_code=='name')
            {
                $mknameattributeid = $mklvattribute->attribute_id;
            }
        }

        $attributeoptionmappings = array();
        foreach ($lvmkattributemapping as $lvattributeid => $mkattributeinfo)
        {
            $lvlabeloptionidtemp = array();
            $mklabeloptionidtemp = array();

            $temp = UBMigrate::getListObjects('Mage3AttributeOption', "attribute_id = '{$lvattributeid}'", -1, -1, "sort_order ASC");
            if($temp) {
                /** @var Mage3AttributeOption $lvoption */
                foreach ($temp as $lvoption) {
                    $optionlabel = Mage3AttributeOptionValue::model()->find("option_id={$lvoption->option_id} and store_id=0");
                    if ($optionlabel) {
                        $lvlabeloptionidtemp[$optionlabel] = $lvoption->option_id;
                    }
                }
            }
            $temp = UBMigrate::getListObjects('Mage2AttributeOption', "attribute_id = '{$mkattributeinfo['id']}'", -1, -1, "sort_order ASC");
            if($temp) {
                /** @var Mage3AttributeOption $lvoption */
                foreach ($temp as $mkoption) {
                    $optionlabel = Mage2AttributeOptionValue::model()->find("option_id={$mkoption->option_id} and store_id=0");
                    if ($optionlabel) {
                        $mklabeloptionidtemp[$optionlabel] = $mkoption->option_id;
                    }
                }
            }

            foreach($lvlabeloptionidtemp as $label => $id)
            {
                if(isset($mklabeloptionidtemp[$label]))
                {
                    $attributeoptionmappings[$id] = $mklabeloptionidtemp[$label];
                }
                else
                {
                    $this->errors[] = "For LV option:" . $label . " no MK option specified";
                }
            }

            $lvmkattributemapping[$lvattributeid]['options'] = $attributeoptionmappings;
        }
        if (!count($this->errors)) {
            try {
                $lvmkcategorymapping = array();
                /** @var Mage3CatalogCategoryEntity $lvlvcategory */
                foreach ($lvlvcategories as $lvlvcategory) {
                    //kategórianév keresés
                    $lvcategorynamestore0 = Mage3CatalogCategoryEntityVarchar::model()->find("attribute_id={$lvnameattributeid} and store_id=0 and entity_id = {$lvlvcategory->entity_id}");

                    //Ha nem jó akkor valami defaultot állítsunk be, bár lennie kellene
                    $lvcategorynamestore0 = $lvcategorynamestore0 ? $lvcategorynamestore0->value : 'Undefined' . $lvlvcategory->entity_id;

                    /** @var Mage2CatalogCategoryEntity $mklvcategory */
                    foreach ($mklvcategories as $mklvcategory) {
                        $mkcategorynamestore0 = Mage2CatalogCategoryEntityVarchar::model()->find("attribute_id={$mknameattributeid} and store_id=0 and entity_id = {$mklvcategory->entity_id}");
                        $mkcategorynamestore0 = $mkcategorynamestore0 ? $mkcategorynamestore0->value : 'Undefined' . $mklvcategory->entity_id;
                        if ($mklvcategory->level == $lvlvcategory->level && $lvcategorynamestore0 == $mkcategorynamestore0) {
                            //kategória jó szinten van és meg van találva szóval rendeljük össze
                            $lvmkcategorymapping[$lvlvcategory->entity_id] = $mklvcategory->entity_id;
                        }
                    }
                    $this->_migrateCatalogCategory($lvlvcategory, $lvmkcategorymapping, $lvrootcategoryidinlv, $storemapping, $lvmkattributemapping);
                }
            }
            catch (\Exception $exception)
            {
                $this->errors[] = $exception->getMessage();
            }
        }
        if ($this->errors) {
            //update step status

            $strErrors = implode('<br/>', $this->errors);
            $rs['errors'] = $strErrors;
        }

        //respond result
        echo json_encode($rs);
        Yii::app()->end();
    }

    /**
     * @param $lvlvcategory Mage3CatalogCategoryEntity
     * @param $lvmkcategorymapping array
     * @param $lvrootcategoryidinlv int
     * @return null|string
     */
    private function _migrateCatalogCategory($lvlvcategory, &$lvmkcategorymapping, $lvrootcategoryidinlv, $storemapping, $lvmkattributemapping)
    {
        if(!isset($lvmkcategorymapping[$lvlvcategory->entity_id]))
        {
            $category2 = new Mage2CatalogCategoryEntity();
            $category2->entity_id = NULL;
            $category2->attribute_set_id = 3;
            $category2->parent_id = $lvlvcategory->entity_id == $lvrootcategoryidinlv ? 1 : $lvmkcategorymapping[$lvlvcategory->parent_id];
            $category2->created_at = $lvlvcategory->created_at;
            $category2->updated_at = $lvlvcategory->updated_at;
            $category2->path = $lvlvcategory->path;
            $category2->position = $lvlvcategory->position;
            $category2->level = $lvlvcategory->level;
            $category2->children_count = $lvlvcategory->children_count;
        }
        else
        {
            $category2 = Mage2CatalogCategoryEntity::model()->find("entity_id={$lvmkcategorymapping[$lvlvcategory->entity_id]}");
            $category2->parent_id = $lvlvcategory->entity_id == $lvrootcategoryidinlv ? 1 : $lvmkcategorymapping[$lvlvcategory->parent_id];
            $category2->updated_at = $lvlvcategory->updated_at;
            $category2->path = $lvlvcategory->path;
            $category2->position = $lvlvcategory->position;
            $category2->level = $lvlvcategory->level;
            $category2->children_count = $lvlvcategory->children_count;
        }

        if (!$category2->save()) {
            $this->errors[] = get_class($category2) . ": " . UBMigrate::getStringErrors($category2->getErrors());
        }
        if(!isset($lvmkcategorymapping[$lvlvcategory->entity_id]))
        {
            $lvmkcategorymapping[$lvlvcategory->entity_id] = $category2->entity_id;
        }

        //re-update path for this category
        $this->_updatePath($category2, $lvmkcategorymapping);
        //migrate category EAV  data
        $this->_migrateCatalogCategoryEAV($lvlvcategory->entity_id, $category2->entity_id, $storemapping, $lvmkattributemapping);

        return true;
    }

    private function _updatePath($category2, $lvmkcategorymapping)
    {
        $path = explode('/', $category2->path); //1/2/3/4
        $m = (sizeof($path) - 1);
        for ($i = 1; $i < $m; $i++) {
            $path[$i] = $lvmkcategorymapping[$path[$i]];
        }
        $path[$m] = $category2->entity_id;
        $category2->path = implode('/', $path);

        return $category2->update();
    }

    private function _migrateCatalogCategoryEAV($entityId1, $entityId2, $mappingStores, $lvmkattributemapping)
    {
        //get string migrated store ids
        $strStoreIds = implode(',', array_keys($mappingStores));
        /*
         * Get black list attribute ids
         * We do not migrate values of bellow attributes
         * So, we will map to reset values of it to default values
        */

        $eavTables = [
            'catalog_category_entity_datetime',
            'catalog_category_entity_decimal',
            'catalog_category_entity_int',
            'catalog_category_entity_text',
            'catalog_category_entity_varchar'
        ];
        foreach ($eavTables as $table) {
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
            $className1 = "Mage3{$className}";
            $className2 = "Mage2{$className}";
            $models = $className1::model()->findAll("entity_id = {$entityId1} AND store_id IN ({$strStoreIds})");
            if ($models) {
                foreach ($models as $model) {
                    $storeId2 = $mappingStores[$model->store_id];
                    $attributeId2 = isset($lvmkattributemapping[$model->attribute_id])?$lvmkattributemapping[$model->attribute_id]['id']:null;
                    if($model->attribute_id==117)
                    {
                        $kuki=$model->value;
                    }
                    if ($attributeId2) {
                        $condition = "entity_id = {$entityId2} AND attribute_id = {$attributeId2} AND store_id = {$storeId2}";
                        $model2 = $className2::model()->find($condition);
                        if (!$model2) {
                            //add new
                            $model2 = new $className2();
                            $model2->attribute_id = $attributeId2;
                            $model2->store_id = $storeId2;
                            $model2->entity_id = $entityId2;
                        }
                        $model2->value = $model->value;
                        /*if(isset($lvmkattributemapping[$model->attribute_id]['options']))
                        {
                            if(isset($lvmkattributemapping[$model->attribute_id]['options'][$model->value]))
                            {
                                $model2->value = $lvmkattributemapping[$model->attribute_id]['options'][$model->value];
                            }
                            else
                            {
                                throw new \Exception("Attribute option id:'. $model->value . ' not exist in MK store");
                            }
                        }*/
                        //check to change name of the root category
                        if ($table == 'catalog_category_entity_varchar') {
                            if (preg_match("/.html/i", $model2->value)) {
                                $model2->value = str_replace('.html', '', $model2->value);
                            }
                        }
                        //save/update
                        if (!$model2->save()) {
                            throw new \Exception("Can not save option category value model:". $model->attribute_id);
                        } else {
                            $this->_traceInfo();
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _traceInfo()
    {
        if ($this->isCLI) {
            echo ".";
        }
    }

}
