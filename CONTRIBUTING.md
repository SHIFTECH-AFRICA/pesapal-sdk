# Contributing

1. Create a focused branch and keep API changes backward-compatible unless a major release is planned.
2. Add or update tests for every behavior change.
3. Run `composer test`, `composer analyse`, and `vendor/bin/pint --test`.
4. Never commit merchant credentials, bearer tokens, PAN, CVV, or production payment payloads.
5. Document any Pesapal contract assumption with a current primary source.
6. Use conventional, descriptive commits and explain migration impact in the pull request.
