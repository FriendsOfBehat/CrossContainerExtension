# Cross Container Extension [![License](https://img.shields.io/packagist/l/friends-of-behat/cross-container-extension.svg)](https://packagist.org/packages/friends-of-behat/cross-container-extension) [![Version](https://img.shields.io/packagist/v/friends-of-behat/cross-container-extension.svg)](https://packagist.org/packages/friends-of-behat/cross-container-extension) [![Build status on Linux](https://img.shields.io/travis/FriendsOfBehat/CrossContainerExtension/master.svg)](http://travis-ci.org/FriendsOfBehat/CrossContainerExtension) [![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/FriendsOfBehat/CrossContainerExtension.svg)](https://scrutinizer-ci.com/g/FriendsOfBehat/CrossContainerExtension/)

Makes possible to inject services and parameters from other containers.

## Usage

1. Install it:
    
    ```bash
    $ composer require friends-of-behat/cross-container-extension --dev
    ```

2. Enable this extension and configure Behat to use it:
    
    ```yaml
    # behat.yml
    default:
        # ...
        extensions:
            FriendsOfBehat\CrossContainerExtension: ~
    ```

3. Use it together with [FriendsOfBehat\ContextServiceExtension](https://github.com/FriendsOfBehat/ContextServiceExtension) or [FriendsOfBehat\ServiceContainerExtension](https://github.com/FriendsOfBehat/ServiceContainerExtension).
