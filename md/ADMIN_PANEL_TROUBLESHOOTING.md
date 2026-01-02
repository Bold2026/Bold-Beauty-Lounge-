# Admin Panel Troubleshooting Guide

## Problem: Admin Panel Won't Open

### Quick Checks

1. **Is the server running?**
   ```bash
   lsof -ti:8080
   ```
   If nothing returns, the server is not running.

2. **Is the URL correct?**
   - Open: `http://localhost:8080`
   - NOT: `https://localhost:8080`
   - NOT: `localhost:8080` (missing http://)

3. **Check browser console**
   - Press F12 in Chrome
   - Go to Console tab
   - Look for red errors

### Common Issues

#### Issue 1: Firebase Configuration Error
**Symptom**: Red screen with Firebase error
**Solution**: 
- The app now handles this gracefully
- UI should load even without Firebase
- Update `lib/firebase_options.dart` with real values if needed

#### Issue 2: Blank White Screen
**Symptom**: Page loads but shows blank white screen
**Possible Causes**:
- JavaScript error in console
- Flutter compilation error
- Missing dependencies

**Solution**:
1. Check Chrome DevTools Console (F12)
2. Look for red errors
3. Check Network tab for failed requests

#### Issue 3: "Cannot Connect" Error
**Symptom**: Browser says "Cannot connect to server"
**Solution**:
1. Verify server is running: `lsof -ti:8080`
2. Try restarting: 
   ```bash
   pkill -f "flutter run"
   flutter run -d chrome --web-port=8080 lib/main_admin_direct.dart
   ```

#### Issue 4: Port Already in Use
**Symptom**: Error about port 8080 being in use
**Solution**:
```bash
# Kill process on port 8080
lsof -ti:8080 | xargs kill -9

# Or use a different port
flutter run -d chrome --web-port=8081 lib/main_admin_direct.dart
```

### Step-by-Step Debugging

1. **Check if Flutter is working**
   ```bash
   flutter doctor
   ```

2. **Check for compilation errors**
   ```bash
   flutter analyze lib/main_admin_direct.dart
   ```

3. **Clean and rebuild**
   ```bash
   flutter clean
   flutter pub get
   flutter run -d chrome --web-port=8080 lib/main_admin_direct.dart
   ```

4. **Check browser console**
   - Open Chrome
   - Press F12
   - Go to Console tab
   - Look for errors

5. **Check network requests**
   - Open Chrome DevTools (F12)
   - Go to Network tab
   - Reload page
   - Look for failed requests (red)

### Expected Behavior

When working correctly:
- Server starts and shows: "Serving at http://localhost:8080"
- Chrome opens automatically
- Admin login screen appears
- Even if Firebase is not configured, UI should still load

### If Nothing Works

Try the minimal test:
```bash
flutter run -d chrome --web-port=8080 lib/main_admin_direct.dart --verbose
```

This will show detailed output of what's happening.

### Contact Points

If the panel still won't open:
1. Share the Chrome DevTools Console errors
2. Share the terminal output from `flutter run`
3. Share any error messages you see

