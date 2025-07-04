<?php
use Glpi\Plugin\Hooks;

define('PLUGIN_VALIDATIONAUTO_VERSION', '1.0.0');

function plugin_init_validationauto() {
    global $PLUGIN_HOOKS;
    
    // Necessário para segurança
    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['validationauto'] = true;
    
    if (Session::haveRight('config', UPDATE)) {
        // Adiciona o menu de configuração
        $PLUGIN_HOOKS['config_page']['validationauto'] = 'front/config.php';
        
        // Adiciona o menu ao GLPI
        $PLUGIN_HOOKS['menu_toadd']['validationauto'] = [
            'config' => 'PluginValidationautoConfig'
        ];
    }
    
    // Registrar o hook para processar followups
    $PLUGIN_HOOKS['item_add']['validationauto'] = [
        'ITILFollowup' => 'plugin_validationauto_process_followup'
    ];
}

function plugin_version_validationauto() {
    return [
        'name'           => 'Aprovação Automatica por email',
        'version'        => PLUGIN_VALIDATIONAUTO_VERSION,
        'author'         => 'Adriano Marinho',
        'license'        => 'GLPv3+',
        'homepage'       => 'https://github.com/malakaygames',
        'requirements'   => [
            'glpi' => [
                'min' => '9.5',
                'max' => '10.1',
                'dev' => false
            ],
            'php' => [
                'min' => '7.4'
            ]
        ]
    ];
}

function plugin_validationauto_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '9.5', 'lt')) {
        echo "Este plugin requer GLPI >= 9.5";
        return false;
    }
    return true;
}

function plugin_validationauto_check_config($verbose = false) {
    if ($verbose) {
        echo 'Configuração OK';
    }
    return true;
}