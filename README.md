# Scouting Nederland OIDC voor Joomla 5

Joomla 5-integratie om gebruikers te laten inloggen via de OpenID Connect-dienst van Scouting Nederland.

**Status:** v1.0.0 — eerste productiepilot succesvol getest op 28 oktober 2026.

## Vereisten

- Joomla 5
- [External Login](https://github.com/akunzai/joomla-external-login) **5.2.0** (exact; wordt gecontroleerd bij installatie)
- Een OIDC-clientregistratie bij Scouting Nederland

## Installeren

1. Installeer External Login 5.2.0 in Joomla.
2. Download `pkg_scoutingoidc-1.0.0.zip` uit de GitHub-release (of `dist/`).
3. Installeer de ZIP via **Systeem → Installeren → Extensies**.
4. Maak in **External Login → Servers** een **nieuwe** server aan met de provider **Scouting Nederland OIDC**.
5. Configureer issuer, clientgegevens en gebruikersclaims volgens de installatiehandleiding in `docs/`.
6. Controleer de registratie-instellingen en activeer de benodigde plugins, waaronder **Accountbeveiliging**.
7. Test met een niet-beheerdersaccount en behoud een afzonderlijke Joomla-beheerderslogin.

> Let op: het simpelweg wijzigen van de `plugin`-kolom van een oude External Login-server leverde bij de eerste productiepilot `Plugin disabled` op. Maak daarom een nieuwe server aan in de Joomla-interface.

## Repository

- `plugins/system/scoutingoidc/`: broncode van de systeemplugin
- `plugins/user/scoutingoidcsecurity/`: broncode van de accountbeveiliging
- `pkg_scoutingoidc.xml` en `pkg_script.php`: Joomla-pakket
- `dist/`: oorspronkelijke installeerbare v1.0.0-release
- `docs/`: handleidingen voor webmasters en beheerders

## Bekende aandachtspunten v1.0.0

- Wanneer een gekoppelde Joomla-gebruiker wordt verwijderd, kan een verweesde identiteit achterblijven in `#__oidc_identities`. Dit kan herregistratie blokkeren met `CONFLICT`. Laat dit door technisch beheer controleren; verwijder geen willekeurige koppelingen.
- De eerste productiepilot gebruikte een nieuw aangemaakte External Login-server. Migratie van oudere serverregistraties verdient extra aandacht.
- Controleer het gebruik van `email_verified` voordat automatische registratie op onbekende accounts wordt toegestaan.

## Licentie en herkomst

GPL, zie `LICENSE` en `NOTICE.md`. Bevat aanpassingen gebaseerd op External Login 5.2.0. Dit is geen officieel product van Scouting Nederland.

## Release-integriteit

De oorspronkelijke installatie-ZIP voor v1.0.0 heeft SHA-256:

`ac52a65db454f252b3dde8e8e6d0e89f63ad20989aa46d2a1644e4999064aaad`
