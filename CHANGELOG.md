# Changelog

## [1.0.2] - 2026-10-30 (gepland)

- Joomla package-update-server geregistreerd; toekomstige releases kunnen via de Joomla Extensie-updater worden aangeboden.
- Updatefeed wordt bij GitHub Releases gegenereerd en via GitHub Pages gepubliceerd, inclusief download-URL en SHA-256.
- Releasedatum in pakket- en pluginmanifesten geactualiseerd; CI controleert consistentie en bij tags de UTC-releasedatum.
- Let op: bestaande v1.0.1-installaties hebben nog geen update-serverregistratie en moeten eenmalig handmatig naar v1.0.2 worden bijgewerkt.
- Publicatie en end-to-end updateherkenning in Joomla moeten nog worden getest.

## [1.0.1] - 2026-10-30

- Veilige standaardwaarden voor nieuwe Scouting OIDC-servers.
- Autoregister, Autoupdate en Change Password vastgezet op Nee; User Validation en Email Validation verplicht leeg, ook bij bestaande servers.
- Server-side validatie van deze instellingen en herstel van provideridentiteit na opslagfouten.
- Client Secret optioneel gemaakt met toelichting voor de aanbevolen Scouting Nederland-registratie-instellingen.
- Succesvol getest met opslag en inloggen zonder Client Secret.


## [1.0.0] - 2026-10-28

- Eerste installeerbare Joomla 5-package met Scouting Nederland OIDC-systeemplugin en accountbeveiligingsplugin.
- Identitybinding en beveiligde registratie.
- Succesvolle eerste productiepilot: tokenuitwisseling, ID-tokenvalidatie, UserInfo en eerste registratie/login.
- Bekend punt: verweesde identiteitskoppeling na verwijderen Joomla-gebruiker.
