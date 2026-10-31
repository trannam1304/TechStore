# -*- coding: utf-8 -*-
with open('public/assets/js/api.js', 'r', encoding='utf-8') as f:
    c = f.read().replace('\r\n', '\n')

old_cat = """const DEFAULT_CATEGORIES = [
  { id: 1, name: 'Điện thoại', icon: '📱', slug: 'dien-thoai', product_count: 28 },
  { id: 2, name: 'Laptop', icon: '💻', slug: 'laptop', product_count: 20 },
  { id: 3, name: 'Tablet', icon: '📋', slug: 'tablet', product_count: 14 },
  { id: 4, name: 'Âm thanh', icon: '🎧', slug: 'am-thanh', product_count: 16 },
  { id: 5, name: 'Smartwatch', icon: '⌚', slug: 'smartwatch', product_count: 12 },
  { id: 6, name: 'Phụ kiện', icon: '🔌', slug: 'phu-kien', product_count: 35 }
];

const DEFAULT_BRANDS = [
  { id: 1, name: 'Apple', logo: '' },
  { id: 2, name: 'Samsung', logo: 'Samsung' },
  { id: 3, name: 'Xiaomi', logo: 'Xiaomi' },
  { id: 4, name: 'ASUS', logo: 'ASUS' },
  { id: 5, name: 'Sony', logo: 'Sony' },
  { id: 6, name: 'Anker', logo: 'Anker' },
  { id: 7, name: 'Dell', logo: 'Dell' }
];"""

new_cat = """const DEFAULT_CATEGORIES = [
  { id: 1, name: 'Điện thoại', icon: '📱', slug: 'dien-thoai', product_count: 17 },
  { id: 2, name: 'Laptop', icon: '💻', slug: 'laptop', product_count: 18 },
  { id: 3, name: 'Tablet', icon: '📋', slug: 'tablet', product_count: 12 },
  { id: 4, name: 'Âm thanh', icon: '🎧', slug: 'am-thanh', product_count: 16 },
  { id: 5, name: 'Smartwatch', icon: '⌚', slug: 'smartwatch', product_count: 9 },
  { id: 6, name: 'Phụ kiện chính hãng', icon: '🔌', slug: 'phu-kien', product_count: 10 }
];

const DEFAULT_BRANDS = [
  { id: 1, name: 'Apple', logo: '' },
  { id: 2, name: 'Samsung', logo: 'Samsung' },
  { id: 3, name: 'Xiaomi', logo: 'Xiaomi' },
  { id: 4, name: 'ASUS', logo: 'ASUS' },
  { id: 5, name: 'Sony', logo: 'Sony' },
  { id: 6, name: 'Dell', logo: 'Dell' },
  { id: 7, name: 'Anker', logo: 'Anker' },
  { id: 8, name: 'Logitech', logo: 'Logitech' },
  { id: 9, name: 'Lenovo', logo: 'Lenovo' },
  { id: 10, name: 'Acer', logo: 'Acer' },
  { id: 11, name: 'Marshall', logo: 'Marshall' },
  { id: 12, name: 'JBL', logo: 'JBL' }
];"""

if old_cat in c:
    c = c.replace(old_cat, new_cat)
    print("Updated DEFAULT_CATEGORIES and DEFAULT_BRANDS in api.js")
else:
    print("ERROR: old_cat not found in api.js")

old_mock_filter = """      const cat = urlObj.searchParams.get('category_id');
      const brand = urlObj.searchParams.get('brand_id');

      let filtered = [...products];
      if (search) {
        filtered = filtered.filter(p => p.name.toLowerCase().includes(search) || p.category_name.toLowerCase().includes(search));
      }
      if (cat) {
        filtered = filtered.filter(p => p.category_id == cat);
      }"""

new_mock_filter = """      const cat = urlObj.searchParams.get('category_id') || urlObj.searchParams.get('category');
      const brand = urlObj.searchParams.get('brand_id') || urlObj.searchParams.get('brand');

      let filtered = [...products];
      if (search) {
        filtered = filtered.filter(p => p.name.toLowerCase().includes(search) || (p.category_name && p.category_name.toLowerCase().includes(search)));
      }
      if (cat && cat !== 'all') {
        filtered = filtered.filter(p => p.category_id == cat || (p.category_name && p.category_name.toLowerCase() === cat.toLowerCase()));
      }"""

if old_mock_filter in c:
    c = c.replace(old_mock_filter, new_mock_filter)
    print("Updated mock products filter in api.js")
else:
    print("ERROR: old_mock_filter not found in api.js")

with open('public/assets/js/api.js', 'w', encoding='utf-8') as f:
    f.write(c)
print("api.js saved successfully")

