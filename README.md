# Sharing Session Pack (Symfony) 

A Symfony sharing session package that allows a remote server to request and receive the sessionId from a host (IDP). The data is transmitted over the HTTP protocol, which is protected by synchronous encryption. Once the systems share the session via a REDIS service, the remote system can retrieve the AuthUser information.

Install the package with:

```console
composer require jpm/session-sharing-bundle
```

## Usage

### PoC Demonstration

In the next section you will find the step-by-step of how to install and use, but if you prefer [visit the video with the presentation of the bundle usage](https://youtu.be/g-CefwgGgqM).

### Settings on Host Side

1. install the `composer require jpm/session-sharing-bundle`.
2. then run `php bin/console jpm:generate-sync-key` to generate a random key.
   - This key is sensitive information that you must keep secret.
   - The output key usually starts with: `def...`.
3. in your `.env` file, add the following keys:
   - `JPM_TOKEN_SYNC_SECRET` with your generated key.
   - `JPM_APP_URL` with your own address
   - JPM_KNOWN_REMOTE_HOSTS` known host domains with a comma between each entry.
     Here is an example:
     ```bash
     ###> jpm/session-sharing keys ###
     JPM_TOKEN_SYNC_SECRET=def0000031429fb75a...69057f875ff4c6118cb8c1
     JPM_APP_URL=http://host-idp.test
     JPM_KNOWN_REMOTE_HOSTS=remote.test
     ###< jpm/session-sharing keys ###
      ```
4. now to create a subscriber use the command: `php bin/console make:subscriber RemoteAuth` or just create a class manually in `./src/EventSubscriber/RemoteAuthSubscriber.php`
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

1. Install the `composer require jpm/session-sharing-bundle` too
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
4. Now needed to create a Subscriber, use the command: `php bin/console make:subscriber SessionManager` or just create a class manually in `./src/EventSubscriber/SessionManagerSubscriber.php`
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

Basically, this allows your remote project to retrieve the SessionId from the HOST via a request and be retrieved by REDIS/database without any issues.


## Architecture life cycle

This bundle uses the Symfony components to solve the problem of sharing a session between different Symfony projects while maintaining a minimal level of security, even when hosted under different domains.

### use case example:

1. the unknown user accesses `http://remote.test`.
2. the request is intercepted by `SessionManagerSubscriber`, and the user is redirected to the identity provider via callback info (HTTP 302).
3. the IDP (host) receives a GET request: `http://host-idp.test/login?callback=aHR0cDovL3JlbW90ZS50ZXN0`.
4. the `RemoteAuthSubscriber` from the IDP (host) intercepts the call, decrypts the callback parameter, extracts the domain, and confirms if it belongs to the allowed list of domains (`JPM_KNOWN_REMOTE_HOSTS`).
   - If not: authorization is performed/query but not forwarded to the unknown requester.
5. once the request is validated, the IDP checks if the user has a valid session open.
   - If not, the IDP authentication form is displayed to the user and the callback parameter is maintained.
6. once the session is created (or exists), the "SessionID" is encrypted with the sync key and defuse lib.
7. now the user is redirected back to the callback URL with the token parameter containing the encrypted value.
8. now the remote application receives a request: `http://remote.test?token=ZGVmNTAyMDAwYjliZDI5ODU5NGQxYzQwYTE...`
9. again the `SessionManagerSubscriber` intercepts the request, but once it finds the token, it decodes and decrypts it, restores the session, and finally lets the identified user access the resource
 
## Note of responsibility

Security is super important and sharing a database/redis between different systems is far from recommended sending sensitive data between GET requests is terrible, but unfortunately, sometimes we need to create some kind of solution for these cases.

The natural solution for session sharing is to use JWT or some other type of token-based solution. I did this project as a study lab, and it might offer us some insights for something different, so I **don't** recommend using this in production unless you understand the risks of sharing sensitive data between systems and HTTP communications.



