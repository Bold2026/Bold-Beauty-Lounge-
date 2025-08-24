@php
    $modules = getshowModuleList();
@endphp
<ul class="nav nav-pills nav-fill cust-nav information-tab mb-4" id="pills-tab" role="tablist">
    @foreach ($modules as $module)
        <li class="nav-item">
            <a class="nav-link text-capitalize {{ ( $slug == ($module)) ? ' active' : '' }} " href="{{ route('marketplace.index', ($module)) }}">{{ Module_Alias_Name($module) }}</a>
        </li>
    @endforeach
</ul>
