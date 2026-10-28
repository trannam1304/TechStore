/**
 * TechStore - Recommendation Component (recommend.js)
 * Implements Item-based & Personalized Recommendation Carousels
 * Task: T40
 * Author: Tuan Kiet (Front-end)
 */

class RecommendComponent {
  /**
   * Render "Gợi ý dành cho bạn" (Personalized Recommendations)
   */
  static async renderForYou(containerId, title = 'Gợi ý dành riêng cho bạn') {
    const container = document.getElementById(containerId);
    if (!container) return;

    try {
      const res = await api.recommendations.getForMe();
      const products = res.data?.for_you || (await api.products.getAll()).data.slice(0, 8);
      this.renderCarousel(container, title, products, 'recommend-for-you');
    } catch (e) {
      console.error('Error rendering For You carousel', e);
    }
  }

  /**
   * Render "Sản phẩm tương tự" (Similar Products)
   */
  static async renderSimilar(productId, containerId, title = 'Sản phẩm tương tự') {
    const container = document.getElementById(containerId);
    if (!container) return;

    try {
      const res = await api.recommendations.getSimilar(productId);
      let products = res.data?.similar || [];
      if (!products.length) {
        const all = (await api.products.getAll()).data;
        products = all.filter(p => p.id != productId).slice(0, 6);
      }
      this.renderCarousel(container, title, products, 'recommend-similar');
    } catch (e) {
      console.error('Error rendering Similar carousel', e);
    }
  }

