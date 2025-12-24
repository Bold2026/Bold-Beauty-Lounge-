# Configuration Admin Panel - Bold Beauty Lounge

## ✅ État actuel

L'Admin Panel est maintenant **connecté à Firebase** et prêt à être utilisé.

## 🔧 Configuration requise

### 1. Créer un compte administrateur dans Firebase Authentication

1. Allez dans la console Firebase : https://console.firebase.google.com
2. Sélectionnez votre projet
3. Allez dans **Authentication** > **Users**
4. Cliquez sur **Add user**
5. Entrez l'email et le mot de passe de l'administrateur
6. Créez l'utilisateur

### 2. Créer le document admin dans Firestore

Une fois l'utilisateur créé dans Firebase Authentication, vous devez créer un document dans la collection `admins` dans Firestore :

1. Allez dans **Firestore Database**
2. Créez une collection nommée `admins` (si elle n'existe pas)
3. Créez un document avec l'ID = **l'UID de l'utilisateur Firebase Auth** (vous pouvez le trouver dans Authentication > Users)
4. Ajoutez les champs suivants :

```json
{
  "id": "UID_DE_L_UTILISATEUR",
  "email": "admin@boldbeauty.com",
  "name": "Nom de l'administrateur",
  "role": "admin",
  "isActive": true,
  "createdAt": "2024-01-01T00:00:00Z",
  "lastLoginAt": null
}
```

### 3. Structure des collections Firestore

L'Admin Panel utilise les collections suivantes :

- **`admins`** : Liste des administrateurs
  - `id` (string) : UID de l'utilisateur Firebase Auth
  - `email` (string) : Email de l'administrateur
  - `name` (string) : Nom de l'administrateur
  - `role` (string) : Rôle (généralement "admin")
  - `isActive` (boolean) : Si l'admin est actif
  - `createdAt` (timestamp) : Date de création
  - `lastLoginAt` (timestamp) : Dernière connexion

- **`bookings`** : Réservations
  - Voir `lib/models/admin/booking_model.dart` pour la structure complète

- **`services`** : Services du salon
  - Voir `lib/models/admin/service_model.dart` pour la structure complète

- **`timeSlots`** : Créneaux horaires
  - Voir `lib/models/admin/time_slot_model.dart` pour la structure complète

## 🚀 Utilisation

1. Lancez l'Admin Panel :
   ```bash
   flutter run -d chrome --web-port=8080 lib/admin_ui_only.dart
   ```

2. Accédez à : **http://localhost:8080**

3. Connectez-vous avec les identifiants de l'administrateur créé dans Firebase Authentication

## 🔐 Sécurité

- Seuls les utilisateurs avec un document dans la collection `admins` avec `isActive: true` peuvent se connecter
- L'authentification utilise Firebase Authentication (email/password)
- Les règles Firestore doivent être configurées pour protéger les données

## 📝 Notes importantes

- L'Admin Panel utilise le même projet Firebase que l'application mobile
- Assurez-vous que Firebase est correctement configuré dans `lib/services/firebase_service.dart`
- Les données sont synchronisées en temps réel avec Firestore

## 🐛 Dépannage

Si vous ne pouvez pas vous connecter :

1. Vérifiez que l'utilisateur existe dans Firebase Authentication
2. Vérifiez que le document existe dans la collection `admins` avec le bon UID
3. Vérifiez que `isActive` est `true` dans le document admin
4. Vérifiez la console du navigateur pour les erreurs Firebase



