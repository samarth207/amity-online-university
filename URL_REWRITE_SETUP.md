# URL Rewriting Setup Guide

This website uses extensionless URLs (e.g., `/application-form` instead of `/application-form.html`). 

## How It Works

The site includes multiple configuration files to support different hosting platforms:

### 1. **Apache Hosting** (Most common shared hosting)
- File: `.htaccess`
- The `.htaccess` file handles URL rewrites automatically
- **Requirements**: mod_rewrite must be enabled on your server
- **Test**: If `.htaccess` isn't working, contact your hosting provider to enable mod_rewrite

### 2. **IIS / Windows Hosting**
- File: `web.config`
- Used for Microsoft IIS servers
- Automatically recognized by IIS

### 3. **Vercel**
- File: `vercel.json`
- Handles rewrites and redirects automatically

### 4. **Netlify**
- File: `_redirects`
- Placed in root directory for automatic detection

### 5. **JavaScript Fallback**
- File: `assets/url-handler.js`
- Loaded on all pages as a backup solution
- Works even if server-side rewrites fail

## Troubleshooting

### Issue: Pages showing 404 errors

**Solution 1: Verify .htaccess is working**
1. Check if your hosting supports `.htaccess` files
2. Ensure `.htaccess` is uploaded to the root directory
3. Contact hosting support to enable `mod_rewrite` module

**Solution 2: Check file permissions**
```bash
chmod 644 .htaccess
chmod 644 *.html
```

**Solution 3: Force .htaccess usage**
Add this to your hosting control panel's Apache configuration (if available):
```
AllowOverride All
```

**Solution 4: Use direct .html URLs temporarily**
If rewrites aren't working, you can temporarily access:
- `https://yourdomain.com/application-form.html`
- `https://yourdomain.com/thank-you.html`

The JavaScript fallback will automatically remove the `.html` from the URL bar.

### Issue: Mixed content errors

The site includes a Content Security Policy that:
- Upgrades all HTTP requests to HTTPS
- Blocks mixed content
- Clear browser cache if you see old errors

## Deployment Checklist

- [ ] Upload all files including `.htaccess`
- [ ] Verify `.htaccess` file is not hidden/ignored
- [ ] Test accessing `/application-form` without `.html`
- [ ] Clear browser cache and test again
- [ ] Check browser console for any errors

## Contact Hosting Support

If extensionless URLs aren't working, ask your hosting provider:
1. "Is mod_rewrite enabled for my account?"
2. "Are .htaccess files processed in my hosting plan?"
3. "Can you enable AllowOverride All for my directory?"

Most hosting providers will enable this within minutes.
