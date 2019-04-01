# Salesfire Magento 1


## Testing

You can setup a test magento using the following script:

```
docker-compose up -d
docker exec -ti magento1_web_1 install-magento
```

If you want to use sample data, this must be added prior to running the install-magento command.
