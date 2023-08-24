Implementing a second factor authentication page
================================================

After the main authentication, a second authentication with
a different form of authentication may be asked to the user. It may send a code
by SMS or email to the user, and he should give this code into a form.
Or it may ask to use a specific device (like a Ubikey or else) etc.

So there is a step named `second_factor` into the authentication workflow, which
occurs if there is an account for the user, because in most case, data (telephone 
number for example) to use the second form of authentication are stored into 
the account of the user into the application.

Create a listener event to register the 2FA process
---------------------------------------------------

So if you want to provide a page that ask a second authentication to the user,
you have to respond to an event, `AuthWorkflowStep`, with a `stepName` parameter
having the value `second_factor`. The event object is implemented with the class
`Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent`.
It will allow you to indicate the url of the page where to redirect the user 
to do the second authentication.

Here an example of a listener:

```php
use Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent;
use Jelix\Authentication\Core\Workflow\WorkflowAction;

class myAuthListener extends jEventListener
{

    function onAuthWorkflowStep(WorkflowStepEvent $event)
    {
        if ($event->getStepName() == 'second_factor') {
            $user = $event->getUserBeingAuthenticated();
            $accId = $user->getAccount()->getAccountId();
       
            $wrkAction = $this->get2FAFromAccount($accId);
            if ($wrkAction) {
                $event->addAction($wrkAction);
            }
        }
    }
    
    protected function get2FAFromAccount($accountId)
    {
    
        $found = false;
        // here the search of the 2FA to use for the given account
        // ...
        // and then
        if (!$found) {
            return null;
        }
        
        // $twoFAUrl = ..
        
        $wrkAction = new WorkflowAction($twoFAUrl, [
           new jSelectorActFast('classic', 'mymodule', 'secondfact:index'),
           new jSelectorActFast('classic', 'mymodule', 'secondfact:done'),
           new jSelectorActFast('classic', 'mymodule', 'secondfact:error'),
        ]);
        return $wrkAction;
    }
}
```


So, in our example, the user will be redirected to the `$twoFAUrl` url. It may
be the url of an external web site, or of a controller of the application.

To the `WorkflowAction`, you should give also the list of actions that may be
called during the process of the second authentication. It will allow the
authentication workflow to check that these actions are part of the workflow,
and it could redirect to the step url if the user tries to go on another
page of the application.


Implementing a controller for the 2FA process
---------------------------------------------

In each actions of the 2FA process, you should check that the
authentication workflow is alive, and you could then retrieve user data
to do the process

```php

class my2FACtrl extends \jController
{

    function index()
    {
    
        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow ) {
            // no workflow, you should stop here: display an error message
            // or redirect the user to the homepage or elsewhere.
            
            return ...;
        }
        
        if (!$workflow->isCurrentStep('second_factor')) {
            // this is not the right step, redirect the user to the right step
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }

        $account = $workflow->getTemporaryUser()->getAccount();

        // we can now process the second authentication. It may redirect
        // the user to several other page, and may end to the "done" action
        // or to the error action.
        // ...
        
        // return $responseObject;   
    }
    
    function done()
    {
        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow ) {
            return ...;
        }
        
        if (!$workflow->isCurrentStep('second_factor')) {
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }


        // we finish here the second authentication process
        // ...
        
        // we can now redirect the user to the next step of the authentication        
        return $this->redirectToUrl($workflow->getNextAuthenticationUrl());
    }

    function error()
    {
        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow ) {
            return ...;
        }
        
        if (!$workflow->isCurrentStep('second_factor')) {
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }

        // this action is called because of an error. We should cancel
        // the authentication process and display a message.
        $workflow->cancel();
        
        $rep = $this->getResponse('html');
        //... show the error...
        return $rep;
    }
}
```

