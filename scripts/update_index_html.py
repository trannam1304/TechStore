# -*- coding: utf-8 -*-
path = 'public/pages/index.html'

with open(path, 'r', encoding='utf-8') as f:
    html = f.read()

# 1. Update Featured Products Tabs
old_featured = """        <div style="display: flex; gap: 8px;">
          <button class="btn btn-outline btn-sm active" onclick="filterFeatured('all', this)">Tất cả</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(1, this)">Điện thoại</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(2, this)">Laptop</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(4, this)">Âm thanh</button>
        </div>"""

new_featured = """        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <button class="btn btn-outline btn-sm active" onclick="filterFeatured('all', this)">Tất cả</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(1, this)">Điện thoại</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(2, this)">Laptop</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(3, this)">Tablet</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(4, this)">Âm thanh</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(5, this)">Smartwatch</button>
          <button class="btn btn-outline btn-sm" onclick="filterFeatured(6, this)">Phụ kiện</button>
        </div>"""

html_norm = html.replace('\r\n', '\n')
if old_featured in html_norm:
    html_norm = html_norm.replace(old_featured, new_featured, 1)
    print("Replaced featured tabs!")
else:
    print("Old featured tabs not found directly, checking regex/substring...")

# 2. Update Footer Category links
old_footer = """        <h4>Danh mục</h4>
        <ul>
          <li><a href="/pages/products.html?category_id=1">Điện thoại iPhone / Samsung</a></li>
          <li><a href="/pages/products.html?category_id=2">Laptop MacBook / Gaming</a></li>
          <li><a href="/pages/products.html?category_id=3">iPad & Máy tính bảng</a></li>
          <li><a href="/pages/products.html?category_id=4">Tai nghe & Loa Bluetooth</a></li>
          <li><a href="/pages/products.html?category_id=6">Phụ kiện sạc cáp 65W</a></li>
        </ul>"""

new_footer = """        <h4>Danh mục</h4>
        <ul>
          <li><a href="/pages/products.html?category_id=1">Điện thoại iPhone / Samsung</a></li>
          <li><a href="/pages/products.html?category_id=2">Laptop MacBook / Gaming</a></li>
          <li><a href="/pages/products.html?category_id=3">iPad & Máy tính bảng</a></li>
          <li><a href="/pages/products.html?category_id=4">Tai nghe & Loa Bluetooth</a></li>
          <li><a href="/pages/products.html?category_id=5">Đồng hồ Smartwatch</a></li>
          <li><a href="/pages/products.html?category_id=6">Phụ kiện công nghệ & Sạc cáp</a></li>
        </ul>"""

if old_footer in html_norm:
    html_norm = html_norm.replace(old_footer, new_footer, 1)
    print("Replaced footer categories!")

# 3. Update Marquee Brand links
old_marquee = """        <div class="marquee-track">
          <a href="/pages/products.html?brand_id=1" class="brand-item"> Apple Authorized</a>
          <a href="/pages/products.html?brand_id=2" class="brand-item">Samsung Galaxy</a>
          <a href="/pages/products.html?brand_id=3" class="brand-item">Xiaomi Smart Life</a>
          <a href="/pages/products.html?brand_id=4" class="brand-item">ASUS ROG Gaming</a>
          <a href="/pages/products.html?brand_id=5" class="brand-item">Sony Hi-Res Studio</a>
          <a href="/pages/products.html?brand_id=6" class="brand-item">Anker GaN Fast</a>
          <a href="/pages/products.html?brand_id=7" class="brand-item">Dell Technologies</a>
          <a href="/pages/products.html" class="brand-item">Marshall Major</a>
          <a href="/pages/products.html" class="brand-item">Logitech Master</a>
          <a href="/pages/products.html" class="brand-item">JBL Pro Audio</a>
          <!-- Duplicate for continuous 60fps infinite loop -->
          <a href="/pages/products.html?brand_id=1" class="brand-item"> Apple Authorized</a>
          <a href="/pages/products.html?brand_id=2" class="brand-item">Samsung Galaxy</a>
          <a href="/pages/products.html?brand_id=3" class="brand-item">Xiaomi Smart Life</a>
          <a href="/pages/products.html?brand_id=4" class="brand-item">ASUS ROG Gaming</a>
          <a href="/pages/products.html?brand_id=5" class="brand-item">Sony Hi-Res Studio</a>
          <a href="/pages/products.html?brand_id=6" class="brand-item">Anker GaN Fast</a>
          <a href="/pages/products.html?brand_id=7" class="brand-item">Dell Technologies</a>
          <a href="/pages/products.html" class="brand-item">Marshall Major</a>
          <a href="/pages/products.html" class="brand-item">Logitech Master</a>
          <a href="/pages/products.html" class="brand-item">JBL Pro Audio</a>
        </div>"""

new_marquee = """        <div class="marquee-track">
          <a href="/pages/products.html?brand_id=1" class="brand-item"> Apple Authorized</a>
          <a href="/pages/products.html?brand_id=2" class="brand-item">Samsung Galaxy</a>
          <a href="/pages/products.html?brand_id=3" class="brand-item">Xiaomi Smart Life</a>
          <a href="/pages/products.html?brand_id=4" class="brand-item">ASUS ROG Gaming</a>
          <a href="/pages/products.html?brand_id=5" class="brand-item">Sony Hi-Res Studio</a>
          <a href="/pages/products.html?brand_id=6" class="brand-item">Anker GaN Fast</a>
          <a href="/pages/products.html?brand_id=7" class="brand-item">Dell Technologies</a>
          <a href="/pages/products.html?brand_id=8" class="brand-item">Logitech Master</a>
          <a href="/pages/products.html?brand_id=9" class="brand-item">Lenovo ThinkPad</a>
          <a href="/pages/products.html?brand_id=10" class="brand-item">Acer Predator</a>
          <a href="/pages/products.html?brand_id=11" class="brand-item">Marshall Major</a>
          <a href="/pages/products.html?brand_id=12" class="brand-item">JBL Pro Audio</a>
          <!-- Duplicate for continuous 60fps infinite loop -->
          <a href="/pages/products.html?brand_id=1" class="brand-item"> Apple Authorized</a>
          <a href="/pages/products.html?brand_id=2" class="brand-item">Samsung Galaxy</a>
          <a href="/pages/products.html?brand_id=3" class="brand-item">Xiaomi Smart Life</a>
          <a href="/pages/products.html?brand_id=4" class="brand-item">ASUS ROG Gaming</a>
          <a href="/pages/products.html?brand_id=5" class="brand-item">Sony Hi-Res Studio</a>
          <a href="/pages/products.html?brand_id=6" class="brand-item">Anker GaN Fast</a>
          <a href="/pages/products.html?brand_id=7" class="brand-item">Dell Technologies</a>
          <a href="/pages/products.html?brand_id=8" class="brand-item">Logitech Master</a>
          <a href="/pages/products.html?brand_id=9" class="brand-item">Lenovo ThinkPad</a>
          <a href="/pages/products.html?brand_id=10" class="brand-item">Acer Predator</a>
          <a href="/pages/products.html?brand_id=11" class="brand-item">Marshall Major</a>
          <a href="/pages/products.html?brand_id=12" class="brand-item">JBL Pro Audio</a>
        </div>"""

if old_marquee in html_norm:
    html_norm = html_norm.replace(old_marquee, new_marquee, 1)
    print("Replaced marquee brands!")

with open(path, 'w', encoding='utf-8') as f:
    f.write(html_norm)

print("index.html successfully updated!")

