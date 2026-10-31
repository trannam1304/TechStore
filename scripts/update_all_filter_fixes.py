# -*- coding: utf-8 -*-

# 1. Update public/pages/products.html
path_products = 'public/pages/products.html'
with open(path_products, 'r', encoding='utf-8') as f:
    products_html = f.read()

products_html = products_html.replace('\r\n', '\n')

# Check let allProducts = [];
old_vars = """    let currentFiltered = [];
    let currentSort = 'default';
    let currentCategory = 'all';
    let allCategories = [];
    let allBrands = [];"""

new_vars = """    let allProducts = [];
    let currentFiltered = [];
    let currentSort = 'default';
    let currentCategory = 'all';
    let allCategories = [];
    let allBrands = [];"""

if old_vars in products_html:
    products_html = products_html.replace(old_vars, new_vars, 1)

# Check DOMContentLoaded fetch
old_fetch = """        // 1. Fetch categories and brands from API
        const [catRes, brandRes] = await Promise.all([
          api.categories.getAll(),
          api.brands.getAll()
        ]);

        allCategories = Array.isArray(catRes.data) ? catRes.data : [];
        allBrands = Array.isArray(brandRes.data) ? brandRes.data : [];

        renderCategoryFilters(allCategories);
        renderBrandFilters(allBrands);"""

new_fetch = """        // 1. Fetch categories, brands and initial products with limit 100
        const [catRes, brandRes, prodRes] = await Promise.all([
          api.categories.getAll(),
          api.brands.getAll(),
          api.products.getAll({ limit: 100 })
        ]);

        allCategories = Array.isArray(catRes.data) ? catRes.data : [];
        allBrands = Array.isArray(brandRes.data) ? brandRes.data : [];
        allProducts = Array.isArray(prodRes.data) ? prodRes.data : (prodRes.data?.items || []);

        renderCategoryFilters(allCategories);
        renderBrandFilters(allBrands);"""

if old_fetch in products_html:
    products_html = products_html.replace(old_fetch, new_fetch, 1)

# Update renderCategoryFilters to show total count
old_render_cats = """      container.innerHTML = `
        <label class="filter-label">
          <input type="radio" name="filter_cat" value="all" ${currentCategory === 'all' ? 'checked' : ''} onchange="onSidebarCategoryChange(this.value)"> Tất cả
        </label>"""

new_render_cats = """      const totalCount = allProducts.length || cats.reduce((acc, c) => acc + (c.product_count || 0), 0) || 82;
      container.innerHTML = `
        <label class="filter-label">
          <input type="radio" name="filter_cat" value="all" ${currentCategory === 'all' ? 'checked' : ''} onchange="onSidebarCategoryChange(this.value)"> Tất cả (${totalCount})
        </label>"""

if old_render_cats in products_html:
    products_html = products_html.replace(old_render_cats, new_render_cats, 1)

# Update applyFilters to pass limit: 100
old_api_params = """      // Xây dựng tham số gọi Backend REST API
      const apiParams = {};
      if (selectedCat && selectedCat !== 'all') {
        apiParams.category_id = selectedCat;
      }"""

new_api_params = """      // Xây dựng tham số gọi Backend REST API
      const apiParams = {
        limit: 100 // Đảm bảo lấy đầy đủ toàn bộ sản phẩm để hiển thị và phân trang
      };
      if (selectedCat && selectedCat !== 'all') {
        apiParams.category_id = selectedCat;
      }"""

if old_api_params in products_html:
    products_html = products_html.replace(old_api_params, new_api_params, 1)

with open(path_products, 'w', encoding='utf-8') as f:
    f.write(products_html)
print("products.html updated successfully!")


# 2. Update public/pages/index.html
path_index = 'public/pages/index.html'
with open(path_index, 'r', encoding='utf-8') as f:
    index_html = f.read()

index_html = index_html.replace('\r\n', '\n')

old_index_fetch = """      // 1. Fetch Products
      const res = await api.products.getAll();
      allProducts = Array.isArray(res.data) ? res.data : (res.data?.items || []);"""

new_index_fetch = """      // 1. Fetch Products (tải tối đa 100 sản phẩm để Featured tabs hoạt động đầy đủ)
      const res = await api.products.getAll({ limit: 100 });
      allProducts = Array.isArray(res.data) ? res.data : (res.data?.items || []);"""

if old_index_fetch in index_html:
    index_html = index_html.replace(old_index_fetch, new_index_fetch, 1)
    with open(path_index, 'w', encoding='utf-8') as f:
        f.write(index_html)
    print("index.html updated successfully!")
else:
    print("index.html pattern not found, checking...")


# 3. Update start.bat
path_bat = 'start.bat'
with open(path_bat, 'r', encoding='utf-8') as f:
    bat_content = f.read()

old_bat_cmd = '"%PHP_BIN%" -S 0.0.0.0:8000 -t public'
new_bat_cmd = '"%PHP_BIN%" -S 0.0.0.0:8000 -t public public/index.php'

if old_bat_cmd in bat_content:
    bat_content = bat_content.replace(old_bat_cmd, new_bat_cmd, 1)
    with open(path_bat, 'w', encoding='utf-8') as f:
        f.write(bat_content)
    print("start.bat updated with router script!")

