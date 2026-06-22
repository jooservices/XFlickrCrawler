# Security Policy

## Supported versions

The latest stable release of **XFlickr Crawler** is supported for security fixes.

Older releases may be unsupported unless maintainers explicitly state otherwise in release notes or repository documentation.

## Reporting a vulnerability

Do not open public GitHub issues for suspected vulnerabilities.

Report security concerns privately to [admin@jooservices.com](mailto:admin@jooservices.com) with:

- a clear summary of the issue
- affected package version(s)
- impact and expected risk
- reproduction details or proof of concept when available

If you are unsure whether a report is security-related, contact maintainers privately first.

## What happens next

Maintainers will acknowledge reports as soon as they can.

Investigation, validation, and any fix or coordinated disclosure timeline will depend on severity, exploitability, and release risk. No guaranteed SLA is promised.

## Scope

This policy covers security issues in repository-managed behavior such as:

- credential handling (`token_payload`, `xflickr_app.*` profiles)
- rate limiter and queue job execution
- SQL persistence and migration safety
- API audit logging and error handling
- dependency and CI configuration that affects package consumers or repository integrity

## Non-security issues

Normal bugs, feature requests, questions, and documentation improvements should use standard GitHub issues instead of private security reporting.

## Credential hygiene

This package stores Flickr user tokens in MySQL (`xflickr_connections.token_payload`) and app secrets in laravel-config (`xflickr_app.{profile}`). Never log `apiSecret`, `oauthTokenSecret`, or full token payloads. Restrict write access to `xflickr_app.*` to operators and admins.
