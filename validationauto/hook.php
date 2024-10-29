<?php
function plugin_validationauto_process_followup(ITILFollowup $followup) {
    global $DB;
    
    if ($followup->getField('itemtype') !== 'Ticket') {
        return;
    }
    
    $ticket_id = $followup->getField('items_id');
    $content = strtolower($followup->getField('content'));
    
    // Buscar palavras-chave ativas (tanto de aprovação quanto negação)
    $keywords = [];
    $denial_keywords = [];
    
    // Buscar palavras de aprovação
    $query = "SELECT keyword FROM glpi_plugin_validationauto_keywords WHERE is_active = 1 AND type = 'approval'";
    $result = $DB->query($query);
    while ($row = $DB->fetchAssoc($result)) {
        $keywords[] = strtolower($row['keyword']);
    }
    
    // Buscar palavras de negação
    $query = "SELECT keyword FROM glpi_plugin_validationauto_keywords WHERE is_active = 1 AND type = 'denial'";
    $result = $DB->query($query);
    while ($row = $DB->fetchAssoc($result)) {
        $denial_keywords[] = strtolower($row['keyword']);
    }
    
    // Verificar palavras-chave no conteúdo
    $found_approval = false;
    $found_denial = false;
    
    foreach ($keywords as $keyword) {
        if (strpos($content, $keyword) !== false) {
            $found_approval = true;
            break;
        }
    }
    
    foreach ($denial_keywords as $keyword) {
        if (strpos($content, $keyword) !== false) {
            $found_denial = true;
            break;
        }
    }
    
    if (!$found_approval && !$found_denial) {
        return;
    }
    
    // Buscar validações pendentes do ticket
    $ticket_validation = new TicketValidation();
    $pending_validations = $ticket_validation->find([
        'tickets_id' => $ticket_id,
        'status' => CommonITILValidation::WAITING
    ]);
    
    // Atualizar status das validações pendentes
    foreach ($pending_validations as $validation) {
        $input = [
            'id' => $validation['id'],
            'status' => $found_approval ? CommonITILValidation::ACCEPTED : CommonITILValidation::REFUSED,
            'validation_date' => $_SESSION["glpi_currenttime"]
        ];
        $ticket_validation->update($input);
    }
}

function plugin_validationauto_install() {
    global $DB;
    
    // Criar tabela para armazenar palavras-chave
    $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_validationauto_keywords` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `keyword` varchar(255) NOT NULL,
        `type` enum('approval','denial') NOT NULL DEFAULT 'approval',
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `date_creation` datetime DEFAULT NULL,
        `date_mod` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `keyword_type` (`keyword`, `type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $DB->query($query);
    
    // Inserir palavras-chave padrão
    $queries = [
        "INSERT INTO `glpi_plugin_validationauto_keywords` 
         (keyword, type, is_active, date_creation) 
         VALUES ('aprovado', 'approval', 1, NOW())
         ON DUPLICATE KEY UPDATE is_active = 1",
        
        "INSERT INTO `glpi_plugin_validationauto_keywords` 
         (keyword, type, is_active, date_creation) 
         VALUES ('negado', 'denial', 1, NOW())
         ON DUPLICATE KEY UPDATE is_active = 1"
    ];
    
    foreach ($queries as $query) {
        $DB->query($query);
    }
    
    return true;
}

function plugin_validationauto_uninstall() {
    global $DB;
    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_validationauto_keywords`");
    return true;
}