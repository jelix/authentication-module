
Events and listeners
=====================

Some events are triggered by the module. You can implement listeners to react to
these events.

`AuthenticationLogin`
---------------------

Event triggered when a user has been authenticated.

Parameters:

- `user`: a `Jelix\Authentication\Core\AuthSession\AuthUser` object
- `identProviderId`: the id of the authentication provider


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
- `identProviderId`: `"loginpass"`

`AuthenticationUserDeletion`
----------------------------

Event triggered when a user has been deleted from a backend of the `loginpass`
identity provider.

You can respond to this event to delete an "account" for example into your
application.

Parameters:

- `user`: a `Jelix\Authentication\Core\AuthSession\AuthUser` object
- `identProviderId`: `"loginpass"`






