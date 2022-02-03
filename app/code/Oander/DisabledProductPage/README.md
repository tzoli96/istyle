# Disabled Product Page

## Overview
...

## Requirements
PHP = 7.0.X

## Compatibility
Magento = 2.1.5

## Dependency
Amasty_Xnotif
Aheadworks_Autorelated

## How to install
### Install via composer
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

...

### Frontend

...
 