# Paubox PHP — Claude Code Guide

## What this repo is

PHP SDK for two Paubox APIs:
- **Transactional Email** (`Paubox\Paubox`) — send HIPAA-compliant email, check delivery status
- **Forms** (`Paubox\PauboxForms`) — retrieve form definitions, submit form responses

## Install dependencies

```bash
composer install
```

## Run tests

```bash
# Offline — no credentials, no outbound requests. The pre-PR default.
vendor/bin/phpunit src/tests/PauboxFormsTest.php

# Read-only integration — needs PAUBOX_FORMS_API_KEY + QA_* fixtures
vendor/bin/phpunit --group network src/tests/PauboxFormsTest.php

# Writes to the target Forms API — QA customer only, never production
vendor/bin/phpunit --group mutating src/tests/PauboxFormsTest.php

# Email suite: real HTTP calls, requires PAUBOX_API_KEY
vendor/bin/phpunit src/tests/PauboxTest.php
```

The default Forms suite is offline and needs no credentials — `phpunit.xml` excludes the
`network` and `mutating` groups. The Email suite has no such split: it makes real calls and
requires `PAUBOX_API_KEY`. See README "Running the tests" for the `QA_*` fixture variables.

## Testing requirements (mandatory for every change)

No change merges without both layers of coverage. "It's a small change" is not an
exemption — a one-line constant swap still needs the offline assertion that pins the
new value.

### Both layers, every time

| Layer | Runs | Network | Must cover |
|---|---|---|---|
| **Unit** | default suite, no credentials | never | argument validation, URL construction, request-body shaping, response parsing, every `throw` path |
| **Integration** | `--group network` (read-only) or `--group mutating` (writes) | yes | real status codes, real response shapes, auth handling, round-trip behavior |

A unit test that cannot run without a socket is an integration test — tag it as one.

### Group tagging is a hard rule

Every test that opens a socket **must** carry `@group network` (read-only) or
`@group mutating` (creates/updates/archives/deletes). Untagged means offline, and
`phpunit.xml` excludes both groups so a bare `vendor/bin/phpunit` never touches a
remote host — even with `PAUBOX_FORMS_API_KEY` and the `QA_*` vars exported.

`markTestSkipped` is not a substitute for a group tag. Skipping when a credential is
absent says nothing about what happens when it is present; only the tag keeps the
default suite offline.

Before opening a PR, verify the contract holds with credentials loaded:

```bash
vendor/bin/phpunit src/tests/PauboxFormsTest.php   # must make zero outbound requests
```

### Coverage checklist for a new or changed client method

Every box needs a test, offline unless marked:

- [ ] rejects each invalid argument (bad UUID, null required field, empty update set)
- [ ] builds the expected URL, including query-string allowlisting and encoding
- [ ] builds the expected request body — and **omits** optional fields the caller never set
- [ ] returns the documented success shape
- [ ] throws `PauboxFormsException` on a non-success status, with `getStatusCode()`
      populated
- [ ] throws `PauboxFormsException` — not a transport exception — when the host is
      unreachable
- [ ] *(integration)* success path against a QA fixture
- [ ] *(integration)* documented failure path (404 / 403) returns the status the unit
      tests assume

The last two boxes are what catch a wrong endpoint path or an unexpected status code;
offline tests assert our assumptions, integration tests check the API shares them.

### Asserting on URLs and bodies without a network

Point the client at a local sink rather than mocking `ApiHelper`:

```php
$forms = new PauboxForms('test-key', 'http://127.0.0.1:8888');
```

This keeps `buildQuery`, `rawurlencode`, and `rtrim` under test — the logic most likely
to break on a base-URL or endpoint change.

### When an endpoint's contract changes

A base-URL move, a path change, or a status-code change is not done until an
integration test has run against the real host. Offline tests only prove the SDK does
what we told it to; they cannot detect that we told it the wrong thing. State
explicitly in the PR whether the integration groups were run, and against which
environment.

## Regenerate autoloader

```bash
composer dump-autoload
```

## Project layout

```
src/
  Paubox.php            Email API client
  PauboxForms.php       Forms API client
  mail/                 Email data models (Message, Header, Content, Attachment, response types)
  forms/                Forms data models (Form, FormSubmission, FormAttachment)
  service/
    ApiHelper.php       Thin httpful wrapper (callToAPIByGet, callToAPIByPost, plus GET/POST/PUT *WithResponse variants)
  tests/
    PauboxTest.php      Email API integration tests
    PauboxFormsTest.php Forms API integration tests
```

## Key conventions

- **Data models** live in `src/mail/` or `src/forms/`, use private properties with explicit getters/setters, no constructor args.
- **Namespaces**: `Paubox\`, `Paubox\Mail\`, `Paubox\Forms\`, `Paubox\Service\` (see `composer.json` autoload).
- **HTTP** is done through `Service\ApiHelper`. Pass `null` for `$auth_header` on unauthenticated endpoints — the helper skips the Authorization header automatically.
- **Email API base URL**: `https://api.paubox.com/v1/email` (auth: `Bearer {PAUBOX_API_KEY}`)
- **Forms API base URL**: `https://api.paubox.com/v1/forms` (public endpoints — get form, submit form — are unauthenticated; management endpoints use a scoped API key with the `forms` scope, sent as `Bearer {PAUBOX_FORMS_API_KEY}` — a distinct credential from the email `PAUBOX_API_KEY`)
- HTML content in emails is base64-encoded before sending; plain text is sent as-is.
- The `*WithResponse()` helpers (`callToAPIByGetWithResponse`, `callToAPIByPostWithResponse`, `callToAPIByPutWithResponse`) return the raw httpful response object (use `->code` and `->raw_body`); `callToAPIByGet`/`callToAPIByPost` return only `->raw_body`.
- PHPUnit version is `^9.6.33` — use `$this->expectException()`, not the removed `@expectedException` annotation; `setUp()`/`tearDown()` need `: void` return types.

## Environment variables

| Variable          | Description                                                                          |
|-------------------|--------------------------------------------------------------------------------------|
| `PAUBOX_API_KEY`        | Email API key, sent as `Authorization: Bearer` to `api.paubox.com/v1/email` — Email API only, do not reuse for Forms |
| `PAUBOX_FORMS_API_KEY`  | Forms scoped API key (`forms` scope), sent as `Authorization: Bearer` to `api.paubox.com/v1/forms`; used by `PauboxForms` when no key is passed to its constructor |
| `PAUBOX_FORMS_BASE_URL` | Optional override of the Forms API base URL (defaults to `https://api.paubox.com/v1/forms`) |

Load via `.env` + `vlucas/phpdotenv` or export directly in the shell before running tests.
