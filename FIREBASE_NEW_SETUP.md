# 🔥 Configuration Firebase - Guide Complet

## 📋 Prérequis

1. **Compte Firebase** : Créez un compte sur [Firebase Console](https://console.firebase.google.com/)
2. **Projet Firebase** : Créez un nouveau projet ou utilisez un projet existant
3. **FlutterFire CLI** : Outil pour générer automatiquement la configuration

---

## 🚀 Étape 1 : Installer FlutterFire CLI

```bash
# Installer FlutterFire CLI globalement
dart pub global activate flutterfire_cli

# Vérifier l'installation
flutterfire --version
```

---

## 🔧 Étape 2 : Se connecter à Firebase

```bash
# Se connecter à votre compte Firebase
firebase login

# Ou si vous utilisez déjà Firebase CLI
firebase login --no-localhost
```

---

## ⚙️ Étape 3 : Configurer Firebase pour votre projet

```bash
# Naviguer vers le répertoire du projet
cd "/Users/jb/Desktop/Bestcrea/codesource/bold_beauty_lounge_beta"

# Lancer la configuration FlutterFire
flutterfire configure
```

### Ce que fait `flutterfire configure` :

1. **Détecte vos projets Firebase** : Affiche la liste de vos projets Firebase
2. **Sélection du projet** : Choisissez votre projet Firebase
3. **Sélection des plateformes** : Choisissez les plateformes à configurer :
   - ✅ Android
   - ✅ iOS
   - ✅ Web
   - ✅ macOS (optionnel)
   - ✅ Windows (optionnel)
4. **Génération automatique** : Génère le fichier `lib/firebase_options.dart` avec vos vraies credentials
5. **Configuration Android** : Télécharge et place `google-services.json` dans `android/app/`
6. **Configuration iOS** : Télécharge et place `GoogleService-Info.plist` dans `ios/Runner/`

---

## 📱 Étape 4 : Vérifier la configuration

### Vérifier `lib/firebase_options.dart`

Le fichier devrait maintenant contenir vos vraies valeurs Firebase au lieu de `TODO_REPLACE_WITH_...`

### Vérifier Android

```bash
# Vérifier que google-services.json existe
ls -la android/app/google-services.json
```

### Vérifier iOS

```bash
# Vérifier que GoogleService-Info.plist existe
ls -la ios/Runner/GoogleService-Info.plist
```

---

## 🔐 Étape 5 : Configurer Firebase Console

### 1. Authentication

1. Allez dans **Firebase Console** → **Authentication**
2. Activez **Email/Password** dans l'onglet "Sign-in method"
3. (Optionnel) Activez **Phone** pour l'authentification par téléphone

### 2. Firestore Database

1. Allez dans **Firestore Database**
2. Créez une base de données en mode **Production** ou **Test**
3. Configurez les règles de sécurité (voir `firestore.rules` dans le projet)

### 3. Storage

1. Allez dans **Storage**
2. Créez un bucket de stockage
3. Configurez les règles de sécurité

### 4. Cloud Messaging (FCM)

1. Allez dans **Cloud Messaging**
2. Configurez les clés API si nécessaire

---

## 📝 Structure Firestore Recommandée

```
users/
  └── {userId}
      ├── email
      ├── name
      ├── phone
      └── createdAt

bookings/
  └── {bookingId}
      ├── userId
      ├── serviceId
      ├── employeeId
      ├── date
      ├── time
      ├── status
      └── createdAt

employees/
  └── {employeeId}
      ├── name
      ├── email
      ├── phone
      ├── role
      └── isActive

services/
  └── {serviceId}
      ├── name
      ├── category
      ├── duration
      ├── price
      └── isActive

categories/
  └── {categoryId}
      ├── name
      └── description

customers/
  └── {customerId}
      ├── name
      ├── email
      ├── phone
      └── createdAt

reviews/
  └── {reviewId}
      ├── userId
      ├── serviceId
      ├── rating
      ├── comment
      └── createdAt
```

---

## ✅ Vérification Finale

### Tester l'initialisation Firebase

```bash
# Lancer l'application
flutter run

# Ou pour le panneau admin
flutter run -d chrome --web-port=8080 lib/main_admin_direct.dart
```

### Vérifier les logs

Vous devriez voir dans la console :
```
✅ Firebase.initializeApp() completed successfully
✅ FirebaseService configured
✅ AnalyticsService initialized
✅ NotificationService initialized
🔥 Firebase initialization completed successfully
```

### Vérifier que firebase_options.dart est configuré

```bash
# Vérifier que le fichier contient de vraies valeurs (pas de TODO_REPLACE)
grep -v "TODO_REPLACE" lib/firebase_options.dart | head -20
```

Le fichier devrait contenir des valeurs réelles comme :
- `apiKey: 'AIza...'` (pas `TODO_REPLACE_WITH_YOUR_...`)
- `projectId: 'votre-projet-id'` (pas `TODO_REPLACE_WITH_YOUR_PROJECT_ID`)

---

## 🐛 Dépannage

### Erreur : "Firebase is not configured. Please run 'flutterfire configure'"

**Solution** : C'est normal avant la configuration. Exécutez simplement :
```bash
flutterfire configure
```

### Erreur : "No Firebase App '[DEFAULT]' has been created"

**Solution** : Vérifiez que `flutterfire configure` a été exécuté et que `firebase_options.dart` contient des valeurs réelles (pas de `TODO_REPLACE`).

### Erreur : "google-services.json not found"

**Solution** : 
```bash
# Ré-exécuter flutterfire configure
flutterfire configure
```

### Erreur : "Firebase project not found"

**Solution** :
```bash
# Vérifier que vous êtes connecté
firebase login

# Ré-exécuter la configuration
flutterfire configure
```

### Erreur sur Web : "FirebaseException"

**Solution** : Vérifiez que les règles Firestore autorisent l'accès depuis le web.

### Erreur : "UnsupportedError: Firebase is not configured"

**Solution** : Le fichier `firebase_options.dart` contient encore des placeholders. Exécutez :
```bash
flutterfire configure
```

---

## 📚 Ressources

- [Documentation FlutterFire](https://firebase.flutter.dev/)
- [Firebase Console](https://console.firebase.google.com/)
- [FlutterFire CLI](https://firebase.flutter.dev/docs/cli/)

---

## 📞 Support

Pour toute question :
- **Développeur** : M. Marouan Bahtit
- **Email** : bahtitmarouan@gmail.com
- **Téléphone** : +212 636 499 140
- **Entreprise** : Bestcrea

---

**Dernière mise à jour** : 2024

