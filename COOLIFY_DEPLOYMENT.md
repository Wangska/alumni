# 🚀 Coolify Deployment Guide - Alumni Management System

## Prerequisites
- Coolify instance running
- MySQL database service available

## Step 1: Set Up MySQL Database in Coolify

1. In Coolify, create a new **MySQL** service
2. Note down the connection details:
   - Host/Service Name
   - Port (usually 3306)
   - Username (usually root)
   - Password (auto-generated or custom)
   - Database name

## Step 2: Configure Environment Variables

In your Coolify project settings, add these environment variables:

```bash
DB_CONNECTION=mysql
DB_HOST=your-mysql-service-name
DB_PORT=3306
DB_DATABASE=alumni_db
DB_USERNAME=root
DB_PASSWORD=your-secure-password
```

### Example with your credentials:
```bash
DB_CONNECTION=mysql
DB_HOST=sks4w8c8wkkokoko4g4wckws
DB_PORT=3306
DB_DATABASE=default
DB_USERNAME=root
DB_PASSWORD=OvVXddMQi1EvINhiAQxE8n8NjDardQefuj2LaPmVFx1KM2wURYLRjaeRUitMY1C3
```

## Step 3: Import Database

### Option A: Via phpMyAdmin (if available)
1. Access phpMyAdmin in Coolify
2. Import `database/alumni_db.sql`
3. Done!

### Option B: Via MySQL Command Line
```bash
mysql -h sks4w8c8wkkokoko4g4wckws \
      -u root \
      -p"OvVXddMQi1EvINhiAQxE8n8NjDardQefuj2LaPmVFx1KM2wURYLRjaeRUitMY1C3" \
      -P 3306 \
      default < database/alumni_db.sql
```

### Option C: Create Database via SQL
Connect to your MySQL service and run:
```sql
CREATE DATABASE IF NOT EXISTS alumni_db;
USE alumni_db;
-- Then import the alumni_db.sql file
```

## Step 4: Deploy Application

1. **Push your code** to your Git repository
2. **Connect Coolify** to your repository
3. **Set environment variables** as shown in Step 2
4. **Deploy** the application

## Step 5: Verify Connection

After deployment, check if the database connection works:

### Test URL:
```
https://your-app.coolify.io/admin/test_connection.php
```

## 📝 Important Notes

### Database Connection
The `admin/db_connect.php` file now supports:
- ✅ Environment variables (for Coolify)
- ✅ Local development (fallback to localhost)

### File Permissions
Ensure these directories are writable:
```bash
chmod -R 755 admin/assets/uploads/
chmod -R 755 admin/assets/img/
```

### Security Recommendations

1. **Change Default Passwords**
   - Admin password: `admin123` → Change immediately!
   - Update in database: `users` table

2. **Update Password Hashing**
   - Current: MD5 (not secure)
   - Recommended: Use `password_hash()` in production

3. **Environment Variables**
   - Never commit `.env` files to Git
   - Keep passwords secure in Coolify secrets

## 🔧 Troubleshooting

### Connection Refused
- ✅ Check if MySQL service is running
- ✅ Verify environment variables are set correctly
- ✅ Ensure DB_HOST matches your MySQL service name

### Database Not Found
- ✅ Import the SQL file: `database/alumni_db.sql`
- ✅ Verify DB_DATABASE matches your database name

### Permission Denied
- ✅ Check MySQL user permissions
- ✅ Verify DB_USERNAME and DB_PASSWORD

### Cannot Access Admin Panel
- ✅ Check Apache/Nginx configuration
- ✅ Ensure `.htaccess` rules are enabled (if using Apache)

## 📊 Default Credentials

### Admin Login
```
URL: https://your-app.coolify.io/admin/
Username: admin
Password: admin123
```
**⚠️ CHANGE THIS IMMEDIATELY AFTER FIRST LOGIN!**

### Test Alumni Account
```
URL: https://your-app.coolify.io/
Email: jaymarcandol9@gmail.com
Password: 123
```

## 🎯 Post-Deployment Checklist

- [ ] Database imported successfully
- [ ] Environment variables configured
- [ ] Admin login works
- [ ] Alumni login works
- [ ] File uploads working
- [ ] Changed default admin password
- [ ] Configured system settings in admin panel
- [ ] Tested all major features

## 🆘 Support

If you encounter issues:

1. Check Coolify logs
2. Review database connection settings
3. Verify environment variables
4. Check file permissions

---

**Happy Deploying! 🚀**

