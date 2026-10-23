<?php
/**
 * TechStore - Input Validation Helper
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T12: Validator, ErrorHandler chuẩn JSON, CORS config
 */

namespace Api\Helpers;

class Validator {
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self {
        $validator = new self($data, $rules);
        $validator->validate();
        return $validator;
    }

    public function validate(): bool {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $ruleList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $val = $this->data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                $ruleName = trim($ruleName);

                if ($ruleName === 'required') {
                    if ($val === null || (is_string($val) && trim($val) === '') || (is_array($val) && empty($val))) {
                        $this->errors[$field][] = "Trường '{$field}' là bắt buộc không được để trống.";
                        break; // Nếu thiếu trường bắt buộc thì dừng kiểm tra các rule tiếp theo của field đó
                    }
                }

                // Nếu trường không required và giá trị null hoặc rỗng thì bỏ qua các rule sau
                if ($val === null || (is_string($val) && trim($val) === '')) {
                    continue;
                }

                if ($ruleName === 'email') {
                    if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$field][] = "Trường '{$field}' phải là địa chỉ email hợp lệ.";
                    }
                }

                if ($ruleName === 'numeric') {
                    if (!is_numeric($val)) {
                        $this->errors[$field][] = "Trường '{$field}' phải là số hợp lệ.";
                    }
                }

                if ($ruleName === 'min' && isset($params[0])) {
                    $min = (float)$params[0];
                    if (in_array('numeric', $ruleList, true) && is_numeric($val)) {
                        if ((float)$val < $min) {
                            $this->errors[$field][] = "Trường '{$field}' phải có giá trị tối thiểu là {$min}.";
                        }
                    } else {
                        if (mb_strlen((string)$val) < $min) {
                            $this->errors[$field][] = "Trường '{$field}' phải có độ dài tối thiểu {$min} ký tự.";
                        }
                    }
                }

                if ($ruleName === 'max' && isset($params[0])) {
                    $max = (float)$params[0];
                    if (in_array('numeric', $ruleList, true) && is_numeric($val)) {
                        if ((float)$val > $max) {
                            $this->errors[$field][] = "Trường '{$field}' không được vượt quá {$max}.";
                        }
                    } else {
                        if (mb_strlen((string)$val) > $max) {
                            $this->errors[$field][] = "Trường '{$field}' không được vượt quá {$max} ký tự.";
                        }
                    }
                }

                if ($ruleName === 'in') {
                    if (!in_array($val, $params, true)) {
                        $allowed = implode(', ', $params);
                        $this->errors[$field][] = "Trường '{$field}' phải thuộc một trong các giá trị: {$allowed}.";
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getFirstError(): string {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return 'Dữ liệu không hợp lệ';
    }

    public function getFlattenedErrors(): array {
        $flat = [];
        foreach ($this->errors as $field => $messages) {
            foreach ($messages as $msg) {
                $flat[] = $msg;
            }
        }
        return $flat;
    }
}
