

The authentication workflow steps
=================================

When an authentication workflow starts, it initializes a number of "steps".
Steps can contain 0 or more page where the user should go, before to be considered
"authenticated".

The steps are:

1. the user account step (named `get_account`): An event is emitted so a module managing accounts can
   give the account object corresponding to the user, if it has one.
2. the create account step (named `create_account`): it is called when there is no account, and if the account
   creation is permitted. An event is emitted so a module managing accounts can give
   the page where to redirect the user, and which shows a form to create the account
   for example.
3. check account step (named `check_account`): An event is emitted to allow some modules
   to check the account, for example, if the account is allowed to access to the application etc.
   Or to load some additional things in session
4. second factor step (named `second_factor`): An event is emitted to allow some modules to give pages
   where the user should go, in order to authenticate against other authentication process,
   like authentication with an encrypted hardware key, an SMS code etc.
5. access validation step (named `access_validation`): An event is emitted to allow some modules to give pages
   where the user should go, in order to finish the process to access to the application.
   It may be a page to get acknowledgment of terms of service for example, or a form to
   force to change the password etc.

All events are named `AuthWorkflowStep`, with a parameter `stepName` indicating the step name.

   