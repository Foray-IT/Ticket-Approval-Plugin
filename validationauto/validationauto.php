<?php
class PluginValidationauto extends Plugin {
    public function getTypeName($nb = 0) {
        return 'Aprovação Automatica por email';
    }
    
    public static function canCreate() {
        return Session::haveRight('config', UPDATE);
    }
    
    public static function canView() {
        return Session::haveRight('config', READ);
    }
    
    public static function getMenuName() {
        return __('Aprovação Automatica por email');
    }
    
    private function showConfigForm() {
        global $DB;
        
        echo "<form name='form' method='post' action=''>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        
        // Tabela de palavras-chave de aprovação
        echo "<div class='center' id='tabsbody'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='4'>" . __('Palavras-chave de Aprovação') . "</th></tr>";
        echo "<tr>
                <th>" . __('Palavra-chave') . "</th>
                <th>" . __('Tipo') . "</th>
                <th>" . __('Status') . "</th>
                <th>" . __('Ações') . "</th>
              </tr>";
        
        // Listar palavras-chave existentes
        $result = $DB->request([
            'FROM' => 'glpi_plugin_validationauto_keywords',
            'ORDER' => ['type', 'keyword']
        ]);
        
        foreach ($result as $data) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . $data['keyword'] . "</td>";
            echo "<td>" . ($data['type'] == 'approval' ? __('Aprovação') : __('Negação')) . "</td>";
            echo "<td>" . ($data['is_active'] ? __('Ativo') : __('Inativo')) . "</td>";
            echo "<td class='center'>";
            
            // Botão para ativar/desativar
            echo "<button type='submit' name='toggle_status' value='" . $data['id'] . "' class='submit'>" .
                 ($data['is_active'] ? __('Desativar') : __('Ativar')) . "</button> ";
            
            // Botão para excluir
            echo "<button type='submit' name='delete' value='" . $data['id'] . "' class='submit' 
                  onclick='return confirm(\"" . __('Tem certeza que deseja excluir esta palavra-chave?') . "\")'>" .
                 __('Excluir') . "</button>";
            
            echo "</td>";
            echo "</tr>";
        }
        
        // Campos para adicionar nova palavra-chave
        echo "<tr class='tab_bg_2'>";
        echo "<td><input type='text' name='new_keyword' placeholder='" . __('Nova palavra-chave') . "'></td>";
        echo "<td><select name='keyword_type'>
                <option value='approval'>" . __('Aprovação') . "</option>
                <option value='denial'>" . __('Negação') . "</option>
              </select></td>";
        echo "<td colspan='2'><input type='submit' name='add' value='" . __('Adicionar') . "' class='submit'></td>";
        echo "</tr>";
        
        echo "</table>";
        echo "</div>";
        Html::closeForm();
    }
    
    static function processConfigForm() {
        global $DB;
        
        if (isset($_POST['add']) && !empty($_POST['new_keyword'])) {
            $keyword = trim($_POST['new_keyword']);
            $type = $_POST['keyword_type'];
            
            $DB->insert(
                'glpi_plugin_validationauto_keywords',
                [
                    'keyword' => $keyword,
                    'type' => $type,
                    'is_active' => 1,
                    'date_creation' => date('Y-m-d H:i:s')
                ]
            );
        }
        
        if (isset($_POST['toggle_status'])) {
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
        }
        
        if (isset($_POST['delete'])) {
            $DB->delete(
                'glpi_plugin_validationauto_keywords',
                ['id' => $_POST['delete']]
            );
        }
    }
    
    public function rawSearchOptions() {
        $tab = [];
        
        $tab[] = [
            'id' => 'common',
            'name' => self::getTypeName(2)
        ];
        
        $tab[] = [
            'id' => '1',
            'table' => 'glpi_plugin_validationauto_keywords',
            'field' => 'keyword',
            'name' => __('Palavra-chave'),
            'datatype' => 'text'
        ];
        
        $tab[] = [
            'id' => '2',
            'table' => 'glpi_plugin_validationauto_keywords',
            'field' => 'type',
            'name' => __('Tipo'),
            'datatype' => 'specific'
        ];
        
        $tab[] = [
            'id' => '3',
            'table' => 'glpi_plugin_validationauto_keywords',
            'field' => 'is_active',
            'name' => __('Ativo'),
            'datatype' => 'bool'
        ];
        
        return $tab;
    }
}