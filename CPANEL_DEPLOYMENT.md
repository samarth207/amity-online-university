# cPanel Deployment Guide

## Current Configuration
Your website is deployed in cPanel's `public_html` directory.

## Files That Must Be Uploaded

### Required Files in public_html:
```
public_html/
├── .htaccess              ⭐ CRITICAL - Must be uploaded!
├── index.html
├── application-form.html
├── thank-you.html
├── robots.txt
├── sitemap.xml
├── vercel.json           (optional)
├── _redirects            (optional)
├── web.config            (optional - for IIS only)
├── test.html             (diagnostic tool)
└── assets/
    ├── url-handler.js    ⭐ IMPORTANT
    └── images/
```

## Step-by-Step Deployment

### 1. Upload .htaccess File
**CRITICAL:** The `.htaccess` file must be uploaded to `public_html/`

**In cPanel File Manager:**
1. Go to File Manager
2. Navigate to `public_html/`
3. Click "Settings" (top right) → Check "Show Hidden Files (dotfiles)"
4. Upload or edit `.htaccess` file
5. Set permissions to `644` (Right-click → Change Permissions)

### 2. Verify .htaccess is Working
After upload, visit: `https://amityonlineadmission.in/test`

The diagnostic page will show if rewrites are working.

### 3. Clear All Caches

**Browser Cache:**
- Press `Ctrl + Shift + Delete`
- Clear "Cached images and files"
- Or use incognito/private mode

**cPanel Cache (if applicable):**
1. Go to cPanel → "Optimize Website"
2. Or cPanel → "LiteSpeed Cache" (if available)
3. Clear all caches

### 4. Test URLs
Try accessing these URLs (without .html):
- `https://amityonlineadmission.in/application-form`
- `https://amityonlineadmission.in/thank-you`

## Troubleshooting

### ❌ Still Getting 404 Errors?

**Check 1: Is .htaccess uploaded?**
```bash
# In cPanel File Manager, look for .htaccess in public_html/
# Make sure "Show Hidden Files" is enabled in Settings
```

**Check 2: File permissions**
`.htaccess` should be `644`:
1. Right-click `.htaccess`
2. Change Permissions
3. Set to `644` (Owner: Read+Write, Group: Read, World: Read)

**Check 3: Are rewrites enabled?**
Contact cPanel support and ask:
> "Please enable mod_rewrite for my account and ensure AllowOverride is set to All"

**Check 4: Test with .html extension**
Temporarily access:
- `https://amityonlineadmission.in/application-form.html`

If this works, the issue is definitely .htaccess configuration.

### ❌ Mixed Content Errors?

The `.htaccess` now includes:
- Force HTTPS redirect
- Content Security Policy headers

**Solution:**
1. Clear browser cache completely
2. Test in incognito mode
3. Check browser console (F12) for specific errors

### ❌ .htaccess Not Working At All?

**Option A: Contact cPanel Support**
Ask them to check:
1. Is mod_rewrite enabled?
2. Is AllowOverride set to All?
3. Are .htaccess files being processed?

**Option B: Check cPanel Error Logs**
1. cPanel → Metrics → Errors
2. Look for .htaccess related errors
3. Share errors with support

**Option C: Use Subdomain Approach**
Some hosts work better with subdomain configuration. Create:
- `application.amityonlineadmission.in` → points to `application-form.html`
- `thankyou.amityonlineadmission.in` → points to `thank-you.html`

## Verification Commands

If you have SSH access, run:
```bash
# Check if .htaccess exists
ls -la ~/public_html/.htaccess

# Check permissions
stat ~/public_html/.htaccess

# Check if mod_rewrite is loaded
httpd -M | grep rewrite
```

## Current .htaccess Features

✅ Extensionless URLs (`/application-form` instead of `/application-form.html`)
✅ HTTPS enforcement (HTTP → HTTPS redirect)
✅ Security headers (XSS protection, frame options)
✅ Mixed content blocking
✅ Clean URL redirects (.html → extensionless)

## Need More Help?

1. **Visit test page:** `https://amityonlineadmission.in/test`
2. **Check browser console:** Press F12 → Console tab
3. **Share error logs:** cPanel → Metrics → Errors
4. **Contact cPanel support:** Provide them with the .htaccess file

## Quick Fix (If Nothing Works)

If extensionless URLs still don't work after all troubleshooting:

1. The `assets/url-handler.js` provides JavaScript fallback
2. Access pages with `.html` extension
3. JavaScript will clean the URL in the browser
4. Continue while waiting for hosting support

This is already set up and loaded on all pages!
