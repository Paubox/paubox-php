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
vendor/bin/phpunit src/tests/PauboxTest.php
vendor/bin/phpunit src/tests/PauboxFormsTest.php
```

Email tests make real HTTP calls and require the `PAUBOX_API_KEY` env var. The default Forms suite is offline and needs no credentials; the `network`/`mutating` Forms groups require `PAUBOX_FORMS_API_KEY` plus the `QA_*` env vars (see README "Running the tests").

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
