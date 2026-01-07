@php
    $tabs = [
        'general' => [
            'title' => __('General Settings'),
            'icon' => 'fas fa-cog',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => __('Title'),
                    'placeholder' => __('Enter block title'),
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => __('Description'),
                    'placeholder' => __('Enter block description'),
                    'rows' => 3,
                ],
            ]
        ],
        'layout' => [
            'title' => __('Layout Settings'),
            'icon' => 'fas fa-th',
            'fields' => [
                'layout_type' => [
                    'type' => 'select',
                    'label' => __('Layout Type'),
                    'choices' => [
                        'slider' => __('Slider'),
                        'grid' => __('Grid'),
                    ],
                    'default' => 'slider',
                ],
                'columns_desktop' => [
                    'type' => 'select',
                    'label' => __('Columns (Desktop)'),
                    'choices' => array_combine(range(1, 6), range(1, 6)),
                    'default' => 3,
                ],
                'columns_tablet' => [
                    'type' => 'select',
                    'label' => __('Columns (Tablet)'),
                    'choices' => array_combine(range(1, 4), range(1, 4)),
                    'default' => 2,
                ],
                'columns_mobile' => [
                    'type' => 'select',
                    'label' => __('Columns (Mobile)'),
                    'choices' => [1 => 1, 2 => 2],
                    'default' => 1,
                ],
            ]
        ],
        'styling' => [
            'title' => __('Styling'),
            'icon' => 'fas fa-palette',
            'fields' => [
                'background_color' => [
                    'type' => 'color',
                    'label' => __('Background Color'),
                    'default' => '#ffffff',
                ],
                'text_color' => [
                    'type' => 'color',
                    'label' => __('Text Color'),
                    'default' => '#333333',
                ],
                'text_align' => [
                    'type' => 'select',
                    'label' => __('Text Alignment'),
                    'choices' => [
                        'left' => __('Left'),
                        'center' => __('Center'),
                        'right' => __('Right'),
                    ],
                    'default' => 'center',
                ],
                'title_font_size' => [
                    'type' => 'select',
                    'label' => __('Title Font Size'),
                    'choices' => [
                        '14px' => '14px', '16px' => '16px', '18px' => '18px',
                        '20px' => '20px', '24px' => '24px', '28px' => '28px',
                        '32px' => '32px', '36px' => '36px',
                    ],
                    'default' => '24px',
                ],
                'description_font_size' => [
                    'type' => 'select',
                    'label' => __('Description Font Size'),
                    'choices' => [
                        '12px' => '12px', '14px' => '14px', '16px' => '16px',
                        '18px' => '18px', '20px' => '20px',
                    ],
                    'default' => '16px',
                ],
            ]
        ],
        'slider' => [
            'title' => __('Slider Settings'),
            'icon' => 'fas fa-sliders-h',
            'fields' => [
                'is_autoplay' => [
                    'type' => 'select',
                    'label' => __('Autoplay'),
                    'choices' => [
                        'no' => __('No'),
                        'yes' => __('Yes'),
                    ],
                    'default' => 'no',
                ],
                'autoplay_speed' => [
                    'type' => 'select',
                    'label' => __('Autoplay Speed (ms)'),
                    'choices' => [
                        '2000' => '2000', '3000' => '3000', '4000' => '4000',
                        '5000' => '5000', '6000' => '6000',
                    ],
                    'default' => '3000',
                ],
                'show_arrows' => [
                    'type' => 'select',
                    'label' => __('Show Navigation Arrows'),
                    'choices' => [
                        'yes' => __('Yes'),
                        'no' => __('No'),
                    ],
                    'default' => 'yes',
                ],
                'show_dots' => [
                    'type' => 'select',
                    'label' => __('Show Pagination Dots'),
                    'choices' => [
                        'yes' => __('Yes'),
                        'no' => __('No'),
                    ],
                    'default' => 'yes',
                ],
            ]
        ],
        'videos' => [
            'title' => __('Videos'),
            'icon' => 'fas fa-video',
            'fields' => [], // Will be populated with video fields
        ]
    ];

    // Add video fields to the videos tab
    for ($i = 1; $i <= 20; $i++) {
        $tabs['videos']['fields']["video_group_$i"] = [
            'type' => 'group',
            'label' => __("Video $i"),
            'fields' => [
                "video_title_$i" => [
                    'type' => 'text',
                    'label' => __('Title'),
                    'placeholder' => __("Enter video $i title"),
                ],
                "video_description_$i" => [
                    'type' => 'textarea',
                    'label' => __('Description'),
                    'placeholder' => __("Enter video $i description"),
                    'rows' => 2,
                ],
                "video_type_$i" => [
                    'type' => 'select',
                    'label' => __('Type'),
                    'choices' => [
                        'upload' => __('Upload'),
                        'youtube' => __('YouTube'),
                        'vimeo' => __('Vimeo'),
                        'external' => __('External Link'),
                    ],
                    'default' => 'upload',
                ],
                "video_source_$i" => [
                    'type' => 'text',
                    'label' => __('Source/URL'),
                    'placeholder' => __('Enter video URL or upload path'),
                ],
                "video_thumbnail_$i" => [
                    'type' => 'mediaImage',
                    'label' => __('Thumbnail'),
                ],
            ]
        ];
    }
