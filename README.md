# TinyPHP Discord OAuth2
Simple Tiny PHP Library to use in your Discord OAuth2

More Info:

https://discordapp.com/developers/docs/topics/oauth2

https://discordapi.com/permissions.html

## Features

### Create the OAuth2 Object

id: ClientID

secret: The Secret ID

scope: Array with the scope

permissions: Permission code

redirect: The redirect URL

state: Protection for your OAuth2

```php 
$tinyDiscord = new tinyDS_OAuth2(
    array(
        'id' => '',
        'scope' => array('identify'),
        'permissions' => 0,
        'redirect' => '',
        'secret' => '',
        'state' => ''
    )
); 
```

### Get OAuth2 Page URL

Use this function to return your URL

```php
$tinyDiscord->getURL();
```

### Get State

Use this function to return your State Value

```php
$tinyDiscord->getState();
```

### Get Token

Use this function to get your token

code: The code result from the OAuth2 Page

```php
$tinyDiscord->getToken($_GET['code']);
```

### Refresh Token

Use this function to refresh your token

refresh: The token refresh code received from getToken

```php
$token = $tinyDiscord->refreshToken($_GET['refresh']);
```

### Get User Data

Use this function to call the user info using your token

refresh: You can insert the value refresh to auto refresh the token if the token is expired

token: The token code

type: The data type. You can see more info in https://discordapp.com/developers/docs/topics/oauth2#shared-resources-oauth2-scopes 

```php
tinyDS_OAuth2::getUser(array(
    'token' => $_GET['token'], 'type' => 'users/@me', 'refresh' => $_GET['refresh']
));
```

<hr>

### Example Code

```php


    session_start();

    // The Auth System
    $tinyDiscord = new tinyDS_OAuth2(array(
        'id' => '',
        'scope' => array('identify'),
        'permissions' => 0,
        'redirect' => '',
        'secret' => '',
        'state' => ''
    ));

    // is Token? Use the token here to test
    
    if (isset($_GET['refresh'])) {

        $token = $tinyDiscord->refreshToken($_GET['refresh']);

        if ((isset($token['err']) == false) && (isset($token['data']->error) == false)) {

            $tiny_user = tinyDS_OAuth2::getUser(array(
                'token' => $token['data']->access_token, 'type' => 'users/@me'
            ));

            if ((isset($tiny_user['err']) == false) && (isset($tiny_user['data']->error) == false)) {

                // Show some token details
                echo '<h2>Token details:</h2>';
                echo 'Token: ' . $token['data']->access_token . "<br/>";
                echo 'Refresh token: ' . $token['data']->refresh_token . "<br/>";
                echo 'Token Type: ' . $token['data']->token_type . "<br/>";

                echo 'Expires: ' . date("m/d/Y h:i:s A T", $tinyDiscord->getExpiration()) . " - ";
                echo ($tinyDiscord->hasExpired() ? 'expired' : 'not expired') . "<br/>";

                echo '<h2>Resource owner details:</h2>';
                printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);

            }

        }

        echo '<pre>';
        print_r($tiny_user);
        echo '</pre>';

        echo '<pre>';
        print_r($token);
        echo '</pre>';

    } else

    if (isset($_GET['token'])) {

        // Get user info
        $tiny_user = tinyDS_OAuth2::getUser(array(
            'token' => $_GET['token'], 'type' => 'users/@me'
        ));

        if ((isset($tiny_user['err']) == false) && (isset($tiny_user['data']->error) == false)) {

            // Show some token details
            echo '<h2>Token details:</h2>';
            echo 'Token: ' . $_GET['token'] . "<br/>";
            echo 'Refresh token: ' . $_GET['refresh_token'] . "<br/>";

            echo '<h2>Resource owner details:</h2>';
            printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);

        }

        echo '<pre>';
        print_r($tiny_user);
        echo '</pre>';

    } else

    // is code from the OAuth2? Use the code here
    if (isset($_GET['code'])) {

        // Protection
        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            http_response_code(405);

            echo 'ERROR STATE';

        } else {

            // Get the user token
            $token = $tinyDiscord->getToken($_GET['code']);

            if ((isset($token['err']) == false) && (isset($token['data']->error) == false)) {

                // Get the user info
                $tiny_user = tinyDS_OAuth2::getUser(array(
                    'token' => $token['data']->access_token, 'type' => 'users/@me'
                ));

                if ((isset($tiny_user['err']) == false) && (isset($tiny_user['data']->error) == false)) {

                    // Show some token details
                    echo '<h2>Token details:</h2>';
                    echo 'Token: ' . $token['data']->access_token . "<br/>";
                    echo 'Refresh token: ' . $token['data']->refresh_token . "<br/>";
                    echo 'Token Type: ' . $token['data']->token_type . "<br/>";

                    echo 'Expires: ' . date("m/d/Y h:i:s A T", $tinyDiscord->getExpiration()) . " - ";
                    echo ($tinyDiscord->hasExpired() ? 'expired' : 'not expired') . "<br/>";

                    echo '<h2>Resource owner details:</h2>';
                    printf('Hello %s#%s!<br/><br/>', $tiny_user['data']['username'], $tiny_user['data']['discriminator']);



                }

            }

            echo '<pre>';
            print_r($tiny_user);
            echo '</pre>';

            echo '<pre>';
            print_r($token);
            echo '</pre>';

        }

    } else {

        // Send the user into the Discord OAuth2
        $_SESSION['oauth2state'] = $tinyDiscord->getState();
        header('Location: ' . $tinyDiscord->getURL());

    }


```