# Changelog

## [1.0.4] - 2026-11-24 (voorbereiding; niet gepubliceerd)

- Installer leest de versie van het geïnstalleerde External Login-**pakket** (`pkg_externallogin`) in plaats van de component (`com_externallogin`), waarvan de versie op productie leeg blijkt.
- Ondersteunde External Login-versies blijven strikt 5.2.0 en 5.3.0; de controle op de authenticatieplugin blijft behouden.
- Verbeterde foutmelding bij een ontbrekende of lege pakketversie.
- Regressietest en vrijgave pas na validatie van de pakketregistratie en de Joomla-updateroute.

## [1.0.3] - 2026-11-24 (voorbereiding; nog niet gepubliceerd)

- Installer accepteert uitsluitend de geteste External Login-versies 5.2.0 en 5.3.0; andere versies blijven geblokkeerd.
- Functionele compatibiliteit met External Login 5.3.0 op Joomla-testomgeving bevestigd: login, logout, hernieuwde login, bestaande identiteitskoppeling en kalenderrechten.
- De automatische update van Scouting OIDC v1.0.2 naar v1.0.3 via Joomla moet nog end-to-end worden getest.
- Het reeds onder External Login 5.2.0 aanwezige testomgevingprobleem met callbackpad `/index.php` wordt afzonderlijk gevolgd in issue #20.


## [1.0.2] - 2026-10-30

- Joomla package-update-server geregistreerd; toekomstige releases kunnen via de Joomla Extensie-updater worden aangeboden.
- Updatefeed wordt bij GitHub Releases gegenereerd en via GitHub Pages gepubliceerd, inclusief download-URL en SHA-256.
- Releasedatum in pakket- en pluginmanifesten geactualiseerd; CI controleert consistentie en bij tags de UTC-releasedatum.
- Let op: bestaande v1.0.1-installaties hebben nog geen update-serverregistratie en moeten eenmalig handmatig naar v1.0.2 worden bijgewerkt.
- Publicatie is afgerond; end-to-end updateherkenning in Joomla moet nog worden getest.

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
