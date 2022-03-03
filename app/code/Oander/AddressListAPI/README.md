# Oander_AddressListAPI

## Overview
The help of module admin can upload CSV with Region and City pairs. Module provides an API to query these data from database.

## Requirements
PHP 7.0

## Compatibility
No cache used
Magento: 2.1

## Dependency
-

## How to install
### Install via copy
Copy files to app/code folder
“`
php bin/magento module:enable Oander_AddressListAPI
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
“`

## Configuration
####Customers / Customer Configuration / Name and Address Options
- State-City CSV Import
  - Store based file upload option with two required column (Region, City)

## Developer info
Module stores the uploaded file Region and City pairs in DB and provide an API for query the database table.
API designed to serve profile and checkout, ui-select fields.