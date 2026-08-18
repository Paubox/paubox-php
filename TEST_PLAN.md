# Test Plan — Forms base URL move & Forms management API

Scope: the `origin/master...claude/paubox-forms-marketing-scope-6kpgsb` diff —
14 files, +1393/−73. Three changes ship together:

1. Email API auth switched to `Authorization: Bearer` (username dropped)
2. Forms base URL moved from `https://apx.paubox.com/forms` to `https://api.paubox.com/v1/forms`
3. Twelve new Forms management methods, plus PHPUnit 5→9 / PHP 8.1 modernization

## Current state

| | Count |
|---|---|
| Tests in `PauboxFormsTest.php` | 43 methods / 109 cases |
| Passing offline | 88 |
| Skipped without credentials | 21 |
| Tagged `@group network` | 3 |
| Tagged `@group mutating` | 5 |
| **Untagged but socket-opening** | **~13** |

The last row is the problem. `phpunit.xml` excludes `network` and `mutating`, so the
default suite looks offline — but that only holds because the untagged network tests
call `markTestSkipped` when credentials are missing. Export `PAUBOX_FORMS_API_KEY` and
the `QA_*` vars and a bare `vendor/bin/phpunit` fires ~13 live requests at whatever
`PAUBOX_FORMS_BASE_URL` points at, which defaults to production.

**Nothing in this diff has been exercised against a real endpoint.** The offline suite
proves the SDK does what the code says; it cannot prove the code says the right thing.

---

## Phase 0 — Blockers (must resolve before any other testing is meaningful)

These three are unanswerable from the codebase. Each needs a live call or a
platform-team answer, and each invalidates downstream tests if it comes back wrong.

### 0.1 Does the new base URL preserve the path layout?

All 14 call sites keep their `/api/forms` and `/public/form_data` suffixes, so requests
now resolve to:

```
https://api.paubox.com/v1/forms/api/forms
https://api.paubox.com/v1/forms/api/forms/{id}/submissions
https://api.paubox.com/v1/forms/public/form_data/{id}
```

Note `/v1/forms/api/forms`. The doubling is inherited — the old base produced
`apx.paubox.com/forms/api/forms` with the same shape — so this is a faithful host swap.
But if the new gateway flattens to `/v1/forms/{id}`, every path suffix needs updating
and this branch is half a migration.

```bash
export PAUBOX_FORMS_API_KEY='<qa scoped key>'
curl -sS -o /dev/null -w '%{http_code} %{url_effective}\n' \
  -H "Authorization: Bearer $PAUBOX_FORMS_API_KEY" \
  'https://api.paubox.com/v1/forms/api/forms?customer_id=<qa>'
```

**Pass:** 200. **Fail:** 404 → re-test against `https://api.paubox.com/v1/forms?customer_id=<qa>`;
if that returns 200, strip `/api/forms` from all 14 call sites and re-run this plan.

### 0.2 What status does `createForm` actually return?

`src/PauboxForms.php:207` treats anything other than 200 as failure, including 201.
`submitForm` in the same class asserts 201 for its POST. If create returns 201, every
call throws *after* the form is created — the UUID is lost in the exception and the
form is orphaned on the customer account.

```bash
curl -sS -o /dev/null -w '%{http_code}\n' -X POST \
  -H "Authorization: Bearer $PAUBOX_FORMS_API_KEY" -H 'Content-Type: application/json' \
  -d '{"title":"status probe","form_json":{"pages":[]},"customer_id":<qa>}' \
  'https://api.paubox.com/v1/forms/api/forms'
```

**Pass:** 200. **Fail:** 201 → widen the check to `!in_array($response->code, [200, 201], true)`.
Delete the probe form afterward either way.

### 0.3 Confirm the Email Bearer-auth switch

The username was dropped from Email auth in `8df4887`. `PauboxTest.php` makes real
calls, so this is covered — but only if the suite is actually run.

```bash
PAUBOX_API_KEY='<key>' vendor/bin/phpunit src/tests/PauboxTest.php
```

**Pass:** green. **Fail:** 401 → the API still expects the username form.

---

## Phase 1 — Unit coverage gaps (offline, no credentials)

