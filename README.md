# NyroCmsBundle
Cms Bundle for Symfony

# Needed npm packages
- copy-webpack-plugin
- jquery
- jquery-ui
- jquery-mobile (for slideshow swipe feature)

```
npm i copy-webpack-plugin jquery jquery-ui jquery-mobile --save-dev
```

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
    setLocale: true

nyro_dev_nyro_cms:
    model:
        namespace: App\Entity
```

config/packages/stof_doctrine_extensions.yaml
```yaml
stof_doctrine_extensions:
    default_locale: "%locale%"
    translation_fallback: true
    class:
        loggable: NyroDev\UtilityBundle\EventListener\LoggableListener
        translatable: NyroDev\UtilityBundle\EventListener\TranslatableListener
    orm:
        default:
            tree: true
            sortable: true
            loggable: true
            translatable: true
            timestampable: true
            softdeleteable: true
```

config/packages/doctrine.yaml
```yaml
doctrine:
    orm:
        filters:
            softdeleteable:
                class: Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter
                enabled: true
```

config/routes/nyrocms.yaml
```yaml
nyrocms_admin:
    resource: "@NyroDevNyroCmsBundle/Resources/config/routingAdmin.yaml"
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
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
        App\Entity\User:
            algorithm: auto
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
            pattern:    ^%adminPrefix%/.*
            provider: db_provider
            logout:
                path:   nyrocms_admin_security_logout
                target: nyrocms_admin_login
            form_login:
                login_path: nyrocms_admin_login
                check_path: nyrocms_admin_security_check
                default_target_path: nyrocms_admin_homepage
    access_control:
        - { path: ^%adminPrefix%/login, roles: PUBLIC_ACCESS }
        - { path: ^%adminPrefix%/forgot, roles: PUBLIC_ACCESS }
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

    .addPlugin(new CopyWebpackPlugin({
        patterns: [
            {from: 'vendor/nyrodev/utility-bundle/Resources/public/vendor/tinymce', to: '../tinymce'}
        ]
    }))

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
