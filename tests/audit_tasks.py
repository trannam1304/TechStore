import os
import re

print("=== TECHSTORE TASK AUDIT (T01 - T17) ===")

checks = {
    "T01": {
        "desc": "Repo + branch rules + board",
        "files": ["README.md", "docs/GIT_WORKFLOW.md"]
    },
    "T02": {
        "desc": "Meeting notes + Thong tin bia",
        "files": ["docs/meeting-notes.md"]
    },
    "T03": {
        "desc": "Dung khung du an (Router, Response, config, /api/v1/ping)",
        "files": ["public/index.php", "api/Router.php", "api/helpers/Response.php", "config/config.php", "api/routes.php"]
    },
    "T04": {
        "desc": "ERD + schema.sql (>=15 bang)",
        "files": ["database/schema.sql", "docs/ERD.md"]
    },
    "T05": {
        "desc": "seed.sql (>=40 SP, 8 hang, 5 danh muc, ~30 don)",
        "files": ["database/seed.sql", "database/data.json"]
    },
    "T06": {
        "desc": "openapi.yaml v0.1 (Auth, Product, Cart, Order)",
        "files": ["docs/openapi.yaml"]
    },
    "T07": {
        "desc": "Wireframe + UI kit (tokens.css)",
        "files": ["public/assets/css/tokens.css", "docs/wireframes"]
    },
    "T08": {
        "desc": "Moi truong chay duoc local",
        "files": ["start.bat", ".env.example", ".env"]
    },
    "T09": {
        "desc": "Khung bao cao dung template",
        "files": ["docs/report"]
    },
    "T10": {
        "desc": "JwtService + AuthMiddleware + RoleMiddleware",
        "files": ["api/services/JwtService.php", "api/middleware/AuthMiddleware.php", "api/middleware/RoleMiddleware.php"]
    },
    "T11": {
        "desc": "Endpoint register / login / refresh / logout / me",
        "files": ["api/controllers/AuthController.php"]
    },
    "T12": {
        "desc": "Validator, ErrorHandler chuan JSON, CORS config",
        "files": ["api/helpers/Validator.php", "api/helpers/ErrorHandler.php", "api/middleware/CorsMiddleware.php"]
    },
    "T13": {
        "desc": "GET /products loc, sap xep, tim kiem, phan trang",
        "files": ["api/controllers/ProductController.php"]
    },
    "T14": {
        "desc": "GET /products/{id} + categories + brands",
        "files": ["api/controllers/ProductController.php"]
    },
    "T15": {
        "desc": "Postman collection + environment + script test tu dong",
        "files": ["tests/postman_collection.json", "tests/postman_environment.json"]
    },
    "T16": {
        "desc": "Layout chung, CSS base, api.js",
        "files": ["public/assets/js/api.js", "public/assets/css/base.css", "public/index.html"]
    },
    "T17": {
        "desc": "Trang dang nhap / dang ky",
        "files": ["public/login.html", "public/register.html"]
    }
}

for task_id, task in checks.items():
    print(f"\n[{task_id}] {task['desc']}")
    for f in task["files"]:
        exists = os.path.exists(f)
        is_dir = os.path.isdir(f)
        status = "OK" if exists else "NOT FOUND"
        print(f"  - {f} [{status}]")

# Audit schema.sql table count
if os.path.exists("database/schema.sql"):
    with open("database/schema.sql", "r", encoding="utf-8") as f:
        schema_content = f.read()
    tables = re.findall(r"CREATE TABLE(?:\s+IF NOT EXISTS)?\s+`?([a-zA-Z0-9_]+)`?", schema_content, re.IGNORECASE)
    print(f"\n[Audit T04 Details] Tables in schema.sql: {len(tables)} tables")
    for t in tables:
        print(f"    * {t}")

# Audit seed.sql counts
if os.path.exists("database/seed.sql"):
    with open("database/seed.sql", "r", encoding="utf-8") as f:
        seed_content = f.read()
    products_count = len(re.findall(r"INSERT INTO\s+`?products`?", seed_content, re.IGNORECASE))
    orders_count = len(re.findall(r"INSERT INTO\s+`?orders`?", seed_content, re.IGNORECASE))
    print(f"\n[Audit T05 Details] Seed inserts:")
    print(f"    * Products inserts found: {products_count}")
    print(f"    * Orders inserts found: {orders_count}")

