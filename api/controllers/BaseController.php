<?php
/**
 * TechStore - Base Controller
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;
use Api\Models\BaseModel;

abstract class BaseController {
    protected BaseModel $model;

    public function __construct() {
        $this->model = new BaseModel();
    }

    protected function getAuthUser(Request $request): ?array {
        return $request->getUser();
    }

    protected function requireAuthUser(Request $request): array {
        $user = $request->getUser();
        if (!$user) {
            Response::error('Yêu cầu đăng nhập để thực hiện thao tác này', 'UNAUTHORIZED', 401);
        }
        return $user;
    }
}
