<<<<<<< HEAD
# SimplePay Integration
=======
# Raiffeisen Payment Integration
>>>>>>> origin/aws-deploy-dev

This extension adds a new payment method to Magento 2 for the RaiffeisenPayment.

## Prerequisites

* Linux or Unix server
* Apache 2.2+ or Nginx 1.10+
* PHP 7.0
* Magento Open Source or Magento Commerce 2.2 or higher
* A Raiffeisen merchant account (contact your account representative for any necessary account information)

## Installation

The extension can be installed using Composer (_recommended_) or directly from the Git repository (available on Github).

### From Git

Clone the extension files in your Magento installation's `app/code` directory:

    $ cd /path/to/magento/app/code
    $ mkdir Oander
    $ cd Oander
    $ git clone https://github.com/# Payment

## Post-installation

Complete the installation by running the following Magento commands:

    $ cd /path/to/magento
    $ php bin/magento setup:upgrade
    $ php bin/magento setup:di:compile
    
If the site is running in production deployment mode (`php bin/magento deploy:mode:show`), you will need to run this command as well:

    $ php bin/magento setup:static-content:deploy

## Configuration

All necessary configuration to get started is done in the Magento Admin by going to "Stores > Settings > Configuration > Payment > Raiffeisen". For more details, please see the User Guide.

## Usage

Once configured, no further interaction with the merchant is required.

## Troubleshooting

The extension creates the following log files:

* "/path/to/magento/var/log/oander_raiffeisen_error.log"

## Removal

Removal of the extension's files depends on the method of installation.

### Installed From Git

If the extension was installed using the package from Github, please run the following commands to uninstall it:

    $ cd /path/to/magento
    $ rm -r app/code/Oander/RaiffeisenPayment

## History

Please see the file CHANGES.md for a full history of changes by version. 
