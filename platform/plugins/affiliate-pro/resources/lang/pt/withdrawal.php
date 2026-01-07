<?php

return [
    'name' => 'Levantamentos',
    'view' => 'Ver Levantamento #:id',
    'affiliate' => 'Afiliado',
    'amount' => 'Montante',
    'status' => 'Estado',
    'payment_method' => 'Método de Pagamento',
    'payment_details' => 'Detalhes de Pagamento',
    'notes' => 'Notas',
    'created_at' => 'Criado Em',
    'approve' => 'Aprovar',
    'reject' => 'Rejeitar',
    'approve_success' => 'Levantamento aprovado com sucesso',
    'reject_success' => 'Levantamento rejeitado com sucesso',
    'approve_withdrawal' => 'Aprovar Levantamento',
    'reject_withdrawal' => 'Rejeitar Levantamento',
    'approve_withdrawal_confirmation' => 'Tem certeza de que deseja aprovar o levantamento #:id? Esta acção não pode ser desfeita.',
    'reject_withdrawal_confirmation' => 'Tem certeza de que deseja rejeitar o levantamento #:id? Esta acção não pode ser desfeita.',
    'statuses' => [
        'pending' => 'Pendente',
        'processing' => 'Em Processamento',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado',
        'canceled' => 'Cancelado',
    ],

    // Customer-facing translations
    'request' => 'Pedido de Levantamento',
    'history' => 'Histórico de Levantamentos',
    'no_withdrawals' => 'Nenhum levantamento encontrado.',
    'withdrawal_id' => 'ID do Levantamento',
    'date' => 'Data',
    'amount_required' => 'O montante do levantamento é obrigatório.',
    'amount_numeric' => 'O montante do levantamento deve ser um número.',
    'amount_min' => 'O montante do levantamento deve ser pelo menos 0.',
    'payment_method_required' => 'O método de pagamento é obrigatório.',
    'payment_details_required' => 'Os detalhes de pagamento são obrigatórios.',
    'account_not_approved' => 'A sua conta de afiliado ainda não foi aprovada.',
    'minimum_amount' => 'O montante mínimo de levantamento é :amount.',
    'insufficient_balance' => 'Não tem saldo suficiente para este levantamento.',
    'request_submitted' => 'O seu pedido de levantamento foi submetido com sucesso.',
    'submit_request' => 'Submeter Pedido de Levantamento',
    'available_balance' => 'Saldo Disponível',
    'enter_amount' => 'Introduzir Montante',
    'select_payment_method' => 'Seleccionar Método de Pagamento',
    'payment_details_placeholder' => 'Introduza os seus detalhes de pagamento (ex: email do PayPal, detalhes da conta bancária)',

    // Additional translations for PayoutPaymentMethodsEnum
    'bank_transfer' => 'Transferência Bancária',
    'paypal' => 'PayPal',
    'stripe' => 'Stripe',
    'bank_information' => 'Informação bancária',
    'paypal_id' => 'ID do PayPal',

    // Payout payment methods
    'payout_payment_methods' => [
        'bank_transfer' => 'Transferência Bancária',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
    ],
];