  /**
   * Render "Thường được mua cùng" (Frequently Bought Together / Bundle Combo)
   * Nâng cấp thuật toán thông minh:
   * - Lấy dữ liệu chất lượng cao từ backend qua API /products/{id}/recommendations (Apriori + Ma trận bổ trợ + Hệ sinh thái)
   * - Fallback thuật toán client phân loại thông minh (Điện thoại -> Củ sạc + Tai nghe; Laptop -> Chuột + Bàn phím/Sạc; Bàn phím -> Chuột cùng bộ)
   * - Cho phép tương tác chọn/bỏ chọn từng phụ kiện với tính toán giá & chiết khấu 8% realtime
   */
  static async renderBoughtTogether(productId, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    try {
      const mainRes = await api.products.getById(productId);
      const mainProduct = mainRes.data;
      if (!mainProduct) return;

      // 1. Lấy phụ kiện từ API thông minh backend
      let accessories = [];
      try {
        const recRes = await api.recommendations.getBoughtTogether(productId);
        if (recRes && recRes.success && recRes.data && Array.isArray(recRes.data.bought_together) && recRes.data.bought_together.length > 0) {
          accessories = recRes.data.bought_together;
        }
      } catch (err) {
        console.warn('API recommendation not reachable, using client smart fallback', err);
      }

      // 2. Fallback nếu API chưa trả về
      if (!accessories.length) {
        const allRes = await api.products.getAll();
        accessories = this.computeSmartBoughtTogether(mainProduct, allRes.data || []);
      }

      if (!accessories.length) return;

      // Đảm bảo kiểu số cho giá sản phẩm
      mainProduct.price = parseFloat(mainProduct.price) || 0;
      accessories.forEach(a => { a.price = parseFloat(a.price) || 0; });

      // Hàm gán badge lý do gợi ý mua kèm trực quan
      const getAccessoryBadge = (target, acc) => {
        const tName = (target.name || '').toLowerCase();
        const aName = (acc.name || '').toLowerCase();
        if (tName.includes('bàn phím') && (aName.includes('chuột') || aName.includes('master 3s'))) return '⭐ Cặp đôi hoàn hảo';
        if (tName.includes('chuột') && (aName.includes('bàn phím') || aName.includes('mechanical'))) return '⭐ Bàn phím cùng bộ';
        if (aName.includes('sạc') || aName.includes('gan')) return '⚡ Củ sạc khuyên dùng';
        if (aName.includes('airpods') || aName.includes('buds') || aName.includes('tai nghe')) return '🎧 Tai nghe đồng bộ';
        if (aName.includes('watch') || aName.includes('đồng hồ')) return '⌚ Thiết bị thông minh';
        return '🎁 Phụ kiện khuyên dùng';
      };

      container.innerHTML = `
        <div class="bought-together-card" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); border: 1.5px solid rgba(226, 232, 240, 0.9); border-radius: var(--radius-xl); padding: 24px; margin-top: 32px; box-shadow: 0 16px 40px -10px rgba(215, 0, 24, 0.12); position: relative; overflow: hidden;">
          <div style="font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="color: #d70018;">🛍️ Thường được mua cùng</span>
              <span style="font-size: 12px; background: linear-gradient(135deg, #fee2e2, #fce7f3); color: #d70018; padding: 3px 10px; border-radius: var(--radius-full); font-weight: 800; border: 1px solid rgba(215, 0, 24, 0.2);">Tiết kiệm combo 8%</span>
            </div>
            <span style="font-size: 12px; color: #64748b; font-weight: 500;">✓ Tương thích 100% với thiết bị của bạn</span>
          </div>

          <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 20px;">
            <!-- Main Product -->
            <div style="text-align: center; width: 150px; background: white; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; position: relative;">
              <span style="position: absolute; top: 6px; left: 6px; font-size: 10px; background: #e0f2fe; color: #0369a1; font-weight: 700; padding: 2px 6px; border-radius: 4px;">Sản phẩm chính</span>
              <img src="${mainProduct.image}" style="width: 100px; height: 100px; object-fit: contain; margin: 12px auto 8px;" alt="${escapeAttr(mainProduct.name)}">
              <div style="font-size: 12px; font-weight: 600; line-height: 1.3; height: 2.6em; overflow: hidden; color: #1e293b;">${escapeHtml(mainProduct.name)}</div>
              <div style="font-size: 13px; font-weight: 800; color: #d70018; margin-top: 4px;">${formatVND(mainProduct.price)}</div>
            </div>

            ${accessories.map((acc, idx) => `
              <div style="font-size: 24px; font-weight: 700; color: #94a3b8;">+</div>
              <div style="text-align: center; width: 150px; background: white; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; position: relative; cursor: pointer;" onclick="document.getElementById('bundle-acc-${acc.id}').click()">
                <span style="position: absolute; top: 6px; left: 6px; font-size: 9px; background: #fef3c7; color: #92400e; font-weight: 700; padding: 2px 6px; border-radius: 4px; max-width: 138px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${getAccessoryBadge(mainProduct, acc)}</span>
                <img src="${acc.image}" style="width: 100px; height: 100px; object-fit: contain; margin: 12px auto 8px;" alt="${escapeAttr(acc.name)}">
                <div style="font-size: 12px; font-weight: 600; line-height: 1.3; height: 2.6em; overflow: hidden; color: #1e293b;">${escapeHtml(acc.name)}</div>
                <div style="font-size: 13px; font-weight: 800; color: #d70018; margin-top: 4px;">${formatVND(acc.price)}</div>
                <div style="margin-top: 6px; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 11px; color: #475569;" onclick="event.stopPropagation()">
                  <input type="checkbox" id="bundle-acc-${acc.id}" class="bundle-accessory-check" data-id="${acc.id}" data-name="${escapeAttr(acc.name)}" data-price="${acc.price}" checked style="cursor: pointer; accent-color: #d70018; width: 14px; height: 14px;">
                  <label for="bundle-acc-${acc.id}" style="cursor: pointer; margin: 0; font-weight: 600;">Chọn mua</label>
                </div>
              </div>
            `).join('')}

            <div style="font-size: 24px; font-weight: 700; color: #94a3b8;">=</div>

            <!-- Combo Summary -->
            <div style="flex: 1; min-width: 240px; padding-left: 20px; border-left: 1px dashed rgba(203, 213, 225, 0.9);">
              <div style="font-size: 13px; color: #64748b;" id="combo-original-price-row">Giá gốc cả bộ: <del id="combo-total-original">0₫</del></div>
              <div style="font-size: 24px; font-weight: 900; color: #d70018; margin: 6px 0;" id="combo-total-price">0₫</div>
              <div style="font-size: 12px; color: #16a34a; font-weight: 700; margin-bottom: 14px;" id="combo-savings-text">
                Tiết kiệm được 0₫ khi mua combo!
              </div>
              <button class="btn btn-primary btn-block btn-lg" id="btn-buy-combo" style="font-weight: 800; font-size: 15px; box-shadow: 0 4px 14px rgba(215, 0, 24, 0.35);">
                ⚡ Mua các sản phẩm đã chọn
              </button>
            </div>
          </div>
        </div>
      `;

      // Hàm tính toán và cập nhật giá combo theo các checkbox
      const updateCombo = () => {
        const checkboxes = container.querySelectorAll('.bundle-accessory-check:checked');
        let selectedAccTotal = 0;
        let selectedCount = 1; // Luôn bao gồm sản phẩm chính

        checkboxes.forEach(cb => {
          selectedAccTotal += parseFloat(cb.dataset.price) || 0;
          selectedCount++;
        });

        const totalOriginal = mainProduct.price + selectedAccTotal;
        // Áp dụng giảm 8% combo khi mua từ 2 món trở lên
        const hasComboDiscount = selectedCount >= 2;
        const discountAmount = hasComboDiscount ? Math.round(totalOriginal * 0.08) : 0;
        const finalTotal = totalOriginal - discountAmount;

        const origEl = container.querySelector('#combo-total-original');
        const finalEl = container.querySelector('#combo-total-price');
        const savEl = container.querySelector('#combo-savings-text');
        const btnEl = container.querySelector('#btn-buy-combo');

        if (origEl) origEl.textContent = formatVND(totalOriginal);
        if (finalEl) finalEl.textContent = formatVND(finalTotal);

        if (savEl) {
          if (hasComboDiscount) {
            savEl.innerHTML = `✓ Tiết kiệm <strong>${formatVND(discountAmount)}</strong> (8% combo)!`;
            savEl.style.display = 'block';
          } else {
            savEl.style.display = 'none';
          }
        }

        if (btnEl) {
          btnEl.textContent = `⚡ Mua ${selectedCount} sản phẩm đã chọn`;
        }
      };

      // Lắng nghe sự kiện checkbox thay đổi
      container.querySelectorAll('.bundle-accessory-check').forEach(cb => {
        cb.addEventListener('change', updateCombo);
      });

      // Chạy tính toán ban đầu
      updateCombo();

      // Combo click handler: Thêm sản phẩm chính + tất cả phụ kiện đã tick vào giỏ hàng
      const btnBuyCombo = container.querySelector('#btn-buy-combo');
      if (btnBuyCombo) {
        btnBuyCombo.addEventListener('click', async () => {
          const checkedCbs = container.querySelectorAll('.bundle-accessory-check:checked');
          btnBuyCombo.disabled = true;
          btnBuyCombo.textContent = 'Đang thêm combo vào giỏ...';

          try {
            // 1. Thêm sản phẩm chính
            const mainVariant = (typeof getSelectedVariant === 'function') ? getSelectedVariant() : 'Mặc định';
            await api.cart.addItem(mainProduct.id, 1, `${mainVariant} (Combo)`);

            // 2. Thêm các phụ kiện đã chọn
            for (const cb of checkedCbs) {
              const accId = parseInt(cb.dataset.id, 10);
              await api.cart.addItem(accId, 1, 'Phụ kiện mua cùng combo');
            }

            const totalAdded = 1 + checkedCbs.length;
            showToast(`Đã thêm combo ${totalAdded} sản phẩm vào giỏ hàng thành công!`, 'success');
            btnBuyCombo.textContent = '✓ Đã thêm vào giỏ!';

            setTimeout(() => {
              window.location.href = '/pages/cart.html';
            }, 800);
          } catch (err) {
            showToast('Lỗi khi thêm combo vào giỏ hàng', 'danger');
            btnBuyCombo.disabled = false;
            updateCombo();
          }
        });
      }
    } catch (e) {
      console.error('Error rendering Bought Together', e);
    }
  }

