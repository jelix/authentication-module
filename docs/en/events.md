
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






