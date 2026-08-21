# Changelog

All notable changes to this project will be documented in this file.

## [1.2.0](https://github.com/Paubox/paubox-php/compare/v1.1.0...v1.2.0) (2026-08-21)

First release since `v1.1.0` in July 2019.

### 🚀 New Features

- Add `PauboxForms` for the Paubox Forms API, with `Form`, `FormSubmission`, `FormAttachment`, and `PauboxFormsException` value types under `Paubox\Forms`
  - Public endpoints, no credential attached: `getForm($formId)`, `submitForm($formId, FormSubmission $submission)`
  - Form management with a scoped API key (`forms` scope, sent as `Authorization: Bearer <key>`): `listForms`, `getFormById`, `createForm`, `updateForm`, `archiveForm`, `unarchiveForm`, `copyForm`, `getFormStats`
  - Submissions: `listSubmissions`, `getSubmissionsCsv`, `getSubmissionCsv`, `getSubmissionPdf`
  - Base URL defaults to `https://api.paubox.com/v1/forms`, overridable via the constructor's `$baseUrl`
- The Email API no longer requires an API username — an API key alone authenticates

### ⚠️ Behavior Changes

- The Email API base URL moves from `https://api.paubox.net/v1/{PAUBOX_API_USER}/` to `https://api.paubox.com/v1/email/`, dropping the per-customer path segment. `PAUBOX_API_USER` is now ignored
- The `Authorization` header moves from `Token token=<key>` to `Bearer <key>`

  Neither is a break for existing users: the API mounts both the legacy username-scoped and the new `/v1/email` paths against the same controllers, and its header parser accepts `Token` and `Bearer` interchangeably. The SDK moved from one supported surface to the other, now-canonical one.

### 💥 Requirements

Composer resolves these rather than breaking existing installs — anyone who cannot satisfy them stays on `v1.1.0`.

- PHP `>=8.1` is now declared. `v1.1.0` declared no PHP constraint, but `nategood/httpful` is pinned at `*` and its only non-vulnerable release requires PHP 8.1, so the floor was already real and undeclared
- `vlucas/phpdotenv` moves from a pinned `3.4.0` to `^5.6`
- `phpunit/phpunit` moves to `^9.6.33`, closing a high-severity Dependabot alert

### 🎉 Enhancements

- Add an Apache 2.0 `LICENSE`
- Add a CI workflow running PHPUnit and a lint pass on PHP 8.1, 8.2, and 8.3, with the network and mutating test groups excluded from the default run

## v1.1.0 / 2019-07-02

### 🚀 New Features

- Add a `cc` field to messages ([#3](https://github.com/Paubox/paubox-php/pull/3))
- Add `ForceSecureNotification` for forcing secure notifications

### 🎉 Enhancements

- Add `vlucas/phpdotenv` as a dependency and use it for environment loading ([#6](https://github.com/Paubox/paubox-php/pull/6), [#7](https://github.com/Paubox/paubox-php/pull/7))
- Convert plain HTML text to a base64 string
- Add test cases covering `cc` in CSV input ([#4](https://github.com/Paubox/paubox-php/pull/4))
- Remove the committed `composer.lock`

## v1.0.1 / 2018-09-30

### 🐛 Fixes

- Fix namespacing
- Remove the `version` key from `composer.json` so Packagist derives versions from tags

## v1.0.0 / 2018-09-27

### 🚀 Major Release

First release of the Paubox Transactional Email API package for PHP.
