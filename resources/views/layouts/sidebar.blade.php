<aside class="main-sidebar sidebar-light-teal elevation-4">
    <a href="{{ route('contact') }}" class="brand-link">
        <span class="brand-text font-weight-heavy logo-name">{{ env('APP_NAME') }}</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @include('layouts.menu')
            </ul>
        </nav>
    </div>

</aside>
