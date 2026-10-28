# Scouting Nederland OIDC voor Joomla 5

Joomla 5-integratie voor inloggen via de OpenID Connect-dienst van Scouting Nederland.

**Status:** v1.0.0 — succesvolle eerste productiepilot op 28 oktober 2026.

## Vereisten

- Joomla 5
- [External Login](https://github.com/akunzai/joomla-external-login) **5.2.0** (exact; wordt gecontroleerd bij installatie)
- OpenID Connect-clientregistratie bij Scouting Nederland

## Installeren

1. Installeer External Login 5.2.0.
2. Installeer `pkg_scoutingoidc-1.0.0.zip` via Joomla → Systeem → Installeren → Extensies.
3. Maak in External Login een **nieuwe** server aan met provider Scouting Nederland OIDC.
4. Configureer issuer, client-ID, clientgeheim en gebruikersclaims; gebruik de installatiehandleiding in `docs/` zodra de broncode is toegevoegd.
5. Controleer de registratie- en gebruikersgroepinstellingen; behoud een afzonderlijk beheerdersaccount.
6. Publiceer de inlogmodule en test met een standaardgebruiker.

## Bekend aandachtspunt in v1.0.0

Na verwijderen van een gekoppelde Joomla-gebruiker kan een verweesde Scouting-identiteit achterblijven in `#__oidc_identities`. Daardoor kan herregistratie leiden tot `CONFLICT`. Laat zulke koppelingen zorgvuldig door technisch beheer controleren.

## Licentie en herkomst

GPL, zie `LICENSE` en `NOTICE.md` zodra de broncode is toegevoegd. De implementatie bevat wijzigingen gebaseerd op External Login 5.2.0. Niet officieel ondersteund door Scouting Nederland.
