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

Tests make real HTTP calls. Email tests require `PAUBOX_API_KEY` and `PAUBOX_API_USER` env vars. Forms tests require valid form UUIDs in the data providers.

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
- **Email API base URL**: `https://api.paubox.net/v1/{PAUBOX_API_USER}/` (auth: `Token token={PAUBOX_API_KEY}`)
- **Forms API base URL**: `https://apx.paubox.com/forms` (public endpoints — get form, submit form — are unauthenticated; management endpoints use a scoped API key with the `forms` scope, sent as `Bearer {PAUBOX_API_KEY}`)
- HTML content in emails is base64-encoded before sending; plain text is sent as-is.
- The `*WithResponse()` helpers (`callToAPIByGetWithResponse`, `callToAPIByPostWithResponse`, `callToAPIByPutWithResponse`) return the raw httpful response object (use `->code` and `->raw_body`); `callToAPIByGet`/`callToAPIByPost` return only `->raw_body`.
- PHPUnit version is `^5.7.9` — use `@expectedException` annotation, not `$this->expectException()`.

## Environment variables

| Variable          | Description                                                                          |
|-------------------|--------------------------------------------------------------------------------------|
| `PAUBOX_API_KEY`  | Email API key / token; also doubles as the Forms scoped API key (`forms` scope) used by `PauboxForms` when no key is passed to its constructor |
| `PAUBOX_API_USER` | Endpoint name (subdomain segment) — Email API only                                   |

Load via `.env` + `vlucas/phpdotenv` or export directly in the shell before running tests.
