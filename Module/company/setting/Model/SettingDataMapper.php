<?php

/*
 * 
 */

namespace Company\Setting\Model;

use Company\Cache\CacheDriver;

class SettingDataMapper extends \Company\SQL\Mapper {

    protected static $settingData = [];

    public function tableAlias() {
        return 'data';
    }

    public function tableName() {
        return 'setting_data';
    }

    function getPkField() {
        return 'fieldID';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('data.fieldID');
    }

    /**
     * 
     * @param type $key
     * @param type $default
     */
    function getSetting($siteID = "master", $key = null, $default = null) {

        $cache = CacheDriver::getInstance(CacheDriver::PRIVATE_MEMORY_CACHE);
        $cacheKey = "setting/$siteID";

        $settingData = $cache->get($cacheKey);
        if (!$settingData) {
            $settingData = [];
            $settings = FieldMapper::makeInstance()
                ->setLoadFieldData($siteID)
                ->lockInShareMode()
                ->getEntities()
                ->toArray();

            foreach ($settings as $setting) {
                if ($setting->fieldDatas)
                    $settingData[$setting->id] = $setting->fieldDatas[0]->value;
            }

            $cache->set($cacheKey, $settingData, 60);
        }

        if ($key === null) {
            return json_encode($settingData);
        }

        if (isset($settingData[$key])) {
            return $settingData[$key];
        }

        return $default;
    }

    function clearCache() {
        static::$settingData = [];
        return $this;
    }

    /**
     * @param type $siteID 
     * @param type $arr mảng dạng key-value
     * Them vao cuoi mang
     * Update field da ton tai
     */
    function setSetting($siteID, $arr) {
        //update
        $this->startTrans();
        foreach ($arr as $key => $val) {
            //execute sql
            $this->makeInstance()
                    ->filterFieldID($key)
                    ->filterSiteFK($siteID)
                    ->replace([
                        'fieldID' => $key,
                        'value' => $val,
                        'siteFK' => $siteID
                    ]);
        }
        $this->completeTransOrFail();
    }

    function filterFieldID($fieldID) {
        if ($fieldID) {
            $this->where('data.fieldID = ?', __FUNCTION__)
                    ->setParamWhere($fieldID, __FUNCTION__);
        }
        return $this;
    }

    function filterSiteFK($siteFK) {
        $this->where('data.siteFK = ?', __FUNCTION__)
                ->setParamWhere($siteFK, __FUNCTION__);
        return $this;
    }

}
