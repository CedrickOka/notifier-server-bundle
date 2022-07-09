Configuration
==================

#### Example full configuration

```yaml
oka_notifier_server:
    channels:
        local:
            enabled: true
	        db_driver: mongodb
	        class_name: App\Document\Message
        email:
            enabled: true
        sms:
            enabled: true
        smpp:
            dsn: '%env(resolve:NOTIFIER_SMPP_DSN)%'
        clickatell:
            url: '%env(resolve:NOTIFIER_CLICKATELL_URL)%'
            token: '%env(NOTIFIER_CLICKATELL_API_TOKEN)%'
        infobip:
            url: '%env(resolve:NOTIFIER_INFOBIP_URL)%'
            api_key: '%env(NOTIFIER_INFOBIP_API_KEY)%'
        wirepick:
            client_id: '%env(NOTIFIER_WIREPICK_CLIENT_ID)%'
            password: '%env(NOTIFIER_WIREPICK_PASSWORD)%'
        firebase:
            enabled: true

    reporting:
        db_driver: mongodb
        class_name: App\Document\SendReport

doctrine_mongodb:
    document_managers:
        default:
            mappings:
                OkaNotifierServerBundle:
                    type: xml
                    dir: '%kernel.project_dir%/vendor/coka/notifier-server-bundle/config/doctrine'
                    prefix: 'Oka\Notifier\ServerBundle\Model'
```