  /**
   * Thuật toán Client Fallback: Tự động tính toán phụ kiện mua kèm thông minh
   * Dựa theo quy luật bổ trợ chức năng và hệ sinh thái thương hiệu
   */
  static computeSmartBoughtTogether(mainProduct, allProducts) {
    if (!allProducts || !allProducts.length) return [];

    const pId = parseInt(mainProduct.id, 10);
    const catId = parseInt(mainProduct.category_id, 10);
    const brandId = parseInt(mainProduct.brand_id, 10);
    const name = (mainProduct.name || '').toLowerCase();
    const price = parseFloat(mainProduct.price) || 0;

    const candidates = allProducts.filter(p => parseInt(p.id, 10) !== pId);

    const detectType = (p) => {
      const pName = (p.name || '').toLowerCase();
      const pCat = parseInt(p.category_id, 10);
      if (pName.includes('bàn phím') || pName.includes('keyboard')) return 'keyboard';
      if (pName.includes('chuột') || pName.includes('mouse')) return 'mouse';
      if (pName.includes('sạc') || pName.includes('charger') || pName.includes('gan')) return 'charger';
      if (pName.includes('watch') || pName.includes('đồng hồ')) return 'watch';
      if (pCat === 4 || pName.includes('tai nghe') || pName.includes('airpods') || pName.includes('buds')) return 'earphone';
      if (pName.includes('loa') || pName.includes('speaker')) return 'speaker';
      if (pCat === 2 || pName.includes('laptop') || pName.includes('macbook')) return 'laptop';
      if (pCat === 3 || pName.includes('tablet') || pName.includes('ipad') || pName.includes('tab')) return 'tablet';
      if (pCat === 1 || pName.includes('phone') || pName.includes('iphone')) return 'phone';
      return 'other';
    };

    const targetType = detectType(mainProduct);

    const scored = candidates.map(cand => {
      const cType = detectType(cand);
      const cBrand = parseInt(cand.brand_id, 10);
      const cPrice = parseFloat(cand.price) || 0;
      let score = 0;

      // Ma trận bổ trợ chức năng
      if (targetType === 'phone') {
        if (cType === 'charger') score += 60;
        else if (cType === 'earphone') score += 55;
        else if (cType === 'watch') score += 35;
      } else if (targetType === 'laptop') {
        if (cType === 'mouse') score += 65;
        else if (cType === 'keyboard') score += 50;
        else if (cType === 'charger') score += 45;
      } else if (targetType === 'keyboard') {
        if (cType === 'mouse') score += 75;
        else if (cType === 'charger') score += 45;
      } else if (targetType === 'mouse') {
        if (cType === 'keyboard') score += 75;
        else if (cType === 'charger') score += 45;
      } else {
        if (cType === 'charger') score += 50;
        else if (cType === 'earphone') score += 45;
      }

      // Thương hiệu
      if (cBrand > 0 && cBrand === brandId) score += 25;
      if (cBrand === 7) score += 15; // Anker phụ kiện

      // Tỉ lệ giá
      if (price > 0) {
        const ratio = cPrice / price;
        if (ratio >= 0.02 && ratio <= 0.45) score += 20;
        else if (ratio > 1.2) score -= 70;
      }

      return { product: cand, score, type: cType };
    });

    scored.sort((a, b) => b.score - a.score);

    const selected = [];
    const chosenTypes = [];
    for (const item of scored) {
      if (!chosenTypes.includes(item.type)) {
        selected.push(item.product);
        chosenTypes.push(item.type);
        if (selected.length >= 2) break;
      }
    }

    return selected;
  }