New tests, all runnable with no network. Each maps to a confirmed review finding.

### 1.1 Base URL resolution — pins this diff's actual change

Currently untested. Three resolution paths plus normalization:

| Case | Setup | Expect |
|---|---|---|
| default | no arg, no env | `https://api.paubox.com/v1/forms` |
| constructor override | `new PauboxForms('k', 'https://x.test/v1/forms')` | that value |
| env override | `PAUBOX_FORMS_BASE_URL=https://y.test/v1/forms` | that value |
| precedence | both set | constructor wins |
| trailing slash | `'https://x.test/v1/forms/'` | slash stripped |
| no double-slash | any base | no `//` in any built URL |

Verified manually during the base-URL change; **not yet encoded as tests.** This is the
single most important gap, because it is the only assertion that would fail loudly if
someone reverts or mistypes the constant.

### 1.2 URL construction for all 14 endpoints

Point the client at a local sink and assert the path — this exercises `buildQuery`,
`rawurlencode`, and `rtrim` without mocking `ApiHelper`:

```php
$forms = new PauboxForms('test-key', 'http://127.0.0.1:8888');
```

Cover: each of the 14 paths; query allowlisting (an unlisted key is dropped, not
forwarded); boolean coercion (`true` → `'true'`, not `'1'`); UUID encoding.

### 1.3 Request-body shaping in `createForm` — finding at `:198`

`createForm` unconditionally sends `signable`, `active`, `version`, and
`submission_count`. Consequences to assert:

- unset `signable`/`active` cast `null` → `false`, so a caller who omits the optional
  setters silently gets an **inactive, unsignable** form instead of the server default
- `getVersion() ?: 1` rewrites a legitimate `0` to `1`
- `submission_count` is server-derived and should not be client-supplied at all

Tests: omitted optional fields are absent from the body; `setVersion(0)` survives as `0`.
These will fail against current code — they document intended behavior and should land
with the fix.

### 1.4 Transport failures wrapped — finding at `:27`

README promises "All Forms methods throw `PauboxFormsException`". DNS/TLS/timeout/refused
currently surface as `Httpful\Exception\ConnectionErrorException`. Reproduced during
review: pointing the base URL at an unreachable host fails 9 tests on this type mismatch.

Test: point at `http://127.0.0.1:1` (connection refused), assert `PauboxFormsException`
for every public method. Fix is a `try/catch` in each method or centrally in `ApiHelper`.

### 1.5 `getForm` status codes — finding at `:30`

`getForm` uses `callToAPIByGet`, which discards the response object, so
`getStatusCode()` is always `null` and every failure reads "Form not found or invalid
response." A 500 or a proxy HTML error page makes an existing form look deleted, and the
README's documented `getStatusCode() === 403` pattern can never match on this path.

Test: stub a 500 and a non-JSON body; assert `getStatusCode()` is populated and the
message distinguishes "not found" from "server error." Fix: switch to
`callToAPIByGetWithResponse`.

### 1.6 UUID validation on public methods — finding at `:24`

`assertUuid` on the public `getForm`/`submitForm` is an undocumented breaking change.
Non-canonical identifiers now throw before any request — including whitespace-padded
UUIDs and vanity slugs, and the SDK itself exposes `vanity_url` as a form field.

Decide first, then test:
- **(a)** intended → document in README as a breaking change, add tests pinning rejection
- **(b)** unintended → relax to accept vanity slugs, add tests for slug acceptance

Do not skip the decision. A vanity-URL form is currently unreachable through this SDK.

---

## Phase 2 — Integration coverage (`@group network`, read-only)

Needs `PAUBOX_FORMS_API_KEY` + `QA_TEST_FORM_UUID`, `QA_TEST_SUBMISSION_UUID`,
`QA_TEST_CUSTOMER_ID`. Read-only — safe to re-run.

### 2.1 Retag the ~13 untagged network tests

The prerequisite for everything else in this phase. Add `@group network` to every
untagged test that opens a socket:

