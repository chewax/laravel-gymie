# Wallet membership cards (Apple & Google)

Members can be issued a digital membership card carrying their **member `code`
as a QR barcode** — the same credential the check-in kiosk validates. Cards are
**static** (issued once); re-issue if a membership renews.

In the admin panel: **Members → row menu → "Wallet card"** shows "Add to Apple
Wallet" / "Add to Google Wallet" buttons. The button only appears for a wallet
that is enabled and configured below.

All credential files live under `storage/app/wallet/` (git-ignored).

---

## Apple Wallet

### What to obtain
1. An **Apple Developer account** ($99/yr).
2. A **Pass Type ID**: Developer portal → Certificates, IDs & Profiles →
   Identifiers → **Pass Type IDs** → register e.g. `pass.cloud.mdevel.gymtastic`.
3. A **certificate** for that Pass Type ID. Create a CSR (Keychain Access →
   Certificate Assistant), upload it, download the `.cer`, import to Keychain,
   then **export as `.p12`** (set a password).
4. Apple's **WWDR** intermediate certificate (G4): https://www.apple.com/certificateauthority/
   Download `AppleWWDRCAG4.cer` and convert to PEM:
   ```
   openssl x509 -inform der -in AppleWWDRCAG4.cer -out wwdr.pem
   ```
5. Your **Team ID** (Apple Developer → Membership).

### Install
```
storage/app/wallet/apple/certificate.p12
storage/app/wallet/apple/wwdr.pem
```
`.env`:
```
WALLET_APPLE_ENABLED=true
WALLET_APPLE_PASS_TYPE_ID=pass.cloud.mdevel.gymtastic
WALLET_APPLE_TEAM_ID=XXXXXXXXXX
WALLET_APPLE_ORG_NAME=Gymtastic
WALLET_APPLE_CERT_PASSWORD=your_p12_password
```
Then `php artisan config:cache`. Tap "Add to Apple Wallet" on a member to test.

---

## Google Wallet

### What to obtain
1. A **Google Cloud project**; enable the **Google Wallet API**.
2. A **Wallet issuer account**: https://pay.google.com/business/console → get your
   numeric **Issuer ID**.
3. A **service account** in the Cloud project with a **JSON key**, and grant that
   service account access in the Wallet console (Users → add the SA email).

### Install
```
storage/app/wallet/google/service-account.json
```
`.env`:
```
WALLET_GOOGLE_ENABLED=true
WALLET_GOOGLE_ISSUER_ID=3388000000022000000
WALLET_GOOGLE_CLASS_SUFFIX=gym_membership
WALLET_GOOGLE_PROGRAM_NAME=Gymtastic
```
Then create the membership class once:
```
php artisan config:cache
php artisan wallet:google-setup
```
Tap "Add to Google Wallet" on a member to test.

---

## Notes
- These are **static** passes. To auto-update a saved Apple pass you'd need a pass
  web service + APNs; Google supports updates by PATCHing the object via the API.
- The kiosk reads the QR (`code`); keep member codes stable.
