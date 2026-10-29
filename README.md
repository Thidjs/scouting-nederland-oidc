# Scouting Nederland OIDC voor Joomla 5

Joomla 5-integratie om gebruikers te laten inloggen via de OpenID Connect-dienst van Scouting Nederland.

**Status:** v1.0.0 — productiepilot op St. Salvius en een schone-installatietest op Regio Westelijke Mijnstreek (Joomla 5.4.10 + External Login 5.2.0) succesvol uitgevoerd op 28–29 oktober 2026.

## Vereisten

- Joomla 5
- [External Login](https://github.com/akunzai/joomla-external-login) **5.2.0** (exact; wordt gecontroleerd bij installatie)
- Een OIDC-clientregistratie bij Scouting Nederland

## Installeren

1. Installeer External Login 5.2.0 in Joomla.
2. Download `pkg_scoutingoidc-1.0.0.zip` uit de GitHub-release (of `dist/`).
3. Installeer de ZIP via **Systeem → Installeren → Extensies**.
4. **Activeer eerst de benodigde plugins** via **Systeem → Plugins**: **Scouting Nederland OIDC** (`system/scoutingoidc`), **Scouting Nederland OIDC – Accountbeveiliging** (`user/scoutingoidcsecurity`), **System – External Login** en **Authentication – External Login**. Zonder de geactiveerde Scouting-systeemplugin is de provider niet beschikbaar bij het aanmaken van een External Login-server.
5. Maak daarna in **External Login → Servers** een **nieuwe** server aan met de provider **Scouting Nederland OIDC**.
6. Configureer de Scouting Nederland OIDC-server zoals hieronder beschreven; gebruik de installatiehandleiding in `docs/` voor de schermafbeeldingen en uitgebreide toelichting.
7. Maak/publiceer een **External Login**-sitemodule en kies bij **Geavanceerd → Weergave** de layout **`default`**. In de geteste omgeving bleef de layout **`alone`** de tekst *Inloggen* tonen terwijl de Joomla-gebruiker al correct was aangemeld.
8. Test met een Scouting-account zonder bestaand lokaal Joomla-account met hetzelfde e-mailadres, controleer de automatische gebruikersaanmaak en beide koppelingen, meld je opnieuw aan en test ook uitloggen. Behoud een afzonderlijke Joomla-beheerderslogin.

> Let op: het simpelweg wijzigen van de `plugin`-kolom van een oude External Login-server leverde bij de eerste productiepilot `Plugin disabled` op. Maak daarom een nieuwe server aan in de Joomla-interface.

## Geteste serverinstellingen (v1.0.0)

Maak een **nieuwe** External Login-server aan met provider `Scouting Nederland OIDC`. Gebruik deze waarden voor de geteste Scouting Nederland-koppeling:

| Instelling | Waarde / advies |
| --- | --- |
| Issuer-URL | `https://login.scouting.nl` |
| Client-ID | De voor deze website geregistreerde Scouting Nederland-client-ID |
| Claim voor gebruikersnaam | `member_id` |
| Claim voor volledige naam | `name` |
| Claim voor e-mailadres | `email` |
| Eigen **Automatische registratie** (`scouting_registration_enabled`, tab Gebruikersgegevens) | **Ja** |
| External Login **Auto-register** (tab Server details) | **Nee** |
| External Login **Auto-update** | **Nee** (geteste instelling) |
| Niet-geverifieerde e-mailadressen toestaan | **Nee** (geteste instelling; controleer de toepasselijke `email_verified`-vereisten) |
| Standaard gebruikersgroep | **Registered** (groep 2) |
| External Login-sitemodule, **Geavanceerd → Weergave** | **`default`**; voor begroeting en uitlogknop ook de betreffende opties activeren |
| Externe loginmodule, cache | Uitgeschakeld (`cache=0`) in de geteste opstelling |

**Let op:** gebruik **niet** de algemene External Login-autoregistratie als vervanging voor de eigen Scouting-registratieroute. Deze maakt na controle van de Scouting-identiteit zowel een Joomla-account als de benodigde identiteitskoppelingen aan.

### Registratie bij Scouting Nederland

Registreer voor iedere Joomla-website een eigen OIDC-client en voeg de exacte redirect-URI('s) en post-logout redirect-URI toe. De geteste aanvraag bevatte scopes voor e-mail, persoonlijke gegevens, lidmaatschap en rollen. Leg eerst vast welke gegevens de website werkelijk nodig heeft; vraag geen overbodige scopes aan.

**Verplichte clientinstellingen bij Scouting Nederland (geteste werkende configuratie):** zet bij de OIDC-clientregistratie **beide vinkjes aan: `Publiek` én `PKCE`**. Alleen `PKCE` aanzetten werkte tijdens onze tests niet. Beide vinkjes ingeschakeld is de enige door ons bevestigde werkende combinatie voor v1.0.0.

**Let op bij opslaan:** wanneer je `Publiek` aanvinkt en direct opslaat, kan Scouting Nederland melden dat PKCE ingeschakeld moet worden. Vink dan **PKCE opnieuw aan terwijl `Publiek` aangevinkt blijft** en sla opnieuw op. Controleer vervolgens dat **beide vinkjes actief zijn**. Dit is het daadwerkelijk geteste opslagverloop, geen alternatieve instelling.

Het gedrag van het Joomla-veld `client_secret` en de tokenuitwisseling blijft een apart aandachtspunt voor een toekomstige versie; verander deze logica niet zonder tests.

### Functionele test (29 oktober 2026)

De schone installatie op **Regio Westelijke Mijnstreek** (Joomla **5.4.10**, External Login **5.2.0**, Scouting Nederland OIDC **1.0.0**) bevestigde:

- OIDC-tokenuitwisseling, ID-tokenverificatie en UserInfo-verwerking slagen.
- Met een nog niet gebruikt e-mailadres wordt automatisch een Joomla-account aangemaakt met `member_id` als gebruikersnaam.
- De koppelingen in `#__externallogin_users` en `#__oidc_identities` worden vastgelegd; bij een volgende aanmelding wordt de identiteit als `KNOWN` herkend.
- De accountbeveiligingsplugin vindt het eenmalige authenticatiebewijs en staat de geldige Scouting-login toe.
- Joomla bouwt een geldige frontend-sessie op: de profielpagina is toegankelijk.
- De **modulelayout `alone`** gaf ten onrechte de indruk dat de login was mislukt; omschakelen naar **`default`** loste de zichtbare begroeting op.

**Bestaand e-mailadres:** is het Scouting-e-mailadres al gekoppeld aan een lokaal Joomla-account, dan weigert de Scouting-registratie bewust een nieuw account aan te maken. Dat voorkomt automatische accountovername op grond van uitsluitend een e-mailadres. De huidige generieke melding is een bekend verbeterpunt ([issue #6](https://github.com/Thidjs/scouting-nederland-oidc/issues/6)). Een beheerder moet een eventuele migratie/koppeling zorgvuldig en gecontroleerd uitvoeren; verwijder geen beheerdersaccounts om een test te laten slagen.

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

## Roadmap

**v1.0.1 (gepland):** verbeterde standaardwaarden en configuratiehulp (waaronder `member_id` en de eigen autoregistratie), begrijpelijke en veilige frontendmeldingen bij registratieconflicten ([issue #6](https://github.com/Thidjs/scouting-nederland-oidc/issues/6)), en verbeterde foutdiagnostiek. Gedrag rond `client_secret` en PKCE alleen aanpassen na verificatie van de echte tokenflow.

**Toekomstige release:** eigen Joomla-loginmodule in Scouting Nederland-stijl ([issue #7](https://github.com/Thidjs/scouting-nederland-oidc/issues/7)), die de bestaande External Login/OIDC-beveiligingsketen hergebruikt.

## Licentie en herkomst

GPL, zie `LICENSE` en `NOTICE.md`. Bevat aanpassingen gebaseerd op External Login 5.2.0. Dit is geen officieel product van Scouting Nederland.

## Release-integriteit

De oorspronkelijke installatie-ZIP voor v1.0.0 heeft SHA-256:

`ac52a65db454f252b3dde8e8e6d0e89f63ad20989aa46d2a1644e4999064aaad`
