# Disabled Product Page

## Overview
This module creates an alternative product page for disabled products. It will appear if the product status is disabled but still visible in catalog. 
Except if it's a configurable product, then all the children products needs to be disabled too. 

## Requirements
PHP = 7.0.X

## Compatibility
Magento = 2.1.5

## Dependency
Amasty_Xnotif  
Aheadworks_Autorelated  
Oander_AjaxCaptainHook  

## How to install
### Install via copy/paste
    copy module to app/code/Oander folder
    php bin/magento module:enable Oander_DisabledProductPage
    php bin/magento setup:upgrade
    php bin/magento setup:static-content:deploy

## Configuration
  
Stores > Settings > Configuration > OANDER > Disabled Product Page
####General
- <b>Enabled</b>  
  Type: Select  
  Scope: Store view  
  Options: [Yes, No]  
  Default value: No
  Description: It's enable / disable the module.  
 

- <b>Substitute products title</b>  
  Type: Text  
  Scope: Store view  
  Default value: Substitute products  
  Description: This text will replace the Aheadworks_Autorelated module related products title if the block appears on a disabled product page.  


- <b>Out of stock text</b>  
  Type: Textarea  
  Scope: Store view  
  Default value: -  
  Description: This text appears as an error message under the product sku on a disabled product page.  


- <b>Indexing rule</b>  
  Type: Select  
  Options: Magento\Config\Model\Config\Source\Design\Robots   
    ['value' => 'INDEX,FOLLOW', 'label' => 'INDEX, FOLLOW'],  
    ['value' => 'NOINDEX,FOLLOW', 'label' => 'NOINDEX, FOLLOW'],  
    ['value' => 'INDEX,NOFOLLOW', 'label' => 'INDEX, NOFOLLOW'],  
    ['value' => 'NOINDEX,NOFOLLOW', 'label' => 'NOINDEX, NOFOLLOW']  
  Scope: Store view  
  Default value: INDEX,FOLLOW  
  Description: This will replace the default meta robots tag if it's a disabled product page HTML.


## Developer info

### Backend
When you load a product url the first entry point is   \Oander\DisabledProductPage\Plugin\Magento\Catalog\Controller\Product\View::aroundExecute  
This is where the module decide if the disabled product page should have to show \Oander\DisabledProductPage\Helper\Product::isShowDisabledProductPage .  
Then store this value a registry, because this option only can be true when we are loading a product page.  
The module rewrites several another modules, for all of them using plugin with an after method.


### Frontend
Most of the product page modifications are within the Oander/DisabledProductPage/view/frontend/layout/disabled_catalog_product_view.xml  
Several container and block removes, related product position movement and a custom block for additional info.
 