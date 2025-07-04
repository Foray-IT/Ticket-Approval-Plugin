<?php
function plugin_validationauto_process_followup(ITILFollowup $followup) {
    global $DB;
    
    if ($followup->getField('itemtype') !== 'Ticket') {
        return;
    }
    
    $ticket_id = $followup->getField('items_id');
    $content = strtolower($followup->getField('content'));
    
    $keywords = [];
    $denial_keywords = [];
    
    $query = "SELECT keyword FROM glpi_plugin_validationauto_keywords WHERE is_active = 1 AND type = 'approval'";
    $result = $DB->query($query);
    while ($row = $DB->fetchAssoc($result)) {
        $keywords[] = strtolower($row['keyword']);
    }
    
    $query = "SELECT keyword FROM glpi_plugin_validationauto_keywords WHERE is_active = 1 AND type = 'denial'";
    $result = $DB->query($query);
    while ($row = $DB->fetchAssoc($result)) {
        $denial_keywords[] = strtolower($row['keyword']);
    }
    
    $first_approval_pos = PHP_INT_MAX;
    $first_denial_pos = PHP_INT_MAX;
    $found_approval = false;
    $found_denial = false;
    
    foreach ($keywords as $keyword) {
        $pos = strpos($content, $keyword);
        if ($pos !== false && $pos < $first_approval_pos) {
            $first_approval_pos = $pos;
            $found_approval = true;
        }
    }
    
    foreach ($denial_keywords as $keyword) {
        $pos = strpos($content, $keyword);
        if ($pos !== false && $pos < $first_denial_pos) {
            $first_denial_pos = $pos;
            $found_denial = true;
        }
    }
    
    if (!$found_approval && !$found_denial) {
        return;
    }
    
    $is_approval = false;
    if ($found_approval && $found_denial) {
        $is_approval = ($first_approval_pos < $first_denial_pos);
    } else {
        $is_approval = $found_approval;
    }
    
    $ticket_validation = new TicketValidation();
    $pending_validations = $ticket_validation->find([
        'tickets_id' => $ticket_id,
        'status' => CommonITILValidation::WAITING
    ]);
    
    foreach ($pending_validations as $validation) {
        $input = [
            'id' => $validation['id'],
            'status' => $is_approval ? CommonITILValidation::ACCEPTED : CommonITILValidation::REFUSED,
            'validation_date' => $_SESSION["glpi_currenttime"],
            'comment_validation' => $is_approval ? 
                'Aprovado automaticamente via e-mail.' : 
                'Negado automaticamente via e-mail.'
        ];
        
        $result = $ticket_validation->update($input);
        
        if (!$result) {
            Toolbox::logInFile(
                'validation_auto', 
                sprintf(
                    'Falha ao atualizar validação ID %d do ticket %d. Status desejado: %d', 
                    $validation['id'], 
                    $ticket_id, 
                    $is_approval ? CommonITILValidation::ACCEPTED : CommonITILValidation::REFUSED
                )
            );
        }
        
        $ticket = new Ticket();
        if ($ticket->getFromDB($ticket_id)) {
            $ticket_update = [
                'id' => $ticket_id,
                'global_validation' => $is_approval ? 
                    CommonITILValidation::ACCEPTED : 
                    CommonITILValidation::REFUSED
            ];
            $ticket->update($ticket_update);
        }
    }
    
    if (!$is_approval) {
        $followup = new ITILFollowup();
        $input = [
            'items_id' => $ticket_id,
            'itemtype' => 'Ticket',
            'content' => __('Validação negada via e-mail.')
        ];
        $followup->add($input);
    }
}

function plugin_validationauto_install() {
    global $DB;
    
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

// Drops (deletes) the database table on plugin uninstall
function plugin_validationauto_uninstall() {
    global $DB;
    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_validationauto_keywords`");
    return true;
