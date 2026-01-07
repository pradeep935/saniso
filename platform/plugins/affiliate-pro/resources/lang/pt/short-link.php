<?php

return [
    'name' => 'Links Curtos',
    'short_link' => 'Link Curto',
    'short_links' => 'Links Curtos',
    'create' => 'Criar Link Curto',
    'edit' => 'Editar Link Curto',
    'delete' => 'Eliminar Link Curto',
    'short_link_details' => 'Detalhes do Link Curto',
    'untitled' => 'Link Sem Título',

    // Form fields
    'affiliate' => 'Afiliado',
    'title' => 'Título',
    'title_placeholder' => 'Introduza um título descritivo para este link curto',
    'short_code' => 'Código Curto',
    'short_code_placeholder' => 'Introduza código curto único (ex: verao2024)',
    'short_code_help' => 'Isto será usado no URL: seusite.com/go/[codigo-curto]',
    'destination_url' => 'URL de Destino',
    'destination_url_help' => 'O URL completo para onde os utilizadores serão redireccionados',
    'product' => 'Produto',
    'product_help' => 'Opcional: Associe este link curto a um produto específico para melhor rastreamento',
    'all_products' => 'Todos os Produtos',
    'short_url' => 'URL Curto',
    'clicks' => 'Cliques',
    'conversions' => 'Conversões',
    'conversion_rate' => 'Taxa de Conversão',
    'total_clicks' => 'Total de Cliques',
    'total_conversions' => 'Total de Conversões',
    'statistics' => 'Estatísticas',
    'actions' => 'Acções',
    'created_at' => 'Criado Em',
    'updated_at' => 'Actualizado Em',

    // Validation messages
    'affiliate_required' => 'Por favor seleccione um afiliado.',
    'affiliate_not_exists' => 'O afiliado seleccionado não existe.',
    'short_code_required' => 'O código curto é obrigatório.',
    'short_code_unique' => 'Este código curto já está em uso.',
    'short_code_alpha_dash' => 'O código curto só pode conter letras, números, traços e underscores.',
    'destination_url_required' => 'O URL de destino é obrigatório.',
    'destination_url_invalid' => 'Por favor introduza um URL válido.',
    'product_not_exists' => 'O produto seleccionado não existe.',

    // Actions
    'copy_url' => 'Copiar URL',
    'copy_short_url' => 'Copiar URL Curto',
    'test_link' => 'Testar Link',
    'visit_destination' => 'Visitar Destino',
    'copied_to_clipboard' => 'URL copiado para a área de transferência!',
    'copy_failed' => 'Falha ao copiar URL. Por favor, tente novamente.',

    // UI sections
    'url_information' => 'Informação do URL',
    'affiliate_product_info' => 'Informação de Afiliado e Produto',
    'quick_actions' => 'Acções Rápidas',
    'performance' => 'Desempenho',
    'product_link' => 'Link de Produto',
    'general_link' => 'Link Geral',

    // Performance indicators
    'excellent' => 'Excelente',
    'good' => 'Bom',
    'average' => 'Médio',
    'no_data' => 'Sem Dados',

    'back' => 'Voltar',

    // Status messages
    'affiliate_not_found' => 'Afiliado não encontrado',
    'short_link_not_found' => 'Link curto não encontrado',

    // Table headers
    'affiliate_column' => 'Afiliado',
    'title_column' => 'Título e Código',
    'destination_column' => 'Destino',
    'short_url_column' => 'URL Curto',
    'product_column' => 'Produto',
    'stats_column' => 'Estatísticas',
    'created_column' => 'Criado',

    // Bulk actions
    'bulk_delete_confirm' => 'Tem certeza de que deseja eliminar estes links curtos?',
    'bulk_delete_success' => 'Links curtos seleccionados foram eliminados com sucesso.',

    // Permissions
    'permissions' => [
        'index' => 'Ver links curtos',
        'create' => 'Criar links curtos',
        'edit' => 'Editar links curtos',
        'destroy' => 'Eliminar links curtos',
    ],
];