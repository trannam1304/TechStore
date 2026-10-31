# -*- coding: utf-8 -*-
import json

with open('database/data.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

products = data['products']
for p in products:
    if 'review_count' not in p and 'reviews_count' in p:
        p['review_count'] = p['reviews_count']
    if 'featured' not in p:
        p['featured'] = (p.get('id', 0) in [1, 2, 3, 4, 11, 12, 21, 22, 27, 28, 35, 36, 39, 43, 50, 58, 64, 72, 77])

json_str = json.dumps(products, ensure_ascii=False, indent=2)

with open('public/assets/js/api.js', 'r', encoding='utf-8') as f:
    api_js = f.read()

# Fix double bracket if any
api_js = api_js.replace(']];\n\nconst DEFAULT_CATEGORIES', '];\n\nconst DEFAULT_CATEGORIES')
api_js = api_js.replace(']];\r\n\r\nconst DEFAULT_CATEGORIES', '];\r\n\r\nconst DEFAULT_CATEGORIES')

start_marker = "const DEFAULT_PRODUCTS = ["
end_marker = "const DEFAULT_CATEGORIES"

idx_start = api_js.find(start_marker)
idx_end = api_js.find(end_marker, idx_start)

if idx_start != -1 and idx_end != -1:
    new_code = f"const DEFAULT_PRODUCTS = {json_str};\n\n"
    api_js = api_js[:idx_start] + new_code + api_js[idx_end:]
    print("Replaced DEFAULT_PRODUCTS cleanly!")

with open('public/assets/js/api.js', 'w', encoding='utf-8') as f:
    f.write(api_js)

print("api.js written!")
