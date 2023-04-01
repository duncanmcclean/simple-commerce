---
title: Support Policy & Versioning
---

## Support Policy

Only the latest Simple Commerce release will receive bug fixes, feature additions and official support. In addition, security fixes will only be issued to the latest release.

After a major release (like 5.x), on request, we may copy over any fixes to the previous major release for a week or two post-release.

### Security

If you discover a security vulnerability, please report it straight away, [via email](mailto:security@doublethree.digital). Please don't report security issues through GitHub Issues.

### Customer Support

If you require support with Simple Commerce, please [open an GitHub issue](https://github.com/duncanmcclean/simple-commerce/issues/new/choose) or [send me an email](mailto:hello@doublethree.digital). I'd be more than happy to help!

## Versioning

Simple Commerce attempts to follow [Semantic Versioning](https://semver.org/). Major versions will tend to be released every few months, while minor and patch releases will generally be released every week or so. However, it's worth noting these timescales are not set in stone and may change.

When referencing Simple Commerce in your `composer.json` file, you should always use a version constraint like `^4.0` to ensure you receive any minor or patch releases when running `composer update`.

When a new major version is released, automating the changes required by any breaking changes will be considered. However, you should still expect to need to do some manual work yourself.
