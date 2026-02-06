
Events and listeners
=====================

Some events are triggered by the module. You can implement listeners to react to
these events (see [`jEvent` documentation](https://docs.jelix.org/en/manual/components/events)).

`AuthenticationCanUseApp`
-------------------------

Event triggered when a user has been authenticated but he has no session yet.
Listen it to verify that the user can access to the application.

Parameters:

- `user`: a `Jelix\Authentication\Core\AuthSession\AuthUser` object
- `identProvider`: the authentication provider object

Response to give:

- `canUseApp` : a boolean. True if the user can use the application, else false.

`AuthenticationLogin`
---------------------

Event triggered when a user has been authenticated.

Parameters:

- `user`: a `Jelix\Authentication\Core\AuthSession\AuthUser` object
- `identProvider`: the authentication provider object

`AuthenticationFail`
--------------------

Event triggered when the authentication failed.

Parameters:

- `login` (optional): it may contain the login. Some identities providers may not provide it.


`AuthenticationLogout`
---------------------

Event triggered when a user has been logout.

Parameters:

- `user`: a `Jelix\Authentication\Core\AuthSession\AuthUser` object


`AuthenticationUserCreation`
----------------------------

Event triggered when a user has been registered into a backend of the `loginpass`
identity provider.

You can respond to this event to create an "account" for example into your
application.

Parameters:

- `user`: a `Jelix\Authentication\Core\AuthSession\AuthUser` object
- `identProvider`: the `loginpass` authentication provider object

`AuthenticationUserDeletion`
----------------------------

Event triggered when a user has been deleted from a backend of the `loginpass`
identity provider.

You can respond to this event to delete an "account" for example into your
application.

Parameters:

- `user`: a `Jelix\Authentication\Core\AuthSession\AuthUser` object
- `identProvider`: the `loginpass` authentication provider object

`AuthWorkflowStep`
------------------

When the user starts the authentication, this event is emitted at each step of the
authentication. The step name is indicated into a `stepName` parameter, and the 
class of the event is `Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent`.

For the step `get_account`, the class is `Jelix\Authentication\Core\Workflow\Event\GetAccountEvent`.


`AuthLPCanResetPassword`
------------------------

This event is sent when a request is made to reset the password
of an identifiant managed by the authloginpass module.

Any module can indicate if the given user is allowed to
reset the password. For example, for an account module,
the module can say no if there is no registrated account.

See the `Jelix\Authentication\LoginPass\AuthLPCanResetPasswordEvent` class.


`ProfileViewPageEvent`
----------------------
This event is sent to extend the content of the "view profile" page.
The view profile currently display information of the profile (username, first name, e-mail, ...) and a button to edit values.
use `addContent(string $content, int $position)` to add some content to the page. Initial content is on position 5.

