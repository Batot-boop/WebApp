# 🎬 Movie Tracker Watchlist
IS333 Web-Based Information Systems – Spring 2026

---

## ⚙️ Setup (Do This First Before Anything)

### Step 1 — Install XAMPP
Download from: https://www.apachefriends.org  
Install it and open **XAMPP Control Panel**  
Start both **Apache** and **MySQL** (they should turn green ✅)

---

### Step 2 — Get The Project
Open your terminal and run:
```bash
git clone <your-github-repo-link>
```
Then move the project folder to:
```
D:\xampp\htdocs\movie_tracker
```
> ⚠️ If your XAMPP is on C: put it in C:\xampp\htdocs\movie_tracker

---

### Step 3 — Create The Database
1. Open your browser and go to: http://localhost/phpmyadmin
2. Click **New** on the left sidebar
3. Name it exactly: `movie_tracker` → click **Create**
4. Click on `movie_tracker` → click **Import** (top menu)
5. Choose the file `movie_tracker.sql` from the project folder → click **Go**

✅ Your database is ready!

---

### Step 4 — Run The Project
Open your browser and go to:
```
http://localhost/movie_tracker/index.php
```

---

## 📁 Project Files

| File | Who Made It | What It Does |
|------|-------------|--------------|
| `DB_Ops.php` | Database Team | All database functions (add, read, update, delete) |
| `movie_tracker.sql` | Database Team | Database backup – import this in phpMyAdmin |
| `index.php` | UI Team | Main page of the app |
| `header.php` | UI Team | Top navigation bar |
| `footer.php` | UI Team | Bottom footer |
| `API_Ops.php` | API Team | Connects to the movie API (TMDB) |
| `API_Ops.js` | API Team | Sends AJAX requests to API_Ops.php |

---

## 🗄️ Database Info

| Info | Value |
|------|-------|
| Database name | `movie_tracker` |
| Tables | `users`, `watchlist` |
| Connection file | `DB_Ops.php` |

---

## 🔌 How To Use DB_Ops.php (For AJAX Team)

All database actions go through `DB_Ops.php` using a POST request.  
Send a JSON body with an `action` field:

### Add a movie
```javascript
fetch('DB_Ops.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'add',
    user_id: 1,
    tmdb_id: 550,
    title: 'Fight Club',
    poster_path: 'https://image.tmdb.org/...',
    genre: 'Drama',
    release_year: 1999,
    status: 'want_to_watch',
    notes: ''
  })
});
```

### Get watchlist
```javascript
fetch('DB_Ops.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'read',
    user_id: 1,
    search: '',       // optional: search by title
    status: ''        // optional: filter by status
  })
});
```

### Update a movie
```javascript
fetch('DB_Ops.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'update',
    user_id: 1,
    id: 5,
    status: 'watched',
    user_rating: 9,
    notes: 'Amazing movie!'
  })
});
```

### Delete a movie
```javascript
fetch('DB_Ops.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'delete',
    user_id: 1,
    id: 5
  })
});
```

---

## 📋 Important Values To Know

**Status options** (use exactly as written):
- `want_to_watch`
- `watching`
- `watched`

**Rating:** number from `1` to `10`

---

## ✅ Submission Checklist

- [ ] `DB_Ops.php` present
- [ ] `movie_tracker.sql` present
- [ ] `index.php`, `header.php`, `footer.php` present
- [ ] `API_Ops.php` and `API_Ops.js` present
- [ ] `Upload.php` present
- [ ] `Team_Members.txt` present
- [ ] Delete `test_db.php` before submitting
- [ ] Folder named: `YourTeamNumber_ASSIGNMENT-1`

---

## ❓ Problems?

| Problem | Fix |
|---------|-----|
| Page not loading | Make sure Apache is running in XAMPP |
| Database error | Make sure MySQL is running and you imported the .sql file |
| Wrong database name | Must be exactly `movie_tracker` (lowercase) |

