<?php return array (
  'Botble\\Base\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Base\\Events\\SendMailEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\SendMailListener',
    ),
    'Botble\\Base\\Events\\CreatedContentEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\CreatedContentListener',
    ),
    'Botble\\Base\\Events\\UpdatedContentEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\UpdatedContentListener',
    ),
    'Botble\\Base\\Events\\DeletedContentEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\DeletedContentListener',
    ),
    'Botble\\Base\\Events\\BeforeEditContentEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\BeforeEditContentListener',
    ),
    'Botble\\Base\\Events\\AdminNotificationEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\AdminNotificationListener',
    ),
    'Botble\\Base\\Events\\UpdatedEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\ClearDashboardMenuCaches',
    ),
    'Illuminate\\Auth\\Events\\Login' => 
    array (
      0 => 'Botble\\Base\\Listeners\\ClearDashboardMenuCachesForLoggedUser',
    ),
    'Botble\\ACL\\Events\\RoleAssignmentEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\ClearDashboardMenuCaches',
    ),
    'Botble\\ACL\\Events\\RoleUpdateEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\ClearDashboardMenuCaches',
    ),
  ),
  'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider' => 
  array (
  ),
  'Botble\\Menu\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Slug\\Events\\UpdatedSlugEvent' => 
    array (
      0 => 'Botble\\Menu\\Listeners\\UpdateMenuNodeUrlListener',
    ),
    'Botble\\Base\\Events\\DeletedContentEvent' => 
    array (
      0 => 'Botble\\Menu\\Listeners\\DeleteMenuNodeListener',
    ),
  ),
  'Botble\\Page\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Theme\\Events\\RenderingSiteMapEvent' => 
    array (
      0 => 'Botble\\Page\\Listeners\\RenderingSiteMapListener',
    ),
  ),
  'Botble\\ACL\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\ACL\\Events\\RoleUpdateEvent' => 
    array (
      0 => 'Botble\\ACL\\Listeners\\RoleUpdateListener',
    ),
    'Botble\\ACL\\Events\\RoleAssignmentEvent' => 
    array (
      0 => 'Botble\\ACL\\Listeners\\RoleAssignmentListener',
    ),
    'Illuminate\\Auth\\Events\\Login' => 
    array (
      0 => 'Botble\\ACL\\Listeners\\LoginListener',
    ),
  ),
  'Botble\\AffiliatePro\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Base\\Events\\RenderingAdminWidgetEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\RegisterAffiliateWidgets',
    ),
    'Botble\\AffiliatePro\\Events\\AffiliateApplicationSubmittedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\SendAffiliateApplicationSubmittedEmailListener',
    ),
    'Botble\\AffiliatePro\\Events\\AffiliateApplicationApprovedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\SendAffiliateApplicationApprovedEmailListener',
    ),
    'Botble\\AffiliatePro\\Events\\AffiliateApplicationRejectedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\SendAffiliateApplicationRejectedEmailListener',
    ),
    'Botble\\AffiliatePro\\Events\\CommissionEarnedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\SendCommissionEarnedEmailListener',
    ),
    'Botble\\AffiliatePro\\Events\\WithdrawalRequestedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\SendWithdrawalRequestedEmailListener',
    ),
    'Botble\\AffiliatePro\\Events\\WithdrawalApprovedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\SendWithdrawalApprovedEmailListener',
    ),
    'Botble\\AffiliatePro\\Events\\WithdrawalRejectedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\SendWithdrawalRejectedEmailListener',
    ),
    'Botble\\Ecommerce\\Events\\OrderCompletedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\OrderCompletedListener',
    ),
    'Botble\\Ecommerce\\Events\\OrderCancelledEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\OrderCancelledListener',
    ),
    'Botble\\Ecommerce\\Events\\OrderPlacedEvent' => 
    array (
      0 => 'Botble\\AffiliatePro\\Listeners\\OrderPlacedListener',
    ),
  ),
  'Botble\\PosPro\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Ecommerce\\Events\\OrderCreated' => 
    array (
      0 => 'Botble\\PosPro\\Listeners\\SendOrderToLocalDeviceListener',
    ),
  ),
  'Botble\\PluginManagement\\Providers\\EventServiceProvider' => 
  array (
    'Illuminate\\Contracts\\Database\\Events\\MigrationEvent' => 
    array (
      0 => 'Botble\\PluginManagement\\Listeners\\ClearPluginCaches',
    ),
    'Botble\\Base\\Events\\SystemUpdateDBMigrated' => 
    array (
      0 => 'Botble\\PluginManagement\\Listeners\\CoreUpdatePluginsDB',
    ),
    'Botble\\Base\\Events\\SystemUpdatePublished' => 
    array (
      0 => 'Botble\\PluginManagement\\Listeners\\PublishPluginAssets',
    ),
    'Botble\\Base\\Events\\SeederPrepared' => 
    array (
      0 => 'Botble\\PluginManagement\\Listeners\\ActivateAllPlugins',
    ),
    'Botble\\PluginManagement\\Events\\ActivatedPluginEvent' => 
    array (
      0 => 'Botble\\Base\\Listeners\\ClearDashboardMenuCaches',
    ),
  ),
  'Botble\\SeoHelper\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Base\\Events\\UpdatedContentEvent' => 
    array (
      0 => 'Botble\\SeoHelper\\Listeners\\UpdatedContentListener',
    ),
    'Botble\\Base\\Events\\CreatedContentEvent' => 
    array (
      0 => 'Botble\\SeoHelper\\Listeners\\CreatedContentListener',
    ),
    'Botble\\Base\\Events\\DeletedContentEvent' => 
    array (
      0 => 'Botble\\SeoHelper\\Listeners\\DeletedContentListener',
    ),
  ),
  'Botble\\Slug\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Base\\Events\\UpdatedContentEvent' => 
    array (
      0 => 'Botble\\Slug\\Listeners\\UpdatedContentListener',
    ),
    'Botble\\Base\\Events\\CreatedContentEvent' => 
    array (
      0 => 'Botble\\Slug\\Listeners\\CreatedContentListener',
    ),
    'Botble\\Base\\Events\\DeletedContentEvent' => 
    array (
      0 => 'Botble\\Slug\\Listeners\\DeletedContentListener',
    ),
    'Botble\\Base\\Events\\SeederPrepared' => 
    array (
      0 => 'Botble\\Slug\\Listeners\\TruncateSlug',
    ),
    'Botble\\Base\\Events\\FinishedSeederEvent' => 
    array (
      0 => 'Botble\\Slug\\Listeners\\CreateMissingSlug',
    ),
  ),
  'Botble\\Theme\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Base\\Events\\SystemUpdateDBMigrated' => 
    array (
      0 => 'Botble\\Theme\\Listeners\\CoreUpdateThemeDB',
    ),
    'Botble\\Base\\Events\\SystemUpdatePublished' => 
    array (
      0 => 'Botble\\Theme\\Listeners\\PublishThemeAssets',
    ),
    'Botble\\Base\\Events\\SeederPrepared' => 
    array (
      0 => 'Botble\\Theme\\Listeners\\SetDefaultTheme',
    ),
    'Botble\\Base\\Events\\FormRendering' => 
    array (
      0 => 'Botble\\Theme\\Listeners\\AddFormJsValidation',
    ),
  ),
  'Botble\\Language\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Base\\Events\\UpdatedContentEvent' => 
    array (
      0 => 'Botble\\Language\\Listeners\\UpdatedContentListener',
    ),
    'Botble\\Base\\Events\\CreatedContentEvent' => 
    array (
      0 => 'Botble\\Language\\Listeners\\CreatedContentListener',
      1 => 'Botble\\Language\\Listeners\\CopyThemeOptions',
      2 => 'Botble\\Language\\Listeners\\CopyThemeWidgets',
    ),
    'Botble\\Base\\Events\\DeletedContentEvent' => 
    array (
      0 => 'Botble\\Language\\Listeners\\DeletedContentListener',
    ),
    'Botble\\Theme\\Events\\ThemeRemoveEvent' => 
    array (
      0 => 'Botble\\Language\\Listeners\\ThemeRemoveListener',
    ),
    'Botble\\PluginManagement\\Events\\ActivatedPluginEvent' => 
    array (
      0 => 'Botble\\Language\\Listeners\\ActivatedPluginListener',
    ),
    'Botble\\Theme\\Events\\RenderingSingleEvent' => 
    array (
      0 => 'Botble\\Language\\Listeners\\AddHrefLangListener',
    ),
    'Botble\\Installer\\Events\\InstallerFinished' => 
    array (
      0 => 'Botble\\Language\\Listeners\\CreateSelectedLanguageWhenInstallationFinished',
    ),
  ),
  'Botble\\LanguageAdvanced\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Base\\Events\\CreatedContentEvent' => 
    array (
      0 => 'Botble\\LanguageAdvanced\\Listeners\\AddDefaultTranslations',
    ),
    'Botble\\Base\\Events\\UpdatedContentEvent' => 
    array (
      0 => 'Botble\\LanguageAdvanced\\Listeners\\ClearCacheAfterUpdateData',
    ),
    'Botble\\PluginManagement\\Events\\ActivatedPluginEvent' => 
    array (
      0 => 'Botble\\LanguageAdvanced\\Listeners\\PriorityLanguageAdvancedPluginListener',
    ),
    'Botble\\Slug\\Events\\UpdatedPermalinkSettings' => 
    array (
      0 => 'Botble\\LanguageAdvanced\\Listeners\\UpdatePermalinkSettingsForEachLanguage',
    ),
    'Botble\\Theme\\Events\\RenderingAdminBar' => 
    array (
      0 => 'Botble\\LanguageAdvanced\\Listeners\\AddRefLangToAdminBar',
    ),
  ),
  'Botble\\AuditLog\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\AuditLog\\Events\\AuditHandlerEvent' => 
    array (
      0 => 'Botble\\AuditLog\\Listeners\\AuditHandlerListener',
    ),
    'Illuminate\\Auth\\Events\\Login' => 
    array (
      0 => 'Botble\\AuditLog\\Listeners\\LoginListener',
      1 => 'Botble\\AuditLog\\Listeners\\CustomerLoginListener',
    ),
    'Illuminate\\Auth\\Events\\Logout' => 
    array (
      0 => 'Botble\\AuditLog\\Listeners\\CustomerLogoutListener',
    ),
    'Illuminate\\Auth\\Events\\Registered' => 
    array (
      0 => 'Botble\\AuditLog\\Listeners\\CustomerRegistrationListener',
    ),
    'Botble\\Base\\Events\\UpdatedContentEvent' => 
    array (
      0 => 'Botble\\AuditLog\\Listeners\\UpdatedContentListener',
    ),
    'Botble\\Base\\Events\\CreatedContentEvent' => 
    array (
      0 => 'Botble\\AuditLog\\Listeners\\CreatedContentListener',
    ),
    'Botble\\Base\\Events\\DeletedContentEvent' => 
    array (
      0 => 'Botble\\AuditLog\\Listeners\\DeletedContentListener',
    ),
  ),
  'Botble\\Blog\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Theme\\Events\\RenderingSiteMapEvent' => 
    array (
      0 => 'Botble\\Blog\\Listeners\\RenderingSiteMapListener',
    ),
  ),
  'Botble\\Ecommerce\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Theme\\Events\\RenderingSiteMapEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\RenderingSiteMapListener',
    ),
    'Botble\\Base\\Events\\CreatedContentEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\AddLanguageForVariantsListener',
      1 => 'Botble\\Ecommerce\\Listeners\\ClearShippingRuleCache',
      2 => 'Botble\\Ecommerce\\Listeners\\SaveProductFaqListener',
      3 => 
      array (
        0 => 'Botble\\Ecommerce\\Listeners\\SyncProductSlug',
        1 => 'handleCreatedContent',
      ),
    ),
    'Botble\\Base\\Events\\UpdatedContentEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\AddLanguageForVariantsListener',
      1 => 'Botble\\Ecommerce\\Listeners\\ClearShippingRuleCache',
      2 => 'Botble\\Ecommerce\\Listeners\\SaveProductFaqListener',
      3 => 'Botble\\Ecommerce\\Listeners\\SendWebhookWhenOrderUpdated',
      4 => 
      array (
        0 => 'Botble\\Ecommerce\\Listeners\\SyncProductSlug',
        1 => 'handleUpdatedContent',
      ),
    ),
    'Botble\\Slug\\Events\\UpdatedSlugEvent' => 
    array (
      0 => 
      array (
        0 => 'Botble\\Ecommerce\\Listeners\\SyncProductSlug',
        1 => 'handleUpdatedSlug',
      ),
    ),
    'Botble\\Base\\Events\\DeletedContentEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\ClearShippingRuleCache',
    ),
    'Illuminate\\Auth\\Events\\Registered' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\SendMailsAfterCustomerRegistered',
    ),
    'Botble\\Ecommerce\\Events\\CustomerEmailVerified' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\SendMailsAfterCustomerEmailVerified',
    ),
    'Botble\\Ecommerce\\Events\\OrderPlacedEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\SendWebhookWhenOrderPlaced',
      1 => 'Botble\\Ecommerce\\Listeners\\GenerateInvoiceListener',
      2 => 'Botble\\Ecommerce\\Listeners\\OrderCreatedNotification',
      3 => 'Botble\\Ecommerce\\Listeners\\MarkCartAsRecovered',
    ),
    'Botble\\Ecommerce\\Events\\OrderCreated' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\GenerateInvoiceListener',
      1 => 'Botble\\Ecommerce\\Listeners\\OrderCreatedNotification',
    ),
    'Botble\\Ecommerce\\Events\\ProductQuantityUpdatedEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\UpdateProductStockStatus',
    ),
    'Botble\\Ecommerce\\Events\\OrderCompletedEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\SendDigitalProductEmailAfterOrderCompleted',
      1 => 'Botble\\Ecommerce\\Listeners\\SendProductReviewsMailAfterOrderCompleted',
      2 => 'Botble\\Ecommerce\\Listeners\\GenerateLicenseCodeAfterOrderCompleted',
      3 => 'Botble\\Ecommerce\\Listeners\\UpdateInvoiceWhenOrderCompleted',
      4 => 'Botble\\Ecommerce\\Listeners\\SendWebhookWhenOrderCompleted',
    ),
    'Botble\\Ecommerce\\Events\\ProductViewed' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\UpdateProductView',
    ),
    'Botble\\Ecommerce\\Events\\ShippingStatusChanged' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\SendShippingStatusChangedNotification',
      1 => 'Botble\\Ecommerce\\Listeners\\SendWebhookWhenShippingStatusUpdated',
    ),
    'Botble\\Base\\Events\\RenderingAdminWidgetEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\RegisterEcommerceWidget',
    ),
    'Botble\\Ecommerce\\Events\\OrderPaymentConfirmedEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\OrderPaymentConfirmedNotification',
      1 => 'Botble\\Ecommerce\\Listeners\\SendWebhookWhenPaymentStatusUpdated',
    ),
    'Botble\\Ecommerce\\Events\\OrderCancelledEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\OrderCancelledNotification',
      1 => 'Botble\\Ecommerce\\Listeners\\UpdateInvoiceAndShippingWhenOrderCancelled',
      2 => 'Botble\\Ecommerce\\Listeners\\SendWebhookWhenOrderCancelled',
    ),
    'Botble\\Ecommerce\\Events\\OrderReturnedEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\OrderReturnedNotification',
    ),
    'Botble\\Ecommerce\\Events\\ProductVariationCreated' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\UpdateProductVariationInfo',
    ),
    'Botble\\Ecommerce\\Events\\ProductFileUpdatedEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\SendProductFileUpdatedNotification',
    ),
    'Botble\\Ecommerce\\Events\\AbandonedCartReminderEvent' => 
    array (
      0 => 'Botble\\Ecommerce\\Listeners\\SendWebhookWhenCartAbandoned',
    ),
  ),
  'Botble\\Faq\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Base\\Events\\UpdatedContentEvent' => 
    array (
      0 => 'Botble\\Faq\\Listeners\\SaveFaqListener',
    ),
    'Botble\\Base\\Events\\CreatedContentEvent' => 
    array (
      0 => 'Botble\\Faq\\Listeners\\SaveFaqListener',
    ),
    'Botble\\Base\\Events\\DeletedContentEvent' => 
    array (
      0 => 'Botble\\Faq\\Listeners\\DeletedContentListener',
    ),
  ),
  'Botble\\Location\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Location\\Events\\ImportedCountryEvent' => 
    array (
      0 => 'Botble\\Location\\Listeners\\CreateCountryTranslationListener',
    ),
    'Botble\\Location\\Events\\ImportedStateEvent' => 
    array (
      0 => 'Botble\\Location\\Listeners\\CreateStateTranslationListener',
    ),
    'Botble\\Location\\Events\\ImportedCityEvent' => 
    array (
      0 => 'Botble\\Location\\Listeners\\CreateCityTranslationListener',
    ),
  ),
  'Botble\\Marketplace\\Providers\\EventServiceProvider' => 
  array (
    'Illuminate\\Auth\\Events\\Registered' => 
    array (
      0 => 'Botble\\Marketplace\\Listeners\\SaveVendorInformationListener',
      1 => 'Botble\\Marketplace\\Listeners\\SendMailAfterVendorRegistered',
    ),
    'Botble\\Theme\\Events\\RenderingSiteMapEvent' => 
    array (
      0 => 'Botble\\Marketplace\\Listeners\\RenderingSiteMapListener',
    ),
    'Botble\\Ecommerce\\Events\\OrderCreated' => 
    array (
      0 => 'Botble\\Marketplace\\Listeners\\OrderCreatedEmailNotification',
    ),
    'Botble\\Ecommerce\\Events\\OrderCancelledEvent' => 
    array (
      0 => 'Botble\\Marketplace\\Listeners\\OrderCancelledEmailNotification',
    ),
    'Botble\\Marketplace\\Events\\WithdrawalRequested' => 
    array (
      0 => 'Botble\\Marketplace\\Listeners\\WithdrawalRequestedNotification',
    ),
    'Botble\\Base\\Events\\RenderingAdminWidgetEvent' => 
    array (
      0 => 'Botble\\Marketplace\\Listeners\\RegisterMarketplaceWidget',
    ),
  ),
  'Botble\\Newsletter\\Providers\\EventServiceProvider' => 
  array (
    'Botble\\Newsletter\\Events\\SubscribeNewsletterEvent' => 
    array (
      0 => 'Botble\\Newsletter\\Listeners\\SendEmailNotificationAboutNewSubscriberListener',
      1 => 'Botble\\Newsletter\\Listeners\\AddSubscriberToMailchimpContactListListener',
      2 => 'Botble\\Newsletter\\Listeners\\AddSubscriberToSendGridContactListListener',
    ),
    'Botble\\Newsletter\\Events\\UnsubscribeNewsletterEvent' => 
    array (
      0 => 'Botble\\Newsletter\\Listeners\\RemoveSubscriberToMailchimpContactListListener',
    ),
  ),
);