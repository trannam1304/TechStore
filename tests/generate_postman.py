import json
import os

collection = {
    "info": {
        "_postman_id": "techstore-api-collection-v21",
        "name": "TechStore API - Automated Test Suite",
        "description": "Bộ kiểm thử API tự động cho TechStore: Authentication (JWT), Products (Filter/Search/Sort/Pagination), Categories & Brands.",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "01. Authentication & Users",
            "item": [
                {
                    "name": "1.1 Register New Customer (201 Created)",
                    "event": [
                        {
                            "listen": "prerequest",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "const randomSuffix = Math.floor(Math.random() * 100000);",
                                    "pm.environment.set('temp_register_email', 'autotest_' + randomSuffix + '@techstore.local');"
                                ]
                            }
                        },
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 201 Created', function () {",
                                    "    pm.response.to.have.status(201);",
                                    "});",
                                    "",
                                    "pm.test('Response contains user data and tokens', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data).to.have.property('user');",
                                    "    pm.expect(jsonData.data).to.have.property('access_token');",
                                    "    pm.expect(jsonData.data).to.have.property('refresh_token');",
                                    "    pm.expect(jsonData.data.user.role).to.eql('customer');",
                                    "    ",
                                    "    // Cập nhật access_token cho các request kế tiếp",
                                    "    pm.environment.set('access_token', jsonData.data.access_token);",
                                    "    pm.environment.set('refresh_token', jsonData.data.refresh_token);",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "name": "Auto Test Customer",
                                "email": "{{temp_register_email}}",
                                "password": "Password@123",
                                "phone": "0987654321"
                            }, indent=2)
                        },
                        "url": {
                            "raw": "{{base_url}}/auth/register",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "register"]
                        }
                    }
                },
                {
                    "name": "1.2 Register Duplicate Email (409 Conflict)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 409 Conflict', function () {",
                                    "    pm.response.to.have.status(409);",
                                    "});",
                                    "",
                                    "pm.test('Error code is EMAIL_ALREADY_EXISTS', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.false;",
                                    "    pm.expect(jsonData.error.code).to.eql('EMAIL_ALREADY_EXISTS');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "name": "Duplicate User",
                                "email": "{{customer_email}}",
                                "password": "Password@123"
                            }, indent=2)
                        },
                        "url": {
                            "raw": "{{base_url}}/auth/register",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "register"]
                        }
                    }
                },
                {
                    "name": "1.3 Register Validation Failure (422 Unprocessable)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 422 Unprocessable Entity', function () {",
                                    "    pm.response.to.have.status(422);",
                                    "});",
                                    "",
                                    "pm.test('Returns validation error details', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.false;",
                                    "    pm.expect(jsonData.error.code).to.eql('VALIDATION_ERROR');",
                                    "    pm.expect(jsonData.error.details).to.be.an('array');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "name": "",
                                "email": "not-an-email",
                                "password": "123"
                            }, indent=2)
                        },
                        "url": {
                            "raw": "{{base_url}}/auth/register",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "register"]
                        }
                    }
                },
                {
                    "name": "1.4 Login Admin (200 OK & Save Admin Token)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Admin login successful and role is admin', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data.user.role).to.eql('admin');",
                                    "    pm.expect(jsonData.data).to.have.property('access_token');",
                                    "    ",
                                    "    // Lưu token admin & token hiện tại",
                                    "    pm.environment.set('admin_token', jsonData.data.access_token);",
                                    "    pm.environment.set('access_token', jsonData.data.access_token);",
                                    "    pm.environment.set('refresh_token', jsonData.data.refresh_token);",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "email": "{{admin_email}}",
                                "password": "{{admin_password}}"
                            }, indent=2)
                        },
                        "url": {
                            "raw": "{{base_url}}/auth/login",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "login"]
                        }
                    }
                },
                {
                    "name": "1.5 Login Customer (200 OK & Save Customer Token)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Customer login successful and role is customer', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data.user.role).to.eql('customer');",
                                    "    ",
                                    "    // Lưu customer token vào access_token",
                                    "    pm.environment.set('access_token', jsonData.data.access_token);",
                                    "    pm.environment.set('refresh_token', jsonData.data.refresh_token);",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "email": "{{customer_email}}",
                                "password": "{{customer_password}}"
                            }, indent=2)
                        },
                        "url": {
                            "raw": "{{base_url}}/auth/login",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "login"]
                        }
                    }
                },
                {
                    "name": "1.6 Login Wrong Password (401 Unauthorized)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 401 Unauthorized', function () {",
                                    "    pm.response.to.have.status(401);",
                                    "});",
                                    "",
                                    "pm.test('Error code is INVALID_CREDENTIALS', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.false;",
                                    "    pm.expect(jsonData.error.code).to.eql('INVALID_CREDENTIALS');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "email": "{{customer_email}}",
                                "password": "WrongPassword999"
                            }, indent=2)
                        },
                        "url": {
                            "raw": "{{base_url}}/auth/login",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "login"]
                        }
                    }
                },
                {
                    "name": "1.7 Get Profile Me (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Returns authenticated user profile', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data).to.have.property('id');",
                                    "    pm.expect(jsonData.data).to.have.property('email');",
                                    "    pm.expect(jsonData.data).to.not.have.property('password_hash');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [
                            {"key": "Authorization", "value": "Bearer {{access_token}}"}
                        ],
                        "url": {
                            "raw": "{{base_url}}/auth/me",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "me"]
                        }
                    }
                },
                {
                    "name": "1.8 Get Profile Me Without Token (401 Unauthorized)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 401 Unauthorized', function () {",
                                    "    pm.response.to.have.status(401);",
                                    "});",
                                    "",
                                    "pm.test('Error indicates token is required', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.false;",
                                    "    pm.expect(jsonData.error.code).to.eql('UNAUTHORIZED');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/auth/me",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "me"]
                        }
                    }
                },
                {
                    "name": "1.9 Refresh Access Token (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Returns new access_token', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data).to.have.property('access_token');",
                                    "    pm.environment.set('access_token', jsonData.data.access_token);",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Content-Type", "value": "application/json"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "refresh_token": "{{refresh_token}}"
                            }, indent=2)
                        },
                        "url": {
                            "raw": "{{base_url}}/auth/refresh",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "refresh"]
                        }
                    }
                },
                {
                    "name": "1.10 Logout (204 No Content)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 204 No Content', function () {",
                                    "    pm.response.to.have.status(204);",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Content-Type", "value": "application/json"},
                            {"key": "Authorization", "value": "Bearer {{access_token}}"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": json.dumps({
                                "refresh_token": "{{refresh_token}}"
                            }, indent=2)
                        },
                        "url": {
                            "raw": "{{base_url}}/auth/logout",
                            "host": ["{{base_url}}"],
                            "path": ["auth", "logout"]
                        }
                    }
                }
            ]
        },
        {
            "name": "02. Products & Catalog",
            "item": [
                {
                    "name": "2.1 Get Products List (200 OK & Pagination)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Response contains data array and meta pagination', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data).to.be.an('array');",
                                    "    pm.expect(jsonData.meta).to.have.property('total_items');",
                                    "    pm.expect(jsonData.meta.current_page).to.eql(1);",
                                    "    pm.expect(jsonData.meta.per_page).to.eql(10);",
                                    "    ",
                                    "    if (jsonData.data.length > 0) {",
                                    "        pm.environment.set('test_product_id', jsonData.data[0].id);",
                                    "    }",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/products?page=1&limit=10",
                            "host": ["{{base_url}}"],
                            "path": ["products"],
                            "query": [
                                {"key": "page", "value": "1"},
                                {"key": "limit", "value": "10"}
                            ]
                        }
                    }
                },
                {
                    "name": "2.2 Search Products by Keyword (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Matching items contain keyword in name or description', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data.length).to.be.above(0);",
                                    "    jsonData.data.forEach(function(item) {",
                                    "        const nameMatch = item.name.toLowerCase().includes('macbook');",
                                    "        const descMatch = item.description ? item.description.toLowerCase().includes('macbook') : false;",
                                    "        const catMatch = item.category_name ? item.category_name.toLowerCase().includes('macbook') : false;",
                                    "        pm.expect(nameMatch || descMatch || catMatch).to.be.true;",
                                    "    });",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/products?search=MacBook",
                            "host": ["{{base_url}}"],
                            "path": ["products"],
                            "query": [
                                {"key": "search", "value": "MacBook"}
                            ]
                        }
                    }
                },
                {
                    "name": "2.3 Filter Products by Category (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('All items belong to category_id 1', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    jsonData.data.forEach(function(item) {",
                                    "        pm.expect(Number(item.category_id)).to.eql(1);",
                                    "    });",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/products?category_id=1",
                            "host": ["{{base_url}}"],
                            "path": ["products"],
                            "query": [
                                {"key": "category_id", "value": "1"}
                            ]
                        }
                    }
                },
                {
                    "name": "2.4 Filter Products by Brand & Price Range (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Items match brand_id 1 and price within [10M, 40M]', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    jsonData.data.forEach(function(item) {",
                                    "        pm.expect(Number(item.brand_id)).to.eql(1);",
                                    "        pm.expect(Number(item.price)).to.be.at.least(10000000);",
                                    "        pm.expect(Number(item.price)).to.be.at.most(40000000);",
                                    "    });",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/products?brand_id=1&min_price=10000000&max_price=40000000",
                            "host": ["{{base_url}}"],
                            "path": ["products"],
                            "query": [
                                {"key": "brand_id", "value": "1"},
                                {"key": "min_price", "value": "10000000"},
                                {"key": "max_price", "value": "40000000"}
                            ]
                        }
                    }
                },
                {
                    "name": "2.5 Sort Products by Price Ascending (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Products are sorted in ascending order of price', function () {",
                                    "    const items = pm.response.json().data;",
                                    "    for (let i = 0; i < items.length - 1; i++) {",
                                    "        pm.expect(Number(items[i].price)).to.be.at.most(Number(items[i + 1].price));",
                                    "    }",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/products?sort=price_asc",
                            "host": ["{{base_url}}"],
                            "path": ["products"],
                            "query": [
                                {"key": "sort", "value": "price_asc"}
                            ]
                        }
                    }
                },
                {
                    "name": "2.6 Get Product Detail by ID (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Product detail contains specs, category_name, and brand_name', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data).to.have.property('id');",
                                    "    pm.expect(jsonData.data).to.have.property('name');",
                                    "    pm.expect(jsonData.data).to.have.property('specs');",
                                    "    pm.expect(jsonData.data).to.have.property('category_name');",
                                    "    pm.expect(jsonData.data).to.have.property('brand_name');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/products/{{test_product_id}}",
                            "host": ["{{base_url}}"],
                            "path": ["products", "{{test_product_id}}"]
                        }
                    }
                },
                {
                    "name": "2.7 Get Product Detail Not Found (404 Not Found)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 404 Not Found', function () {",
                                    "    pm.response.to.have.status(404);",
                                    "});",
                                    "",
                                    "pm.test('Error code is PRODUCT_NOT_FOUND', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.false;",
                                    "    pm.expect(jsonData.error.code).to.eql('PRODUCT_NOT_FOUND');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/products/999999",
                            "host": ["{{base_url}}"],
                            "path": ["products", "999999"]
                        }
                    }
                }
            ]
        },
        {
            "name": "03. Categories & Brands",
            "item": [
                {
                    "name": "3.1 Get All Categories (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Returns list of categories with product count', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data).to.be.an('array');",
                                    "    pm.expect(jsonData.data.length).to.be.at.least(5);",
                                    "    pm.expect(jsonData.data[0]).to.have.property('name');",
                                    "    pm.expect(jsonData.data[0]).to.have.property('product_count');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/categories",
                            "host": ["{{base_url}}"],
                            "path": ["categories"]
                        }
                    }
                },
                {
                    "name": "3.2 Get All Brands (200 OK)",
                    "event": [
                        {
                            "listen": "test",
                            "script": {
                                "type": "text/javascript",
                                "exec": [
                                    "pm.test('Status code is 200 OK', function () {",
                                    "    pm.response.to.have.status(200);",
                                    "});",
                                    "",
                                    "pm.test('Returns list of brands with product count', function () {",
                                    "    const jsonData = pm.response.json();",
                                    "    pm.expect(jsonData.success).to.be.true;",
                                    "    pm.expect(jsonData.data).to.be.an('array');",
                                    "    pm.expect(jsonData.data.length).to.be.at.least(8);",
                                    "    pm.expect(jsonData.data[0]).to.have.property('name');",
                                    "});"
                                ]
                            }
                        }
                    ],
                    "request": {
                        "method": "GET",
                        "header": [],
                        "url": {
                            "raw": "{{base_url}}/brands",
                            "host": ["{{base_url}}"],
                            "path": ["brands"]
                        }
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
