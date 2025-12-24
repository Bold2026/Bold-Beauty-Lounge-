# ✅ Restauration de la Page "Mon profil" - Version 29/11/2025

**Date de restauration :** 19 décembre 2025  
**Version cible :** 29 novembre 2025

---

## 📋 Structure de la Page Profil (Non Connecté)

### **Header**
- Titre : "Mon profil" (centré, noir)
- Icône profil à droite

### **Avatar**
- Cercle beige (`Color(0xFFE9D7C2)`)
- Silhouette grise (icône person_outline)
- Taille : 100x100

### **Carte de Connexion**
- Fond blanc avec coins arrondis
- Texte : "Connectez-vous pour profiter de toutes les fonctionnalités"
- Bouton noir : "Se connecter"
- Lien : "Pas encore de compte ? S'inscrire" (souligné)

### **Carte Statistiques**
- Fond blanc avec coins arrondis
- 3 sections horizontales :
  1. **Points fidélité** : Icône beige circulaire, "0 pts"
  2. **Réservations** : Icône beige circulaire, "0"
  3. **Offres actives** : Icône beige circulaire, "0"
- Icônes dans des cercles beiges (`Color(0xFFE9D7C2)`)

### **Options Menu**
- Carte blanche avec 2 options uniquement :
  1. **Contacter le support**
     - Icône : settings (beige)
     - Sous-titre : "Email ou téléphone"
  2. **Langues**
     - Icône : globe (beige)
     - Sous-titre : "Choisissez votre langue préférée"

---

## 📱 Modal de Connexion

### **Design**
- Fond gris foncé (`Color(0xFF111111)`)
- Coins arrondis en haut
- Bouton de fermeture (X) en haut à droite

### **Contenu**
1. **Titre** : "Connectez-vous" (blanc, gras)
2. **Description** : "Accédez à vos réservations, points fidélité et avantages exclusifs." (blanc, opacité 0.7)
3. **Section** : "Connexion rapide par téléphone"
4. **Formulaire** :
   - Prénom (champ texte avec icône user)
   - Indicatif + Numéro de téléphone (2 champs côte à côte)
   - Adresse e-mail (champ texte avec icône mail)
5. **Boutons de confirmation** :
   - "Confirmer via Gmail" (rouge, `Colors.redAccent`)
   - "Confirmer via WhatsApp" (vert, `Colors.green`)
6. **Séparateur** : "Ou" (ligne horizontale)
7. **Bouton Google** : "Continuer avec Google" (bordure blanche)
8. **Lien** : "Pas encore de compte ? S'inscrire" (blanc, opacité 0.8)

---

## 🔄 Modifications Effectuées

### Fichier : `lib/screens/profile/offline_profile_screen.dart`

1. **Avatar restauré** :
   - Couleur beige (`Color(0xFFE9D7C2)`)
   - Forme circulaire
   - Icône silhouette grise

2. **Carte statistiques restaurée** :
   - Icônes dans des cercles beiges (au lieu de carrés)
   - 3 sections : Points fidélité, Réservations, Offres actives
   - Layout horizontal avec `Expanded` pour équilibrer

3. **Menu simplifié** :
   - ❌ **Supprimé :** Bold Info, FAQ, À propos, Conditions, Confidentialité
   - ✅ **Conservé :** Contacter le support, Langues
   - Icône "Contacter le support" changée en `settings` (au lieu de `lifeBuoy`)

4. **Modal de connexion** :
   - Bouton de fermeture (X) ajouté en haut à droite
   - Structure déjà conforme aux captures d'écran

---

## ✅ Fonctionnalités

- ✅ Header avec titre "Mon profil" et icône
- ✅ Avatar beige avec silhouette
- ✅ Carte de connexion blanche
- ✅ Bouton "Se connecter" noir
- ✅ Lien "S'inscrire"
- ✅ Carte statistiques avec 3 sections
- ✅ Icônes dans cercles beiges
- ✅ Menu simplifié (2 options)
- ✅ Modal de connexion avec formulaire
- ✅ Boutons Gmail (rouge) et WhatsApp (vert)
- ✅ Bouton Google avec bordure blanche

---

## 📝 Notes Importantes

1. **Statistiques** :
   - Les valeurs sont statiques (0 pts, 0 réservations, 0 offres)
   - Les icônes sont dans des cercles beiges de 48x48

2. **Menu** :
   - Seulement 2 options visibles pour la version non connectée
   - Les autres options (FAQ, À propos, etc.) sont accessibles après connexion

3. **Modal de connexion** :
   - Formulaire avec validation par Gmail ou WhatsApp
   - Option Google disponible
   - Lien vers inscription

---

## ✅ Statut

**Restauration complète terminée !** ✅

La page "Mon profil" correspond maintenant exactement à la version du 29 novembre 2025 avec toutes les spécifications demandées.







