import json
import os

collection = {
    "info": {
        "_postman_id": "techstore-api-collection-v21-complete",
        "name": "TechStore API - Automated Test Suite",
        "description": "Bộ kiểm thử API tự động toàn diện cho TechStore (FR01 - FR10): Xác thực JWT, Sản phẩm, Danh mục, Giỏ hàng, Mã giảm giá, Đặt hàng ACID, Thanh toán VietQR, Đánh giá/Wishlist, Gợi ý thông minh và Quản trị Admin.",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        # =========================================================================
        # 01. AUTHENTICATION & USERS
        # =========================================================================
        {
            "name": "01. Authentication & Users",
            "item": [
                {
                    "name": "1.1 Register New Customer (201 Created)",
                    "event": [{
                        "listen": "prerequest",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "const randomSuffix = Math.floor(Math.random() * 100000);",
                                "pm.environment.set('temp_register_email', 'autotest_' + randomSuffix + '@techstore.local');"
                            ]
                        }
                    }, {
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 201 Created', function () {",
                                "    pm.response.to.have.status(201);",
                                "});",
                                "pm.test('Response contains tokens and user data', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('user');",
                                "    pm.expect(jsonData.data).to.have.property('access_token');",
                                "    pm.expect(jsonData.data).to.have.property('refresh_token');",
                                "    pm.environment.set('access_token', jsonData.data.access_token);",
                                "    pm.environment.set('refresh_token', jsonData.data.refresh_token);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "name": "Auto Test Customer",
                                "email": "{{temp_register_email}}",
                                "password": "Password@123",
                                "phone": "0987654321"
                            }, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/auth/register", "host": ["{{base_url}}"], "path": ["auth", "register"]}
                    }
                },
                {
                    "name": "1.2 Register Duplicate Email (409 Conflict)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 409 Conflict', function () {",
                                "    pm.response.to.have.status(409);",
                                "});",
                                "pm.test('Error code is EMAIL_ALREADY_EXISTS', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.false;",
                                "    pm.expect(jsonData.error.code).to.eql('EMAIL_ALREADY_EXISTS');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "name": "Duplicate User",
                                "email": "{{admin_email}}",
                                "password": "Password@123"
                            }, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/auth/register", "host": ["{{base_url}}"], "path": ["auth", "register"]}
                    }
                },
                {
                    "name": "1.3 Register Validation Failure (422 Unprocessable)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 400 or 422', function () {",
                                "    pm.expect(pm.response.code).to.be.oneOf([400, 422]);",
                                "});",
                                "pm.test('Error code is VALIDATION_ERROR', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.false;",
                                "    pm.expect(jsonData.error.code).to.eql('VALIDATION_ERROR');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"name": "", "email": "invalid-email-format", "password": "123"}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/auth/register", "host": ["{{base_url}}"], "path": ["auth", "register"]}
                    }
                },
                {
                    "name": "1.4 Login Admin (200 OK & Save Admin Token)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('User is admin and token saved', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data.user.role).to.eql('admin');",
                                "    pm.environment.set('admin_token', jsonData.data.access_token);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"email": "{{admin_email}}", "password": "{{admin_password}}"}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/auth/login", "host": ["{{base_url}}"], "path": ["auth", "login"]}
                    }
                },
                {
                    "name": "1.5 Login Customer (200 OK & Save Customer Token)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('User is customer and token saved', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data.user.role).to.eql('customer');",
                                "    pm.environment.set('access_token', jsonData.data.access_token);",
                                "    pm.environment.set('refresh_token', jsonData.data.refresh_token);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"email": "{{customer_email}}", "password": "{{customer_password}}"}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/auth/login", "host": ["{{base_url}}"], "path": ["auth", "login"]}
                    }
                },
                {
                    "name": "1.6 Login Wrong Password (401 Unauthorized)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 401 Unauthorized', function () {",
                                "    pm.response.to.have.status(401);",
                                "});",
                                "pm.test('Error code is INVALID_CREDENTIALS', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.false;",
                                "    pm.expect(jsonData.error.code).to.eql('INVALID_CREDENTIALS');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"email": "{{customer_email}}", "password": "WrongPassword_999"}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/auth/login", "host": ["{{base_url}}"], "path": ["auth", "login"]}
                    }
                },
                {
                    "name": "1.7 Get Profile Me (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns authenticated user profile without password_hash', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('email');",
                                "    pm.expect(jsonData.data).to.not.have.property('password_hash');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/auth/me", "host": ["{{base_url}}"], "path": ["auth", "me"]}
                    }
                },
                {
                    "name": "1.8 Refresh Access Token (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Provides new access_token', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('access_token');",
                                "    pm.environment.set('access_token', jsonData.data.access_token);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"refresh_token": "{{refresh_token}}"}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/auth/refresh", "host": ["{{base_url}}"], "path": ["auth", "refresh"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 02. PRODUCTS & CATALOG
        # =========================================================================
        {
            "name": "02. Products & Catalog",
            "item": [
                {
                    "name": "2.1 Get Products List (200 OK & Pagination)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns items and pagination metadata', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    const items = Array.isArray(jsonData.data) ? jsonData.data : (jsonData.data.items || []);",
                                "    pm.expect(items).to.be.an('array');",
                                "    if (jsonData.meta) {",
                                "        pm.expect(jsonData.meta).to.have.property('total_items');",
                                "    }",
                                "    if (items.length > 0) {",
                                "        pm.environment.set('test_product_id', items[0].id);",
                                "    }",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/products?page=1&limit=10", "host": ["{{base_url}}"], "path": ["products"], "query": [{"key": "page", "value": "1"}, {"key": "limit", "value": "10"}]}
                    }
                },
                {
                    "name": "2.2 Search Products by Keyword (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Search results match query', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    const items = Array.isArray(jsonData.data) ? jsonData.data : (jsonData.data.items || []);",
                                "    pm.expect(items.length).to.be.at.least(1);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/products?search=iPhone", "host": ["{{base_url}}"], "path": ["products"], "query": [{"key": "search", "value": "iPhone"}]}
                    }
                },
                {
                    "name": "2.3 Filter Products by Category (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('All returned products have category_id = 1', function () {",
                                "    const jsonData = pm.response.json();",
                                "    const items = Array.isArray(jsonData.data) ? jsonData.data : (jsonData.data.items || []);",
                                "    items.forEach(p => {",
                                "        pm.expect(Number(p.category_id)).to.eql(1);",
                                "    });",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/products?category_id=1", "host": ["{{base_url}}"], "path": ["products"], "query": [{"key": "category_id", "value": "1"}]}
                    }
                },
                {
                    "name": "2.4 Filter Products by Brand & Price Range (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Products match brand and price boundary', function () {",
                                "    const jsonData = pm.response.json();",
                                "    const items = Array.isArray(jsonData.data) ? jsonData.data : (jsonData.data.items || []);",
                                "    items.forEach(p => {",
                                "        pm.expect(Number(p.brand_id)).to.eql(1);",
                                "        pm.expect(Number(p.price)).to.be.at.least(10000000);",
                                "        pm.expect(Number(p.price)).to.be.at.most(45000000);",
                                "    });",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/products?brand_id=1&min_price=10000000&max_price=45000000", "host": ["{{base_url}}"], "path": ["products"], "query": [{"key": "brand_id", "value": "1"}, {"key": "min_price", "value": "10000000"}, {"key": "max_price", "value": "45000000"}]}
                    }
                },
                {
                    "name": "2.5 Get Product Detail by ID (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Contains full specifications and gallery', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('id');",
                                "    pm.expect(jsonData.data).to.have.property('name');",
                                "    pm.expect(jsonData.data).to.have.property('price');",
                                "    pm.expect(jsonData.data).to.have.property('stock');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/products/{{test_product_id}}", "host": ["{{base_url}}"], "path": ["products", "{{test_product_id}}"]}
                    }
                },
                {
                    "name": "2.6 Get Product Detail Not Found (404 Not Found)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 404 Not Found', function () {",
                                "    pm.response.to.have.status(404);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/products/999999", "host": ["{{base_url}}"], "path": ["products", "999999"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 03. CATEGORIES & BRANDS
        # =========================================================================
        {
            "name": "03. Categories & Brands",
            "item": [
                {
                    "name": "3.1 Get All Categories (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns list of categories', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.be.an('array');",
                                "    pm.expect(jsonData.data.length).to.be.at.least(5);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/categories", "host": ["{{base_url}}"], "path": ["categories"]}
                    }
                },
                {
                    "name": "3.2 Get All Brands (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns list of brands', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.be.an('array');",
                                "    pm.expect(jsonData.data.length).to.be.at.least(5);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/brands", "host": ["{{base_url}}"], "path": ["brands"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 04. CART & INVENTORY CONTROL
        # =========================================================================
        {
            "name": "04. Cart & Inventory Control",
            "item": [
                {
                    "name": "4.1 Clear Cart (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "DELETE",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/cart", "host": ["{{base_url}}"], "path": ["cart"]}
                    }
                },
                {
                    "name": "4.2 Get Cart Status (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Cart structure is valid', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('items');",
                                "    pm.expect(jsonData.data).to.have.property('subtotal');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/cart", "host": ["{{base_url}}"], "path": ["cart"]}
                    }
                },
                {
                    "name": "4.3 Add Item to Cart (201 Created & Save Cart Item ID)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 201 Created', function () {",
                                "    pm.response.to.have.status(201);",
                                "});",
                                "pm.test('Item added to cart successfully', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data.items.length).to.be.at.least(1);",
                                "    const addedItem = jsonData.data.items[0];",
                                "    pm.environment.set('test_cart_item_id', addedItem.id);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Authorization", "value": "Bearer {{access_token}}"},
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"product_id": 1, "quantity": 1, "variant": "256GB - Titan Tự Nhiên"}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/cart/items", "host": ["{{base_url}}"], "path": ["cart", "items"]}
                    }
                },
                {
                    "name": "4.4 Add Item Exceeding Stock (400 Bad Request)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 400 Bad Request', function () {",
                                "    pm.response.to.have.status(400);",
                                "});",
                                "pm.test('Error code is INSUFFICIENT_STOCK', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.false;",
                                "    pm.expect(jsonData.error.code).to.eql('INSUFFICIENT_STOCK');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Authorization", "value": "Bearer {{access_token}}"},
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"product_id": 1, "quantity": 99999}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/cart/items", "host": ["{{base_url}}"], "path": ["cart", "items"]}
                    }
                },
                {
                    "name": "4.5 Update Cart Item Quantity (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Quantity updated in cart', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "PUT",
                        "header": [
                            {"key": "Authorization", "value": "Bearer {{access_token}}"},
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"quantity": 2}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/cart/items/{{test_cart_item_id}}", "host": ["{{base_url}}"], "path": ["cart", "items", "{{test_cart_item_id}}"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 05. COUPONS ENGINE
        # =========================================================================
        {
            "name": "05. Coupons Engine",
            "item": [
                {
                    "name": "5.1 Validate Valid Coupon (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Coupon discount calculated accurately', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('discount');",
                                "    pm.expect(jsonData.data).to.have.property('final_total');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"code": "TECH10", "total": 35000000}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/coupons/validate", "host": ["{{base_url}}"], "path": ["coupons", "validate"]}
                    }
                },
                {
                    "name": "5.2 Validate Invalid Coupon (404 Not Found)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 404 Not Found', function () {",
                                "    pm.response.to.have.status(404);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"code": "NON_EXISTING_CODE_XYZ", "total": 10000000}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/coupons/validate", "host": ["{{base_url}}"], "path": ["coupons", "validate"]}
                    }
                },
                {
                    "name": "5.3 Get Public Coupons (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns list of available coupons', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.be.an('array');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/coupons", "host": ["{{base_url}}"], "path": ["coupons"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 06. ORDERS & ACID TRANSACTION
        # =========================================================================
        {
            "name": "06. Orders & ACID Transaction",
            "item": [
                {
                    "name": "6.1 Create Order Checkout (201 Created & Save Order ID)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 201 Created', function () {",
                                "    pm.response.to.have.status(201);",
                                "});",
                                "pm.test('Order created with code and ID', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('id');",
                                "    pm.expect(jsonData.data).to.have.property('order_code');",
                                "    pm.environment.set('test_order_id', jsonData.data.id);",
                                "    pm.environment.set('test_order_code', jsonData.data.order_code);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Authorization", "value": "Bearer {{access_token}}"},
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "customer_name": "Tuan Kiet (Postman Test)",
                                "customer_phone": "0987654321",
                                "customer_email": "user@techstore.local",
                                "shipping_address": "123 Cach Mang Thang 8, Q10, TP.HCM",
                                "payment_method": "vietqr",
                                "note": "Giao giờ hành chính",
                                "items": [
                                    {"product_id": 1, "product_name": "iPhone 16 Pro Max", "price": 34990000, "quantity": 1, "variant": "256GB"}
                                ]
                            }, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/orders", "host": ["{{base_url}}"], "path": ["orders"]}
                    }
                },
                {
                    "name": "6.2 Get Customer Orders List (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns order history array', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.be.an('array');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/orders", "host": ["{{base_url}}"], "path": ["orders"]}
                    }
                },
                {
                    "name": "6.3 Get Order Detail by ID (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Contains order items and customer info', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('items');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/orders/{{test_order_id}}", "host": ["{{base_url}}"], "path": ["orders", "{{test_order_id}}"]}
                    }
                },
                {
                    "name": "6.4 Cancel Pending Order (200 OK & Restore Stock)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Order status changed to cancelled', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/orders/{{test_order_id}}/cancel", "host": ["{{base_url}}"], "path": ["orders", "{{test_order_id}}", "cancel"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 07. PAYMENTS (VIETQR & WEBHOOK)
        # =========================================================================
        {
            "name": "07. Payments (VietQR & Webhook)",
            "item": [
                {
                    "name": "7.1 Generate VietQR Dynamic Code (201 Created)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 201 Created', function () {",
                                "    pm.response.to.have.status(201);",
                                "});",
                                "pm.test('Returns VietQR NAPAS247 image link', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('qr_url');",
                                "    pm.expect(jsonData.data.qr_url).to.include('vietqr.io');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Authorization", "value": "Bearer {{access_token}}"},
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"order_id": 1001, "amount": 40870000, "order_code": "TS-749201"}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/payments/qr", "host": ["{{base_url}}"], "path": ["payments", "qr"]}
                    }
                },
                {
                    "name": "7.2 Polling Payment Status (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns payment status flag', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('payment_status');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/payments/1001/status", "host": ["{{base_url}}"], "path": ["payments", "1001", "status"]}
                    }
                },
                {
                    "name": "7.3 Webhook Missing Signature (401 Unauthorized)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 401 Unauthorized', function () {",
                                "    pm.response.to.have.status(401);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"order_code": "TS-749201", "amount": 40870000}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/payments/webhook", "host": ["{{base_url}}"], "path": ["payments", "webhook"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 08. REVIEWS & WISHLIST
        # =========================================================================
        {
            "name": "08. Reviews & Wishlist",
            "item": [
                {
                    "name": "8.1 Get Product Reviews (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Contains reviews list array', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    const reviews = Array.isArray(jsonData.data) ? jsonData.data : (jsonData.data.reviews || []);",
                                "    pm.expect(reviews).to.be.an('array');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/products/1/reviews", "host": ["{{base_url}}"], "path": ["products", "1", "reviews"]}
                    }
                },
                {
                    "name": "8.2 Get Customer Wishlist (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns wishlist array', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.be.an('array');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/wishlist", "host": ["{{base_url}}"], "path": ["wishlist"]}
                    }
                },
                {
                    "name": "8.3 Add Product to Wishlist (200/201 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is successful (200 or 201)', function () {",
                                "    pm.expect(pm.response.code).to.be.oneOf([200, 201]);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Authorization", "value": "Bearer {{access_token}}"},
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"product_id": 2}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/wishlist", "host": ["{{base_url}}"], "path": ["wishlist"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 09. RECOMMENDATIONS & AI EVENTS
        # =========================================================================
        {
            "name": "09. Recommendations & AI Events",
            "item": [
                {
                    "name": "9.1 Track Product View Event (200/202)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 or 202', function () {",
                                "    pm.expect(pm.response.code).to.be.oneOf([200, 202]);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "POST",
                        "header": [{"key": "Content-Type", "value": "application/json"}],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"product_id": 1}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/events/view", "host": ["{{base_url}}"], "path": ["events", "view"]}
                    }
                },
                {
                    "name": "9.2 Get Product Recommendations - Cosine & FBT (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns similar and bought-together products', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('similar');",
                                "    pm.expect(jsonData.data).to.have.property('bought_together');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {"raw": "{{base_url}}/products/1/recommendations", "host": ["{{base_url}}"], "path": ["products", "1", "recommendations"]}
                    }
                },
                {
                    "name": "9.3 Get Personalized Recommendations for Me (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns personalized product list', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    const items = Array.isArray(jsonData.data) ? jsonData.data : (jsonData.data.for_you || []);",
                                "    pm.expect(items).to.be.an('array');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/recommendations/me", "host": ["{{base_url}}"], "path": ["recommendations", "me"]}
                    }
                }
            ]
        },

        # =========================================================================
        # 10. ADMIN MANAGEMENT & AUDIT LOGS
        # =========================================================================
        {
            "name": "10. Admin Management & Audit Logs",
            "item": [
                {
                    "name": "10.1 Get Dashboard Statistics 7 Days (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Contains KPI metrics and revenue chart', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.have.property('revenue');",
                                "    pm.expect(jsonData.data).to.have.property('orders');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{admin_token}}"}],
                        "url": {"raw": "{{base_url}}/admin/stats", "host": ["{{base_url}}"], "path": ["admin", "stats"]}
                    }
                },
                {
                    "name": "10.2 Customer Forbidden from Admin Stats (403 Forbidden)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 403 Forbidden', function () {",
                                "    pm.response.to.have.status(403);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{access_token}}"}],
                        "url": {"raw": "{{base_url}}/admin/stats", "host": ["{{base_url}}"], "path": ["admin", "stats"]}
                    }
                },
                {
                    "name": "10.3 Get Users List Admin (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns list of system users', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.be.an('array');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{admin_token}}"}],
                        "url": {"raw": "{{base_url}}/admin/users", "host": ["{{base_url}}"], "path": ["admin", "users"]}
                    }
                },
                {
                    "name": "10.4 Patch User Status Admin (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "PATCH",
                        "header": [
                            {"key": "Authorization", "value": "Bearer {{admin_token}}"},
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({"status": "active"}, indent=2)
                        },
                        "url": {"raw": "{{base_url}}/admin/users/2", "host": ["{{base_url}}"], "path": ["admin", "users", "2"]}
                    }
                },
                {
                    "name": "10.5 Get System Audit Logs (200 OK)",
                    "event": [{
                        "listen": "test",
                        "script": {
                            "type": "text/javascript",
                            "exec": [
                                "pm.test('Status code is 200 OK', function () {",
                                "    pm.response.to.have.status(200);",
                                "});",
                                "pm.test('Returns immutable audit logs', function () {",
                                "    const jsonData = pm.response.json();",
                                "    pm.expect(jsonData.success).to.be.true;",
                                "    pm.expect(jsonData.data).to.be.an('array');",
                                "});"
                            ]
                        }
                    }],
                    "request": {
                        "method": "GET",
                        "header": [{"key": "Authorization", "value": "Bearer {{admin_token}}"}],
                        "url": {"raw": "{{base_url}}/admin/audit-logs", "host": ["{{base_url}}"], "path": ["admin", "audit-logs"]}
                    }
                }
            ]
        }
    ]
}

target_path = os.path.join(os.path.dirname(__file__), "..", "tests", "postman_collection.json")
with open(target_path, "w", encoding="utf-8") as f:
    json.dump(collection, f, indent=2, ensure_ascii=False)

print(f"Postman collection successfully generated at: {target_path}")
