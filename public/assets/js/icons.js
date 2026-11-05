/**
 * TechStore - Synchronized Custom Handcrafted SVG Icon Library (icons.js)
 * High-precision 24x24 vector icons matching modern CellphoneS & Apple design system
 * Author: Tuan Kiet (Front-end & UI Designer)
 */

(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    define([], factory);
  } else if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.TechIcons = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  const ICONS = {
    // ----------------------------------------------------
    // Brand & Logo
    // ----------------------------------------------------
    'logo-mark': {
      viewBox: '0 0 24 24',
      svg: `
        <defs>
          <linearGradient id="ti-logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#d70018"/>
            <stop offset="50%" stop-color="#ff007a"/>
            <stop offset="100%" stop-color="#7928ca"/>
          </linearGradient>
        </defs>
        <path d="M12 2L2 7l10 5 10-5-10-5z" fill="url(#ti-logo-grad)" opacity="0.9"/>
        <path d="M2 17l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'brand-apple': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.38c.62-.75 1.04-1.8 0.93-2.85-.9.04-1.99.6-2.63 1.35-.57.65-1.06 1.72-.93 2.74 1.01.08 2.02-.49 2.63-1.24z" fill="currentColor"/>
      `
    },

    // ----------------------------------------------------
    // Category Icons (Self-drawn modern hardware silhouettes)
    // ----------------------------------------------------
    'category-all': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="3" y="3" width="7" height="7" rx="2" stroke="currentColor" stroke-width="2"/>
        <rect x="14" y="3" width="7" height="7" rx="2" stroke="currentColor" stroke-width="2"/>
        <rect x="14" y="14" width="7" height="7" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M6.5 14v7M3 17.5h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'category-phone': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="6" y="2" width="12" height="20" rx="3" stroke="currentColor" stroke-width="2"/>
        <path d="M10.5 5.5h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <circle cx="12" cy="18" r="1" fill="currentColor"/>
      `
    },
    'category-laptop': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="4" y="4" width="16" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M2 19h20M9 19v-1h6v1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="12" cy="9.5" r="1.2" fill="currentColor"/>
      `
    },
    'category-tablet': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="4" y="3" width="16" height="18" rx="2.5" stroke="currentColor" stroke-width="2"/>
        <circle cx="12" cy="18" r="1" fill="currentColor"/>
        <path d="M11 5.5h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'category-audio': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M4 14v-3a8 8 0 0116 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <rect x="3" y="13" width="4" height="7" rx="2" stroke="currentColor" stroke-width="2" fill="currentColor" fill-opacity="0.15"/>
        <rect x="17" y="13" width="4" height="7" rx="2" stroke="currentColor" stroke-width="2" fill="currentColor" fill-opacity="0.15"/>
      `
    },
    'category-watch': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="6.5" y="6" width="11" height="12" rx="3.5" stroke="currentColor" stroke-width="2"/>
        <path d="M9 6V2h6v4M9 18v4h6v-4M18.5 10v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
      `
    },
    'category-accessory': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="7" y="8" width="10" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M9 4v4M15 4v4M12 12v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M10.5 14l3-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
      `
    },

    // ----------------------------------------------------
    // Benefits / Trust Badges
    // ----------------------------------------------------
    'shield-check': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 2L4 5.5v6.2c0 5.3 3.4 10.2 8 11.3 4.6-1.1 8-6 8-11.3V5.5L12 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" fill="currentColor" fill-opacity="0.12"/>
        <path d="M8.5 11.8l2.3 2.3 4.7-4.7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'truck-fast': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M2 7h11v10H2zm11 3h4l3 3v4h-7z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <circle cx="6" cy="18" r="2" stroke="currentColor" stroke-width="2"/>
        <circle cx="17" cy="18" r="2" stroke="currentColor" stroke-width="2"/>
        <path d="M2 4h6M3 7H1M1 11h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'refresh-sync': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M21 12a9 9 0 01-14.85 6.83L4 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M4 21v-4h4M3 12a9 9 0 0114.85-6.83L20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M20 3v4h-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'vietqr-badge': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="3" y="4" width="18" height="16" rx="3" stroke="currentColor" stroke-width="2"/>
        <rect x="6" y="7" width="4" height="4" rx="1" fill="currentColor"/>
        <rect x="14" y="7" width="4" height="4" rx="1" fill="currentColor"/>
        <rect x="6" y="13" width="4" height="4" rx="1" fill="currentColor"/>
        <path d="M14 13h4v4h-4zM16 15h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },

    // ----------------------------------------------------
    // Actions, E-Commerce & Controls
    // ----------------------------------------------------
    'search': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
        <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
        <path d="M8 11a3 3 0 013-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" opacity="0.6"/>
      `
    },
    'cart': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M3 4h2l2.2 10.3a2 2 0 002 1.7h8.8a2 2 0 002-1.6L21.5 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="9.5" cy="19.5" r="1.5" fill="currentColor"/>
        <circle cx="17.5" cy="19.5" r="1.5" fill="currentColor"/>
      `
    },
    'cart-plus': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M3 4h2l2.2 10.3a2 2 0 002 1.7h8.8a2 2 0 002-1.6L21.5 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="9.5" cy="19.5" r="1.5" fill="currentColor"/>
        <circle cx="17.5" cy="19.5" r="1.5" fill="currentColor"/>
        <path d="M12 9v4M10 11h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'heart': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 20.36L4.32 12.7A5 5 0 0111.4 5.64l.6.61.6-.61a5 5 0 017.08 7.07L12 20.36z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'heart-filled': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 20.36L4.32 12.7A5 5 0 0111.4 5.64l.6.61.6-.61a5 5 0 017.08 7.07L12 20.36z" fill="#d70018" stroke="#d70018" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'user': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
        <path d="M5 19.5a7 7 0 0114 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'user-plus': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="10" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
        <path d="M3 19.5a7 7 0 0112.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M19 8v6M16 11h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'key': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="8" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
        <circle cx="8" cy="12" r="2" fill="currentColor"/>
        <path d="M13 12h8v3h-2v2h-2v-2h-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'package': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 3l9 4.8v8.4l-9 4.8-9-4.8V7.8L12 3z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M12 3v9.6M21 7.8l-9 4.8M3 7.8l9 4.8" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M8 12.8l4 2.1 4-2.1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" opacity="0.6"/>
      `
    },
    'gear': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="2"/>
        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2"/>
      `
    },
    'logout': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'hotline': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M15 2a6 6 0 016 6M15 5a3 3 0 013 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
      `
    },
    'api-code': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="3" y="4" width="18" height="16" rx="3" stroke="currentColor" stroke-width="2"/>
        <path d="M8 10l-2 2 2 2M16 10l2 2-2 2M13 9l-2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'flame-flash': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 2c1 3.5 3.5 5 4.5 7.5A6.5 6.5 0 116.5 14C6.5 9 10 7.5 12 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" fill="currentColor" fill-opacity="0.2"/>
        <path d="M12 13a2.5 2.5 0 00-2.5 2.5c0 1.5 1 2.5 2.5 2.5s2.5-1 2.5-2.5c0-1.5-1.5-2-2.5-2.5z" fill="currentColor"/>
      `
    },
    'bolt': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" fill="currentColor" fill-opacity="0.2"/>
      `
    },
    'star-filled': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="#f59e0b" stroke="#f59e0b" stroke-width="1.5" stroke-linejoin="round"/>
      `
    },
    'star-outline': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
      `
    },
    'trash': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'eye': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
      `
    },
    'eye-off': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'copy': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="8" y="8" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M5 16H4a2 2 0 01-2-2V4a2 2 0 012-2h10a2 2 0 012 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'check': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'check-circle': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
        <path d="M8.5 12.5l2.5 2.5 5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'close': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
      `
    },
    'info-circle': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
        <path d="M12 8h.01M12 11v5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
      `
    },
    'alert-triangle': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
      `
    },
    'ticket': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M3 8a3 3 0 013-3h12a3 3 0 013 3v2a2 2 0 000 4v2a3 3 0 01-3 3H6a3 3 0 01-3-3v-2a2 2 0 000-4V8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M9 12h.01M15 12h.01M12 9v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'credit-card': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor" stroke-width="2"/>
        <path d="M2 10h20M6 15h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'bank': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M3 21h18M3 10h18M12 3l9 5H3l9-5zM5 10v7M9 10v7M15 10v7M19 10v7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'cash': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
        <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="2"/>
        <path d="M6 12h.01M18 12h.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      `
    },
    'lock': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="4" y="10" width="16" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M7 10V6a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <circle cx="12" cy="15.5" r="1.5" fill="currentColor"/>
      `
    },
    'unlock': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="4" y="10" width="16" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M7 10V6a5 5 0 019.9-1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <circle cx="12" cy="15.5" r="1.5" fill="currentColor"/>
      `
    },
    'map-pin': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
      `
    },
    'filter': {
      viewBox: '0 0 24 24',
      svg: `
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'chevron-right': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'chevron-left': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'chevron-down': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'arrow-right': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'share': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
        <circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
        <circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
        <path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98" stroke="currentColor" stroke-width="2"/>
      `
    },
    'chip': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="6" y="6" width="12" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M9 9h6v6H9z" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.5"/>
        <path d="M9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'battery': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="2" y="7" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M20 10v4M6 11h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'camera': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="2"/>
      `
    },
    'screen': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="3" y="4" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M8 21h8M12 18v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'sparkle': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 2l2.4 6.6L21 11l-6.6 2.4L12 20l-2.4-6.6L3 11l6.6-2.4L12 2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.25"/>
      `
    },
    'clock': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
        <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'menu': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
      `
    },
    'dashboard': {
      viewBox: '0 0 24 24',
      svg: `
        <rect x="3" y="3" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
        <rect x="13" y="3" width="8" height="5" rx="2" stroke="currentColor" stroke-width="2"/>
        <rect x="13" y="11" width="8" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
        <rect x="3" y="14" width="8" height="7" rx="2" stroke="currentColor" stroke-width="2"/>
      `
    },
    'trend-up': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M23 6l-9.5 9.5-5-5L1 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M17 6h6v6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'users': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
        <path d="M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M16 3.13a4 4 0 010 7.75M21 21v-2a4 4 0 00-3-3.85" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'dollar-circle': {
      viewBox: '0 0 24 24',
      svg: `
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
        <path d="M12 6v12M15 9.5c0-1.5-1.5-2-3-2s-3 .5-3 2 1.5 2 3 2 3 .5 3 2-1.5 2-3 2-3-.5-3-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'plus': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
      `
    },
    'edit': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'shopping-bag': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'external-link': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'printer': {
      viewBox: '0 0 24 24',
      svg: `
        <polyline points="6 9 6 2 18 2 18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="6" y="14" width="12" height="8" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="18" cy="14" r="1" fill="currentColor"/>
      `
    },
    'file-text': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <line x1="10" y1="9" x2="8" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    },
    'book-open': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      `
    },
    'chart-bar': {
      viewBox: '0 0 24 24',
      svg: `
        <path d="M12 20V10M18 20V4M6 20v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M3 20h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      `
    }
  };

  /**
   * Render an SVG string for the specified icon name
   * @param {string} name - Name of icon in ICONS registry
   * @param {object} [opts] - Configuration options: size, className, color, strokeWidth, style
   */
  function render(name, opts = {}) {
    const icon = ICONS[name] || ICONS['sparkle'];
    const size = opts.size || 20;
    const cls = opts.className ? `tech-icon ${opts.className}` : 'tech-icon';
    const color = opts.color || 'currentColor';
    const strokeWidth = opts.strokeWidth ? `stroke-width="${opts.strokeWidth}"` : '';
    const style = opts.style ? `style="${opts.style}"` : '';

    return `<svg class="${cls}" width="${size}" height="${size}" viewBox="${icon.viewBox}" fill="none" color="${color}" ${strokeWidth} ${style} aria-hidden="true">${icon.svg}</svg>`;
  }

  /**
   * Scan DOM and hydrate elements containing [data-tech-icon]
   */
  function mountAll(container = document) {
    const elements = container.querySelectorAll('[data-tech-icon]');
    elements.forEach(el => {
      const iconName = el.getAttribute('data-tech-icon');
      const size = el.getAttribute('data-size') || (el.classList.contains('icon-lg') ? 24 : (el.classList.contains('icon-sm') ? 16 : 20));
      const cls = el.getAttribute('data-class') || '';
      el.innerHTML = render(iconName, { size, className: cls });
      el.removeAttribute('data-tech-icon');
    });
  }

  // Auto-mount on DOMContentLoaded
  if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => mountAll());
    } else {
      mountAll();
    }
  }

  return {
    registry: ICONS,
    render: render,
    mountAll: mountAll,
    get: render
  };
});
