# symfony-login-demo
Simple Symfony app that demonstrate login/logout with email or social media 

    symfony new --full my-app && cd my-app
    
    # for loading recipes automatically
    composer config extra.symfony.allow-contrib true
    
    # tweaks to use sqlite, load front end assets, etc.

    composer req survos/landing-bundle
    bin/console survos:prepare --no-interaction
    
    # Configure the user and security using MsgPhp UserBundle and User valuex
    composer req maker --dev
    composer req messenger msgphp/user-bundle msgphp/eav-bundle msgphp/user-eav -n
    bin/console make:user:msgphp --no-interaction
    bin/console doctrine:schema:update --force
    bin/console survos:setup --no-interaction
    
## Add HWI/OAutho

    # Load from master to get http-plug 2
    composer config minimum-stability dev
    composer req hwi/oauth-bundle:dev-master php-http/guzzle6-adapter php-http/httplug-bundle 
    
At this point, you'll get an ugly error.  Open security.yaml and add the following keys under 'firewall: main: '

```yaml
            oauth:
                login_path: /login
                failure_path: /login
                resource_owners:
                    google: /oauth/login-check/google
                    facebook: /oauth/login-check/facebook
                oauth_user_provider:
                    service: App\Security\OauthUserProvider
            guard:
                authenticators:
                    - App\Security\OneTimeLoginAuthenticator
```

Alas, those services don't exist.  You can copy them from msgphp/symfony-demo-app

```bash
mkdir src/Security
wget https://raw.githubusercontent.com/msgphp/symfony-demo-app/master/src/Security/OauthUserProvider.php -O src/Security/OauthUserProvider.php 
wget https://raw.githubusercontent.com/msgphp/symfony-demo-app/master/src/Security/OneTimeLoginAuthenticator.php -O src/Security/OneTimeLoginAuthenticator.php 

```

Clear the cache, and you get this error:

 Cannot autowire service "App\Security\OauthUserProvider": argument "$userAttributeValueRepository" of method "__construct()" references interface "MsgPhp\User\Repository\UserAttributeValueRepository" but no su  
  ch service exists. Did you create a class that implements this interface?    

