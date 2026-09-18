# AGENTS.md

This file provides guidance to AI when working with code in this repository.

## Project Overview

Authentication framework for Jelix applications (PHP >=8.2, Jelix >=1.8.0). Provides pluggable identity providers, a step-based authentication workflow, session management, and optional account management. This is the replacement for the legacy `jauth`/`jauthdb` modules. Package: `jelix/authentication-module`.

## Running Tests

Tests run inside Docker containers (PHP-FPM + PostgreSQL + OpenLDAP + Nginx):

```bash
cd test

# First-time setup
./run-docker build
./run-docker
./appctl ldapreset
./appctl reset-setup

# Run all unit tests
./appctl unit-tests

# Run a single test file (passes extra args to phpunit)
./appctl unit-tests --filter=standardWorkflowTest
```

PHPUnit config is at `test/tests/phpunit.xml`, bootstrap at `test/tests/bootstrap.php`. Tests use PHPUnit 8.x.

Other useful `appctl` commands: `reset`, `install`, `composer-update`, `clean_tmp`, `pgsql` (interactive psql shell).

The test app is accessible at `http://localhost:8028` (or `http://jelixauth.local:8028` with a hosts entry). Test credentials: `admin`/`jelix` (ini file user), `john`/`passjohn` and `jane`/`passjane` (LDAP users).

## Architecture

### Documentation

Documentation for integration into a Jelix application, and for core developers is into `docs/en/`.


### Modules

All code lives under `modules/`. Each module follows the Jelix module structure (module.xml for metadata/dependencies/autoload, controllers/, lib/, plugins/, daos/, install/, locales/, templates/).

**Module dependency chain:** `authcore` ← `account` ← `accountadmin`, and `authcore` ← `authloginpass`.

- **authcore** — Core authentication engine. Namespace: `Jelix\Authentication\Core`. Contains the workflow engine, session management, identity provider interface, and the `jAuthentication` static facade (the main entry point for application code).
- **authloginpass** — Login/password identity provider. Namespace: `Jelix\Authentication\LoginPass`. Supports pluggable backends (database DAO, LDAP, ini file) via `BackendPluginInterface`. Includes password reset flow.
- **account** — Full account management: profile page, CLI commands, email notifications, event listener for workflow steps. Namespace: `Jelix\Authentication\Account`.
- **accountadmin** — Admin UI for account management.

### Core architecture

The philosophy of the authentication module is to provide a flexible and extensible framework for managing user authentication and account management in Jelix applications. It achieves this by separating concerns into distinct components, allowing developers to customize and extend the functionality as needed.

### Authentication Workflow

The core design is a state-machine workflow (`Workflow` class) with named steps and transitions. `StandardWorkflow` wires the default pipeline:

`get_account` → `create_account` (if no account) → `check_account` → `second_factor` → `access_validation` → `auth_done`

Each step is a class implementing `StepInterface` under `authcore/lib/Core/Workflow/Step/`. Steps emit events (`WorkflowStepEvent`, `CreateAccountEvent`, `CheckAccountEvent`, `GetAccountEvent`) that third-party modules can listen to (Jelix event system).

A `fail` transition can fire from most steps, leading to `auth_fail`.

### Plugin System

Three plugin types (loaded via Jelix's `jApp::loadPlugin`):

- **authidp** — Identity providers implementing `IdentityProviderInterface`. Registered in config as `authentication[idp]`. The loginpass module provides `loginpass.authidp.php`.
- **authlp** — Login/password backends implementing `BackendPluginInterface`. Three built-in: `dbdao`, `inifile`, `ldap`.
- **authsession** — Session handlers implementing `AuthSessionHandlerInterface`. Built-in: `php` (PHP sessions), `var` (variable-based, for tests).

### Key Entry Points

- `jAuthentication` (static facade in `authcore/lib/jAuthentication.php`) — the main API for modules. Provides `::manager()`, `::session()`, `::getCurrentUser()`, `::startAuthenticationWorkflow()`, `::signout()`.
- `sessionauthCoordPlugin` (coordinator plugin in `authcore/plugins/coord/sessionauth/`) — intercepts every request to enforce authentication, drives the workflow forward.
- `AuthUser` — the authenticated user value object carrying attributes and optional `UserAccountInterface`.

### Locales

Bilingual: `en_US` and `fr_FR` under each module's `locales/` directory.

### DAOs

Jelix XML DAO definitions: `accounts.dao.xml` and `account_idp.dao.xml` (account), `user.dao.xml` (authloginpass), `auth_user_requests.dao.xml` (authcore).
