# 🚀 Comment lancer le panneau d'administration web

## ⚠️ Important
L'application ne s'ouvrira **PAS automatiquement** dans le navigateur.

## 📋 Instructions

### 1. Lancer l'application (sans ouverture automatique)

```bash
cd admin_web_panel
flutter run -d chrome --web-port=8080 --no-web-browser
```

### 2. Ouvrir manuellement dans le navigateur

Une fois que vous voyez dans le terminal :
```
Flutter run key commands.
r Hot reload. 🔥🔥🔥
R Hot restart.
...
```

**Ouvrez Chrome manuellement** et allez à :
```
http://localhost:8080
```

---

## 🔧 Alternative : Build et servir

### Option 1 : Build statique
```bash
cd admin_web_panel
flutter build web --release
```

Puis servez le dossier `build/web` avec un serveur local.

### Option 2 : Avec un serveur Python
```bash
cd admin_web_panel
flutter build web --release
cd build/web
python3 -m http.server 8080
```

Puis ouvrez : `http://localhost:8080`

---

## 📝 Note

L'application est prête et fonctionne. Elle attend juste que vous l'ouvriez manuellement dans votre navigateur à l'URL indiquée.




