<?php

namespace App\Enums;

use BenSampo\Enum\Enum as BaseEnum;
use BenSampo\Enum\Contracts\LocalizedEnum;

/**
 * 枚举值类型
 * 1. php artisan make:enum Mdm/UserType
 * 2. 更改生成文件，改为继承该类，定义枚举常量，参考：Mdm\UserType
 * 3. 获取枚举值，和常量方式一致：UserType::USER_TYPE_VISITOR
 * 4. 获取枚举值描述，见 getDesc()
 * 5. 获取枚举值键值对，见 getPair()
 * 6. 获取枚举值组，见 getGroup()
 * 6. 获取枚举值列表，见 getList()
 * 7. 其他参考：https://github.com/BenSampo/laravel-enum
 */
class Enum extends BaseEnum implements LocalizedEnum
{
    /**
     * 获取枚举值描述，在 resources/lang/zh_CN/enums.php 定义国际化描述
     * @param  string     $value 枚举值
     * @return string
     */
    public static function getDesc($value, array $params = []): string
    {
        return __(parent::getDescription($value), $params);
    }

    /**
     * 获取枚举值键值对，如：[1 => 'name']
     * @param  mixed     $value 枚举值
     * @return array
     */
    public static function getPair($value): array
    {
        return [
            $value => parent::getDescription($value),
        ];
    }

    /**
     * 获取一组枚举值，如：['id' => 1, 'name' => '']
     * @param  mixed     $value 枚举值
     * @param  string     $id    值的名称，默认 'id'
     * @param  string     $name  描述的名称，默认 'name'
     * @return array
     */
    public static function getGroup($value, $keyAsId = false, $id = 'id', $name = 'name'): array
    {
        return [
            $id     => $keyAsId ? strtolower(self::getKey($value)) : $value,
            $name   => parent::getDescription($value),
            'key'   => strtolower(parent::getKey($value))
        ];
    }

    /**
     * 获取枚举值列表，如：[1 => 'name1', 2 => 'name2']
     * @return array
     */
    public static function getList(): array
    {
        return parent::asSelectArray();
    }

    /**
     * 获取枚举值列表，格式：['id => 'ID', 'name' => '界面展示名称’, 'key' => '常量名称']
     *
     * @return array
     */
    public static function getGroupList(): array
    {
        $list = [];

        foreach (parent::asSelectArray() as $key => $val) {
            $list[] = [
                'id'   => $key,
                'name' => $val,
                'key'  => strtolower(parent::getKey($key)),
            ];
        }

        return $list;
    }

    /**
     * 根据名称获取枚举值
     * @param  string     $key
     * @return string
     */
    public static function getValue($key):string
    {
        return static::getConstants()[strtoupper($key)] ?? '';
    }

    /**
     * 根据名称数组获取枚举值
     * @param  string     $key
     * @return mixed
     */
    public static function getKeyValues($keys)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[] = parent::getValue(strtoupper($key));
        }
        return $values;
    }

    /**
     * 获取大小写形式的枚举值名称
     * @param  string     $key
     * @return mixed
     */
    public static function getKeysByCase($case = null)
    {
        $keys = parent::getKeys();
        switch ($case) {
            case 'upper':
                array_walk($keys, function (&$key, $idx) {
                    $key = strtoupper($key);
                });
                break;
            case 'lower':
                array_walk($keys, function (&$key, $idx) {
                    $key = strtolower($key);
                });
                break;
        }
        return $keys;
    }

    /**
     * 枚举名称是否存在/有效
     *
     * @param string $key
     * @param string $case
     * @return boolean
     */
    public static function isKeyValid($key, $case = 'lower')
    {
        $keys = self::getKeysByCase($case);
        return in_array($key, $keys);
    }

    /**
     * 枚举值是否存在/有效
     *
     * @param mixed $val
     * @return boolean
     */
    public static function isValueValid($val)
    {
        $vals = self::getValues();
        return in_array($val, $vals);
    }
}
