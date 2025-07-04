<?php
include ("../../../inc/includes.php");
Session::checkRight("config", UPDATE);
Html::header('Automatic Ticket Validation', $_SERVER['PHP_SELF'], 'config', 'plugins');

// Process form actions
if (isset($_POST['add_keyword'])) {
    global $DB;
    
    if (!empty($_POST['new_keyword'])) {
        $DB->insert(
            'glpi_plugin_validationauto_keywords',
            [
                'keyword' => $_POST['new_keyword'],
                'type' => $_POST['keyword_type'],
                'is_active' => 1,
                'date_creation' => date('Y-m-d H:i:s')
            ]
        );
    }
    Html::back();
}

// Process status toggles
if (isset($_POST['toggle_status'])) {
    global $DB;
    $id = $_POST['toggle_status'];
    
    $current = $DB->request([
        'FROM' => 'glpi_plugin_validationauto_keywords',
        'WHERE' => ['id' => $id]
    ])->current();
    
    if ($current) {
        $DB->update(
            'glpi_plugin_validationauto_keywords',
            [
                'is_active' => $current['is_active'] ? 0 : 1,
                'date_mod' => date('Y-m-d H:i:s')
            ],
            ['id' => $id]
        );
    }
    Html::back();
}

// Process deletions
if (isset($_POST['delete'])) {
    global $DB;
    $DB->delete(
        'glpi_plugin_validationauto_keywords',
        ['id' => $_POST['delete']]
    );
    Html::back();
}

// Interface
echo "<div class='center'>";
echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='3'>" . __('Automatic Ticket Validation Settings') . "</th></tr>";

// Table Header
echo "<tr>";
echo "<th>" . __('Keyword/Phrase') . "</th>";
echo "<th>" . __('Action') . "</th>";
echo "<th>" . __('Status') . "</th>";
echo "</tr>";

// Lists existing keywords
global $DB;
$result = $DB->request([
    'FROM' => 'glpi_plugin_validationauto_keywords',
    'ORDER' => ['type', 'keyword']
]);

if (count($result) > 0) {
    foreach ($result as $data) {
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $data['keyword'] . "</td>";
        echo "<td>" . ($data['type'] == 'approval' ? __('Approve') : __('Reject')) . "</td>";
        echo "<td>" . ($data['is_active'] ? __('Active') : __('Inactive')) . "</td>";
        echo "<td class='center'>";
        
        // Form for actions
        echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "' style='display:inline;'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        
        // Button for toggling status
        echo "<button type='submit' name='toggle_status' value='" . $data['id'] . "' class='submit'>" .
             ($data['is_active'] ? __('Disable') : __('Enable')) . "</button> ";
        
        // Button for deletion
        echo "<button type='submit' name='delete' value='" . $data['id'] . "' class='submit' 
              onclick='return confirm(\"" . __('Are you sure you want to delete this keyword/phrase?') . "\")'>" .
             __('Delete') . "</button>";
        
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
}

// Form to add new keyword
echo "<tr class='tab_bg_2'>";
echo "<td colspan='3' class='center'>";
echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<input type='text' name='new_keyword' placeholder='" . __('New Keyword/Phrase') . "'>";
echo "&nbsp;";
echo "<select name='keyword_type'>";
echo "<option value='approval'>" . __('Approve') . "</option>";
echo "<option value='denial'>" . __('Reject') . "</option>";
echo "</select>";
echo "&nbsp;";
echo "<input type='submit' name='add_keyword' value='" . __('Add') . "' class='submit'>";
echo "</form>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</div>";

Html::footer();