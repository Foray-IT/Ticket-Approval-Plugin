<?php
include ("../../../inc/includes.php");
Session::checkRight("config", UPDATE);
Html::header('Automatic Ticket Validation', $_SERVER['PHP_SELF'], 'config', 'plugins');
Html::header('Aprovação Automatica por email', $_SERVER['PHP_SELF'], 'config', 'plugins');

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
echo "<tr><th colspan='4'>" . __('Automatic Ticket Validation Settings') . "</th></tr>";
echo "<tr><th colspan='4'>" . __('Configuração de Aprovação Automatica por email') . "</th></tr>";

echo "<tr>";
echo "<th>" . __('Keyword') . "</th>";
echo "<th>" . __('Action') . "</th>";
echo "<th>" . __('Palavra-chave') . "</th>";
echo "<th>" . __('Tipo') . "</th>";
echo "<th>" . __('Status') . "</th>";
echo "<th>" . __('Occurences') . "</th>";
echo "<th>" . __('Ações') . "</th>";
echo "</tr>";

global $DB;
$result = $DB->request([
    'FROM' => 'glpi_plugin_validationauto_keywords',
    'ORDER' => ['type', 'keyword']
]);

if (count($result) > 0) {
    foreach ($result as $data) {
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $data['keyword'] . "</td>";
        echo "<td>" . ($data['type'] == 'approve' ? __('Approve') : __('Reject')) . "</td>";
        echo "<td>" . ($data['is_active'] ? __('Active') : __('Inactive')) . "</td>";
        echo "<td>" . ($data['type'] == 'approval' ? __('Aprovação') : __('Negação')) . "</td>";
        echo "<td>" . ($data['is_active'] ? __('Ativo') : __('Inativo')) . "</td>";
        echo "<td class='center'>";
        
        echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "' style='display:inline;'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        
        echo "<button type='submit' name='toggle_status' value='" . $data['id'] . "' class='submit'>" .
             ($data['is_active'] ? __('Disable') : __('Enable')) . "</button> ";
             ($data['is_active'] ? __('Desativar') : __('Ativar')) . "</button> ";
        
        echo "<button type='submit' name='delete' value='" . $data['id'] . "' class='submit' 
              onclick='return confirm(\"" . __('Are you sure you want to delete this keyword?') . "\")'>" .
             __('Delete') . "</button>";
              onclick='return confirm(\"" . __('Tem certeza que deseja excluir esta palavra-chave?') . "\")'>" .
             __('Excluir') . "</button>";
        
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
}

echo "<tr class='tab_bg_2'>";
echo "<td colspan='4' class='center'>";
echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<input type='text' name='new_keyword' placeholder='" . __('New Keyword') . "'>";
echo "<input type='text' name='new_keyword' placeholder='" . __('Nova palavra-chave') . "'>";
echo "&nbsp;";
echo "<select name='keyword_type'>";
echo "<option value='approval'>" . __('Approve') . "</option>";
echo "<option value='denial'>" . __('Reject') . "</option>";
echo "<option value='approval'>" . __('Aprovação') . "</option>";
echo "<option value='denial'>" . __('Negação') . "</option>";
echo "</select>";
echo "&nbsp;";
echo "<input type='submit' name='add_keyword' value='" . __('Add') . "' class='submit'>";
echo "<input type='submit' name='add_keyword' value='" . __('Adicionar') . "' class='submit'>";
echo "</form>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</div>";

Html::footer();