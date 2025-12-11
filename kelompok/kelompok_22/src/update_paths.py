#!/usr/bin/env python3
"""
Script untuk update path references dari struktur lama ke struktur baru
"""

import os
import re
from pathlib import Path

# Base directory
BASE_DIR = Path(__file__).parent

# Path mappings
PATH_MAPPINGS = {
    # Frontend pages
    "frontend/pages": {
        "includes/auth.php": "../../backend/middleware/auth.php",
        "includes/config.php": "../../backend/utils/config.php",
        "includes/admin_utils.php": "../../backend/utils/admin_utils.php",
        "css/styles.css": "../assets/css/styles.css",
        "js/app.js": "../assets/js/app.js",
        "admin.php": "../../backend/controllers/admin.php",
        "petugas.php": "../../backend/controllers/petugas.php",
        "super_admin.php": "../../backend/controllers/super_admin.php",
        "index.html": "index.html",
        "login.php": "login.php",
    },
    # Backend controllers
    "backend/controllers": {
        "includes/auth.php": "../middleware/auth.php",
        "includes/config.php": "../utils/config.php",
        "includes/admin_utils.php": "../utils/admin_utils.php",
        "'includes/": "'../middleware/",  # For string literals
        "\"includes/": "\"../middleware/",  # For double quotes
    },
    # Backend middleware
    "backend/middleware": {
        "includes/config.php": "../utils/config.php",
        "__DIR__ . '/config.php'": "__DIR__ . '/../utils/config.php'",
    }
}

def update_file(filepath, mappings):
    """Update path references in a file"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        # Apply mappings
        for old_path, new_path in mappings.items():
            content = content.replace(old_path, new_path)
        
        # Write back if changed
        if content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"✓ Updated: {filepath}")
            return True
        else:
            print(f"- No changes: {filepath}")
            return False
            
    except Exception as e:
        print(f"✗ Error updating {filepath}: {e}")
        return False

def main():
    print("=" * 60)
    print("SiPaMaLi - Path References Updater")
    print("=" * 60)
    
    updated_count = 0
    
    # Update frontend pages
    frontend_pages = BASE_DIR / "frontend" / "pages"
    if frontend_pages.exists():
        print("\n📁 Updating Frontend Pages...")
        for php_file in frontend_pages.glob("*.php"):
            if update_file(php_file, PATH_MAPPINGS["frontend/pages"]):
                updated_count += 1
        for html_file in frontend_pages.glob("*.html"):
            # HTML files might need CSS/JS path updates
            mappings = {k: v for k, v in PATH_MAPPINGS["frontend/pages"].items() 
                       if 'css' in k or 'js' in k}
            if update_file(html_file, mappings):
                updated_count += 1
    
    # Update backend controllers
    backend_controllers = BASE_DIR / "backend" / "controllers"
    if backend_controllers.exists():
        print("\n📁 Updating Backend Controllers...")
        for php_file in backend_controllers.glob("*.php"):
            if update_file(php_file, PATH_MAPPINGS["backend/controllers"]):
                updated_count += 1
    
    # Update backend middleware
    backend_middleware = BASE_DIR / "backend" / "middleware"
    if backend_middleware.exists():
        print("\n📁 Updating Backend Middleware...")
        for php_file in backend_middleware.glob("*.php"):
            if update_file(php_file, PATH_MAPPINGS["backend/middleware"]):
                updated_count += 1
    
    print("\n" + "=" * 60)
    print(f"✅ Update complete! {updated_count} files modified.")
    print("=" * 60)

if __name__ == "__main__":
    main()
