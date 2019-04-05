# Magento 1.x Salesfire Module
Salesfire is a service that provides a number of tools that help to increase sales using various on site methods.

https://www.salesfire.co.uk/


## FAQs

#### Q: Do you offer a free trial?
A: Yes, we offer a free 14 day trial.

#### Q: Is there any additional costs?
A: Yes, we provide the software which helps increase sales for a fee which is tailored to your business. This is to provide you with the best ROI as possible.

You can find out more information and even get a free trial at https://www.salesfire.co.uk/


## How to install

### Method 1: Extension Manager

1. Add Salesfire to your magento account (https://marketplace.magento.com/salesfire-magento1.html)
2. Copy the access keys URL from within your magento marketplace account (Similar to https://connect20.magentocommerce.com/xxxx/salesfire+Salesfire-1.0.1)
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


## Testing

You can setup a test magento using the following script:

```
docker-compose up -d
docker exec -ti <magento web container> install-magento
```

If you want to use sample data, this must be added prior to running the install-magento command.