@endphp

<div class="advanced-video-block-admin">
    <style>
        .advanced-video-block-admin {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .video-admin-tabs {
            display: flex;
            border-bottom: 2px solid #e1e5e9;
            margin-bottom: 20px;
        }
        
        .video-admin-tab {
            padding: 12px 20px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            margin-right: 2px;
        }
        
        .video-admin-tab:hover {
            background: #e9ecef;
        }
        
        .video-admin-tab.active {
            background: #007bff;
            color: white;
        }
        
        .video-admin-tab i {
            font-size: 14px;
        }
        
        .video-admin-content {
            display: none;
        }
        
        .video-admin-content.active {
            display: block;
        }
        
        .video-field-group {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .video-field-group h4 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 16px;
            font-weight: 600;
        }
        
        .video-field-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .video-field {
            flex: 1;
            min-width: 200px;
        }
        
        .video-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #495057;
        }
        
        .video-field input,
        .video-field select,
        .video-field textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }
        
        .video-field input:focus,
        .video-field select:focus,
        .video-field textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .video-preview {
            width: 100%;
            max-width: 200px;
            height: 120px;
            background: #e9ecef;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            margin-top: 10px;
        }
        
        .video-help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .collapsible-group {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .collapsible-header {
            background: #f8f9fa;
            padding: 12px 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        
        .collapsible-header:hover {
            background: #e9ecef;
        }
        
        .collapsible-content {
            padding: 15px;
            display: none;
        }
        
        .collapsible-content.active {
            display: block;
        }
        
        .toggle-icon {
            transition: transform 0.3s ease;
        }
        
        .toggle-icon.rotated {
            transform: rotate(180deg);
        }
    </style>

    <div class="video-admin-tabs">
        @foreach($tabs as $tabKey => $tab)
            <button type="button" class="video-admin-tab {{ $loop->first ? 'active' : '' }}" data-tab="{{ $tabKey }}">
                <i class="{{ $tab['icon'] }}"></i>
                {{ $tab['title'] }}
            </button>
        @endforeach
    </div>

    @foreach($tabs as $tabKey => $tab)
        <div class="video-admin-content {{ $loop->first ? 'active' : '' }}" data-content="{{ $tabKey }}">
            @if($tabKey === 'videos')
                @for($i = 1; $i <= 20; $i++)
                    <div class="collapsible-group">
                        <div class="collapsible-header" onclick="toggleVideoGroup({{ $i }})">
                            <span><i class="fas fa-video"></i> {{ __("Video $i") }}</span>
                            <i class="fas fa-chevron-down toggle-icon" id="toggle-icon-{{ $i }}"></i>
                        </div>
                        <div class="collapsible-content" id="video-group-{{ $i }}">
                            <div class="video-field-row">
                                <div class="video-field">
                                    <label for="video_title_{{ $i }}">{{ __('Title') }}</label>
                                    <input type="text" 
                                           id="video_title_{{ $i }}" 
                                           name="video_title_{{ $i }}" 
                                           value="{{ old("video_title_$i", $attributes["video_title_$i"] ?? '') }}"
                                           placeholder="{{ __("Enter video $i title") }}">
                                </div>
                                <div class="video-field">
                                    <label for="video_type_{{ $i }}">{{ __('Type') }}</label>
                                    <select id="video_type_{{ $i }}" 
                                            name="video_type_{{ $i }}" 
                                            onchange="updateVideoTypeHelp({{ $i }})">
                                        <option value="upload" {{ ($attributes["video_type_$i"] ?? 'upload') === 'upload' ? 'selected' : '' }}>{{ __('Upload') }}</option>
                                        <option value="youtube" {{ ($attributes["video_type_$i"] ?? '') === 'youtube' ? 'selected' : '' }}>{{ __('YouTube') }}</option>
                                        <option value="vimeo" {{ ($attributes["video_type_$i"] ?? '') === 'vimeo' ? 'selected' : '' }}>{{ __('Vimeo') }}</option>
                                        <option value="external" {{ ($attributes["video_type_$i"] ?? '') === 'external' ? 'selected' : '' }}>{{ __('External Link') }}</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="video-field-row">
                                <div class="video-field">
                                    <label for="video_source_{{ $i }}">{{ __('Source/URL') }}</label>
                                    <input type="text" 
                                           id="video_source_{{ $i }}" 
                                           name="video_source_{{ $i }}" 
                                           value="{{ old("video_source_$i", $attributes["video_source_$i"] ?? '') }}"
                                           placeholder="{{ __('Enter video URL or upload path') }}">
                                    <div class="video-help-text" id="video_help_{{ $i }}">
                                        {{ __('Enter the full path to your uploaded video file') }}
                                    </div>
                                </div>
                                <div class="video-field">
                                    <label for="video_thumbnail_{{ $i }}">{{ __('Thumbnail') }}</label>
                                    <input type="text" 
                                           id="video_thumbnail_{{ $i }}" 
                                           name="video_thumbnail_{{ $i }}" 
                                           value="{{ old("video_thumbnail_$i", $attributes["video_thumbnail_$i"] ?? '') }}"
                                           placeholder="{{ __('Thumbnail URL (optional)') }}">
                                    <div class="video-help-text">
                                        {{ __('Optional custom thumbnail. YouTube thumbnails are auto-generated.') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="video-field">
                                <label for="video_description_{{ $i }}">{{ __('Description') }}</label>
                                <textarea id="video_description_{{ $i }}" 
                                          name="video_description_{{ $i }}" 
                                          rows="2" 
                                          placeholder="{{ __("Enter video $i description") }}">{{ old("video_description_$i", $attributes["video_description_$i"] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                @endfor
            @else
                @foreach($tab['fields'] as $fieldKey => $field)
                    <div class="video-field" style="margin-bottom: 20px;">
                        <label for="{{ $fieldKey }}">{{ $field['label'] }}</label>
                        
                        @if($field['type'] === 'text')
                            <input type="text" 
                                   id="{{ $fieldKey }}" 
                                   name="{{ $fieldKey }}" 
                                   value="{{ old($fieldKey, $attributes[$fieldKey] ?? $field['default'] ?? '') }}"
                                   placeholder="{{ $field['placeholder'] ?? '' }}">
                        @elseif($field['type'] === 'textarea')
                            <textarea id="{{ $fieldKey }}" 
                                      name="{{ $fieldKey }}" 
                                      rows="{{ $field['rows'] ?? 3 }}" 
                                      placeholder="{{ $field['placeholder'] ?? '' }}">{{ old($fieldKey, $attributes[$fieldKey] ?? $field['default'] ?? '') }}</textarea>
                        @elseif($field['type'] === 'select')
                            <select id="{{ $fieldKey }}" name="{{ $fieldKey }}">
                                @foreach($field['choices'] as $value => $label)
                                    <option value="{{ $value }}" {{ ($attributes[$fieldKey] ?? $field['default'] ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'color')
                            <input type="color" 
                                   id="{{ $fieldKey }}" 
                                   name="{{ $fieldKey }}" 
                                   value="{{ old($fieldKey, $attributes[$fieldKey] ?? $field['default'] ?? '#ffffff') }}">
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</div>

<script>
// Tab switching functionality
document.querySelectorAll('.video-admin-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const targetTab = this.dataset.tab;
        
        // Remove active class from all tabs and contents
        document.querySelectorAll('.video-admin-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.video-admin-content').forEach(c => c.classList.remove('active'));
        
        // Add active class to clicked tab and corresponding content
        this.classList.add('active');
        document.querySelector(`[data-content="${targetTab}"]`).classList.add('active');
    });
});

// Video group toggle functionality
function toggleVideoGroup(index) {
    const content = document.getElementById(`video-group-${index}`);
    const icon = document.getElementById(`toggle-icon-${index}`);
    
    content.classList.toggle('active');
    icon.classList.toggle('rotated');
}

// Update help text based on video type
function updateVideoTypeHelp(index) {
    const typeSelect = document.getElementById(`video_type_${index}`);
    const helpText = document.getElementById(`video_help_${index}`);
    const type = typeSelect.value;
    
    const helpTexts = {
        'upload': '{{ __("Enter the full path to your uploaded video file") }}',
        'youtube': '{{ __("Enter YouTube video URL (e.g., https://www.youtube.com/watch?v=VIDEO_ID)") }}',
        'vimeo': '{{ __("Enter Vimeo video URL (e.g., https://vimeo.com/VIDEO_ID)") }}',
        'external': '{{ __("Enter external video URL or embed code") }}'
    };
    
    helpText.textContent = helpTexts[type] || helpTexts.upload;
}

// Initialize help texts
document.addEventListener('DOMContentLoaded', function() {
    for (let i = 1; i <= 20; i++) {
        updateVideoTypeHelp(i);
    }
});
</script>
