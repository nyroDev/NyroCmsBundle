# NyroCmsBundle
Cms Bundle for Symfony

# Needed npm packages
- copy-webpack-plugin
- jquery
- jquery-ui
- jquery-mobile (for slideshow swipe feature)

# Needed configuration
config/packages/nyrodev.yaml
```yaml
parameters:
    adminPrefix: /admin
    localeNames:
        fr: Français
        en: English

nyro_dev_utility:
    db_driver: orm

nyro_dev_nyro_cms:
    model:
        namespace: App\Entity
```

config/routes/nyrocms.yaml
```yaml
nyrocms_admin:
    resource: "@NyroDevNyroCmsBundle/Resources/config/routingAdmin.yml"
    prefix:   /admin

frontenay:
    resource: frontenay@App\Controller\FrontController
    type: nyrocms
```

Type for nyroCms routes could also add elements seperated with _ : 
- forceLang
- homepage in order to add _homepage route alias

config/security.yaml
```yaml
security:
    encoders:
        App\Entity\User:
            algorithm: bcrypt
    providers:
        db_provider:
            entity:
                class: App\Entity\User
    role_hierarchy:
        ROLE_SUPERADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        admin:
            anonymous: ~
            pattern:    ^%adminPrefix%/.*
            http_basic: ~
            provider: db_provider
            logout:
                path:   nyrocms_admin_security_logout
                target: nyrocms_admin_login
            form_login:
                login_path: nyrocms_admin_login
                check_path: nyrocms_admin_security_check
                default_target_path: nyrocms_admin_homepage
    access_control:
        - { path: ^%adminPrefix%/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^%adminPrefix%/forgot, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^%adminPrefix%/contentHandler, roles: ROLE_DEVELOPPER }
        - { path: ^%adminPrefix%/userRole, roles: ROLE_SUPERADMIN }
        - { path: ^%adminPrefix%/user, roles: ROLE_SUPERADMIN }
        - { path: ^%adminPrefix%, roles: ROLE_ADMIN }
```


# Webpack config Entries needed
```js
    .addEntry('js/admin/nyroCms', './vendor/nyrodev/nyrocms-bundle/Resources/public/js/nyroCms.js')
    .addEntry('css/admin/nyroCms', './vendor/nyrodev/nyrocms-bundle/Resources/public/css/nyroCms.scss')

    .addEntry('js/admin/nyroCmsComposer', './vendor/nyrodev/nyrocms-bundle/Resources/public/js/nyroCmsComposer.js')
    .addEntry('css/admin/nyroCmsComposer', './vendor/nyrodev/nyrocms-bundle/Resources/public/css/nyroCmsComposer.scss')

    .addPlugin(new CopyWebpackPlugin([
        {from: 'vendor/nyrodev/utility-bundle/Resources/public/vendor/tinymce', to: '../tinymce'}
    ]))


    .enableSassLoader()
    .autoProvidejQuery()
```

# Command for entities and mapping creation
`./bin/console nyrocms:createDbClasse`

# Others commands
`./bin/console nyrocms:addUser`
`./bin/console nyrocms:addRootContent`

# Edit config/bootstrap.php
`$loader = require dirname(__DIR__).'/vendor/autoload.php';`

# Overwrite a template
Copy the file from Resources/views into your own folder: src/Resources/NyroDevNyroCmsBundle/views/