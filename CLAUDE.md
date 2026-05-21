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
    ApiHelper.php       Thin httpful wrapper (callToAPIByGet, callToAPIByPost, callToAPIByPostWithResponse)
  tests/
    PauboxTest.php      Email API integration tests
    PauboxFormsTest.php Forms API integration tests
```

## Key conventions

- **Data models** live in `src/mail/` or `src/forms/`, use private properties with explicit getters/setters, no constructor args.
- **Namespaces**: `Paubox\`, `Paubox\Mail\`, `Paubox\Forms\`, `Paubox\Service\` (see `composer.json` autoload).
- **HTTP** is done through `Service\ApiHelper`. Pass `null` for `$auth_header` on unauthenticated endpoints — the helper skips the Authorization header automatically.
- **Email API base URL**: `https://api.paubox.net/v1/{PAUBOX_API_USER}/` (auth: `Token token={PAUBOX_API_KEY}`)
- **Forms API base URL**: `https://apx.paubox.com/forms` (no auth)
- HTML content in emails is base64-encoded before sending; plain text is sent as-is.
- `callToAPIByPostWithResponse()` returns the raw httpful response object (use `->code` and `->raw_body`); the other two helpers return only `->raw_body`.
- PHPUnit version is `^5.7.9` — use `@expectedException` annotation, not `$this->expectException()`.

## Environment variables (Email API only)

| Variable          | Description                        |
|-------------------|------------------------------------|
| `PAUBOX_API_KEY`  | API key / token                    |
| `PAUBOX_API_USER` | Endpoint name (subdomain segment)  |

Load via `.env` + `vlucas/phpdotenv` or export directly in the shell before running tests.
