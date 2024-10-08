How to manage an account
========================

The account module provide a workflow step to load account information about the user
which is authenticated. You don't have to do anything.

However, if you don't want to use the account module, and if you want to provide your 
own account management, you need to "plug" this management to the authentication system.

Here is how.

The account object
------------------

When the application supports accounts, the authentication system stores an
object representing the account, into the session. It should implement the
interface `Jelix\Authentication\Core\AuthSession\UserAccountInterface`.
It declares three methods to retrieve the account id of the user, his name and his email.
Here is an example of the object you should implement:

```php
class MyAccountImplementation implements \Jelix\Authentication\Core\AuthSession\UserAccountInterface
{
    protected $accountId;

    protected $username;

    protected $realname;

    protected $email;

    function __construct($accountId, $username, $realname, $email)
    {
        $this->accountId = $accountId;
        $this->username = $username;
        $this->realname = $realname;
        $this->email = $email;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getUserName()
    {
        return $this->username;
    }

    public function getRealName()
    {
        return $this->realname;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
```

This object could also implement other methods you may need into your
application.

A listener to set the account into the session
------------------------------------------------

To give this object to the authentication system during the authentication
process, you must create an event listener, that will listen after the
event named `AuthWorkflowStep`. The object representing this event
has the class `Jelix\Authentication\Core\Workflow\Event\GetAccountEvent`
(inheriting from the class `WorkflowStepEvent`). You can test also 
its `stepName` parameter to check that the step is `get_account`.


An example of listener, to store into `classe/myGetAccountListener.listener.php`
of your module.

```php
use Jelix\Authentication\Core\Workflow\Event\GetAccountEvent;

class myGetAccountListener extends jEventListener
{

    function onAuthWorkflowStep($event)
    {
        if ($event instanceof GetAccountEvent) {
        
        }
    }
}

```

On the event, you have a method to retrieve the user being authenticated,
`getUserBeingAuthenticated()`. It gives a `Jelix\Authentication\Core\AuthSession\AuthUser`
object. It allows you to search the corresponding account.

If there is an account (or you have juste created one on the fly), call
the method `setAccount($account)` on the event.

Else call `setUnknownAccount()` if an account can be automatically created.
Else throw an exception with `StepException`, and the user will return to the authentication form.
The error message indicated to `StepException` may be displayed (this is the case
with the authentication form of the authloginpass module). 

```php
use Jelix\Authentication\Core\Workflow\Event\GetAccountEvent;
use Jelix\Authentication\Core\Workflow\Step\StepException;

class myGetAccountListener extends jEventListener
{

    function onAuthWorkflowStep($event)
    {
        if ($event instanceof GetAccountEvent) {
            $user = $event->getUserBeingAuthenticated();
    
            $account = $this->searchAccountFromUserId($user->getUserId());
            if ($account) {
                $event->setAccount($account);
            }
            else {
                $isAccountCreationAllowed = ...;
                if ($isAccountCreationAllowed) {
                    // it will go to the account creation step
                    $event->setUnknownAccount();
                }
                else {
                    throw new StepException('No account found');
                }
            }
        }
    }
    
    /**
    * @param $userId
    * @return \Jelix\Authentication\Core\AuthSession\UserAccountInterface|null
    */
    protected function searchAccountFromUserId($userId)
    {
        // here the search of the account
        // ...
        // return $account;
    }
}
```

If you call `setAccount()`, or if you don't call `setUnknownAccount()`, the user 
will be redirected to the next authentication step like 2FA or access validation.

If you call `setUnknownAccount()` the user will be redirected to the
account creation step, and if some module respond to this step, the user will
have probably a form to fill etc. And then the workflow continue with other
steps.


A listener to create an account
-------------------------------

In the case where there is no account linked to the authenticated
user (the listener of `GetAccountEvent` called `$event->setUnknownAccount()`),
a `AuthWorkflowStep` event is emitted, with a `stepName` parameter
having the value `create_account`. The event object is implemented with the class
`Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent`.

A listener for this event should then add actions with the `WorkflowStepEvent` object,
to where the user will be redirected in order to create the account. 
It could be a form to fill with personal data for example (name, preferences etc).

Here is an example of a listener:

```php
use Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent;
use Jelix\Authentication\Core\Workflow\WorkflowAction;

class myAuthAccountListener extends jEventListener
{

    function onAuthWorkflowStep(WorkflowStepEvent $event)
    {
        if ($event->getStepName() == 'create_account') {
            $user = $event->getUserBeingAuthenticated();
            
            // the url to redirect immediately, to fill a form for example  
            $formUrl = jUrl::get('mymodule~createaccount:form', 
                array('uid'=>$user->getUserId()));
          
            // add this workflow action, by indicating the possible other action
            // after filling the form
            $wrkAction = new WorkflowAction($formUrl, [
               new jSelectorActFast('classic', 'mymodule', 'createaccount:save'),
            ]);
            $event->addAction($wrkAction);  
        }
    }
}
```

In the case where the account cannot be created



Implementing a controller to create an account
-----------------------------------------------

In each actions of the account creation process, you should check that the
authentication workflow is alive, and you could then retrieve user data
to do the process.

At the end of the account creation process, you should give your account
object to the authentication system with `$workflow->getTemporaryUser()->setAccount($account);`.


Here is an example of controller:

```php

class createaccountCtrl extends \jController
{

    function form()
    {
    
        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow ) {
            // no workflow, you should stop here: display an error message
            // or redirect the user to the homepage or elsewhere.
            
            return ...;
        }
        
        if (!$workflow->isCurrentStep('create_account')) {
            // this is not the right step, redirect the user to the right step
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }

        $resp = $this->getResponse('html');
        // we display the form
        // ..
        
        return $resp;   
    }

    function save()
    {
        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow ) {
            return ...;
        }
        
        if (!$workflow->isCurrentStep('create_account')) {
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }

        // here we check the form...
        $formOk = ...;
        
        if ($formOk) {
            // the content of the form is ok
            
            // here we create the account
            $account = ...;
            // save the account here
            // ...
            
            // now we can indicate the account to the authenticated user
            $workflow->getTemporaryUser()->setAccount($account);

            // we can now redirect the user to the next step of the authentication
            return $this->redirectToUrl($workflow->getNextAuthenticationUrl());
        }
        
        // bad form, we return to the form
        
        return $this->redirect('createaccount:form')
    }
}
```


A listener to check an account
-------------------------------

After an account is found and loaded, or is created, the next step
is to check the account. An application may want to do additionnal check,
relative to its context, model rules etc.

An event `AuthWorkflowStep` event is emitted, with a `stepName` parameter
having the value `check_account`. The event object is implemented with the class
`Jelix\Authentication\Core\Workflow\Event\CheckAccountEvent`.

A listener for this event can then check the account (it will found the account
on the `getAccount()` method of the event object). It can add some actions if needed,
with the `WorkflowStepEvent` object, to where the user will be redirected in 
order to do more check.

Here is an example of a listener:

```php
use Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent;
use Jelix\Authentication\Core\Workflow\WorkflowAction;

class myAuthAccountListener extends jEventListener
{

    function onAuthWorkflowStep(WorkflowStepEvent $event)
    {
        if ($event->getStepName() == 'check_account') {
            $user = $event->getUserBeingAuthenticated();
            $account = $event->getAccount();
            
            // here do some checks, load more data etc.
            // ...
            // if the authentication process should be stopped, because
            // an error during the check:
            
            if ($error) {
                throw new \Jelix\Authentication\Core\Workflow\Step\StepException("You cannot login because....")
            }              
        }
    }
}
```




