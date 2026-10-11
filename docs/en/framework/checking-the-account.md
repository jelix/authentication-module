
A listener to check an account
==============================

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
