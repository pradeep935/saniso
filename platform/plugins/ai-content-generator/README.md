# AI Content Generator Plugin

## Overview
The AI Content Generator plugin adds artificial intelligence-powered content generation capabilities to your Botble CMS. It safely integrates with existing product and blog forms using Botble's hook system, ensuring no errors occur when the plugin is deactivated.

## Features
- **Safe Integration**: Uses Botble's hook system - no core file modifications
- **Dashboard Settings**: Manage API keys and settings from the admin panel
- **Product Content Generation**: Auto-generate descriptions, detailed content, and features
- **Blog Content Generation**: Create engaging blog posts with various writing styles
- **Multiple Languages**: Support for English, Spanish, French, German
- **Customizable Styles**: Professional, casual, technical, luxury, storytelling
- **Rate Limiting**: Built-in protection against API abuse
- **Real-time Generation**: Progress indicators and instant content population

## Installation

1. **Copy Plugin Files**
   ```
   Copy the entire ai-content-generator folder to:
   platform/plugins/ai-content-generator/
   ```

2. **Activate Plugin**
   - Go to Admin Panel > Plugins
   - Find "AI Content Generator" and click "Activate"
   - No core files are modified - completely safe to activate/deactivate

3. **Configure Settings**
   - Go to Admin Panel > Settings > AI Content Generator
   - Add your OpenAI API key
   - Test the connection
   - Save settings

## Configuration

### 1. Dashboard Settings
Navigate to **Admin Panel > Settings > AI Content Generator**:

- **API Key**: Your OpenAI API key (required)
- **API URL**: Default is OpenAI, can be changed for other providers
- **AI Model**: Choose between GPT-3.5 Turbo, GPT-4, etc.
- **Max Tokens**: Control response length (100-4000)
- **Temperature**: Creativity level (0-2)
- **Timeout**: Request timeout in seconds
- **Feature Toggles**: Enable/disable for products and blogs
- **Rate Limiting**: Control request frequency

### 2. API Key Setup
1. Visit https://platform.openai.com/api-keys
2. Create a new API key
3. Go to Settings > AI Content Generator in your admin panel
4. Paste the API key and click "Test Connection"
5. Save settings

### 3. No Environment Variables Needed
Everything is managed through the dashboard settings - no need to modify `.env` files.

## Usage

### Product Content Generation

1. **Create/Edit Product**
   - Go to Admin Panel > Products > Create or Edit a product
   - You'll see a new "AI Content Generator" tab

2. **Generate Content**
   - Switch to the "AI Content Generator" tab
   - Choose content style and language
   - Click "Generate Product Content"
   - Content will populate in the General tab fields

3. **Review and Edit**
   - Switch back to General tab to see generated content
   - Review and edit as needed
   - Save your product

### Blog Content Generation

1. **Create/Edit Blog Post**
   - Go to Admin Panel > Blog > Posts > Create or Edit
   - You'll see a new "AI Content Generator" tab

2. **Generate Content**
   - Switch to the "AI Content Generator" tab
   - Choose writing style, language, and length
   - Click "Generate Blog Content"
   - Content will populate in the General tab fields

3. **Review and Publish**
   - Switch back to General tab to see generated content
   - Review and edit as needed
   - Publish your post

## Architecture

### Safe Plugin Design
- **No Core Modifications**: Uses Botble's hook system exclusively
- **Hook-Based Integration**: Adds tabs to forms without modifying core files
- **Conditional Loading**: Only loads when plugin is active
- **Clean Deactivation**: No errors when plugin is deactivated

### File Structure
```
platform/plugins/ai-content-generator/
├── src/
│   ├── Services/AIContentService.php (AI API integration)
│   ├── Http/Controllers/
│   │   ├── AIContentController.php (Content generation)
│   │   └── SettingsController.php (Dashboard settings)
│   ├── Http/Requests/SettingsRequest.php
│   └── Providers/AIContentGeneratorServiceProvider.php (Hook integration)
├── resources/
│   ├── views/
│   │   ├── settings.blade.php (Settings page)
│   │   ├── product-form-tab.blade.php (Product form tab)
│   │   └── blog-form-tab.blade.php (Blog form tab)
│   ├── assets/
│   │   ├── js/ai-content-generator.js (Frontend logic)
│   │   └── css/ai-content-generator.css (Styling)
│   └── lang/en/ai-content-generator.php (Translations)
├── config/
│   ├── ai-content-generator.php (Default config)
│   └── permissions.php (Permission definitions)
├── routes/web.php (Plugin routes)
└── README.md
```

## Troubleshooting

### Plugin Activation/Deactivation
- **Safe Design**: No errors when activating or deactivating
- **Clean Integration**: Uses Botble's official hook system
- **No Core Changes**: Original forms remain untouched

### Common Issues

1. **AI tab not showing**
   - Ensure plugin is activated
   - Check if you have permissions
   - Verify you're on product/blog edit pages

2. **"Service not configured"**
   - Go to Settings > AI Content Generator
   - Add your API key
   - Test connection
   - Save settings

3. **Content not generating**
   - Check API key is valid
   - Verify internet connection
   - Check error logs
   - Test connection in settings

### Debug Steps
1. Check plugin is activated: Admin > Plugins
2. Verify settings: Admin > Settings > AI Content Generator
3. Test connection: Click "Test Connection" button
4. Check logs: `storage/logs/laravel.log`

## Benefits Over Manual Integration

### ✅ **Safe Architecture**
- No core file modifications
- Uses official Botble hooks
- Clean activation/deactivation
- No breaking changes

### ✅ **Dashboard Management**
- API keys managed in admin panel
- No environment variable changes needed
- Easy configuration interface
- Test connection feature

### ✅ **Professional Integration**
- Follows Botble conventions
- Proper permission system
- Translation support
- Responsive design

## API Costs

### OpenAI Pricing (estimated)
- **GPT-3.5-turbo**: ~$0.002 per 1K tokens
- **GPT-4**: ~$0.03 per 1K tokens
- **Average product**: ~500-800 tokens ($0.001-$0.024)
- **Average blog post**: ~1000-1500 tokens ($0.002-$0.045)

### Cost Management
- Rate limiting built-in
- Token limits configurable
- Usage monitoring through OpenAI dashboard
- Choose appropriate model for budget

## Security Features

1. **Secure API Key Storage**: Stored in database, not code
2. **Permission System**: Only authorized users can access
3. **Rate Limiting**: Prevents API abuse
4. **Input Validation**: All requests validated
5. **Error Handling**: Graceful failure handling

## Support

For assistance:
1. Check this README
2. Review plugin settings
3. Test API connection
4. Check error logs
5. Contact your development team

## License
This plugin is open source and available under the MIT license.
