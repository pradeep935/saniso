<?php

// Simple mobile menu predefined links for the menu builder
// Add predefined special links to the menu builder interface

if (request() && request()->is('admin/*')) {
    
    // Use the correct Botble admin footer hook
    add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function($html) {
        if (request()->is('admin/menus/*')) {
            $html .= '
            <style>
            .mobile-menu-shortcuts {
                background: #f8f9fa !important;
                border: 1px solid #dee2e6 !important;
                border-radius: 8px !important;
                padding: 15px !important;
                margin: 15px 0 !important;
            }
            </style>
            
            <script>
            console.log("DEBUG: Botble admin footer hook script loading");
            
            function insertMobileMenuShortcuts() {
                console.log("DEBUG: insertMobileMenuShortcuts called");
                
                var existingShortcuts = document.querySelector(".mobile-menu-shortcuts");
                if (existingShortcuts) {
                    console.log("DEBUG: Shortcuts already exist");
                    return;
                }
                
                var targetElement = document.querySelector(".row-cards") || 
                                   document.querySelector(".card-body") || 
                                   document.querySelector(".main-content") || 
                                   document.body;
                
                console.log("DEBUG: Creating shortcuts panel");
                
                var shortcutsDiv = document.createElement("div");
                shortcutsDiv.className = "mobile-menu-shortcuts";
                shortcutsDiv.innerHTML = `
                    <h4 style="margin: 0 0 10px 0; color: #495057; font-size: 14px;">📱 Mobile Menu Special Links</h4>
                    <p style="margin: 0 0 15px 0; font-size: 12px; color: #6c757d;">Copy these special links for mobile menu:</p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-family: monospace;">
                        <div style="background: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <strong>🏷️ Categories:</strong><br>
                            <code style="color: #007bff;">#categories</code>
                        </div>
                        <div style="background: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <strong>� Cart:</strong><br>
                            <code style="color: #007bff;">#cart</code>
                        </div>
                        <div style="background: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <strong>� Search:</strong><br>
                            <code style="color: #007bff;">#search</code>
                        </div>
                        <div style="background: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <strong>👤 Account:</strong><br>
                            <code style="color: #007bff;">#account</code>
                        </div>
                        <div style="background: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <strong>❤️ Wishlist:</strong><br>
                            <code style="color: #007bff;">#wishlist</code>
                        </div>
                        <div style="background: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <strong>📦 Orders:</strong><br>
                            <code style="color: #007bff;">#orders</code>
                        </div>
                        <div style="background: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <strong>📞 Contact:</strong><br>
                            <code style="color: #007bff;">#contact</code>
                        </div>
                        <div style="background: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <strong>🎯 Offers:</strong><br>
                            <code style="color: #007bff;">#offers</code>
                        </div>
                    </div>
                    <div style="margin-top: 10px; font-size: 11px; color: #6c757d; text-align: center;">
                        � Copy the link (like <code>#account</code>) and paste in the Link field above
                    </div>
                `;
                
                targetElement.insertBefore(shortcutsDiv, targetElement.firstChild);
                console.log("DEBUG: Mobile menu shortcuts panel added successfully!");
            }
            
            // Try multiple times to insert
            insertMobileMenuShortcuts();
            
            if (typeof jQuery !== "undefined") {
                jQuery(document).ready(insertMobileMenuShortcuts);
            }
            
            window.addEventListener("load", function() {
                setTimeout(insertMobileMenuShortcuts, 1000);
            });
            
            </script>';
        }
        return $html;
    }, 999);
}