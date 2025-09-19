# Mobile App Quick Reference

## Current Status
✅ **Ready for Xcode** - All configuration completed, waiting for Xcode installation

## Key Commands

### Development
```bash
# Start Laravel server (required for mobile app)
php artisan serve

# Open iOS simulator (after Xcode is installed)
npx cap open ios

# Sync changes to mobile app
npx cap sync ios

# Copy web assets only
npx cap copy ios
```

### Build Assets
```bash
# Build production assets
npm run build

# Development assets
npm run dev
```

## Important Files

- `capacitor.config.json` - Mobile app configuration
- `public/index.html` - Mobile app entry point
- `ios/` - Native iOS project (generated)
- `.env` - Updated with mobile-friendly settings

## Next Steps

1. **Install Xcode** from Mac App Store
2. **Run**: `npx cap open ios`
3. **Test** on iOS simulator
4. **Configure** signing & certificates
5. **Submit** to App Store

## App Details

- **Name**: Memorize Your Scripture
- **Bundle ID**: org.memorizeyourscripture.www
- **Platform**: iOS (ready), Android (not configured)
- **Type**: Hybrid (Laravel in WebView)

## Troubleshooting

**If mobile app won't load:**
1. Ensure Laravel server is running: `php artisan serve`
2. Check server is on localhost:8000
3. Sync changes: `npx cap sync ios`

**If Xcode errors:**
1. Ensure full Xcode is installed (not just command line tools)
2. Run: `cd ios/App && pod install`
3. Try: `npx cap sync ios`