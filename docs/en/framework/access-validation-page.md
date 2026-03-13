Implementing an access validation page
======================================

After the authentication, before to let the user go to some page of the
application, it may have some extra steps, to force the user to do additional
tasks. For example, it may force the user to change its password, or it may ask
him if he accepts new term of services etc.

So there is a final step named `access_validation` into the authentication workflow.

Create a listener event to register an access validation page
------------------------------------------------------------

To provide an access validation page, you have to respond to an event, 
`AuthWorkflowStep`, with a `stepName` parameter
having the value `access_validation`. The event object is implemented with the class
`Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent`.
It will allow you to indicate the url of the page where to redirect the user
to your page.

Here an example of a listener:

```php
use Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent;
use Jelix\Authentication\Core\Workflow\WorkflowAction;

class myAuthListener extends jEventListener
{

    function onAuthWorkflowStep(WorkflowStepEvent $event)
    {
        if ($event->getStepName() == 'access_validation') {
            $user = $event->getUserBeingAuthenticated();
       
            // $changePasswdUrl = ...
            
            $wrkAction = new WorkflowAction($changePasswdUrl, [
               new jSelectorActFast('classic', 'mymodule', 'changepasswd:index'),
               new jSelectorActFast('classic', 'mymodule', 'changepasswd:done'),
               new jSelectorActFast('classic', 'mymodule', 'changepasswd:error'),
            ]);
            $event->addAction($wrkAction);
        }
    }
   
}
```


So, in our example, the user will be redirected to the `$changePasswdUrl` url. 

To the `WorkflowAction`, you should give also the list of actions that may be
called during the process of your access validation page. It will allow the
authentication workflow to check that these actions are part of the workflow,
and it could redirect to the step url if the user tries to go on another
page of the application.


Implementing a controller for an access validation page
-------------------------------------------------------

In each actions of the access validation process, you should check that the
authentication workflow is alive, and you could then retrieve user data
to do the process

```php

class myAccessPageCtrl extends \jController
{

    function index()
    {
    
        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow ) {
            // no workflow, you should stop here: display an error message
            // or redirect the user to the homepage or elsewhere.
            
            return ...;
        }
        
        if (!$workflow->isCurrentStep('access_validation')) {
            // this is not the right step, redirect the user to the right step
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }

        $user = $workflow->getTemporaryUser();

        // we can now process the access validation page. It may redirect
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
        
        if (!$workflow->isCurrentStep('access_validation')) {
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }


        // we finish here the access validation process
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
        
        if (!$workflow->isCurrentStep('access_validation')) {
            return $this->redirectToUrl($workflow->getCurrentStepUrl());
        }

        // this action is called because of an error. We should cancel
        // the authentication process and display a message.
        $workflow->cancel('The reason');
        
        $rep = $this->getResponse('html');
        //... show the error...
        return $rep;
    }
}
```
