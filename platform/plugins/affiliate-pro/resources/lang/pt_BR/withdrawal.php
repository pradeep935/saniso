<?php

return [
    'name' => 'Saques',
    'view' => 'Ver Saque #:id',
    'affiliate' => 'Afiliado',
    'amount' => 'Valor',
    'status' => 'Status',
    'payment_method' => 'Método de Pagamento',
    'payment_details' => 'Detalhes de Pagamento',
    'notes' => 'Notas',
    'created_at' => 'Criado Em',
    'approve' => 'Aprovar',
    'reject' => 'Rejeitar',
    'approve_success' => 'Saque aprovado com sucesso',
    'reject_success' => 'Saque rejeitado com sucesso',
    'approve_withdrawal' => 'Aprovar Saque',
    'reject_withdrawal' => 'Rejeitar Saque',
    'approve_withdrawal_confirmation' => 'Tem certeza de que deseja aprovar o saque #:id? Esta ação não pode ser desfeita.',
    'reject_withdrawal_confirmation' => 'Tem certeza de que deseja rejeitar o saque #:id? Esta ação não pode ser desfeita.',
    'statuses' => [
        'pending' => 'Pendente',
        'processing' => 'Em Processamento',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado',
        'canceled' => 'Cancelado',
    ],

    // Customer-facing translations
    'request' => 'Pedido de Saque',
    'history' => 'Histórico de Saques',
    'no_withdrawals' => 'Nenhum saque encontrado.',
    'withdrawal_id' => 'ID do Saque',
    'date' => 'Data',
    'amount_required' => 'O valor do saque é obrigatório.',
    'amount_numeric' => 'O valor do saque deve ser um número.',
    'amount_min' => 'O valor do saque deve ser pelo menos 0.',
    'payment_method_required' => 'O método de pagamento é obrigatório.',
    'payment_details_required' => 'Os detalhes de pagamento são obrigatórios.',
    'account_not_approved' => 'Sua conta de afiliado ainda não foi aprovada.',
    'minimum_amount' => 'O valor mínimo de saque é :amount.',
    'insufficient_balance' => 'Você não tem saldo suficiente para este saque.',
    'request_submitted' => 'Seu pedido de saque foi enviado com sucesso.',
    'submit_request' => 'Enviar Pedido de Saque',
    'available_balance' => 'Saldo Disponível',
    'enter_amount' => 'Inserir Valor',
    'select_payment_method' => 'Selecionar Método de Pagamento',
    'payment_details_placeholder' => 'Insira seus detalhes de pagamento (ex: email do PayPal, detalhes da conta bancária)',

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