<?php

class PluginValidationautoConfig extends CommonDBTM {
    
    static protected $notable = true;
    
    static function getTypeName($nb = 0) {
        return __('Automatic Ticket Validation', 'validationauto');
    }
    
    static function canCreate() {
        return Session::haveRight('config', UPDATE);
    }

    static function canView() {
        return Session::haveRight('config', READ);
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        return self::getTypeName();
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $DB;
        
        if ($item->getType() == __CLASS__) {
            self::showConfigForm();
        }
        return true;
    }

    static function showConfigForm() {
        global $DB;

        echo "<form method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2'>" . __('Automatic Approval Settings') . "</th></tr>";

        // List existing keywords
        $result = $DB->request([
            'FROM' => 'glpi_plugin_validationauto_keywords',
            'ORDER' => 'keyword'
        ]);

        foreach ($result as $data) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . $data['keyword'] . "</td>";
            echo "<td>" . ($data['is_active'] ? __('Yes') : __('No')) . "</td>";
            echo "</tr>";
        }

        echo "<tr class='tab_bg_2'>";
        echo "<td colspan='2' class='center'>";
        echo "<input type='text' name='new_keyword' placeholder='" . __('New Keyword') . "'>";
        echo "&nbsp;";
        echo "<input type='submit' name='add' value='" . __('Add') . "' class='submit'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();
    }
}