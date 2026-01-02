# Firebase Setup Instructions

## Step 1: Install FlutterFire CLI

```bash
dart pub global activate flutterfire_cli
```

Add to your PATH (if not already):
```bash
export PATH="$PATH":"$HOME/.pub-cache/bin"
```

## Step 2: Configure Firebase

Run the following command in your project root:

```bash
cd "/Users/jb/Desktop/Bestcrea/codesource/bold_beauty_lounge_beta"
flutterfire configure --project=bold-beauty-app
```

This will:
- Connect to your Firebase project
- Generate `lib/firebase_options.dart` with proper configuration
- Update Android and iOS configuration files

## Step 3: Android Configuration (Already Done)

✅ The following is already configured in `android/app/build.gradle.kts`:

```kotlin
plugins {
    id("com.google.gms.google-services")
}

dependencies {
    implementation(platform("com.google.firebase:firebase-bom:34.7.0"))
    implementation("com.google.firebase:firebase-analytics")
    implementation("com.google.firebase:firebase-auth")
    implementation("com.google.firebase:firebase-firestore")
    implementation("com.google.firebase:firebase-storage")
    implementation("com.google.firebase:firebase-messaging")
}
```

✅ And in `android/build.gradle.kts`:

```kotlin
plugins {
    id("com.google.gms.google-services") version "4.4.4" apply false
}
```

## Step 4: Flutter Code (Already Updated)

✅ The `main_admin_direct.dart` file now properly initializes Firebase:

```dart
await Firebase.initializeApp(
  options: DefaultFirebaseOptions.currentPlatform,
);
```

## Next Steps

1. **Run flutterfire configure:**
   ```bash
   flutterfire configure --project=bold-beauty-app
   ```

2. **Verify firebase_options.dart was generated:**
   ```bash
   ls -la lib/firebase_options.dart
   ```

3. **Run the app:**
   ```bash
   flutter run -d chrome --web-port=8080 lib/main_admin_direct.dart
   ```

## Troubleshooting

If `flutterfire configure` fails:
- Make sure you're logged into Firebase: `firebase login`
- Check your Firebase project ID
- Ensure you have the correct permissions

If you see Firebase errors after configuration:
- Verify `firebase_options.dart` has real values (not "YOUR_*")
- Check that your Firebase project has Web app enabled
- Ensure Firebase services are enabled in Firebase Console

