# Sharing Session Pack (Symfony) 

A symfony Sharing Session Package to allow a remote server request and receive the sessionId from a host (IDP). The data is transferred by HTTP protocol protected by synchronous encryption, and once the systems share the session by a REDIS service the remote system will be able to retrieve the AuthUser information.  

Install the package with:

```console
composer require jpm/session-sharing-bundle
```

## Usage

### Settings on Host Side

1. Install the `Sharing Session Pack`
2. Then run `php bin/console jpm:generate-sync-key` to generate a random key.
   - This key is sensitive information **keep it private!**
   - The output key usually start with: `def...`
3. In your `.env` file add the following keys:
   - `JPM_TOKEN_SYNC_SECRET` with your generated key
   - `JPM_APP_URL` with your own address
   - `JPM_KNOWN_REMOTE_HOSTS` known hosts domains using a comma between each entry
   Here is an example: 
      ```bash
      ###> jpm/session-sharing keys ###
      JPM_TOKEN_SYNC_SECRET=def0000031429fb75a...69057f875ff4c6118cb8c1
      JPM_APP_URL=http://host-idp.test
      JPM_KNOWN_REMOTE_HOSTS=remote.test
      ###< jpm/session-sharing keys ###
      ```
4. Now is needed to create a Subscriber, use the command: `php bin/console make:subscriber RemoteAuth` or just create a class manually in `./src/EventSubscriber/RemoteAuthSubscriber.php`
   ```php
    use JPM\SessionSharingBundle\Service\JpmSessionClient;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ResponseEvent;
    use Symfony\Component\HttpKernel\KernelEvents;
    
    class RemoteAuthSubscriber implements EventSubscriberInterface
    {
     public function __construct(
        private JpmSessionClient $remoteAppHelper
     ){}
    
     public function onKernelResponse(
        ResponseEvent $event
     ): void
    {
       $this->remoteAppHelper->watchRemoteSessionRequest($event);
    }
    
    public static function getSubscribedEvents(): array
    {
       return [
          KernelEvents::RESPONSE => 'onKernelResponse'
       ];
    }
    }
   ```

### Settings on a Remote Side

1. Install the `Sharing Session Pack` too
2. Now in your `.env` file add the following keys:
   - `JPM_TOKEN_SYNC_SECRET` with the same key you are using in the host
   - `JPM_APP_URL` with your real domain
   - `JPM_IDP_URL` with the host route which do login action
     Here is an example:
      ```bash
      ###> jpm/session-sharing keys ###
      JPM_TOKEN_SYNC_SECRET=def0000031429fb75a...69057f875ff4c6118cb8c1
      JPM_APP_URL=http://remote.test
      JPM_IDP_URL=http://host-idp.test/login
      ###< jpm/session-sharing keys ###
      ```
4. Now is needed to create a Subscriber, use the command: `php bin/console make:subscriber SessionManager` or just create a class manually in `./src/EventSubscriber/SessionManagerSubscriber.php`
   ```php
    use JPM\SessionSharingBundle\Service\JpmSessionClient;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ControllerEvent;
    use Symfony\Component\HttpKernel\KernelEvents;
    
    class SessionManagerSubscriber implements EventSubscriberInterface
    {
    
        public function __construct(
            private JpmSessionClient $remoteAppHelper,
        ){}
    
        public function onKernelController(ControllerEvent $event): void
        {
            $this->remoteAppHelper->watchSessionRefresh($event);
        }
    
        public static function getSubscribedEvents(): array
        {
            return [
                KernelEvents::CONTROLLER => 'onKernelController',
            ];
        }
    }
    ```
   
Basically with this your remote project will be able to retrieve the sessionId from the HOST via request and retrieved from REDIS/database with any issue.


## Architecture life cycle

This bundle uses the symfony components to solve the problem of sharing a same session among different symfony projects keeping a minimal level of security, even if they are hosted under different domains.

### use case example:

1. The unknown user access `http://remote.test`
2. The request is intercepted by the `SessionManagerSubscriber` the user is redirect (HTTP 302) to the identity provider using a callback info
3. The IDP (Host) receives a GET request: `http://host-idp.test/login?callback=aHR0cDovL3JlbW90ZS50ZXN0`.
4. The `RemoteAuthSubscriber` from IDP (Host) intercepts the call, decode the callback parameter, extract the domain, and confirm if it belongs to the allow list of domains (`JPM_KNOWN_REMOTE_HOSTS`) 
   - if not: it will do/ask the auth but will not redirect to the unknown requester.
5. Once the request is validated, the IDP verifies if the user has a valid session open.
   - if not: the auth form from idp is shown to the user and the callback parameter is kept.
6. Once the session is created (or exist) the `sessionID` is encrypted using the sync key ad the defuse lib.
7. Now the user is redirect back to the callback URL with the token param holding the encrypted value.
8. Now the remote app receives a request: `http://remote.test?token=ZGVmNTAyMDAwYjliZDI5ODU5NGQxYzQwYTE...`
9. Again the `SessionManagerSubscriber` intercepts the request but now once it find the token it decodes, decrypts, restores the session and finally let the identified user access the resource.
    - if the given session is not valid/found: a 403 is throw and stops the loop.
 
## Note of responsibility

Security is super important and sharing a database/redis among different systems is far way of been recommended AND send sensitive data between GET request is terrible but unfortunately sometimes we need create some sort of solution for these cases.

The natural solution for sharing session is making usage of JWT, or any other kind of token based solution. I did this project as a study-laboratory, and it could offer us some insight for something else, so i do **not** recommend to use this in production if you do not understand the risks of sharing sensitive data between systems and http communication.





