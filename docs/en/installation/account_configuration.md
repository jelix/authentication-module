Configuration for accounts
==========================

```ini

[accounts]

; "on" value: when a user sign in, if he has no account, it's created automatically.
autoCreateAccountOnLogin=on


```


Claim mapping
-------------

When an account is created from an authenticated user, the account
module needs to know how to map identity attributes provided by the identity
provider to account fields (username, firstname, lastname, email...).

This mapping is configured in the `[account:claim-mapping]` section of the
`app/system/mainconfig.ini.php` configuration file.

The keys are account field names and the values are identity attribute keys
(URI-style keys defined by the class `Jelix\Authentication\Core\IdentityAttributes`).

Here is the default mapping, which is used if the section is not present or
if a field is not specified:

```ini
[account:claim-mapping]
username=jelix/identity/username
firstname=jelix/identity/givenname
lastname=jelix/identity/surname
email=jelix/identity/emailaddress
```

An identity provider always provides data using URI-style keys defined by the
class `Jelix\Authentication\Core\IdentityAttributes`. Except for data that are not defined into
`IdentityAttributes`. In this case it could be any other URI-style key or simple named key.
If you added columns into the account table and redefined the corresponding DAO, you may need to add more fields
into the mapping.

For example, if your identity provider provides custom attribute keys like `http://my.idp.com/data/leisure` for
the leisure column, and an other column prefered_color without a specific claim, you added:

```ini
[account:claim-mapping]
username=jelix/identity/username
firstname=jelix/identity/givenname
lastname=jelix/identity/surname
email=jelix/identity/emailaddress
leisure=http://my.idp.com/data/leisure
prefered_color=prefered_color
```

If the identity provider does not supply `firstname` and `lastname` separately
but provides a `displayname` (mapped to `jelix/identity/displayname`), the
account module will attempt to split the display name into first and last names.

Note: the claim mapping may be used by identity providers in order to map their own claims to Jelix authentication claims.
