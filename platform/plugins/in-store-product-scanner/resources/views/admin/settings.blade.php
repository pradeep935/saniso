@extends('core::base')

@section('content')
  <div class="wrap">
    <h1>In-Store Scanner Settings</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ url('admin/instore-scanner/settings/save') }}">
      @csrf
      <table class="form-table">
        <tr><th>Enable</th><td><input type="checkbox" name="enable" value="1" {{ !empty($settings['enable']) ? 'checked' : '' }}></td></tr>
        <tr><th>Enable Camera</th><td><input type="checkbox" name="enable_camera" value="1" {{ !empty($settings['enable_camera']) ? 'checked' : '' }}></td></tr>
        <tr><th>Enable USB Scanner</th><td><input type="checkbox" name="enable_usb_scanner" value="1" {{ !empty($settings['enable_usb_scanner']) ? 'checked' : '' }}></td></tr>
        <tr><th>Barcode Field</th><td><input type="text" name="barcode_field" value="{{ $settings['barcode_field'] ?? config('scanner.barcode_field') }}"></td></tr>
        <tr><th>Custom Meta Key</th><td><input type="text" name="custom_meta_key" value="{{ $settings['custom_meta_key'] ?? '' }}"></td></tr>
        <tr><th>Show Stock</th><td><input type="checkbox" name="show_stock" value="1" {{ !empty($settings['show_stock']) ? 'checked' : '' }}></td></tr>
        <tr><th>Page Slug</th><td><input type="text" name="page_slug" value="{{ $settings['page_slug'] ?? config('scanner.page_slug') }}"></td></tr>
        <tr><th>UI Theme</th><td><select name="ui_theme"><option value="light" {{ ( ($settings['ui_theme'] ?? 'light') === 'light') ? 'selected' : '' }}>Light</option><option value="dark" {{ ( ($settings['ui_theme'] ?? '') === 'dark') ? 'selected' : '' }}>Dark</option></select></td></tr>
      </table>
      <button class="btn btn-primary" type="submit">Save</button>
    </form>
  </div>
@endsection
