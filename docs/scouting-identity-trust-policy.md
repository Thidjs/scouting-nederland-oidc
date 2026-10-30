# Scouting OIDC identity and email trust policy (issue #3)

## Identity is not email ownership

For the Scouting Nederland issuer (`https://login.scouting.nl`), authentication is based on cryptographically validated OIDC tokens and consistent `iss` + `sub` claims from ID Token and UserInfo. `member_id` is validated and configured as Joomla's `username_claim`. Email is a contact attribute, **not** a lookup key for linking to an existing Joomla account.

During the pilot, UserInfo returned `email_verified: false`. This does not invalidate the already verified Scouting identity. Both `false` and missing `email_verified` mean that email ownership has **not** been established. Do not treat the address as verified or use it to authorize account linking, recovery, elevated permissions, or email-sensitive workflows.

## Registration and login

- **NEW**: Allow registration only after validated Scouting identity, opt-in `scouting_registration_enabled`, configured `username_claim=member_id`, successful identity inspection, and a transactional registration with a new Joomla account. The current implementation registers before the generic email-verification gate. It requires a syntactically valid email from verified OIDC claims but **does not require that email address to be verified**.
- **KNOWN**: Allow login only when issuer/subject/member ID, Joomla username and External Login server binding agree. For this Scouting-only path, `email_verified: false` does not revoke an otherwise valid identity binding.
- **CONFLICT / existing email / existing username / existing identity**: Deny automatic linking or account takeover. Require administrator-led resolution. Do not disclose personal data in frontend messages or logs.
- **Other OIDC issuers**: Preserve their existing `email_verified` rules; this policy does not weaken them.
- **Unverified email**: Store as an unverified contact attribute; any workflow that relies on email ownership needs its own verification before use. Joomla account activation state alone must not be interpreted as proof of email ownership.

## Verification matrix (must be tested before closing issue #3)

| Case | Expected outcome |
| --- | --- |
| NEW, valid Scouting identity, `email_verified: true`, unused email and username | Register and bind |
| NEW, valid Scouting identity, `email_verified: false`, unused email and username | Register and bind; email remains unverified |
| NEW, valid Scouting identity, `email_verified` missing, unused email and username | Register and bind; email remains unverified |
| KNOWN, valid bindings, `email_verified: false` | Login succeeds |
| NEW, existing Joomla email | Deny; no new binding |
| NEW, existing Joomla username/member ID | Deny; no new binding |
| Existing `iss/sub` with conflicting member ID, user ID or server binding | Deny |
| Invalid issuer, mismatched ID Token/UserInfo `sub`, malformed member ID | Deny |
| Non-Scouting OIDC, `email_verified: false` | Preserve generic denial |

## Follow-up

Add automated tests around identity inspection, transactional registration and the end-to-end authentication response. Review Joomla's email-dependent workflows to ensure unverified contact addresses are never silently treated as ownership proof.