  /**
   * Generic Carousel Renderer with Loop Wrap-around
   */
  static renderCarousel(container, title, products, uniqueId) {
    if (!products || !products.length) return;

    container.innerHTML = `
      <div class="recommend-carousel-wrapper" style="margin: 36px 0;">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h3 class="section-title" style="margin: 0;">${title}</h3>
          <div class="carousel-nav" style="display: flex; gap: 8px;">
            <button class="btn btn-outline btn-sm" id="${uniqueId}-prev" aria-label="Previous">◀</button>
            <button class="btn btn-outline btn-sm" id="${uniqueId}-next" aria-label="Next">▶</button>
          </div>
        </div>

        <div id="${uniqueId}-track" style="display: flex; gap: 16px; overflow-x: auto; scroll-behavior: smooth; padding: 8px 4px 20px 4px; scrollbar-width: none;">
          ${products.map(p => `
            <div style="flex: 0 0 250px; width: 250px;">
              <div class="product-card">
                <div class="product-badges">
                  ${p.original_price > p.price ? `<span class="badge-discount">-${Math.round((1 - p.price/p.original_price)*100)}%</span>` : ''}
                  <span class="badge-installment">Trả góp 0%</span>
                </div>
                <button class="btn-wishlist" onclick="toggleWishlist(${p.id}, this)">❤️</button>
                <a href="/pages/product-detail.html?id=${p.id}" class="product-thumb-wrap">
                  <img src="${p.image}" class="product-thumb" alt="${p.name}" loading="lazy">
                </a>
                <a href="/pages/product-detail.html?id=${p.id}">
                  <h4 class="product-title">${p.name}</h4>
                </a>
                <div class="product-price-row">
                  <span class="price-current">${formatVND(p.price)}</span>
                  ${p.original_price > p.price ? `<span class="price-original">${formatVND(p.original_price)}</span>` : ''}
                </div>
                <div class="product-rating">
                  ★ ${p.rating || 5.0} <span class="rating-count">(${p.review_count || 12})</span>
                </div>
                <div class="product-card-actions">
                  <button class="btn btn-outline-primary btn-sm btn-block" onclick="quickAddToCart(${p.id})">
                    🛒 Thêm giỏ
                  </button>
                </div>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    `;

    // Wire Up Prev / Next Buttons with Loop Wrap-around
    const track = container.querySelector(`#${uniqueId}-track`);
    const prevBtn = container.querySelector(`#${uniqueId}-prev`);
    const nextBtn = container.querySelector(`#${uniqueId}-next`);

    if (track && prevBtn && nextBtn) {
      prevBtn.addEventListener('click', () => {
        if (track.scrollLeft <= 10) {
          track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });
        } else {
          track.scrollBy({ left: -270, behavior: 'smooth' });
        }
      });
      nextBtn.addEventListener('click', () => {
        if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 20) {
          track.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
          track.scrollBy({ left: 270, behavior: 'smooth' });
        }
      });
    }
  }
}

// Global Quick Action Functions
window.quickAddToCart = async function(productId) {
  await api.cart.addItem(productId, 1);
  showToast('Đã thêm sản phẩm vào giỏ hàng!', 'success');
};

window.toggleWishlist = function(productId, btn) {
  btn.classList.toggle('active');
  const isActive = btn.classList.contains('active');
  btn.style.color = isActive ? '#d70018' : '#94a3b8';
  showToast(isActive ? 'Đã thêm vào danh sách yêu thích' : 'Đã bỏ khỏi danh sách yêu thích', 'info');
};
