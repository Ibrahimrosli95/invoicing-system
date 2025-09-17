# Security Guidelines

## ⚠️ Important Security Notice

This repository contains **enterprise-grade application code** that requires proper security configuration before deployment.

## 🔒 Before Deployment

### 1. Environment Configuration
**NEVER use the example environment files in production!**

- Copy `.env.example.prod` to `.env`
- Generate a unique `APP_KEY`: `php artisan key:generate`
- Use strong database credentials
- Configure proper mail settings
- Set `APP_DEBUG=false` in production

### 2. Database Security
- Use strong database passwords (minimum 16 characters)
- Limit database user privileges to only necessary permissions
- Never use default database names like `laravel` or `app`
- Enable database SSL connections if available

### 3. File Permissions
Set secure file permissions on your server:
```bash
chmod -R 755 /path/to/application
chmod -R 775 storage bootstrap/cache
chmod 600 .env
```

### 4. HTTPS Configuration
- **Always use HTTPS in production**
- Set `APP_URL` to use `https://`
- Configure proper SSL/TLS certificates
- Enable HSTS headers

### 5. Remove Development Tools
Before production deployment:
- Remove Laravel Debugbar: `composer remove barryvdh/laravel-debugbar`
- Ensure `APP_DEBUG=false`
- Clear all log files
- Remove any test data

## 🚫 Security Don'ts

- ❌ **Never commit .env files**
- ❌ **Never use default passwords**
- ❌ **Never enable debug mode in production**
- ❌ **Never commit database files**
- ❌ **Never expose sensitive API keys**
- ❌ **Never use weak session keys**

## 🔐 Authentication Security

### Two-Factor Authentication
- 2FA is available for all users
- Encourage all users to enable 2FA
- QR codes use Google Authenticator compatible format

### Password Security
- Minimum 8 characters required
- Passwords are hashed with bcrypt
- Account lockout after 5 failed attempts
- Password reset tokens expire after 1 hour

## 📊 Audit & Monitoring

The system includes:
- Comprehensive audit logging
- Security event monitoring
- Failed login attempt tracking
- Suspicious activity detection
- Real-time security dashboard

## 🔧 Security Headers

Configure these security headers on your web server:
```apache
# .htaccess example
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set Content-Security-Policy "default-src 'self'"
```

## 🆘 Security Issues

If you discover a security vulnerability:
1. **Do NOT create a public GitHub issue**
2. Contact the development team privately
3. Provide detailed information about the vulnerability
4. Allow reasonable time for fixes before disclosure

## 📋 Security Checklist

Before going live:
- [ ] Environment file configured with strong credentials
- [ ] APP_DEBUG set to false
- [ ] HTTPS enabled and configured
- [ ] Database passwords are strong and unique
- [ ] File permissions set correctly
- [ ] Security headers configured
- [ ] 2FA enabled for admin accounts
- [ ] Regular backups configured
- [ ] Monitoring and logging enabled
- [ ] Development tools removed

## 🔄 Regular Security Maintenance

1. **Update Dependencies**: Run `composer update` regularly
2. **Review Logs**: Check security logs weekly
3. **User Audit**: Review user accounts monthly
4. **Backup Testing**: Test backup restoration quarterly
5. **Security Scan**: Perform security scans periodically

---

**Remember**: Security is an ongoing process, not a one-time setup!