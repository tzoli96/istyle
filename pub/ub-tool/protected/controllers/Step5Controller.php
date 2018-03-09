<?php

include_once('BaseController.php');

/**
 * @todo: Catalog Products migration
 *
 * Class Step5Controller
 */
class Step5Controller extends BaseController
{
    protected $stepIndex = 5;

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
        //get step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $result = UBMigrate::checkStep($step->sorder);
        if ($result['allowed']) {
            //get current setting data
            $settingData = $step->getSettingData();

            //get selected attribute sets
            $selectedAttributeSetIds = UBMigrate::getSetting(3, 'attribute_set_ids');
            //get selected category ids
            $selectedCategoryIds = UBMigrate::getSetting(4, 'category_ids');
            //product types
            $productTypes = array('simple', 'configurable', 'grouped', 'virtual', 'bundle', 'downloadable');
            if (Yii::app()->request->isPostRequest) {
                //check required settings
                if ($selectedAttributeSetIds && $selectedCategoryIds) {
                    //get selected data ids
                    $selectedProductTypes = Yii::app()->request->getParam('product_types', array());
                    $selectedProductTypes = array_unique($selectedProductTypes);
                    $keepOriginalId = Yii::app()->request->getParam('keep_original_id', 0);
                    if ($selectedProductTypes) {
                        //make setting data to save
                        $newSettingData = [
                            'product_types' => $selectedProductTypes,
                            'select_all_product' => (sizeof($selectedProductTypes) == sizeof($productTypes)) ? 1 : 0,
                            'keep_original_id' => $keepOriginalId,
                            'migrated_product_types' => (isset($settingData['migrated_product_types'])) ? array_unique($settingData['migrated_product_types'])  : []
                        ];
                        $step->setting_data = base64_encode(serialize($newSettingData));
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
                        Yii::app()->user->setFlash('note', Yii::t('frontend', 'You must select at least one Product type to migrate or you can skip this step.'));
                    }
                } else {
                    if (!sizeof($selectedAttributeSetIds)) {
                        Yii::app()->user->setFlash('note', Yii::t('frontend', 'Reminder! You have to complete all settings in the step #3 (Attributes) first'));
                    } else if (!sizeof($selectedCategoryIds)) {
                        Yii::app()->user->setFlash('note', Yii::t('frontend', 'Reminder! You have to complete all settings in the step #4 (Categories) first'));
                    }
                }
            }
            $assignData = array(
                'step' => $step,
                'productTypes' => $productTypes,
                'settingData' => $settingData
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
        $step->updateStatus(UBMigrate::STATUS_MIGRATING);
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

        $lvrootcategoryidinmk = 2097;
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
                    $lvmkattributemapping[$lvlvattribute->attribute_id] = $mklvattribute->attribute_id;
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
                }

                $productmappinglvmk = array();
                foreach($lvmkcategorymapping as $lvcategoryid => $mkcategoryid)
                {
                    $mkcategoryproducts = Mage2CatalogCategoryProduct::model()->findAll("category_id={$mkcategoryid}");
                    foreach ($mkcategoryproducts as $mkcategoryproduct)
                    {
                        $mkcategoryproduct->delete();
                    }
                    $lvcategoryproducts = Mage3CatalogCategoryProduct::model()->findAll("category_id={$lvcategoryid}");
                    foreach ($lvcategoryproducts as $lvcategoryproduct)
                    {
                        $mkproductid=null;
                        if(isset($productmappinglvmk[$lvcategoryproduct->product_id]))
                        {
                            $mkproductid = $productmappinglvmk[$lvcategoryproduct->product_id];
                        }
                        else
                        {
                            $lvproduct = Mage3CatalogProductEntity::model()->find("entity_id={$lvcategoryproduct->product_id}");
                            if($lvproduct)
                            {
                                $mkproduct = Mage2CatalogProductEntity::model()->find("sku='{$lvproduct->sku}'");
                                if($mkproduct)
                                {
                                    $productmappinglvmk[$lvcategoryproduct->product_id] = $mkproduct->entity_id;
                                    $mkproductid = $mkproduct->entity_id;
                                }
                            }
                        }
                        if(!is_null($mkproductid))
                        {
                            $this->_migrateCatalogCategoryProduct($mkproductid, $mkcategoryid, $lvcategoryproduct->position);
                        }
                        else
                        {
                            //throw new \Exception("Product not exist in MK db:". $lvcategoryproduct->product_id);
                        }
                    }
                }
            }
            catch (\Exception $exception)
            {
                $this->errors[] = $exception->getMessage();
            }
        }
        if ($this->errors) {
            //update step status
            $step->updateStatus(UBMigrate::STATUS_ERROR);
            $rs['step_status_text'] = $step->getStepStatusText();
            $strErrors = implode('<br/>', $this->errors);
            $rs['errors'] = $strErrors;
        }

        //respond result
        echo json_encode($rs);
        Yii::app()->end();
    }

    private function _migrateCatalogCategoryProduct($productId2, $categoryId2, $position)
    {
        $model = Mage2CatalogCategoryProduct::model()->find("product_id = {$productId2} AND category_id = {$categoryId2}");
        if (!$model) {
            $model = new Mage2CatalogCategoryProduct();
            $model->category_id = $categoryId2;
            $model->product_id = $productId2;
        }
        $model->position = $position;
        if (!$model->save()) {
            throw new Exception("Could not save Mage2CatalogCategoryProduct with data:" . $productId2 . "_" . $position . "_" . $categoryId2);
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
