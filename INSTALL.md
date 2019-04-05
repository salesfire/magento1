## How to install

### Method 1: Extension Manager

1. Add Salesfire to your magento account (https://marketplace.magento.com/salesfire-salesfire.html)
2. Copy the access keys URL from within your magento marketplace account (Similar to https://connect20.magentocommerce.com/xxxx/salesfire+Salesfire-1.1.0)
3. Navigate to the Magento Connect Manager within your store admin (System > Magento Connect > Magento Connect Manager)
4. Paste the previously copied access keys URL into the `Install New Extensions` section then click Install
5. Click Proceed which will appear below to continue the install
6. Continue to setup

### Method 2: Manual install

1. Access to your server via SSH
2. Download the zip package at https://github.com/salesfire/magento1/archive/master.zip and unzip
3. Copy the `app` and `lib` folder to the root directory of your magento store
4. Continue to setup


## How to setup

After installing you will need to enter your Salesfire details by following the steps below:

1. Navigate to the store configuration (System > Configuration)
2. Navigate to the Salesfire settings (Salesfire > General)
4. Populate the Site ID (This can be found within your Salesfire admin)
5. Mark enabled as Yes
6. Done

