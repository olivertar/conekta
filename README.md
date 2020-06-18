# Conekta Payments extension for Magento 2

This version has been tested with Magento 2.3.0 to 2.3.5

## How to install 

This module depends on the Conekta PHP module, before proceeding make sure it has been installed.
You can find instructions on how to do it [here](https://github.com/conekta/conekta-php)

- Download [the latest version Conekta Payments here](https://github.com/olivertar/conekta/archive/master.zip) 
- Extract `master.zip` file to `app/code/Conekta/Payments` ; You should create a folder path `app/code/Conekta/Payments` if not exist.
- Go to Magento root folder and run upgrade command line to install `Conekta/Payments`:

```
php bin/magento setup:upgrade
bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```
