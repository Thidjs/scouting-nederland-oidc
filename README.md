# Scouting Nederland OIDC voor Joomla 5

Joomla 5-integratie om gebruikers te laten inloggen via de OpenID Connect-dienst van Scouting Nederland.

**Status:** v1.0.2 — gepubliceerd op 30 oktober 2026. De eerdere productiepilot op St. Salvius en de schone installatie en upgrade naar v1.0.1 op Regio Westelijke Mijnstreek (Joomla 5.4.10 + External Login 5.2.0) zijn succesvol getest. De handmatige pakketupgrade naar v1.0.2 op Regio Westelijke Mijnstreek is geslaagd; Joomla heeft de nieuwe update-server geregistreerd. Een automatische update naar een nieuwere versie is nog niet end-to-end getest.

## Vereisten

- Joomla 5
- [External Login](https://github.com/akunzai/joomla-external-login) **5.2.0** (exact; wordt gecontroleerd bij installatie)
- Een OIDC-clientregistratie bij Scouting Nederland

## Registratie bij Scouting Nederland

Registreer voor iedere Joomla-website een eigen OIDC-client en voeg de exacte redirect-URI('s) en post-logout redirect-URI toe. De geteste aanvraag bevatte scopes voor e-mail, persoonlijke gegevens, lidmaatschap en rollen. Leg eerst vast welke gegevens de website werkelijk nodig heeft; vraag geen overbodige scopes aan. Let op, de scopes E-mail, Persoonlijk, Lidmaatschap en Rollen en lidmaatschappen zijn vereist. 

Ga hiervoor naar https://login.scouting.nl Let op, dit werkt alleen als je in SOL3.0 de rol Webmaster hebt voor jouw organisatie.

**Verplichte clientinstellingen bij Scouting Nederland (geteste werkende configuratie):** zet bij de OIDC-clientregistratie **beide vinkjes aan: `Publiek` én `PKCE`**. Alleen `PKCE` aanzetten werkte tijdens onze tests niet. Beide vinkjes ingeschakeld is de door ons bevestigde werkende combinatie voor v1.0.0 en v1.0.1.

**Let op bij opslaan:** wanneer je `Publiek` aanvinkt en direct opslaat, kan Scouting Nederland melden dat PKCE ingeschakeld moet worden. Vink dan **PKCE opnieuw aan terwijl `Publiek` aangevinkt blijft** en sla opnieuw op. Controleer vervolgens dat **beide vinkjes actief zijn**. Dit is het daadwerkelijk geteste opslagverloop, geen alternatieve instelling.

**Client Secret (v1.0.1):** bij de hierboven geteste registratie met **Publiek** en **PKCE** is geen Client Secret nodig. Het veld in Joomla is daarom optioneel en mag leeg blijven. Opslaan en opnieuw inloggen zonder Client Secret zijn op 30 oktober 2026 succesvol getest. Gebruik voor een andere clientconfiguratie alleen instellingen die je zelf hebt gevalideerd.

## Installeren

1. Installeer External Login 5.2.0 in Joomla.
2. Download **[`pkg_scoutingoidc-1.0.2.zip`](https://github.com/Thidjs/scouting-nederland-oidc/releases/download/v1.0.2/pkg_scoutingoidc-1.0.2.zip)** uit de [v1.0.2-release](https://github.com/Thidjs/scouting-nederland-oidc/releases/tag/v1.0.2). Gebruik het releasebestand, niet de broncode-ZIP van GitHub.
3. Installeer de ZIP via **Systeem → Installeren → Extensies**.
4. **Activeer eerst de benodigde plugins** via **Systeem → Plugins**: **Scouting Nederland OIDC** (`system/scoutingoidc`), **Scouting Nederland OIDC – Accountbeveiliging** (`user/scoutingoidcsecurity`), **System – External Login** en **Authentication – External Login**. Zonder de geactiveerde Scouting-systeemplugin is de provider niet beschikbaar bij het aanmaken van een External Login-server.
5. Maak daarna in **External Login → Servers** een **nieuwe** server aan met de provider **Scouting Nederland OIDC**.
6. Configureer de Scouting Nederland OIDC-server, vul bij Client het Client ID in dat je tijdens de registratie bij Scouting Nederland gekregen hebt (zie hierboven)
7. Maak/publiceer een **External Login**-sitemodule en kies bij **Geavanceerd → Weergave** de layout **`default`**. In de geteste omgeving bleef de layout **`alone`** de tekst *Inloggen* tonen terwijl de Joomla-gebruiker al correct was aangemeld.
8. Test met een Scouting-account zonder bestaand lokaal Joomla-account met hetzelfde e-mailadres, controleer de automatische gebruikersaanmaak en beide koppelingen, meld je opnieuw aan en test ook uitloggen. Behoud een afzonderlijke Joomla-beheerderslogin.

## Upgraden van v1.0.0 naar v1.0.1

1. Maak vooraf een back-up van Joomla-bestanden en database.
2. **Verwijder de bestaande plugins of External Login-server niet.** Installeer `pkg_scoutingoidc-1.0.1.zip` over de bestaande installatie via **Systeem → Installeren → Extensies**.
3. Controleer dat zowel de systeemplugin als de accountbeveiligingsplugin versie **1.0.1** tonen.
4. Open de bestaande Scouting Nederland OIDC-server, controleer de vergrendelde instellingen en sla indien nodig op. Bestaande servergegevens en identiteitskoppelingen moeten behouden blijven.
5. Test uitloggen en opnieuw inloggen met een reeds gekoppeld Scouting-account. Controleer daarnaast de standaardinstellingen bij het aanmaken van een nieuwe server.

## Upgraden van v1.0.1 naar v1.0.2

1. Maak vooraf een back-up van Joomla-bestanden en database.
2. **Verwijder de bestaande plugins of External Login-server niet.** Download [`pkg_scoutingoidc-1.0.2.zip`](https://github.com/Thidjs/scouting-nederland-oidc/releases/download/v1.0.2/pkg_scoutingoidc-1.0.2.zip) en installeer het pakket over v1.0.1 via **Systeem → Installeren → Extensies**.
3. Controleer de geïnstalleerde pakket- en pluginversies en test de bestaande OIDC-login.
4. Controleer onder **Systeem → Update → Updatesites** of **Scouting Nederland OIDC Updates** ingeschakeld is, met type **Pakket** en URL `https://thidjs.github.io/scouting-nederland-oidc/updates/scoutingoidc.xml`.
5. Gebruik **Systeem → Update → Extensies → Controleren op updates** om te controleren of Joomla de updatebron zonder fouten kan raadplegen. Omdat v1.0.2 de nieuwste versie is, wordt nog geen nieuwere update verwacht.

**Let op:** v1.0.1 registreert nog geen updatesite. Daarom moet de overstap naar v1.0.2 eenmalig handmatig gebeuren. Vanaf v1.0.2 is de update-server geregistreerd; het daadwerkelijk detecteren en installeren van een nieuwe release via Joomla moet bij een volgende versie (bijvoorbeeld v1.0.3) nog worden getest.

**Problemen bij upgraden:** op de geteste server gaf Joomla aanvankelijk `Copy file failed` bij `plg_user_scoutingoidcsecurity.zip`. Het actieve bestand `plugins/user/scoutingoidcsecurity/src/Extension/Scoutingoidcsecurity.php` was na eerdere handmatige SSH-wijzigingen eigendom van `root:root` (rechten `644`), terwijl PHP-FPM als `www-data` draaide. Na herstel van het eigenaarschap slaagde de upgrade. Controleer bij een vergelijkbare fout eerst de bestandsrechten, de PHP-FPM-gebruiker en welke onderdelen al zijn bijgewerkt. **Verwijder of schakel de bestaande plugins niet blind uit.**

## Geteste serverinstellingen (v1.0.1)

Maak een **nieuwe** External Login-server aan met provider `Scouting Nederland OIDC`. Gebruik deze waarden voor de geteste Scouting Nederland-koppeling:

| Instelling | Waarde / advies |
| --- | --- |
| Issuer-URL | `https://login.scouting.nl` |
| Client-ID | De voor deze website geregistreerde Scouting Nederland-client-ID |
| Claim voor gebruikersnaam | `member_id` |
| Claim voor volledige naam | `name` |
| Claim voor e-mailadres | `email` |
| Eigen **Automatische registratie** (`scouting_registration_enabled`, tab Gebruikersgegevens) | **Ja** |
| External Login **Auto-register** (tab Server details) | **Nee** (verplicht en vergrendeld) |
| External Login **Auto-update** | **Nee** (verplicht en vergrendeld) |
| External Login **Change Password** | **Nee** (verplicht en vergrendeld) |
| External Login **User Validation** (`regex_user`) | **Leeg** (verplicht en vergrendeld) |
| External Login **Email Validation** (`regex_email`) | **Leeg** (verplicht en vergrendeld) |
| Niet-geverifieerde e-mailadressen toestaan | **Nee** (geteste instelling; controleer de toepasselijke `email_verified`-vereisten) |
| Standaard gebruikersgroep | **Registered** (groep 2) |
| External Login-sitemodule, **Geavanceerd → Weergave** | **`default`**; voor begroeting en uitlogknop ook de betreffende opties activeren |
| Externe loginmodule, cache | Uitgeschakeld (`cache=0`) in de geteste opstelling |

**Let op:** gebruik **niet** de algemene External Login-autoregistratie als vervanging voor de eigen Scouting-registratieroute. Deze maakt na controle van de Scouting-identiteit zowel een Joomla-account als de benodigde identiteitskoppelingen aan. Sinds v1.0.1 worden Auto-register, Auto-update en Change Password automatisch op **Nee** gezet en worden User Validation en Email Validation leeggemaakt en vergrendeld. Dit geldt voor nieuwe én bestaande Scouting OIDC-servers; bij opslaan controleert de plugin deze waarden ook server-side. Andere External Login-providers worden niet aangepast.

### Functionele test (29 oktober 2026)

De schone installatie op **Regio Westelijke Mijnstreek** (Joomla **5.4.10**, External Login **5.2.0**, Scouting Nederland OIDC **1.0.0**) bevestigde:

- OIDC-tokenuitwisseling, ID-tokenverificatie en UserInfo-verwerking slagen.
- Met een nog niet gebruikt e-mailadres wordt automatisch een Joomla-account aangemaakt met `member_id` als gebruikersnaam.
- De koppelingen in `#__externallogin_users` en `#__oidc_identities` worden vastgelegd; bij een volgende aanmelding wordt de identiteit als `KNOWN` herkend.
- De accountbeveiligingsplugin vindt het eenmalige authenticatiebewijs en staat de geldige Scouting-login toe.
- Joomla bouwt een geldige frontend-sessie op: de profielpagina is toegankelijk.
- De **modulelayout `alone`** gaf ten onrechte de indruk dat de login was mislukt; omschakelen naar **`default`** loste de zichtbare begroeting op.

**Bestaand e-mailadres:** is het Scouting-e-mailadres al gekoppeld aan een lokaal Joomla-account, dan weigert de Scouting-registratie bewust een nieuw account aan te maken. Dat voorkomt automatische accountovername op grond van uitsluitend een e-mailadres. Sinds de oplossing van [issue #6](https://github.com/Thidjs/scouting-nederland-oidc/issues/6) is voor dit conflict een veilige, vertaalbare frontendmelding geïmplementeerd; persoonsgegevens en exceptiondetails horen daarin niet thuis. Een beheerder moet een eventuele migratie/koppeling zorgvuldig en gecontroleerd uitvoeren; verwijder geen beheerdersaccounts om een test te laten slagen.

### Upgrade- en regressietest (30 oktober 2026)

Op Regio Westelijke Mijnstreek is het Joomla-pakket **v1.0.1** over **v1.0.0** geïnstalleerd. Na herstel van het eigenaarschap van één eerder handmatig aangepast PHP-bestand slaagde de pakketinstallatie. Beide pluginmanifesten tonen v1.0.1 en beide PHP-syntaxcontroles zijn geslaagd. Uitloggen, opnieuw inloggen met een bestaand Scouting-account, de bestaande serverinstellingen en de standaardwaarden van een nieuwe Scouting OIDC-server werkten na de upgrade naar behoren.

### Release- en updatefeedtest (30 oktober 2026)

De GitHub Actions-releaseworkflow voor **v1.0.2** heeft het installatiepakket gebouwd, de GitHub Release gepubliceerd, de XML-updatefeed gegenereerd en deze via GitHub Pages gepubliceerd. De Pages-deployment vereiste eenmalig een GitHub Environment-regel die release-tags `v*` toestaat; na het aanpassen van **Settings → Environments → github-pages → Deployment branches and tags** en **Re-run failed jobs** waren alle vier jobs succesvol.

De publieke [Joomla-updatefeed](https://thidjs.github.io/scouting-nederland-oidc/updates/scoutingoidc.xml) toont `pkg_scoutingoidc`, pakketversie `1.0.2`, de bijbehorende release-ZIP, een SHA-256-waarde, Joomla 5 en PHP 8.2 als minimum. Op Regio Westelijke Mijnstreek verliep de installatie van v1.0.2 succesvol; Joomla registreerde de ingeschakelde updatesite als **Pakket** (tijdens de test ID 19). De SHA-256-waarde in de feed is nog niet onafhankelijk tegen het ZIP-bestand gecontroleerd en een toekomstige automatische update is nog niet end-to-end getest.

## Repository

- `plugins/system/scoutingoidc/`: broncode van de systeemplugin
- `plugins/user/scoutingoidcsecurity/`: broncode van de accountbeveiliging
- `pkg_scoutingoidc.xml` en `pkg_script.php`: Joomla-pakket
- `dist/`: build-uitvoer; gebruik voor installatie de ZIP uit de betreffende GitHub-release
- `docs/`: handleidingen voor webmasters en beheerders (dienen nog geupdate te worden!!)

## Bekende aandachtspunten

- Sinds de oplossing van [issue #1](https://github.com/Thidjs/scouting-nederland-oidc/issues/1) verwijdert de accountbeveiligingsplugin de bijbehorende Scouting-identiteit uit `#__oidc_identities` na een succesvolle Joomla-gebruikersverwijdering. Oudere, reeds verweesde records worden hiermee niet automatisch gerepareerd; laat bestaande inconsistenties door technisch beheer controleren.
- Upgraden van een bestaande v1.0.0-installatie is op Regio Westelijke Mijnstreek getest. Handmatig als `root` gewijzigde pluginbestanden kunnen een latere Joomla-upgrade belemmeren; controleer eigenaarschap bij kopieerfouten.

## Roadmap

**v1.0.2 (uitgebracht):** Joomla-updatefeed via GitHub Pages, automatische releasepublicatie en controle op consistente manifestdatums ([issue #15](https://github.com/Thidjs/scouting-nederland-oidc/issues/15)). De installatie en updatesite-registratie zijn getest; detectie van een nieuwere update volgt bij een volgende release. Zie [CHANGELOG.md](CHANGELOG.md).

**v1.0.1 (uitgebracht):** veilige standaardwaarden en vergrendelde serverinstellingen, optioneel Client Secret bij de geteste publieke PKCE-client en succesvolle upgrade over v1.0.0. Zie [CHANGELOG.md](CHANGELOG.md).

**Afgerond vóór v1.0.1:** opruimen van Scouting-identiteiten na succesvolle Joomla-gebruikersverwijdering ([issue #1](https://github.com/Thidjs/scouting-nederland-oidc/issues/1), [PR #8](https://github.com/Thidjs/scouting-nederland-oidc/pull/8)); veilige, vertaalbare Nederlandstalige en Engelstalige frontendmeldingen bij registratieconflicten ([issue #6](https://github.com/Thidjs/scouting-nederland-oidc/issues/6), [PR #9](https://github.com/Thidjs/scouting-nederland-oidc/pull/9)). Het bestaande-e-mailadresconflict wordt intern herkend als `EXISTING_EMAIL` zonder automatisch op e-mailadres te koppelen. Andere registratieproblemen blijven generiek afgehandeld.

**Servermigratie:** [issue #4](https://github.com/Thidjs/scouting-nederland-oidc/issues/4) is administratief afgerond. Een bestaande External Login-server wordt niet automatisch naar de Scouting-provider omgezet; wijzig de `plugin`-kolom niet rechtstreeks in de database. Nieuwe servers via de Joomla-interface zijn de gedocumenteerde route.

**Open verbeterpunten:** volledige integratieverificatie van de Scouting trust policy en `email_verified` ([issue #3](https://github.com/Thidjs/scouting-nederland-oidc/issues/3); documentatie via [PR #10](https://github.com/Thidjs/scouting-nederland-oidc/pull/10)), end-to-end-test van de Joomla Update Manager bij een volgende release ([issue #5](https://github.com/Thidjs/scouting-nederland-oidc/issues/5)) en verdere foutdiagnostiek. De handmatige pakketupgrade via Joomla is wel getest.

**Toekomstige release:** eigen Joomla-loginmodule in Scouting Nederland-stijl ([issue #7](https://github.com/Thidjs/scouting-nederland-oidc/issues/7)), die de bestaande External Login/OIDC-beveiligingsketen hergebruikt.

## Licentie en herkomst

GPL, zie `LICENSE` en `NOTICE.md`. Bevat aanpassingen gebaseerd op External Login 5.2.0. Dit is geen officieel product van Scouting Nederland.

## Release-integriteit

De oorspronkelijke installatie-ZIP voor v1.0.0 heeft SHA-256:

`ac52a65db454f252b3dde8e8e6d0e89f63ad20989aa46d2a1644e4999064aaad`

De installatie-ZIP voor v1.0.1 heeft SHA-256:

`8136a36c876aa8eba36ac67c847cb9a66bedd585ee844144791ebbbacaf8d8b8`

De installatie-ZIP voor v1.0.2 heeft volgens de gepubliceerde Joomla-updatefeed SHA-256:

`ef327a6ebdc7552dc5bd9ccf296649f165b5eb459cb3714dcd48711bd278e3a5`
