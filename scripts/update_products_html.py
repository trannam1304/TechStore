# -*- coding: utf-8 -*-
import os

path = 'public/pages/products.html'

with open(path, 'r', encoding='utf-8') as f:
    html = f.read()

# 1. Add nav-categories if not present
nav_categories_markup = """  <!-- Navigation Categories (Filter Bar) -->
  <nav class="nav-categories">
    <div class="container">
      <ul class="nav-cat-list" id="nav-category-list">
        <li class="nav-cat-item active" data-cat-id="all">
          <a href="javascript:void(0)" onclick="selectCategoryTab('all')">⚡ Tất cả sản phẩm</a>
        </li>
        <li class="nav-cat-item" data-cat-id="1">
          <a href="javascript:void(0)" onclick="selectCategoryTab('1')">📱 Điện thoại</a>
        </li>
        <li class="nav-cat-item" data-cat-id="2">
          <a href="javascript:void(0)" onclick="selectCategoryTab('2')">💻 Laptop</a>
        </li>
        <li class="nav-cat-item" data-cat-id="3">
          <a href="javascript:void(0)" onclick="selectCategoryTab('3')">📋 Tablet</a>
        </li>
        <li class="nav-cat-item" data-cat-id="4">
          <a href="javascript:void(0)" onclick="selectCategoryTab('4')">🎧 Âm thanh</a>
        </li>
        <li class="nav-cat-item" data-cat-id="5">
          <a href="javascript:void(0)" onclick="selectCategoryTab('5')">⌚ Smartwatch</a>
        </li>
        <li class="nav-cat-item" data-cat-id="6">
          <a href="javascript:void(0)" onclick="selectCategoryTab('6')">🔌 Phụ kiện chính hãng</a>
        </li>
      </ul>
    </div>
  </nav>

"""

if '<nav class="nav-categories">' not in html:
    header_end = '</header>'
    idx = html.find(header_end)
    if idx != -1:
        html = html[:idx + len(header_end)] + "\n\n" + nav_categories_markup + html[idx + len(header_end):]

# 2. Replace the main script section
script_start = '<script>'
script_end = '</script>\n</body>'
if script_end not in html:
    script_end = '</script>\r\n</body>'

idx_start = html.rfind(script_start)
idx_end = html.rfind(script_end)

