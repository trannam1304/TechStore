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
   */
  static async renderBoughtTogether(productId, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    try {
      const mainRes = await api.products.getById(productId);
      const mainProduct = mainRes.data;

      // Recommended accessories / bundle items
      const allRes = await api.products.getAll();
      const accessories = allRes.data.filter(p => p.id != productId && (p.category_id === 6 || p.category_id === 4)).slice(0, 2);

      if (!accessories.length) return;

      const comboTotalOriginal = mainProduct.price + accessories.reduce((sum, a) => sum + a.price, 0);
      const comboDiscount = Math.round(comboTotalOriginal * 0.08); // 8% bundle combo discount
      const comboTotal = comboTotalOriginal - comboDiscount;

      container.innerHTML = `
        <div class="bought-together-card" style="background: rgba(255, 255, 255, 0.78); backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); border: 1.5px solid rgba(255, 255, 255, 0.85); border-radius: var(--radius-xl); padding: 24px; margin-top: 32px; box-shadow: 0 16px 40px -10px rgba(215, 0, 24, 0.15), inset 0 0 0 1px rgba(255, 255, 255, 0.9); position: relative; overflow: hidden;">
          <div style="font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <span style="color: #d70018;">🛍️ Thường được mua cùng</span>
            <span style="font-size: 12px; background: linear-gradient(135deg, #fee2e2, #fce7f3); color: #d70018; padding: 3px 10px; border-radius: var(--radius-full); font-weight: 800; border: 1px solid rgba(215, 0, 24, 0.2);">Tiết kiệm combo 8%</span>
          </div>

          <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 20px;">
            <!-- Main Product -->
            <div style="text-align: center; width: 140px;">
              <img src="${mainProduct.image}" style="width: 100px; height: 100px; object-fit: contain; margin: 0 auto 8px;" alt="${mainProduct.name}">
              <div style="font-size: 12px; font-weight: 600; line-height: 1.3; height: 2.6em; overflow: hidden;">${mainProduct.name}</div>
              <div style="font-size: 13px; font-weight: 700; color: #d70018; margin-top: 4px;">${formatVND(mainProduct.price)}</div>
            </div>

            ${accessories.map((acc, idx) => `
              <div style="font-size: 24px; font-weight: 700; color: #94a3b8;">+</div>
              <div style="text-align: center; width: 140px;">
                <img src="${acc.image}" style="width: 100px; height: 100px; object-fit: contain; margin: 0 auto 8px;" alt="${acc.name}">
                <div style="font-size: 12px; font-weight: 600; line-height: 1.3; height: 2.6em; overflow: hidden;">${acc.name}</div>
                <div style="font-size: 13px; font-weight: 700; color: #d70018; margin-top: 4px;">${formatVND(acc.price)}</div>
              </div>
            `).join('')}

            <div style="font-size: 24px; font-weight: 700; color: #94a3b8;">=</div>

            <!-- Combo Summary -->
            <div style="flex: 1; min-width: 220px; padding-left: 16px; border-left: 1px dashed rgba(226, 232, 240, 0.8);">
              <div style="font-size: 13px; color: #64748b;">Giá gốc cả bộ: <del>${formatVND(comboTotalOriginal)}</del></div>
              <div style="font-size: 20px; font-weight: 900; color: #d70018; margin: 4px 0;">${formatVND(comboTotal)}</div>
              <div style="font-size: 12px; color: #16a34a; font-weight: 600; margin-bottom: 12px;">Tiết kiệm được ${formatVND(comboDiscount)} khi mua combo!</div>
              <button class="btn btn-primary btn-block" id="btn-buy-combo" style="font-weight: 700;">
                ⚡ Mua cả ${accessories.length + 1} sản phẩm
              </button>
            </div>
          </div>
        </div>
      `;

      // Combo click handler
      const btnBuyCombo = container.querySelector('#btn-buy-combo');
      if (btnBuyCombo) {
        btnBuyCombo.addEventListener('click', async () => {
          btnBuyCombo.disabled = true;
          btnBuyCombo.textContent = 'Đang thêm vào giỏ...';
          await api.cart.addItem(mainProduct.id, 1, 'Combo');
          for (const acc of accessories) {
            await api.cart.addItem(acc.id, 1, 'Combo phụ kiện');
          }
          showToast(`Đã thêm combo ${accessories.length + 1} món vào giỏ hàng!`, 'success');
          btnBuyCombo.textContent = '⚡ Đã thêm vào giỏ!';
          setTimeout(() => {
            window.location.href = '/pages/cart.html';
          }, 800);
        });
      }
    } catch (e) {
      console.error('Error rendering Bought Together', e);
    }
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
