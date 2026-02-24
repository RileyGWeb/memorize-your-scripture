# TestFlight Setup Instructions

## For You (The Developer)

### 1. Archive Your App in Xcode

1. Open your project in Xcode
2. At the top, select **Any iOS Device (arm64)** as the build destination (not a simulator)
3. Go to **Product** → **Archive**
4. Wait for the archive to complete (this may take a few minutes)

### 2. Upload to App Store Connect

1. Once archiving finishes, the **Organizer** window will open
2. Select your latest archive
3. Click **Distribute App**
4. Choose **TestFlight & App Store**
5. Click **Next**
6. Choose **Upload** and click **Next**
7. Select your distribution certificate and provisioning profile (usually auto-selected)
8. Click **Upload**
9. Wait for the upload to complete (5-10 minutes depending on your internet)

### 3. Process Your Build in App Store Connect

1. Go to [App Store Connect](https://appstoreconnect.apple.com)
2. Sign in with your Apple Developer account
3. Click **My Apps**
4. Find and click **Memorize Your Scripture**
5. Click the **TestFlight** tab at the top
6. Wait for your build to finish processing (you'll get an email when ready - usually 10-30 minutes)
7. Once processed, you may need to add **Export Compliance** information:
   - Click on your build
   - Answer "No" to encryption questions (unless you added custom encryption)
   - Submit

### 4. Create a Test Group (First Time Only)

1. Still in the **TestFlight** tab
2. Click the **+** button next to **Internal Testers** or **External Testers**
   - **Internal Testers**: Up to 100 people on your Apple Developer team (instant access)
   - **External Testers**: Anyone with an email (requires Apple review, takes 24-48 hours)
3. For quick testing, choose **Internal Testers** if your friend can be added to your team, otherwise use **External Testers**
4. Name your group (e.g., "Friends Testing")
5. Click **Create**

### 5. Add Your Friend as a Tester

**Option A: Internal Tester (Faster - No Apple Review)**
1. Go to **Users and Access** in App Store Connect
2. Click the **+** button
3. Add your friend's email
4. Assign them the role **App Manager** or **Developer** 
5. They'll receive an invitation email to join your team
6. Once they accept, add them to your TestFlight group

**Option B: External Tester (Easier - Requires Apple Review)**
1. In the **TestFlight** tab, click your test group
2. Click the **+** button next to **Testers**
3. Enter your friend's email address
4. Click **Add**
5. Your friend will receive an invite email within 24-48 hours (after Apple reviews)

---

## For Your Friend (The Tester)

### What They Need to Do

**Step 1: Install TestFlight**
1. Open the **App Store** on your iPhone
2. Search for **TestFlight**
3. Download and install the free TestFlight app from Apple

**Step 2: Accept the Invitation**
1. Check your email for an invitation from "App Store Connect"
2. Open the email and tap **View in TestFlight** or **Accept Invitation**
3. If prompted, tap **Open** to launch TestFlight

**Step 3: Install the App**
1. TestFlight will open automatically
2. You'll see "Memorize Your Scripture" 
3. Tap **Accept** then tap **Install**
4. Enter your iPhone passcode if prompted
5. Wait for the app to download and install

**Step 4: Open and Use the App**
1. Find the app on your home screen (it will have a small orange dot indicating it's a beta)
2. Tap to open and start using it!

**If They Have Issues:**
- Make sure they're signed in to iCloud/App Store with the same email you sent the invite to
- Check spam/junk folder for the invitation email
- The invite link expires after 30 days
- TestFlight is free and required to install beta apps

---

## Quick Summary

**Your Steps:**
1. Archive in Xcode → Distribute → Upload to App Store Connect
2. Wait for build to process (check email)
3. Add friend's email as External Tester in TestFlight tab
4. Wait 24-48 hours for Apple review

**Their Steps:**
1. Install TestFlight app from App Store
2. Open email invitation and tap "View in TestFlight"
3. Tap Install
4. Done!

---

## Troubleshooting

**Build not appearing?**
- Check **Activity** tab in App Store Connect for processing status
- You'll receive an email when processing is complete

**Friend didn't receive email?**
- Check spam/junk folders
- Verify you entered the correct email
- Resend invitation from TestFlight tab

**Can't archive?**
- Make sure you selected "Any iOS Device" not a simulator
- Check that your signing & capabilities are configured correctly
- Clean build folder: Product → Clean Build Folder

**Upload failing?**
- Check your internet connection
- Make sure your Apple Developer account is in good standing
- Try again - sometimes App Store Connect is temporarily unavailable