```
testGetForm_ReturnSuccess              testGetFormStats_ReturnSuccess
testGetFormById_ReturnSuccess          testGetFormStats_ReturnError
testGetFormById_ReturnNotFound         testListSubmissions_ReturnSuccess
testListForms_ReturnSuccess_*  (×2)    testListSubmissions_ReturnSuccess_WithPagination
testUpdateForm_ReturnNotFound          testListSubmissions_ReturnNotFound
testArchiveForm_ReturnNotFound         testGetSubmissionsCsv_ReturnSuccess / _ReturnNotFound
testUnarchiveForm_ReturnNotFound       testGetSubmissionCsv_ReturnSuccess / _ReturnNotFound
testCopyForm_ReturnNotFound            testGetSubmissionPdf_ReturnSuccess / _ReturnNotFound
```

Verify with credentials **exported**:

```bash
vendor/bin/phpunit src/tests/PauboxFormsTest.php   # must issue zero outbound requests
```

Confirm with `tcpdump`, a proxy log, or by pointing `PAUBOX_FORMS_BASE_URL` at a local
listener and asserting no connection arrives.

### 2.2 Success and 404 paths against QA

Per read method — `getForm`, `getFormById`, `listForms`, `getFormStats`,
`listSubmissions`, `getSubmissionsCsv`, `getSubmissionCsv`, `getSubmissionPdf`:

- success returns the documented shape (`getFormById` unwraps `->data`)
- unknown UUID → `PauboxFormsException` with `getStatusCode() === 404`
- pagination: `page`/`items` change the result set
- CSV returns CSV bytes, not JSON; PDF starts with `%PDF`
- **auth negative:** a valid-format but wrong key → 401/403, not a generic failure

### 2.3 Response-shape drift

`getFormById` throws if `->data` is missing; `createForm` throws if `->id` is missing.
Both assume a wrapper shape that is asserted nowhere against the live API. Assert the
real envelope for each method.

---

## Phase 3 — Mutating coverage (`@group mutating`, QA customer only)

> **Never against production.** Confirm `PAUBOX_FORMS_BASE_URL` and the customer id
> point at QA before running. Existing mutating tests restore state in a `finally`
> block, but a crash between archive and restore leaves the form archived.

| Test | Covers |
|---|---|
| `createForm` full round-trip | 0.2's status answer; asserts returned UUID is real |
| `createForm` minimal fields | 1.3 — server defaults survive when optionals are omitted |
| `updateForm` partial | only supplied keys change; others untouched |
| `updateForm` boolean coercion | `active` accepts `bool`, `'true'`/`'false'`, `int` |
| archive → unarchive | round-trip restores state |
| `copyForm` | copy exists with the new title; original unchanged |
| `submitForm` + attachments | 201 accepted; attachment round-trips |

Add teardown that deletes or archives anything created, keyed off a run-scoped title
prefix so orphans from a crashed run are identifiable.

---

## Exit criteria

- [ ] 0.1 answered — path layout confirmed against the live host
- [ ] 0.2 answered — `createForm` status confirmed, check widened if 201
- [ ] 0.3 green — Email Bearer auth verified
- [ ] Every socket-opening test tagged `network` or `mutating`
- [ ] Bare `vendor/bin/phpunit` issues zero outbound requests **with credentials exported**
- [ ] Base-URL resolution covered by offline tests (all 6 cases in 1.1)
- [ ] All 14 endpoint paths asserted offline
- [ ] Transport failures wrapped in `PauboxFormsException` across all methods
- [ ] `getForm` reports real status codes
- [ ] UUID-validation decision made and documented
- [ ] `--group network` green against QA
- [ ] `--group mutating` green against QA with clean teardown
- [ ] PR states which integration groups ran, and against which environment

## Risk if shipped as-is

| Risk | Severity | Caught by |
|---|---|---|
| Wrong endpoint path → every Forms call 404s | **Critical** | 0.1 |
| `createForm` 201 → orphaned forms, lost UUIDs | **Critical** | 0.2 |
| Default suite hits production | High | 2.1 |
| Silent inactive/unsignable forms | High | 1.3 |
| Transport errors break documented contract | Medium | 1.4 |
| Existing form reported as deleted on a 500 | Medium | 1.5 |
| Vanity-URL forms unreachable | Medium | 1.6 |
