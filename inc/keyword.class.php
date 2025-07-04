<?php

class PluginValidationautoKeyword extends CommonDBTM {
    
    static $rightname = 'config';
    
    public static function canCreate() {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canView() {
        return Session::haveRight(self::$rightname, READ);
    }

    public static function getTypeName($nb = 0) {
        return _n('Approval Keyword', 'Approval Keywords', $nb);
    }

    function showForm($ID, array $options = []) {
        global $DB;

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Keyword') . "</td>";
        echo "<td>";
        echo Html::input('keyword', ['value' => $this->fields['keyword']]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Active') . "</td>";
        echo "<td>";
        Dropdown::showYesNo('is_active', $this->fields['is_active']);
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == __CLASS__) {
            $item->showForm($item->getID());
        }
        return true;
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() == __CLASS__) {
            return __('Keyword');
        }
        return '';
    }
}