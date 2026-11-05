import urllib.request
import json
import sys, io

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

def test_rest():
    print("=== TESTING REST LEVEL 3 & 6 CONSTRAINTS ===")

    # 1. Test GET /products with Cache-Control, ETag, HATEOAS
    url = "http://127.0.0.1:8000/api/v1/products"
    req = urllib.request.Request(url)
    with urllib.request.urlopen(req) as resp:
        code = resp.getcode()
        etag = resp.headers.get('ETag')
        cache_control = resp.headers.get('Cache-Control')
        api_version = resp.headers.get('X-API-Version')
        body = json.loads(resp.read().decode('utf-8'))
        
        print(f"\n[Test 1] GET /products")
        print(f"  - HTTP Status: {code} (Expect 200)")
        print(f"  - ETag Header: {etag}")
        print(f"  - Cache-Control: {cache_control}")
        print(f"  - X-API-Version: {api_version}")
        print(f"  - HATEOAS _links present: {'_links' in body}")
        if '_links' in body:
            print(f"    * _links keys: {list(body['_links'].keys())}")
        assert code == 200
        assert etag is not None
        assert "max-age=300" in cache_control
        assert api_version == "1.0"
        assert '_links' in body

    # 2. Test 304 Not Modified with If-None-Match
    req2 = urllib.request.Request(url, headers={'If-None-Match': etag})
    try:
        urllib.request.urlopen(req2)
        print("  - [FAIL] Expected 304 but got 200")
        sys.exit(1)
    except urllib.error.HTTPError as e:
        print(f"\n[Test 2] GET /products with If-None-Match: {etag}")
        print(f"  - HTTP Status: {e.code} (Expect 304 Not Modified)")
        print(f"  - ETag in 304: {e.headers.get('ETag')}")
        print(f"  - Cache-Control in 304: {e.headers.get('Cache-Control')}")
        assert e.code == 304

    # 3. Test GET /products/1 with HATEOAS
    url_p1 = "http://127.0.0.1:8000/api/v1/products/1"
    req3 = urllib.request.Request(url_p1)
    with urllib.request.urlopen(req3) as resp:
        code = resp.getcode()
        body = json.loads(resp.read().decode('utf-8'))
        print(f"\n[Test 3] GET /products/1 (Detail HATEOAS)")
        print(f"  - HTTP Status: {code}")
        print(f"  - _links: {json.dumps(body.get('_links', {}), ensure_ascii=False, indent=4)}")
        assert '_links' in body
        assert 'self' in body['_links']
        assert 'reviews' in body['_links']
        assert 'add_to_cart' in body['_links']

    # 4. Test Error Response RFC 7807
    url_err = "http://127.0.0.1:8000/api/v1/products/99999"
    try:
        urllib.request.urlopen(url_err)
    except urllib.error.HTTPError as e:
        err_body = json.loads(e.read().decode('utf-8'))
        print(f"\n[Test 4] RFC 7807 Error Response on 404")
        print(f"  - HTTP Status: {e.code}")
        print(f"  - Content-Type: {e.headers.get('Content-Type')}")
        print(f"  - RFC 7807 Fields:")
        print(f"    * type: {err_body.get('type')}")
        print(f"    * title: {err_body.get('title')}")
        print(f"    * status: {err_body.get('status')}")
        print(f"    * detail: {err_body.get('detail')}")
        print(f"    * instance: {err_body.get('instance')}")
        print(f"    * legacy error.code: {err_body.get('error', {}).get('code')}")
        assert err_body.get('status') == 404
        assert err_body.get('title') == 'Resource Not Found'
        assert err_body.get('type') is not None
        assert err_body.get('error', {}).get('code') == 'PRODUCT_NOT_FOUND'

    # 5. Test GET /categories ETag and 304
    url_cat = "http://127.0.0.1:8000/api/v1/categories"
    with urllib.request.urlopen(url_cat) as resp:
        etag_cat = resp.headers.get('ETag')
        cc_cat = resp.headers.get('Cache-Control')
        print(f"\n[Test 5] GET /categories (Cache 600s)")
        print(f"  - ETag: {etag_cat}")
        print(f"  - Cache-Control: {cc_cat}")
        assert "max-age=600" in cc_cat
        assert etag_cat is not None

    req_cat_304 = urllib.request.Request(url_cat, headers={'If-None-Match': etag_cat})
    try:
        urllib.request.urlopen(req_cat_304)
        sys.exit(1)
    except urllib.error.HTTPError as e:
        print(f"\n[Test 6] GET /categories with If-None-Match")
        print(f"  - HTTP Status: {e.code} (Expect 304 Not Modified)")
        assert e.code == 304

    print("\nALL 6 REST VERIFICATION TESTS PASSED SUCCESSFULLY!")

if __name__ == '__main__':
    test_rest()
