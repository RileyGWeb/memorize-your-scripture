# App Store Preflight Checklist (Memorize Your Scripture)

Use this before every App Store upload.

## 1) Release Configuration

- [x] `capacitor.config.json` does **not** point to localhost (`server.url` removed)
- [ ] App version (`MARKETING_VERSION`) updated in Xcode
- [ ] Build number (`CURRENT_PROJECT_VERSION`) incremented in Xcode
- [ ] Production API/environment values confirmed

## 2) Build + Sync

From repo root:

```bash
npm run build
npx cap sync ios
```

Then in Xcode:

- [ ] Open `ios/App/App.xcworkspace`
- [ ] Product → Clean Build Folder
- [ ] Product → Archive (device target, not simulator)

## 3) Apple Metadata + Compliance

In App Store Connect:

- [ ] App Privacy answers completed
- [ ] Export Compliance answered
- [ ] Age rating completed
- [ ] App category selected
- [ ] Privacy Policy URL set
- [ ] Support URL set

## 4) Required URLs (already in app)

- Privacy Policy route: `/privacy-policy`
- Contact route: `/contact`

## 5) Account & Deletion Requirement

If users can create accounts, Apple expects account deletion support.

- [ ] Confirm in-app deletion flow works from Profile settings
- [ ] Confirm deletion support instructions exist on Privacy/Contact page

## 6) TestFlight Pass

- [ ] Upload archive to App Store Connect
- [ ] Add Internal Testers
- [ ] Verify core flows on TestFlight build:
  - [ ] Launch + auth modal
  - [ ] Memorize flow
  - [ ] Quiz flow
  - [ ] Memory bank
  - [ ] Safe area/header/footer on multiple pages

## 7) Submission Readiness

- [ ] App icon + screenshots for required devices
- [ ] Review notes for Apple reviewer added (include demo account if needed)
- [ ] No debug/dev references in UI or networking
- [ ] Submit for Review

---

## Useful Notes

- For release builds, do not use `server.url` in Capacitor config.
- If style/content appears stale in native app, run:

```bash
npm run build && npx cap sync ios
```

and rebuild from Xcode.