new_script = """<script>
    let currentFiltered = [];
    let currentSort = 'default';
    let currentCategory = 'all';
    let allCategories = [];
    let allBrands = [];
    let currentPage = 1;
    const ITEMS_PER_PAGE = 12;

    const CATEGORY_NAMES = {
      'all': 'Tất cả sản phẩm',
      '1': 'Điện thoại',
      '2': 'Laptop',
      '3': 'Tablet',
      '4': 'Âm thanh',
      '5': 'Smartwatch',
      '6': 'Phụ kiện chính hãng'
    };

    document.addEventListener('DOMContentLoaded', async () => {
      try {
        // 1. Fetch categories and brands from API
        const [catRes, brandRes] = await Promise.all([
          api.categories.getAll(),
          api.brands.getAll()
        ]);

        allCategories = Array.isArray(catRes.data) ? catRes.data : [];
        allBrands = Array.isArray(brandRes.data) ? brandRes.data : [];

        renderCategoryFilters(allCategories);
        renderBrandFilters(allBrands);

        // 2. Read URL params (e.g. ?category_id=5 or ?search=iphone)
        const urlParams = new URLSearchParams(window.location.search);
        const initialCat = urlParams.get('category_id') || urlParams.get('category') || 'all';
        const initialBrand = urlParams.get('brand_id') || urlParams.get('brand');
        const initialSearch = urlParams.get('search') || urlParams.get('q');

        if (initialSearch) {
          document.getElementById('global-search-input').value = initialSearch;
        }

        if (initialBrand) {
          const cb = document.querySelector(`input[name="filter_brand"][value="${initialBrand}"]`);
          if (cb) cb.checked = true;
        }

        // 3. Attach search form listener
        const searchForm = document.getElementById('global-search-form');
        if (searchForm) {
          searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            applyFilters();
          });
        }
        const searchInput = document.getElementById('global-search-input');
        if (searchInput) {
          searchInput.addEventListener('input', debounce(() => {
            applyFilters();
          }, 400));
        }

        // 4. Activate initial category and load products
        await selectCategoryTab(initialCat);

      } catch (err) {
        console.error('Lỗi khởi tạo Catalog:', err);
        showToast('Không thể kết nối đến máy chủ TechStore', 'danger');
      }
    });

    /**
     * Chuyển đổi tab danh mục và kích hoạt tải lại danh sách sản phẩm
     */
    async function selectCategoryTab(catId) {
      currentCategory = String(catId || 'all');

      // 1. Cập nhật trạng thái active trên Category Bar
      document.querySelectorAll('#nav-category-list .nav-cat-item').forEach(item => {
        const id = item.getAttribute('data-cat-id');
        item.classList.toggle('active', id === currentCategory);
      });

      // 2. Đồng bộ radio button trong Sidebar bộ lọc
      const radio = document.querySelector(`input[name="filter_cat"][value="${currentCategory}"]`);
      if (radio) {
        radio.checked = true;
      }

      // 3. Cập nhật breadcrumb
      const breadcrumbEl = document.getElementById('catalog-breadcrumb');
      if (breadcrumbEl) {
        breadcrumbEl.textContent = CATEGORY_NAMES[currentCategory] || 'Danh mục sản phẩm';
      }

      // 4. Cập nhật URL trình duyệt (không reload)
      const url = new URL(window.location.href);
      if (currentCategory === 'all') {
        url.searchParams.delete('category_id');
      } else {
        url.searchParams.set('category_id', currentCategory);
      }
      window.history.replaceState({}, '', url.toString());

      // 5. Gửi request API và cập nhật danh sách
      await applyFilters(1);
    }

    /**
     * Xử lý khi click chọn radio danh mục ở sidebar
     */
    function onSidebarCategoryChange(val) {
      selectCategoryTab(val);
    }

    /**
     * Render danh sách danh mục trong Sidebar
     */
    function renderCategoryFilters(cats) {
      const container = document.getElementById('category-filter-list');
      if (!container) return;

      container.innerHTML = `
        <label class="filter-label">
          <input type="radio" name="filter_cat" value="all" ${currentCategory === 'all' ? 'checked' : ''} onchange="onSidebarCategoryChange(this.value)"> Tất cả
        </label>
        ${cats.map(c => `
          <label class="filter-label">
            <input type="radio" name="filter_cat" value="${c.id}" ${String(c.id) === String(currentCategory) ? 'checked' : ''} onchange="onSidebarCategoryChange(this.value)">
            <span>${c.icon ? c.icon + ' ' : ''}${c.name}</span>
            ${c.product_count !== undefined ? `<span style="font-size: 11px; color: var(--color-gray); margin-left: auto;">(${c.product_count})</span>` : ''}
          </label>
        `).join('')}
      `;
    }

    /**
     * Render danh sách thương hiệu trong Sidebar
     */
    function renderBrandFilters(brands) {
      const container = document.getElementById('brand-filter-list');
      if (!container) return;

      container.innerHTML = brands.map(b => `
        <label class="filter-label">
          <input type="checkbox" name="filter_brand" value="${b.id}" onchange="applyFilters()"> ${b.name}
        </label>
      `).join('');
    }

    /**
     * Cập nhật tiêu chí sắp xếp
     */
    function setSort(sortType, btn) {
      document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentSort = sortType;
      applySortingAndRender();
    }

    /**
     * Tải sản phẩm từ API và áp dụng bộ lọc Client + Server
     */
    async function applyFilters(page = 1) {
      currentPage = page;
      const searchKeyword = (document.getElementById('global-search-input').value || '').trim();

      // Danh mục đã chọn
      const selectedCat = currentCategory;

      // Hãng đã chọn
      const selectedBrands = Array.from(document.querySelectorAll('input[name="filter_brand"]:checked')).map(el => el.value);

      // Khoảng giá
      const selectedPriceEl = document.querySelector('input[name="price_range"]:checked');
      const priceRange = selectedPriceEl ? selectedPriceEl.value : 'all';

      // RAM
      const selectedRams = Array.from(document.querySelectorAll('input[name="filter_ram"]:checked')).map(el => el.value);

      // Xây dựng tham số gọi Backend REST API
      const apiParams = {};
      if (selectedCat && selectedCat !== 'all') {
        apiParams.category_id = selectedCat;
      }
      if (searchKeyword) {
        apiParams.search = searchKeyword;
      }
      if (selectedBrands.length === 1) {
        apiParams.brand_id = selectedBrands[0];
      }

      try {
        const res = await api.products.getAll(apiParams);
        let items = Array.isArray(res.data) ? res.data : (res.data?.items || []);

        // Lọc phụ trên client cho các tổ hợp nâng cao
        if (selectedBrands.length > 1) {
          items = items.filter(p => selectedBrands.includes(String(p.brand_id)));
        }

        if (priceRange === 'under_5m') {
          items = items.filter(p => p.price < 5000000);
        } else if (priceRange === '5m_15m') {
          items = items.filter(p => p.price >= 5000000 && p.price <= 15000000);
        } else if (priceRange === '15m_30m') {
          items = items.filter(p => p.price > 15000000 && p.price <= 30000000);
        } else if (priceRange === 'above_30m') {
          items = items.filter(p => p.price > 30000000);
        }

        if (selectedRams.length > 0) {
          items = items.filter(p => selectedRams.some(r => (p.ram || '').includes(r)));
        }

        currentFiltered = items;

        // Render các tag filter đang kích hoạt
        renderActiveFilterBadges(selectedCat, selectedBrands, priceRange, selectedRams, searchKeyword);

        // Sắp xếp và render ra giao diện
        applySortingAndRender();

      } catch (err) {
        console.error('Lỗi khi lọc sản phẩm:', err);
        showToast('Lỗi khi lấy danh sách sản phẩm', 'danger');
      }
    }

    /**
     * Áp dụng sắp xếp và phân trang hiển thị
     */
    function applySortingAndRender() {
      if (currentSort === 'price_asc') {
        currentFiltered.sort((a, b) => a.price - b.price);
      } else if (currentSort === 'price_desc') {
        currentFiltered.sort((a, b) => b.price - a.price);
      } else if (currentSort === 'rating') {
        currentFiltered.sort((a, b) => (b.rating || 0) - (a.rating || 0));
      }

      renderPaginationAndGrid();
    }

    /**
     * Phân trang và render Grid sản phẩm
     */
    function renderPaginationAndGrid() {
      const total = currentFiltered.length;
      const totalPages = Math.ceil(total / ITEMS_PER_PAGE) || 1;
      if (currentPage > totalPages) currentPage = 1;

      const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
      const pagedItems = currentFiltered.slice(startIndex, startIndex + ITEMS_PER_PAGE);

      renderCatalogGrid(pagedItems, total);
      renderPaginationControls(totalPages);
    }

    /**
     * Render lưới card sản phẩm
     */
    function renderCatalogGrid(items, totalCount) {
      const container = document.getElementById('catalog-products-grid');
      const emptyState = document.getElementById('empty-state');
      const countEl = document.getElementById('results-count');

      if (countEl) {
        countEl.textContent = `Tìm thấy ${totalCount !== undefined ? totalCount : items.length} sản phẩm`;
      }

      if (items.length === 0) {
        if (container) container.style.display = 'none';
        if (emptyState) emptyState.style.display = 'block';
        return;
      }

      if (container) container.style.display = 'grid';
      if (emptyState) emptyState.style.display = 'none';

      container.innerHTML = items.map(p => `
        <div class="product-card">
          <div class="product-badges">
            ${p.original_price > p.price ? `<span class="badge-discount">-${Math.round((1 - p.price / p.original_price) * 100)}%</span>` : ''}
            <span class="badge-installment">Trả góp 0%</span>
          </div>
          <button class="btn-wishlist" onclick="toggleWishlist(${p.id}, this)">❤️</button>
          
          <a href="/pages/product-detail.html?id=${p.id}" class="product-thumb-wrap">
            <img src="${p.image}" class="product-thumb" alt="${p.name}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400'">
          </a>
          
          <a href="/pages/product-detail.html?id=${p.id}">
            <h4 class="product-title">${p.name}</h4>
          </a>

          <div class="product-specs-preview">
            ${p.ram && p.ram !== '-' ? `<span class="spec-chip">${p.ram}</span>` : ''}
            ${p.rom && p.rom !== '-' ? `<span class="spec-chip">${p.rom}</span>` : ''}
          </div>

          <div class="product-price-row">
            <span class="price-current">${formatVND(p.price)}</span>
            ${p.original_price > p.price ? `<span class="price-original">${formatVND(p.original_price)}</span>` : ''}
          </div>

          <div class="product-rating">
            ★ ${p.rating || 5.0} <span class="rating-count">(${p.review_count || p.reviews_count || 12})</span>
          </div>

          <div class="product-card-actions">
            <button class="btn btn-primary btn-sm btn-block" onclick="quickAddToCart(${p.id})">
              🛒 Thêm giỏ hàng
            </button>
          </div>
        </div>
      `).join('');
    }

    /**
     * Render các nút điều hướng phân trang
     */
    function renderPaginationControls(totalPages) {
      const container = document.getElementById('catalog-pagination');
      if (!container) return;

      if (totalPages <= 1) {
        container.innerHTML = '';
        container.style.display = 'none';
        return;
      }

      container.style.display = 'flex';
      let html = `
        <button class="page-item ${currentPage === 1 ? 'disabled' : ''}" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>&lt;</button>
      `;
      for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-item ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
      }
      html += `
        <button class="page-item ${currentPage === totalPages ? 'disabled' : ''}" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>&gt;</button>
      `;
      container.innerHTML = html;
    }

    function goToPage(page) {
      if (page < 1) return;
      const totalPages = Math.ceil(currentFiltered.length / ITEMS_PER_PAGE) || 1;
      if (page > totalPages) return;
      currentPage = page;
      renderPaginationAndGrid();
      window.scrollTo({ top: 120, behavior: 'smooth' });
    }

    /**
     * Render các tag filter đang chọn
     */
    function renderActiveFilterBadges(selectedCat, selectedBrands, priceRange, selectedRams, searchKeyword) {
      const bar = document.getElementById('active-filters-bar');
      if (!bar) return;

      const badges = [];

      if (selectedCat && selectedCat !== 'all') {
        badges.push(`
          <span class="active-tag">
            Danh mục: <strong>${CATEGORY_NAMES[selectedCat] || selectedCat}</strong>
            <span class="active-tag-close" onclick="selectCategoryTab('all')">✕</span>
          </span>
        `);
      }

      if (searchKeyword) {
        badges.push(`
          <span class="active-tag">
            Từ khóa: <strong>"${searchKeyword}"</strong>
            <span class="active-tag-close" onclick="clearSearchFilter()">✕</span>
          </span>
        `);
      }

      selectedBrands.forEach(bId => {
        const brandObj = (allBrands || []).find(b => String(b.id) === String(bId));
        const bName = brandObj ? brandObj.name : `Hãng #${bId}`;
        badges.push(`
          <span class="active-tag">
            Hãng: <strong>${bName}</strong>
            <span class="active-tag-close" onclick="removeBrandFilter('${bId}')">✕</span>
          </span>
        `);
      });

      const priceNames = {
        'under_5m': 'Dưới 5 triệu',
        '5m_15m': 'Từ 5 - 15 triệu',
        '15m_30m': 'Từ 15 - 30 triệu',
        'above_30m': 'Trên 30 triệu'
      };
      if (priceRange && priceRange !== 'all') {
        badges.push(`
          <span class="active-tag">
            Giá: <strong>${priceNames[priceRange] || priceRange}</strong>
            <span class="active-tag-close" onclick="resetPriceFilter()">✕</span>
          </span>
        `);
      }

      selectedRams.forEach(ram => {
        badges.push(`
          <span class="active-tag">
            RAM: <strong>${ram}</strong>
            <span class="active-tag-close" onclick="removeRamFilter('${ram}')">✕</span>
          </span>
        `);
      });

      if (badges.length > 0) {
        bar.innerHTML = badges.join('') + `<button onclick="resetFilters()" style="margin-left: 8px; font-size: 12px; color: var(--color-danger); text-decoration: underline; background: none; border: none; cursor: pointer;">Xóa tất cả</button>`;
        bar.style.display = 'flex';
      } else {
        bar.innerHTML = '';
        bar.style.display = 'none';
      }
    }

    function clearSearchFilter() {
      document.getElementById('global-search-input').value = '';
      applyFilters();
    }

    function removeBrandFilter(bId) {
      const cb = document.querySelector(`input[name="filter_brand"][value="${bId}"]`);
      if (cb) cb.checked = false;
      applyFilters();
    }

    function resetPriceFilter() {
      const allPrice = document.querySelector('input[name="price_range"][value="all"]');
      if (allPrice) allPrice.checked = true;
      applyFilters();
    }

    function removeRamFilter(ram) {
      const cb = document.querySelector(`input[name="filter_ram"][value="${ram}"]`);
      if (cb) cb.checked = false;
      applyFilters();
    }

    /**
     * Đặt lại toàn bộ bộ lọc về mặc định
     */
    function resetFilters() {
      currentCategory = 'all';
      document.querySelectorAll('#nav-category-list .nav-cat-item').forEach(item => {
        item.classList.toggle('active', item.getAttribute('data-cat-id') === 'all');
      });

      const allCat = document.querySelector('input[name="filter_cat"][value="all"]');
      if (allCat) allCat.checked = true;

      const allPrice = document.querySelector('input[name="price_range"][value="all"]');
      if (allPrice) allPrice.checked = true;

      document.querySelectorAll('input[name="filter_brand"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('input[name="filter_ram"]').forEach(cb => cb.checked = false);
      document.getElementById('global-search-input').value = '';

      const breadcrumbEl = document.getElementById('catalog-breadcrumb');
      if (breadcrumbEl) breadcrumbEl.textContent = 'Tất cả sản phẩm';

      const url = new URL(window.location.href);
      url.search = '';
      window.history.replaceState({}, '', url.toString());

      applyFilters(1);
    }
  </script>"""

if idx_start != -1 and idx_end != -1:
    html = html[:idx_start] + new_script + html[idx_end + len(script_end) - 7:]

with open(path, 'w', encoding='utf-8') as f:
    f.write(html)

print("Updated products.html successfully!")

