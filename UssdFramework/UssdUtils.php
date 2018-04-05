<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 *
 * @author Aaron Baffour-Awuah
 */
class UssdUtils {
    
    /**
     * @param UssdForm $form
     * @return string
     */
    static function marshallUssdForm($form) {
        $repr = UssdForm::serialize($form);
        return $repr;
    }
    
    /**
     * @param string $repr
     * @return UssdForm
     */
    static function unmarshallUssdForm($repr) {
        $form = UssdForm::deserialize($repr);
        return $form;
    }
    
    /**
     * @param UssdMenu $menu
     * @return string
     */
    static function marshallUssdMenu($menu) {
        $repr = UssdMenu::serialize($menu);
        return $repr;
    }
    
    /**
     * @param string $repr
     * @return UssdMenu
     */
    static function unmarshallUssdMenu($repr) {
        $menu = UssdMenu::deSerialize($repr);
        return $menu;
    }
    
    static function marshallIndexedArray($arr) {
        $serialized = '';
        foreach ($arr as $item) {
            if ($item === null) {
                $serialized .= '-1|';
            }
            else {
                $serialized .= strlen($item) . '|';
                $serialized .= $item;
            }
        }
        return $serialized;
    }
    
    static function unmarshallIndexedArray($serialized) {
        $arr = array();
        $offset = 0;
        $sepIndex = strpos($serialized, '|', $offset);
        while ($sepIndex !== false) {
            $itemLength = intval(substr($serialized, $offset, 
                    $sepIndex - $offset));
            if ($itemLength < 0) {
                array_push($arr, null);
                $itemLength = 0;
            }
            else {
                $item = substr($serialized, $sepIndex+1, $itemLength);
                array_push($arr, $item);
            }
            
            $offset = $sepIndex + $itemLength + 1;
            $sepIndex = strpos($serialized, '|', $offset);
        }
        if ($offset !== strlen($serialized)) {
            throw new FrameworkException('unmarshallIndexedArray() algorithm '
                    . 'is wrong. Expected final offset to be '
                    . strlen($serialized)
                    . ' but got ' . $offset . '. Argument was: ' . $serialized);
        }
        return $arr;
    }
}
