@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::form
        :url="route('ecommerce.settings.picking-list-template.update')"
        method="PUT"
    >
        <input type="hidden" name="template" value="{{ $currentTemplate }}">

        <x-core-setting::section
            :title="trans('plugins/ecommerce::picking-list-template.setting')"
            :description="trans('plugins/ecommerce::picking-list-template.setting_description')"
        >
            @if(count($templatesForSelect) > 1)
                <x-core::form.select
                    name="template"
                    :label="trans('plugins/ecommerce::picking-list-template.template')"
                    :options="$templatesForSelect"
                    :value="$currentTemplate"
                    onchange="window.location.href = '{{ route('ecommerce.settings.picking-list-template') }}?template=' + this.value"
                />
            @endif

            <x-core::form-group>
                <x-core::form.label for="email_content">
                    {{ trans('plugins/ecommerce::picking-list-template.setting_content') }}
                </x-core::form.label>

                <x-core::twig-editor
                    :variables="value(Arr::get($template, 'variables', []))"
                    :functions="EmailHandler::getFunctions()"
                    :value="value(Arr::get($template, 'content'))"
                    name="content"
                    mode="html"
                >
                </x-core::twig-editor>
            </x-core::form-group>
        </x-core-setting::section>

        <x-core-setting::section.action>
            <div class="btn-list">
                <x-core::button
                    type="submit"
                    color="primary"
                    icon="ti ti-device-floppy"
                >
                    {{ trans('core/setting::setting.save_settings') }}
                </x-core::button>

                <x-core::button
                    class="btn-trigger-reset-to-default"
                    icon="ti ti-arrow-back-up"
                    data-bb-toggle="reset-default"
                >
                    {{ trans('plugins/ecommerce::picking-list-template.reset_to_default') }}
                </x-core::button>

                @if(Arr::get($template, 'preview'))
                    <x-core::button
                        target="_blank"
                        tag="a"
                        href="{{ route('ecommerce.settings.picking-list-template.preview', $currentTemplate) }}"
                        icon="ti ti-eye"
                    >
                        {{ trans('plugins/ecommerce::picking-list-template.preview') }}
                    </x-core::button>
                @endif
            </div>
        </x-core-setting::section.action>
    </x-core::form>

    <x-core::modal.action
        type="warning"
        id="reset-template-to-default-modal"
        :title="trans('plugins/ecommerce::picking-list-template.confirm_reset')"
        :submit-button-label="trans('plugins/ecommerce::picking-list-template.continue')"
        :submit-button-attrs="['id' => 'reset-template-to-default-button', 'data-target' => route('ecommerce.settings.picking-list-template.reset', $currentTemplate)]"
    >
        {!! BaseHelper::clean(trans('plugins/ecommerce::picking-list-template.confirm_message')) !!}
    </x-core::modal.action>
@endsection